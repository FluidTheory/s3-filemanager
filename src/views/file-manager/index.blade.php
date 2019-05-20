<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>File Manager</title>
    <!-- Include our stylesheet -->
    <link href="/css/filemanager/styles.css?v=3.1" rel="stylesheet"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
<div id="fm_header">
    <div class="inside-data">
        <span id="mySelected">
            <button type="button" id="insert-btn" class="btn btn-theme-color btn-lg waves-effect filemanager-btn" style="display: none;">
                Insert
            </button>
        </span>
        <form action="/filemanager/upload" method="post" enctype="multipart/form-data" role="form" id="upload-form">

            <input type="hidden" class="path" name="path" value="{{@$path}}">
            <input type="hidden" class="path" name="_token" value="{{csrf_token()}}">
            <input type="hidden" class="path" name="multi-select" id="multi-select" value="false">
            <input type="file" style="display: none" name="file[]" accept="image/x-png,image/jpeg,image/gif,video/mp4,application/pdf" id="file-input" multiple>
            <span onclick="openDialog()">
                    <button type="button" class="btn btn-theme-color btn-lg waves-effect filemanager-btn">
                        Upload
                    </button>
                </span>
        </form>
    </div>
</div>
<div class="messages"></div>
@if(Session::has('message'))

    <div class="response-message">{{Session::get('message')}}</div>
@elseif(Session::has('error'))
    <div class="response-message"> {{Session::get('error')}}</div>
@endif
<div class="filemanager">
    <div class="breadcrumbs">
        <?php
            $tokens = explode('/', $path);
            $lastToken = array_pop($tokens);
            $path = [];
         ?>
        @foreach($tokens as $k)
            <?php $path[] = $k; $current = implode('/', $path); ?>
                <a href="/filemanager?path={{$current}}">
                    @if($path[0] == $current)
                        <span class="folderName">
                            <i class="fas fa-home"></i>
                        </span>
                    @else
                        <span class="folderName">
                            {!! $k !!}
                        </span>
                    @endif
                </a>
                <span class="arrow">→</span>

        @endforeach
            @if(!empty($tokens))
                <span class="folderName">{!! $lastToken !!}</span>
            @endif
        <span class="folderName"></span>
    </div>
    <ul class="data">
        <ul id="load_data" class="data animated img-gallery">
            <?php $count = 0; ?>
            @foreach($final['files'] as $k)
                <?php
                    $checked = '';
                    $li_class = '';
                    $ids = $k['id'];
                    if(!empty($image_ids) && in_array($ids, $image_ids)){
                        if($multiple == 'true'){
                            $checked = 'checked';
                            $li_class = 'add-background selected';
                        } else {
                            if($count == 0){
                                $checked = 'checked';
                                $li_class = 'add-background selected';
                            }
                            $count++;
                        }
                ?>
                <script>$("#insert-btn").show();</script>
                <?php
                    }
                ?>
                <li class="image-li check-{{ $k['id'] }} {{ $li_class }}" data-type="{{$k['type']}}" data-action="no" data-id="{!! $k['id'] !!}" id="li-{!! $k['id'] !!}">
                    <span class="image">
                        @if($k['type'] == 'image' || $k['type'] == 'pdf')
                            <img class="img-select" id="img-select" src="{{($k['type'] == 'pdf' ? 'images/pdf-icon.png' : $k['src'] )}}"
                             data-value="{{ $k['name'] }}" data-id="{{ $k['id'] }}" data-size="{{ $k['size'] }}" value="this.naturalHeight">
                        @endif

                        @if($k['type'] == 'video')
                            <video class="img-select" id="img-select"
                             data-value="{{ $k['name'] }}" data-id="{{ $k['id'] }}" data-size="{{ $k['size'] }}" value="this.naturalHeight" >
                                <source src="{{$k['src']}}" type="video/mp4">
                            </video>
                        @endif
                    </span>
                    <div id="outer-{!! $k['id'] !!}" class="outer-div">
                        <span class="inputGroup">
                            <input {{ $checked }} class="check-input check-{{ $k['id'] }} {{(($k['type'] == 'image') ? 'checkb-image' : 'checkb-video' )}}" data-id="{!! $k['id'] !!}" data-type="{{$k['type']}}" data-action="box" id="option-{!! $k['id'] !!}" name="option{!! $k['id'] !!}" type="checkbox" disabled/>
                            <label for="option-{!! $k['name'] !!}"></label>
                        </span>
                        <span class="name" value="{{$k['name']}}">{{$k['name']}}</span>
                        <div class="box-bottom">
                            <span class="image-size" value="{!! $k['size'] !!}">{!! $k['size'].' KB' !!}</span>
                            <span class="delbtn" data-value="{{$k['name']}}" data-id="{{ $k['id'] }}" data-type="{{$k['type']}}" data-action="del" data-name="file" >
                            <i class="fas fa-trash del-icon"></i>
                        </span>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </ul>
    <div id="load_data_message"></div>
    <div class="loader" style="display: none"></div>
    <div class="nothingfound">
        <div class="nofiles"></div>
        <span>No files here.</span>
    </div>
