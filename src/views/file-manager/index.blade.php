<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>File Manager</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.11.0/css/mdb.min.css" rel="stylesheet">
    <link href="/css/filemanager/styles.css?v=4.13" rel="stylesheet"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body class="overlay">
<div id="fm_header">
    @if(!empty($message))
        <div class="alert alert-info error-message">{!! $message !!} </div>
    @endif
    <div class="inside-data">
        <span id="mySelected">
            <button type="button" id="insert-btn" class="btn btn-default btn-square filemanager-btn" {!! (!empty($image_ids) ? 'style="display : block;"' : 'style="display: none;"') !!}>
                Insert {!! (!empty($image_ids) ? "(".count($image_ids).")" : "") !!}
            </button>
        </span>
        <form action="/filemanager/upload" method="post" enctype="multipart/form-data" role="form" id="upload-form">
            <span class="breadcrumbs">
                                <a href="/filemanager?path={{$client_id}}"><i class="fas fa-home custom-breadcrumbs"></i></a>
                                <input type="hidden" class="path" name="folder_path" value="{{ $client_id }}">
                @foreach($breadcrumbs as $key => $value)
                    <span class="arrow" style="color: #ffffff;font-size: 20px;font-weight: bold"> / </span>
                    @if($path != $value['slug'])
                        <a class="child-breadcrumbs" href="/filemanager?path={{$value['slug']}}">{!! $value['name'] !!}</a>
                    @else
                        <input type="hidden" class="path" name="folder_path" value="{{ $value['slug'] }}">
                        <span class="folderName child-breadcrumbs">{!! $value['name'] !!}</span>
                    @endif
                @endforeach
            </span>
            <input type="hidden" class="path" name="path" value="{{@$path}}">
            <input type="hidden" class="path" name="_token" value="{{csrf_token()}}">
            <input type="hidden" class="path" name="multi-select" id="multi-select" value="false">
            <input type="file" style="display: none" name="file[]" accept="image/*,video/mp4,application/pdf" id="file-input" multiple>
            <span class="mobile-icons">
                <button type="button" class="myBtn btn btn-default btn-square filemanager-btn">
                     <i class="fas fa-plus"></i>
                </button>
            </span>
            <span onclick="openDialog()" class="mobile-icons">
                <button type="button" class="btn btn-default btn-square filemanager-btn">
                    <i class="fas fa-upload"></i>
                </button>
            </span>
            <span class="desktop-btn">
                <button type="button" class="myBtn btn btn-default btn-square filemanager-btn">
                    Add Folder
                </button>
            </span>
            <span onclick="openDialog()" class="desktop-btn">
                <button type="button" class="btn btn-default btn-square filemanager-btn">
                    Upload
                </button>
            </span>
        </form>
    </div>
