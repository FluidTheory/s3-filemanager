<?php

namespace Fluidtheory\Filemanager\Http\Controllers;

use App\Http\Controllers\Controller;
use Fluidtheory\Filemanager\Models\Asset;
use Fluidtheory\Filemanager\Models\Directory;
use Illuminate\Http\Request;
use Auth;
use File;
use Illuminate\Support\Facades\Storage;
use DB;

class FileManagerController extends Controller
{
    /**
     * Index page of the file manager.
     *
     * @return mixed
     */
    public function index()
    {
        $message = '';
        $currentDir = null;
        $breadcrumbs = array();
        $isRoot = false;
        $path = $_GET;
        $files = [];
        $last_id = 0;
        $multiple = 'false';
        $folderId = null;
        $image_ids = array();
        $pathExp = explode("/", $path['path']);
        $clientId = $pathExp[0];

        if (empty($pathExp[1])) {
            $isRoot = true;
            $client_id = $pathExp[0];
            $directories = Directory::select('id','name','client_id')->where(['client_id' => $client_id, 'parent_id' => 0, 'deleted_at' => null])->get();
            $countImage = 20 - count($directories);
            $images = Asset::select('id','name','size','type','alt','title','description','updated_at')->where(['client_id' => $client_id, 'directory_id' => null, 'deleted_at' => null])->orderBy('id', 'DESC')->limit($countImage)->get();
        } else {
            $currentDir = end($pathExp);
            $client_id = $pathExp[0];
            $folderId = end($pathExp);
            $directories = Directory::select('id','name','client_id')->where(['client_id' => $pathExp[0], 'parent_id' => end($pathExp), 'deleted_at' => null])->get();
            $countImage = 20 - count($directories);
            $images = Asset::select('id','name','size','type','alt','title','description','updated_at')->where(['client_id' => $pathExp[0], 'directory_id' => end($pathExp)])->where('deleted_at', null)->orderBy('id', 'DESC')->limit($countImage)->get();
            $slug_url = $client_id;
            foreach ($pathExp as $key => $value) {
                if ($client_id != array_shift($pathExp)) {
                    $lastFolder = Directory::select('id','name','client_id')->where(['client_id' => $client_id, 'id' => $value])->first();
                    if (!empty($lastFolder->parent_id)) {
                        $slug_url = $slug_url . '/' . $lastFolder->id;
                        $breadcrumbs[] = array('name' => $lastFolder->name, 'slug' => $slug_url);
                    } else {
                        $slug_url = $slug_url . '/' . $lastFolder->id;
                        $breadcrumbs[] = array('name' => $lastFolder->name, 'slug' => $slug_url);
                    }
                }
            }
        }
        foreach ($images as $key => $value) {
            if ($key == 0) {
                $last_id = $value['id'];
            }
            $dt = $value['updated_at'];
            $name = $value['name'];

            $imgSize = $value['size'];
            $files[] = [
                'id' => $value['id'],
                'type' => $value['type'],
                'name' => $name,
                'modified' => $dt,
                'alt' => $value['alt'],
                'title' => $value['title'],
                'desc' => $value['description'],
                'size' => $imgSize,
                'copySrc' => env('AWS_URL') . $value['id'] . '/' . $name,
                'src' => env('AWS_URL') .'tr:n-media_thumb/'. $value['id'] . '/' . $name
            ];
        }

        $modified = array();
        foreach ($files as $key => $value) {
            $modified[$key] = $value['modified'];
        }

        // get All folders
        $allDir = $this->getAllFolders($clientId);

        $final = [
            'files' => $files,
            'directories' => $directories
        ];

        if (isset($path['ids'])) {
            $image_ids = explode(',', $path['ids']);
        }
        if (isset($path['multiple'])) {
            $multiple = $path['multiple'];
        }
        if (!empty($path['message'])) {
            $message = $path['message'];
        }
        return view('filemanager::file-manager.index')->with(array('message' => $message, 'multiple' => $multiple, 'image_ids' => $image_ids, 'final' => $final, 'isRoot' => $isRoot, 'allDir' => $allDir, 'currentDir' => $currentDir, 'path' => $path['path'], 'folder_path' => $path['path'], 'folderId' => $folderId, 'client_id' => $client_id, 'breadcrumbs' => $breadcrumbs));
    }

