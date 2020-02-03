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
        $breadcrumbs = array();
        $path = $_GET;
        $files = [];
        $last_id = 0;
        $multiple = 'false';
        $folderId = null;
        $image_ids = array();
        $pathExp = explode("/", $path['path']);
        if (empty($pathExp[1])) {
            $client_id = $pathExp[0];
            $directories = Directory::where(['client_id' => $client_id, 'parent_id' => 0, 'deleted_at' => null])->get();
            $countImage = 20 - count($directories);
            $images = Asset::where(['client_id' => $client_id, 'directory_id' => null, 'deleted_at' => null])->orderBy('id', 'DESC')->limit($countImage)->get();
        } else {
            $client_id = $pathExp[0];
            $folderId = end($pathExp);
            $directories = Directory::where(['client_id' => $pathExp[0], 'parent_id' => end($pathExp), 'deleted_at' => null])->get();
            $countImage = 20 - count($directories);
            $images = Asset::where(['client_id' => $pathExp[0], 'directory_id' => end($pathExp)])->where('deleted_at', null)->orderBy('id', 'DESC')->limit($countImage)->get();
            $slug_url = $client_id;
            foreach ($pathExp as $key => $value) {
                if ($client_id != array_shift($pathExp)) {
                    $lastFolder = Directory::where(['client_id' => $client_id, 'id' => $value])->first();
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
                'src' => env('AWS_URL') . $value['id'] . '/' . $name
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

        if (isset($path['ids'])) {
            $image_ids = explode(',', $path['ids']);
        }
        if (isset($path['multiple'])) {
            $multiple = $path['multiple'];
        }
        if (!empty($path['message'])) {
            $message = $path['message'];
        }
        return view('filemanager::file-manager.index')->with(array('message' => $message, 'multiple' => $multiple, 'image_ids' => $image_ids, 'final' => $final, 'path' => $path['path'], 'folder_path' => $path['path'], 'folderId' => $folderId, 'client_id' => $client_id, 'breadcrumbs' => $breadcrumbs));
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
//        echo "<pre>";
//        print_r($data); die;
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
                'src' => env('AWS_URL') . $value['id'] . '/' . $name
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

    public function filterData(Request $request)
    {
        $data = $request->all();
        if(!empty($data['folderId'])){
            $folderId = $data['folderId'];
        } else{
            $folderId = null;
        }
        $message = '';
        $multiple = 'false';
        $breadcrumbs = array();
        $directories = array();
        $files = [];
        $last_id = 0;
        $image_ids = array();

        if (empty($folderId)) {
            $client_id = $data['id'];
            if($data['filter'] == 'all'){
                $filter = '';
                if($data['type'] != 'scroll'){
                    $directories = Directory::where(['client_id' => $client_id, 'parent_id' => 0, 'deleted_at' => null])->get();
                }
            } else{
                if($data['filter'] == 'images'){
                    $filter = 'image';
                } elseif ($data['filter'] == 'videos'){
                    $filter = 'video';
                } else{
                    $filter = 'pdf';
                }
                $directories = array();
            }
            if(!empty($directories)){
                $countImage = 20 - count($directories);
            } else{
                $countImage = 20;
            }
            if(empty($filter)){
                $images = Asset::where(['client_id' => $client_id, 'directory_id' => null, 'deleted_at' => null])->orderBy('id', 'DESC')->offset($data['start'])->limit($countImage)->get();
            } else{
                $images = Asset::where(['client_id' => $client_id, 'directory_id' => null,'type' => $filter, 'deleted_at' => null])->orderBy('id', 'DESC')->offset($data['start'])->limit($countImage)->get();
            }

        } else {
            $client_id = $data['id'];
            $directories = Directory::where(['client_id' => $client_id, 'parent_id' => end($pathExp), 'deleted_at' => null])->get();
            $countImage = 20 - count($directories);
            $images = Asset::where(['client_id' => $client_id, 'directory_id' => end($pathExp)])->where('deleted_at', null)->orderBy('id', 'DESC')->limit($countImage)->get();
            $slug_url = $client_id;
            foreach ($pathExp as $key => $value) {
                if ($client_id != array_shift($pathExp)) {
                    $lastFolder = Directory::where(['client_id' => $client_id, 'id' => $value])->first();
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
                'src' => env('AWS_URL') . $value['id'] . '/' . $name
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
        if (!empty($path['message'])) {
            $message = $path['message'];
        }

        return view('filemanager::file-manager.dataList')->with(array('message' => $message, 'image_ids' => $image_ids, 'final' => $final, 'path' => $client_id, 'folder_path' => $client_id, 'client_id' => $client_id, 'breadcrumbs' => $breadcrumbs,'multiple' => $multiple));
    }
}