</div>
<div class="messages"></div>
<div class="filemanager row">
    <div class="col-md-9">
    <ul class="data">
        <ul id="load_data" class="data animated img-gallery">
            @foreach($final['directories'] as $k)
                <li class="folders">
                    <span class="folders">
                        <span onclick="location.href = '/filemanager?path={{ $path.'/'.$k['id'] }}'" data-clientid="{{$k['client_id']}}" class="icon folder full folder-details icon-font"></span>
                        <span class="name folder-name" :aria-valuemax="">{{$k['name']}}</span>
                        <div class="folder-outer-div">
                            <span class="folder-box-bottom">
                                <i class="fas fa-trash del-icon delete-folder" data-id="{{$k['id']}}"></i>
                            </span>
                        </div>
                    </span>
                </li>
            @endforeach
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
                            <input {{ $checked }} class="check-input check-{{ $k['id'] }} {{(($k['type'] == 'image') ? 'checkb-image' : 'checkb-video' )}}" data-id="{!! $k['id'] !!}" data-type="{{$k['type']}}" data-action="box" id="option-{!! $k['id'] !!}" data-alt="{!! $k['alt'] !!}" data-title="{!! $k['title'] !!}" data-desc="{!! $k['desc'] !!}" name="option{!! $k['id'] !!}" type="checkbox" disabled/>
                            <label for="option-{!! $k['name'] !!}"></label>
                        </span>
                        <span class="name" value="{{$k['name']}}">{{$k['name']}}</span>
                        <div class="box-bottom">
                            <span id="copyClipboard" data-toggle="tooltip" data-placement="top" title="Copy to clipboard" class="copy_clipboard fa fa-2x fa-copy" onclick="copyToClipboard(this)" copyval="{{$k['src']}}"></span>
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
    </div>

    <div class="col-md-3 assetData">
        <div class="col-md-12 text-center">
            <img class="z-depth-2" alt="100x100" id="imgThumb" src="" data-holder-rendered="true">
            <div class="my-2" id="imgName"></div>
        </div>
        <form id="updateData">
            <div class="md-form md-outline">
                <input type="text" maxlength="72" id="title" class="form-control" value="">
                <label for="form1">Title</label>
                <p>
                    <span class="limitTxt"> (Max 72 characters)</span>
                </p>
            </div>
            <div class="md-form md-outline">
                <textarea id="alt" maxlength="160" class="md-textarea form-control" rows="1"></textarea>
                <label for="form1">Alt</label>
                <p>
                    <span class="limitTxt"> (Max 160 characters)</span>
                </p>
            </div>
            <div class="md-form md-outline">
                <textarea id="description" maxlength="160" class="md-textarea form-control" rows="2"></textarea>
                <label for="form75">Description</label>
                <p>
                    <span class="limitTxt"> (Max 160 characters)</span>
                </p>
            </div>
            {{csrf_token()}}
            <input type="hidden" id="assetId" value="">
            <button type="submit" class="btn btn-primary updateBtn">Update</button><span class="" id="messageBox"></span>
        </form>
    </div>
    <div id="load_data_message"></div>
    <div class="loader" style="display: none"></div>
    <div class="nothingfound">
        <div class="nofiles"></div>
        <span>No files here.</span>
    </div>
    <input type="hidden" data-value="" id="selectedIds">
</div>
<!-- The Modal -->
<div id="fileManageAddFolderModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content model-content-folder">

    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<!-- Bootstrap tooltips -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
