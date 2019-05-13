<?php

namespace Fluidtheory\Filemanager\Http\Controllers;

use App\Http\Controllers\Controller;
use Fluidtheory\Filemanager\Models\Asset;
use Illuminate\Http\Request;
use Auth;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use DB;

class FileManagerController extends Controller
{
    public function index()
    {
        $path = $_GET;
        $files = [];
        $last_id = 0;
        $multiple = 'false';
        $image_ids = array();
        $client_id = $path['path'];
        $images = Asset::where('client_id', $client_id)->where('deleted_at',null)->orderBy('id', 'DESC')->get();

        foreach ($images as $key => $value) {
            if($key == 0){
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
                'size' => $imgSize,
                'src' => env('AWS_URL').$value['id'].'/'.$name
            ];
        }

        $modified = array();
        foreach ($files as $key => $value)
        {
            $modified[$key] = $value['modified'];
        }
        $final = [
            'files' => $files
        ];

        if(isset($path['ids'])){
            $image_ids = explode(',',$path['ids']);
        }
        if(isset($path['multiple'])){
            $multiple = $path['multiple'];
        }

        return view('filemanager::file-manager.index')->with(array('multiple' => $multiple,'image_ids' => $image_ids,'final' => $final, 'path' => $path['path'], 'folder_path' => $path['path'],'client_id' => $client_id));

    }

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
                $name = $file->getClientOriginalName();
                $name = str_replace(array(" ",".","(",")"),"-",$name);

                $arr = explode('.',$name);
                $ext = end($arr);

                if($ext == 'mp4'){
                    $type = 'video';
                } elseif($ext == 'pdf') {
                    $type = 'pdf';
                }else {
                    $param = getimagesize($file);
                    $width = $param[0];
                    $height = $param[1];
                    $type = 'image';
                }
                $size = floor(filesize($file)/1024);

                if($size > 10240){
                    return redirect()->back()->with('flash', 'danger')->with('error', 'Please upload file less than 10MB .');
                }

                $image = Asset::insertGetId(array(
                    'name' => $name,
                    'client_id' => $data['path'],
                    'width' => $width,
                    'height' => $height,
                    'size' => $size,
                    'type' => $type,
                    'created_at' => gmdate("Y-m-d H:i:s")
                ));

                $dir = $image;
                $result = Storage::disk('s3')->makeDirectory($dir);

                $filePath = $image . '/' . $name;
                $results = Storage::disk('s3')->put($filePath, file_get_contents($file));

                $image_array[] = $image;
            }
            $ids = implode(',',$image_array);
            if ($results) {
                return redirect('/filemanager?path='.$data['path'].'&ids='.$ids.'&multiple='.$multiSelect)->with('flash', 'success')->with('message', 'Image uploaded successfully');
            } else {
                return redirect()->back()->with('flash', 'danger')->with('error', 'Image not uploaded.');
            }
        }
    }

    public function addfolder(Request $request)
    {
        $data = $request->all();
        $dir = $data['path'] . '/' . $data['folder_name'];
        $result = Storage::disk('s3')->makeDirectory($dir);
        if ($result) {
            return redirect()->back()->with('flash', 'success')->with('message', 'Folder Created successfully');
        } else {
            return redirect()->back()->with('flash', 'danger')->with('error', 'Folder not Created.');
        }
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        $del_file = Asset::where('id','=',$data['id'])->update(array('deleted_at' => gmdate("Y-m-d H:i:s")));

        if ($del_file) {
            return 'true';
        } else {
            return 'false';
        }
    }
}