</div>
<!-- The Modal -->
<div id="fileManageAddFolderModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <form action="/filemanager/addfolder" method="post" enctype="multipart/form-data" role="form">
            {{ csrf_field() }}
            <span class="close">x</span>
            <div class="modal-data">
                <p class="icon folder"></p>
                <br/>
                <input type="text" name="folder_name" placeholder="Enter Folder Name" value="" class="input" required autofocus>
                <br/>
                <input type="hidden" name="path" value="{{@$folder_path}}">
                <button type="submit" class="btn">ADD</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script>
    $(document).ready(function () {

        // load images
        var limit = 20;
        var start = 1;
        var action = 'inactive';

        function load_images(limit, start,id)
        {
            $.ajax({
                url:"fetch",
                method:"POST",
                data:{limit:limit, start:start, id:id},
                cache:false,
                success:function(response)
                {
                    var data = '';
                    $.each(response, function () {
                        data += '<li class="image-li check-'+this.id+'" data-type="'+this.type+'" data-action="no" data-id="'+this.id+'" id="li-'+this.id+'">';
                        data += '<span class="image">';
                        if(this.type == 'image' || this.type == 'pdf'){
                            data += '<img class="img-select" id="img-select" src="'+(this.type == "pdf" ? "images/pdf-icon.png" : this.src)+'"data-value="'+this.name+'" data-id="'+this.id+'" data-size="'+this.size+'" value="'+this.naturalHeight+'">';
                        }
                        if(this.type == 'video'){
                            data += '<video class="img-select" id="img-select" data-value="'+this.name+'" data-id="'+this.id+'" data-size="'+this.size+'" value="'+this.naturalHeight+'" ><source src="'+this.src+'" type="video/mp4"></video>';
                        }
                        data += '</span>';
                        data += '<div id="outer-'+this.id+'" class="outer-div">';
                        data += '<span class="inputGroup">';
                        data += '<input class="check-input check-'+this.id+' '+(this.type == "image" ? "checkb-image" : "checkb-video")+'" id="option-'+this.id+'" data-id="'+this.id+'" data-type="'+this.type+'" data-action="box" name="option'+this.id+'" type="checkbox" disabled/>';
                        data += '<label for="option-'+ this.name +'"></label>';
                        data += '</span>';
                        data += '<span class="name" value="'+this.name+'">'+this.name+'</span>';
                        data += '<div class="box-bottom">';
                        data += '<span class="image-size" value="'+this.size+'">'+this.size+' KB</span><span class="delbtn" data-value="'+this.name+'" data-id="'+this.id+'" data-type="'+this.type+'" data-action="del" data-name="file"> <i class="fas fa-trash del-icon"></i>';
                        data += '</span>';
                        data += '</div>';
                        data += '</li>';
                    });
                    $('#load_data').append(data);
                    if(data == '')
                    {
                        $('.loader').fadeOut();
                        action = 'active';
                    }
                    else
                    {
                        $('.loader').fadeOut();
                        action = "inactive";
                    }
                }
            });
        }

        $(window).scroll(function(){
            if($(window).scrollTop() + $(window).height() > $("#load_data").height() && action == 'inactive')
            {
                $('.loader').show();
                action = 'active';
                start = start + limit;
                var id = parent.document.getElementById('folder-id').value;
                setTimeout(function(){
                    load_images(limit, start,id);
                }, 1000);
            }
        });

        $('.delbtn').mousedown(function (event) {
            event.preventDefault();
            var type = $(this).data('name');
            if(type == 'file') {
                var r = confirm("Are you sure want to delete Image?");
            }else if(type == 'folder'){
                var r = confirm("Are you sure want to delete Folder?");
            }
            if(r == true) {
                var id = $(this).data('id');
                var token = $('input[name=_token]').val();
                $.blockUI({
                    css: {
                        border: 'none',
                        backgroundColor: 'transparent'
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: '/delete_file',
                    data: 'id=' + id + '&_token=' + token +'&type=' + type,
                    success: function (response) {
                        if (response = true) {
                            location.reload();
                        } else {
                            $('.messages').html('Unable to delete ...');
                        }
                    },
                    complete: function () {
                        $.unblockUI();

                    }
                });
            }
        });
    });

    $(document).on('click','.image-li, .check-input, .delbtn', function () {
        var type = $(this).data('type');
        var id = $(this).data('id');
        var action = $(this).data('action');
        if(action == 'no'){
            show_border(id,type);
        } else{
            show_border(id,type,action);
        }
    });


    function openDialog() {
        var type = parent.document.getElementById('file_manager').getAttribute('data-type');
        if(type == 'image'){
            $('#file-input').attr('accept','image/x-png,image/jpeg,image/gif');
        }
        else if(type == 'video'){
            $('#file-input').attr('accept','video/mp4');
        } else if(type == 'image-video'){
            $('#file-input').attr('accept','image/x-png,image/jpeg,image/gif,video/mp4');
        } else if(type == 'file'){
            $('#file-input').attr('accept','application/pdf');
        } else{
            $('#file-input').attr('accept','image/x-png,image/jpeg,image/gif,video/mp4,application/pdf');
        }
        var multiSelect = parent.document.getElementById('file_manager').getAttribute('data-select');
        if(multiSelect == 'true'){
            $('#multi-select').val('true');
        }else{
            $('#multi-select').val('false');
        }
        document.getElementById("file-input").click();
    }

    document.getElementById("file-input").onchange = function (e) {
        var error = 0;
        $('#file').each(function (index) {
            var get_size = this.files[index].size;
            var size = (Math.round((get_size / 1024) * 100) / 100);
            if(size >= '10000') {
                error+= 1;
                alert("Please upload an file less than 10MB");
                e.preventDefault(e);
            }
        });
        if(error == 0){
            document.getElementById("upload-form").submit();
        }
    };

    // Get the modal
    var modal = document.getElementById('fileManageAddFolderModal');

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }


        function show_border(checkName,current, actionFrom = null){
            var type = window.frameElement.getAttribute("data-type");
            if(type == 'all'){ // For all Files

            } else if(type == 'image-video') { // For Image and video
                if(current == 'image' || current == 'video') {

                } else {
                    alert('You can select Image/Video  file only !!');
                    return false;
                }
            }else if(current != type){
                    alert('You can select '+type+' file only !!');
                    return false;
            }
            var multiple = parent.document.getElementById('multiple-img').value;
            if ($("#option-"+checkName).prop("checked") == true) {
                if(actionFrom == null) { // click on selected image box
                    if (multiple === 'false') {
                        $(".check-input").prop("checked", false);
                        $('li.image-li').removeClass('add-background');
                        $('li.image-li').removeClass('selected');
                    } else{
                        $("#option-" + checkName).prop("checked", false);
                        $('#li-' + checkName).removeClass('add-background');
                        $('#li-' + checkName).removeClass('selected');
                    }
                }else{ // click on unselected image checkbox
                    if (multiple === 'false') {
                        $(".check-input").prop("checked", false);
                        $('li.image-li').removeClass('add-background');
                        $('li.image-li').removeClass('selected');
                        $("#option-" + checkName).prop("checked", true);
                        $('#li-' + checkName).addClass('add-background');
                        $('#li-' + checkName).addClass('selected');
                    }else {
                        $('#li-' + checkName).addClass('add-background');
                        $('#li-' + checkName).addClass('selected');
                    }
                }
            }else{
                if(actionFrom == 'del') {
                    $("#option-" + checkName).prop("checked", false);
                    $('#li-' + checkName).removeClass('add-background');
                }else if(actionFrom == null){ // image box click when checkbox unchecked
                    if (multiple === 'false') {
                        $(".check-input").prop("checked", false);
                        $('li.image-li').removeClass('add-background');
                        $('li.image-li').removeClass('selected');
                        $("#option-" + checkName).prop("checked", true);
                        $('#li-' + checkName).addClass('add-background');
                        $('#li-' + checkName).addClass('selected');
                    }else {
                        $("#option-" + checkName).prop("checked", true);
                        $('#li-' + checkName).addClass('add-background');
                        $('#li-' + checkName).addClass('selected');
                    }
                }
                else { // checkbox unchecked click
                    if (multiple === 'false') {
                        $(".check-input").prop("checked", false);
                        $('li.image-li').removeClass('add-background');
                        $('li.image-li').removeClass('selected');
                    } else {
                        $('#li-' + checkName).removeClass('add-background');
                        $('#li-' + checkName).removeClass('selected');
                    }
                }
            }
            var count = $('li.image-li.selected').length;
            if(count > 0){
                $('#insert-btn').text('Insert ('+ count +')');
                // parent.document.getElementById('insert-btn').text = 'Insert ('+ count +')';
            }
            var $insert_btn = $("#insert-btn").hide();
            $insert_btn.toggle( $("input[type='checkbox']").is(":checked") );
        }