<!-- Bootstrap core JavaScript -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.min.js"></script>
<!-- MDB core JavaScript -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.11.0/js/mdb.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script>
    // Copy to clipboard
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).attr('copyval')).select();
        document.execCommand("copy");
        $temp.remove();
        $(element).css('color','#40a7de');
        parent.$('li.image-li').removeClass('selected');
        setTimeout(function() { $(element).css('color','#ffffff'); }, 5000);
    }
    let dataArray = [];
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
                        data += '<span id="copyClipboard" data-toggle="tooltip" data-placement="top" title="Copy to clipboard" class="copy_clipboard fa fa-2x fa-copy" onclick="copyToClipboard(this)" copyval="'+this.src+'"></span>';
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
                parent.$('#loader').show();
                $('body').addClass('overlay');
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

        $('.delete-folder').click(function () {
            var r = confirm("Are you sure want to delete Folder?");
            if(r == true){
                var id = $(this).data('id');
                var token = $('input[name=_token]').val();
                if(id != ''){
                    parent.$('#loader').show();
                    $.ajax({
                        type: 'POST',
                        url: '/delete-folders',
                        data: 'id='+id+'&_token='+token,
                        success: function (response) {
                            if(response.status === 'true'){
                                location.reload();
                            }
                        }
                    });
                }
            }
        });

        $('.add-btn').click(function () {
            var folderName = $('.custom-input').val();
            if(folderName != ''){
                parent.$('#loader').show();
                $('#fileManageAddFolderModal').css('display','none');
            }
        });

        $(document).on('click','.myBtn', function (e) {
            $('#fileManageAddFolderModal').css('display','block');
        });

        $('.updateBtn').click(function (e) {
            e.preventDefault();
           var id = $('#assetId').val();
            $.post( "/updateAssetData", { _token: $('input[name=_token]').val(), assetId: id,alt: $.trim($('#alt').val()),title: $('#title').val(), desc: $.trim($('#description').val()) })
            .done(function(data) {
                if(data.error == 'false'){
                    $("#option-"+id).data('alt',$('#alt').text());
                    $("#option-"+id).data('title',$('#title').val());
                    $("#option-"+id).data('desc',$('#description').text());
                    $('#messageBox').removeClass('fail').addClass('success');
                    $('#messageBox').html('Updated successfully.').show().delay(5000).fadeOut(800);
                } else{
                    $('#messageBox').removeClass('success').addClass('fail');
                    $('#messageBox').html('Failed to update.').show().delay(5000).fadeOut(800);
                }
            });
        });
    });

    $(document).on('click','.image-li, .check-input, .delbtn', function (e) {
        if(e.target.id == "copyClipboard")
            return;
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
            $('#file-input').attr('accept','image/*');
        }
        else if(type == 'video'){
            $('#file-input').attr('accept','video/mp4');
        } else if(type == 'image-video'){
            $('#file-input').attr('accept','image/*,video/mp4');
        } else if(type == 'file'){
            $('#file-input').attr('accept','application/pdf');
        } else{
            $('#file-input').attr('accept','image/*,video/mp4,application/pdf');
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
            parent.$('#loader').show();
            $('body').addClass('overlay');
            document.getElementById("upload-form").submit();
        }
    };

    // Get the modal
    var modal = document.getElementById('fileManageAddFolderModal');
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

    function show_border(checkName,current){
        var actionFrom = arguments.length > 2 && arguments[2] !== null ? arguments[2] : null;
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
        var assetId = $("#option-"+checkName).data('id');
        var token = $('input[name=_token]').val();
        var elId = $("#option-"+checkName);

        if (elId.prop("checked") == true) {
            if(actionFrom == null) { // click on selected image box
                if (multiple === 'false') {
                    dataArray = [];
                    $('.assetData').hide();
                    $(".check-input").prop("checked", false);
                    $('li.image-li').removeClass('add-background');
                    $('li.image-li').removeClass('selected');
                } else{
                    dataArray = dataArray.filter(item => item !== assetId);
                    if (typeof dataArray !== 'undefined' && dataArray.length > 0) {
                        var id = dataArray.slice(-1)[0];
                        var elId = $("#option-"+id);
                        $('#imgThumb').attr('src',elId.closest('li.image-li').find('img.img-select').attr('src'));
                        $('#imgName').text(elId.closest('li.image-li').find('img.img-select').data('value'));
                        $('#assetId').val(id);
                        $('#alt').text(elId.data('alt'));
                        $('#title').val(elId.data('title'));
                        $('#description').text(elId.data('desc'));
                        setDataStyle();
                    } else{
                        $('.assetData').hide();
                    }
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
            $('.assetData').show();
            dataArray.push(assetId);
            var elId = $('#option-'+assetId);
            $('#imgThumb').attr('src',elId.closest('li.image-li').find('img.img-select').attr('src'));
            $('#imgName').text(elId.closest('li.image-li').find('img.img-select').data('value'));
            $('#assetId').val(assetId);
            $('#alt').text(elId.data('alt'));
            $('#title').val(elId.data('title'));
            $('#description').text(elId.data('desc'));
            setDataStyle();

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
            if(screen.width >= '568'){
                $('#insert-btn').text('Insert ('+ count +')');
            } else{
                $('#insert-btn').addClass('insert-btn-icon');
                $('#insert-btn').html('<i class="fas fa-check"></i> ('+ count +')');
            }
            // parent.document.getElementById('insert-btn').text = 'Insert ('+ count +')';
        }
        var $insert_btn = $("#insert-btn").hide();
        $insert_btn.toggle( $("input[type='checkbox']").is(":checked") );
    }

    function setDataStyle() {
        $(".md-form label").addClass("active");
    }

</script>
</body>
</html>