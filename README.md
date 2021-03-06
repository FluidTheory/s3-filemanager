# Laravel Filemanager

File Manager with S3 Intrgration and integrate with Trumbowyg Editor

## Steps to Integarte File Manager with S3 Integration

- Run following command to install package: 
```php
composer require fluidtheory/s3-filemanager
```

- Add Publisher class in app.php of config folder
```php        
Fluidtheory\Filemanager\FileManagerServiceProvider::class
```

- After successful installation, to publish files to project you need to run, 
```php 
php artisan vendor:publish
```

- Include File Manager css in header
```php
<link rel="stylesheet" href="/css/filemanager-custom.css">
```

##### Note: Include Bootstrap css and js for modal style if not available in your project. Please include jquery file in header

- Include Modal in your view file where input and button are added
```php
@include('filemanager::file-manager.iframe')
```

- Set following attribute for upload button,
```php
   1) data-multiple="true" for multi-select image or data-multiple="false" for single image select
   2) Set another attribute data-click=""
```  

- Add input field to set image value
```php
<input type="text" class="form-control fm-image" name="new_images" value="" readonly>
```

- Pass folder name with hidden input in view file
```php
    <input type="hidden" id="folder-id" value="<your-folder-name>">
```  

- Change default value of "FILESYSTEM_DRIVER" in filesystems.php file to s3 and add update s3 array as follwoing
```php
'driver' => 's3',
'key' => env('AWS_KEY'),
'secret' => env('AWS_SECRET'),
'region' => env('AWS_REGION'),
'bucket' => env('AWS_BUCKET'),
'visibility' => 'public',
'url' => env('AWS_URL'),
```

- Final Step , Set following env variable in .env file with value
```php
AWS_KEY=
AWS_SECRET=
AWS_REGION=
AWS_BUCKET=
VISIBILITY='public'
AWS_BLOG_URL=
AWS_URL=
```

##### If you want to include file manager in trumbowyg editor please include following:
```php
Include following js file first,
<script src="/js/file-manager/trumbowyg.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.15.1/plugins/emoji/trumbowyg.emoji.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.15.1/plugins/table/trumbowyg.table.min.js"></script>
```

- Also, include css of trumbowyg editor
```php
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.15.1/ui/trumbowyg.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.15.1/plugins/emoji/ui/trumbowyg.emoji.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.15.1/plugins/table/ui/trumbowyg.table.min.css">
```

- Final steps for Editor with s3-filemanager, give class to textarea to integrate Trumbowyg editor
```php
$('.<your-class>').trumbowyg();
```