</script>
<style>
    body {
        background-color: transparent;
    }
    #fm_header{
        padding-right: 2.3em;
        position: fixed;
        width: 100%;
        height: 60px;
        margin: 0px !important;
        background-color: #38a7de;
        top: 0px;
        padding-top: 20px;
        z-index: 1000;
    }
    .inside-data{
        width: 95%;
    }
    .filemanager .data li {
        border-radius: 10px;
        background-color: #373743;
        border: 1px solid #373743;
        width: 19%;
        height: 200px !important;
    }
    .img-gallery img.img-select{
        transform: translate(-50%, -50%);
        max-width: 100%;
        position: absolute;
        left: 50%;
        top: 50%;
    }
    .image-li:hover .image{
        opacity: 0.35;
    }

    .image-size{
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        position: absolute;
        left: 4px;
        bottom: 5px;
    }
    .delbtn{
        position: absolute;
        right: 4px;
        bottom: 5px;
    }

    .inputGroup {
        position: absolute;
        right: 4px;
        top: 5px;
        opacity: 0;
    }
    .inputGroup label {
        padding: 12px 30px;
        display: inline-block;
        text-align: left;
        color: #3C454C;
        cursor: pointer;
        position: relative;
        z-index: 2;
        transition: color 200ms ease-in;
        overflow: hidden;

    }
    .inputGroup label:before {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale3d(1, 1, 1);
        transition: all 300ms cubic-bezier(0.4, 0.0, 0.2, 1);
        opacity: 0;
        z-index: -1;
    }

    .inputGroup label:after {
        width: 16px;
        height: 16px;
        content: '';
        border: 2px solid #ccc;
        background-color: #ccc;
        background-image: url("data:image/svg+xml,%3Csvg width='24' height='24' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.414 11L4 12.414l5.414 5.414L20.828 6.414 19.414 5l-10 10z' fill='%23fff' fill-rule='nonzero'/%3E%3C/svg%3E ");
        background-repeat: no-repeat;
        background-position: -1px 0px;
        border-radius: 50%;
        z-index: 2;
        position: absolute;
        right: 0px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        transition: all 200ms ease-in;
    }

    .inputGroup input:checked ~ label {
        color: #fff;
    }
    .inputGroup input:checked ~ label:before {
        transform: translate(-50%, -50%) scale3d(56, 56, 1);
        opacity: 1;
    }
    .inputGroup input:checked ~ label:after {
        background-color: #2684FF;
        border-color: #2684FF;
    }

    .check-input {
        width: 32px;
        height: 32px;
        order: 1;
        z-index: 2;
        position: relative;
        cursor: pointer;
        visibility: hidden;
    }

    .outer-div{
        width: 95%;
        height: 200px;
        position: absolute;
        top: 0px;
    }

    .box-bottom{
        opacity: 0;
        -webkit-box-align: center;
        align-items: center;
        z-index: 1;
        bottom: 10px;
        left: 5px;
        position: absolute;
        width: 100%;
        transform: translateY(35px);
        transition: transform 0.2s ease 0s, opacity 0.5s ease 0s;
    }
    .outer-div .name{
        opacity: 0;
    }
    .filemanager .data li:hover > .outer-div .box-bottom{
        opacity: 1;
        transform: translateY(0px);
        transition: transform 0.2s ease 0s, opacity 0.5s ease 0s;
    }
    .filemanager .data li:hover > .outer-div .name, .filemanager .data li:hover > .outer-div .inputGroup{
        opacity: 1;
    }
    li.selected .inputGroup{
        opacity: 1;
    }
    .filemanager-btn:hover {
        background-color: #212529 !important;
        color: #ffffff !important;
    }
    .add-background{
        border: 2px solid #2684FF !important;
    }
    .loader {
        position: absolute;
        left: 49%;
        border: 12px solid #f3f3f3;
        border-radius: 50%;
        border-top: 12px solid #3498db;
        width: 30px;
        height: 30px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
</body>
</html>