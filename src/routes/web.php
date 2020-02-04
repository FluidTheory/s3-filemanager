<?php
/**
 * Created by PhpStorm.
 * User: sanjogkaskar
 * Date: 3/28/19
 * Time: 4:28 PM
 */


Route::group(['namespace' => 'Fluidtheory\Filemanager\Http\Controllers'], function (){

    Route::post('/delete_file', 'FileManagerController@delete');
    Route::post('/filemanager/upload', 'FileManagerController@upload');
    Route::post('/filemanager/addfolder', 'FileManagerController@addfolder');
    Route::get('/filemanager/{path?}/{ids?}/{multiple?}/{message?}', 'FileManagerController@index');
    Route::post('/fetch', 'FileManagerController@fetchImages');
    Route::post('/filter', 'FileManagerController@filterData');

//  Folder Routes
    Route::post('/filemanager/addfolder', 'FileManagerController@addFolder');
    Route::post('/delete-folders', 'FileManagerController@deleteFolder');
    Route::post('/updateAssetData', 'FileManagerController@updateAssetData');
});