    public function getAllFolders($clientId)
    {
        $dirList = array();
        if(!empty($clientId)){
            $sql = 'Select t1.id, t1.name,t1.parent_id, t2.name AS parent_name From directories t1 LEFT JOIN directories t2 ON t1.parent_id = t2.id where t1.client_id = '.$clientId.' and t1.deleted_at is null and t2.deleted_at is null';
            $allFolders = DB::select(DB::raw($sql));
            $allFolders = array_map(function ($value) {
                return (array)$value;
            }, $allFolders);

            $array_column = array_column($allFolders, 'parent_id');
            $allParentDir = array_keys($array_column, "0");  //get all base folders

            $i = 0;
            foreach ($allParentDir as $value) {
                $dir = $allFolders[$value];
                $dirList[$i]['id'] = $dir['id'];
                $dirList[$i]['name'] = $dir['name'];
                $dirList[$i]['parent'] = $dir['parent_name'];
                $i++;
                $parent_id = $dir['id'];
                $nodeNumber = 1;
                $lastElem = FALSE;//is this last element of the folder
                $nodeArray = [];//save all the nodes of the folder
                while ( $parent_id != $dir['id'] || $lastElem == FALSE) {
                    /*************************************************************/
                    //get details of a specific folder
                    $last = 1;
                    $sub_dir_ids = array_keys($array_column, $parent_id);
                    if($sub_dir_ids)
                    {
                        $sub_dir = isset($sub_dir_ids[$nodeNumber-1])? $sub_dir_ids[$nodeNumber-1] : null;
                        if($sub_dir){
                            $res = $allFolders[$sub_dir];
                            $dirList[$i]['id'] = $res['id'];
                            $dirList[$i]['name'] = $res['name'];
                            $dirList[$i]['parent'] = $res['parent_name'];
                            $i++;
                            if($res){$last = 0; }
                        }
                    }
                    /*************************************************************/
                    if($last == 1){
                        if($parent_id != $dir['id']){//if $parent_id didn't reach the first element (starting point)
                            $lastElement = array_pop($nodeArray);
                            $parent_id = $lastElement[0];
                            $nodeNumber = $lastElement[1] + 1;
                        }else{$lastElem = TRUE;}
                    }else{
                        $lastElem = FALSE;
                        array_push($nodeArray, array($parent_id,$nodeNumber));
                        $parent_id = $res['id'];//$parent_id for inner folder
                        $nodeNumber = 1;//go to next step - inner folder
                    }
                }
            }
        }
        return $dirList;
    }

    /**
     * Fetching the files/images from the DB.
     *
     * @param Request $request
     * @return array
     */
    public function fetchImages(Request $request)
    {
        $data = $request->all();
        $files = [];
        $image_ids = array();
        $images = Asset::where('client_id', $data['id'])->where('deleted_at', null)->orderBy('id', 'DESC')->offset($data['start'])->limit($data['limit'])->get();

        foreach ($images as $key => $value) {
            $dt = $value['updated_at'];
            $name = $value['name'];

            $imgSize = $value['size'];
            $files[] = [
                'id' => $value['id'],
                'type' => $value['type'],
                'name' => $name,
                'alt' => $value['alt'],
                'title' => $value['title'],
                'desc' => $value['description'],
                'modified' => $dt,
                'size' => $imgSize,
                'copySrc' => env('AWS_URL') . $value['id'] . '/' . $name,
                'src' => env('AWS_URL') .'tr:n-media_thumb/'. $value['id'] . '/' . $name
            ];
        }
        return $files;
    }

    /**
     * File upload functionality into S3.
     *
     * @param Request $request
     * @return mixed
     */
    public function upload(Request $request)
    {
        $data = $request->all();
        $width = 0;
        $height = 0;
        $multiSelect = $data['multi-select'];

        $image_array = array();
        if ($request->hasFile('file')) {
            $files = $request->file('file');

            foreach ($files as $file) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file);
                $name = $file->getClientOriginalName();
                $name = str_replace(" ", "-", $name);

                $arr = explode('.', $name);
                $ext = end($arr);

                if ($ext == 'mp4') {
                    $type = 'video';
                } elseif ($ext == 'pdf') {
                    $type = 'pdf';
                } else {
                    $param = getimagesize($file);
                    $width = $param[0];
                    $height = $param[1];
                    $type = 'image';
                }

                $size = floor(filesize($file) / 1024);

                if ($size > 10240) {
                    $error = 'Please upload file less than 10MB .';
                    return redirect()->secure('filemanager?path=' . $data["folder_path"] . '&message=' . $error)->with('message', 'test message');
                }

                //directories
                if (!empty($data['folder_path'])) {
                    $directoryIds = explode("/", $data['folder_path']);
                    if (!empty($directoryIds[1])) {
                        $directoryId = end($directoryIds);
                    } else {
                        $directoryId = null;
                    }
                } else {
                    $directoryIds = array(0);
                    $directoryId = null;
                }
                $image = Asset::insertGetId(array(
                    'name' => $name,
                    'client_id' => array_shift($directoryIds),
                    'width' => $width,
                    'height' => $height,
                    'size' => $size,
                    'type' => $type,
                    'mime_type' => $mimeType,
                    'directory_id' => $directoryId,
                    'created_at' => gmdate("Y-m-d H:i:s")
                ));

                $dir = $image;
                $result = Storage::disk('s3')->makeDirectory($dir);

                $filePath = $image . '/' . $name;
                $results = Storage::disk('s3')->put($filePath, file_get_contents($file));

                $image_array[] = $image;
            }
            $ids = implode(',', $image_array);
            if ($results) {
                return redirect()->secure('/filemanager?path=' . $data['folder_path'] . '&ids=' . $ids . '&multiple=' . $multiSelect)->with('flash', 'success')->with('message', 'Image uploaded successfully');
            } else {
                return redirect()->back()->with('flash', 'danger')->with('error', 'Image not uploaded.');
            }
        }
    }

    /**
     * Add folders.
     *
     * @param Request $request
     * @return mixed
     */
    public function addfolder(Request $request)
    {
        $data = $request->all();
        $path = explode("/", $data['path']);
        if (!empty($path[1])) {
            $clientId = $path[0];
            $parentId = end($path);
        } else {
            $clientId = $path[0];
            $parentId = 0;
        }
        //insert data.
        $insert = array(
            'client_id' => $clientId,
            'parent_id' => $parentId,
            'name' => $data['folder_name'],
            'created_at' => gmdate("Y-m-d H:i:s")
        );
        //create new folder into table.
        $result = Directory::create($insert);
        if ($result) {
            return redirect()->back()->with('flash', 'success')->with('message', 'Folder Created successfully');
        } else {
            return redirect()->back()->with('flash', 'danger')->with('error', 'Folder not Created.');
        }
    }

    /**
     * Delete files only.
     *
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $data = $request->all();
        $del_file = Asset::where('id', '=', $data['id'])->update(array('deleted_at' => gmdate("Y-m-d H:i:s")));

        if ($del_file) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * Delete folders and files.
     *
     * @param Request $request
     * @return array
     */
    public function deleteFolder(Request $request)
    {
        $data = $request->all();
        $response = array();
        $parentDirectory = Directory::where('id', $data['id'])->update(array('deleted_at' => gmdate("Y-m-d H:i:s")));
        $childDirectory = Directory::where('parent_id', $data['id'])->update(array('deleted_at' => gmdate("Y-m-d H:i:s")));
        $asset = Asset::where('directory_id', $data['id'])->update(array('deleted_at' => gmdate("Y-m-d H:i:s")));
        if ($parentDirectory || $childDirectory || $asset) {
            $response['status'] = 'true';
            return $response;
        } else {
            $response['status'] = 'false';
            return $response;
        }
    }

    /**
     * Get Alt, Title and description of Asset in File manager
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function updateAssetData(Request $request)
    {
        $data = $request->all();
        $response = array();
        if (!empty($data['assetId'])) {
            $update = array(
                'alt' => $data['alt'],
                'title' => $data['title'],
                'description' => $data['desc'],
            );
            $result = Asset::where('id', $data['assetId'])->update($update);
            if (!empty($result)) {
                $response['error'] = 'false';
            } else {
                $response['error'] = 'true';
            }
        } else {
            $response['error'] = 'true';
        }
        return $response;
    }

    /**
     * Search and Filter API for media manager
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filterData(Request $request)
    {
        $data = $request->all();
        if (!empty($data['folderId'])) {
            $folderId = $data['folderId'];
        } else {
            $folderId = 0;
        }
        $message = '';
        $multiple = 'false';
        $directories = array();
        $files = [];
        $last_id = 0;
        $image_ids = array();

        $client_id = $data['id'];
        if ($data['filter'] == 'all') { // if filter is selected all
            $filter = '';
            if ($data['type'] != 'scroll') {
                $directories = Directory::select('id','name','client_id')->where(['client_id' => $client_id, 'parent_id' => $folderId, 'deleted_at' => null])->where('name','like','%'.$data['searchTxt'].'%')->get();
            }
        } else { // if other filter selected
            if ($data['filter'] == 'images') {
                $filter = 'image';
            } elseif ($data['filter'] == 'videos') {
                $filter = 'video';
            } else {
                $filter = 'pdf';
            }
            $directories = array();
        }

        if (!empty($directories)) {
            $countImage = 20 - count($directories);
        } else {
            $countImage = 20;
        }
        if (empty($filter)) { // data without filter type
            $images = Asset::select('id','name','size','type','alt','title','description','updated_at')->where(['client_id' => $client_id, 'directory_id' => !empty($folderId) ? $folderId : null, 'deleted_at' => null])->
            where(function ($query) use ($data) {
                $query->where('name','like','%'.$data['searchTxt'].'%')
                    ->orWhere('alt','like','%'.$data['searchTxt'].'%')
                    ->orWhere('title','like','%'.$data['searchTxt'].'%')
                    ->orWhere('description','like','%'.$data['searchTxt'].'%');
            })->orderBy('id', 'DESC')->offset($data['start'])->limit($countImage)->get();
        } else { // data with filter type
            $images = Asset::select('id','name','size','type','alt','title','description','updated_at')->where(['client_id' => $client_id, 'directory_id' => !empty($folderId) ? $folderId : null, 'type' => $filter, 'deleted_at' => null])->
            where(function ($query) use ($data) {
                $query->where('name','like','%'.$data['searchTxt'].'%')
                    ->orWhere('alt','like','%'.$data['searchTxt'].'%')
                    ->orWhere('title','like','%'.$data['searchTxt'].'%')
                    ->orWhere('description','like','%'.$data['searchTxt'].'%');
            })->orderBy('id', 'DESC')->offset($data['start'])->limit($countImage)->get();
        }

        foreach ($images as $key => $value) {
            if ($key == 0) {
                $last_id = $value['id'];
            }
            $dt = $value['updated_at'];
            $name = $value['name'];

            $imgSize = $value['size'];
            $files[] = [
                'id' => $value['id'],
                'type' => $value['type'],
                'name' => $name,
                'modified' => $dt,
                'alt' => $value['alt'],
                'title' => $value['title'],
                'desc' => $value['description'],
                'size' => $imgSize,
                'copySrc' => env('AWS_URL') . $value['id'] . '/' . $name,
                'src' => env('AWS_URL') .'tr:n-media_thumb/'. $value['id'] . '/' . $name
            ];
        }

        $modified = array();
        foreach ($files as $key => $value) {
            $modified[$key] = $value['modified'];
        }
        $final = [
            'files' => $files,
            'directories' => $directories
        ];

        if (isset($data['activeIds'])) {
            $image_ids = explode(',', $data['activeIds']);
        }
        if ($data['multiple'] == 'true') {
            $multiple = $data['multiple'];
        }

        return view('filemanager::file-manager.dataList')->with(array('message' => $message, 'image_ids' => $image_ids, 'final' => $final, 'path' => $client_id, 'folder_path' => $client_id, 'client_id' => $client_id, 'multiple' => $multiple));
    }

    /**
     * Move files to folder
     * @param Request $request
     * @return mixed
     */
    public function moveToFolder(Request $request)
    {
        $response['status'] = '';
        $data = $request->all();
        if(isset($data['destFolderId']) && !empty($data['selected'])){
            $dirId = ($data['destFolderId'] == 0 ? null : $data['destFolderId']);
            $update = Asset::whereIn('id',$data['selected'])->update(array(
                'directory_id' => $dirId
            ));
            if($update){
                $response['status'] = 'success';
            }
        }
        return $response;
    }
}