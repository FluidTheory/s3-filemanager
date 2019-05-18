{{--css--}}
<link rel="stylesheet" href="/css/filemanager.css?v=1.2">

{{--start model for S3 file manager --}}
<div class="modal filemanager-iframe" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="fileManagerModalLabel"
     aria-hidden="true">
    <div class="modal-body">
        <div id="loader"></div>
        <iframe src="" id="file_manager" name="iframe" data-type="" data-select="" style="width: 100%;height: 100%" ></iframe>
        <div id="err_message" class="text-center"></div>
        <input type="hidden" id="selected-ids"  value="">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-lg btn-theme-color waves-effect" id="fm_cancel" data-dismiss="modal" aria-hidden="true" style="float: right;">Cancel</button>
    </div>
</div>
<input type="hidden" id="multiple-img" value="">
{{--end model for S3 file manager --}}

<script>
    $(document).on('click','.s3-upload',function () {
        $('.s3-upload').attr('data-click','');
        $(this).attr('data-click','set');
        var client_id = $('#folder-id').val();
        var multiple = $(this).data('multiple');
        var type = $(this).attr('data-type');
        $('#file_manager').attr('data-type',type);
        $('#file_manager').attr('data-select',multiple);
        var current = $(this).parent().find('.image-ids').val();
        getS3Images(client_id,multiple,current,type);
    });
    $(document).on('click','.clear-file', function () {
        $(this).parent().find('.fm-image').val('');
        $(this).parent().find('.image-ids').val('');
    });
    function getS3Images(client_id,multiple,current,type) {
        if(client_id === undefined || client_id === ''){
            client_id = null;
            $('#file_manager').hide();
            $('#loader').hide();
            $('#err_message').html('Please add data-client attribute to upload button and set folder value !!');
        }

        if(client_id != null){
            var iframeSrc = $('#file_manager').attr('src');
            if(iframeSrc == '') {
                if (multiple == true) {
                    $('#multiple-img').val('true');
                    var url = '/filemanager?path=' + client_id;
                }
                else {
                    $('#multiple-img').val('false');
                    var url = '/filemanager?path=' + client_id;
                }
                $('#file_manager').attr('src', url);
            }else{
                var iframe = $('#file_manager').contents();

                if(multiple == false) {
                    $('#multiple-img').val('false');
                    if(current == '') {
                        iframe.find(".check-input").prop("checked", false);
                        iframe.find('li.image-li').removeClass('add-background');
                        iframe.find('li.image-li').removeClass('selected');
                    } else{
                        iframe.find(".check-input").prop("checked", false);
                        iframe.find('li.image-li').removeClass('add-background');
                        iframe.find('li.image-li').removeClass('selected');

                        iframe.find(".check-"+current).prop("checked", true);
                        iframe.find('li.check-'+current).addClass('add-background');
                        iframe.find('li.check-'+current).addClass('selected');
                    }
                }else{
                    var ids = current.split(',');
                    iframe.find(".check-input").prop("checked", false);
                    iframe.find('li.image-li').removeClass('add-background');
                    iframe.find('li.image-li').removeClass('selected');

                    if(ids != ''){
                        $(ids).each(function(index) {

                            iframe.find(".check-"+ids[index]).prop("checked", true);
                            iframe.find('li.check-'+ids[index]).addClass('add-background');
                            iframe.find('li.check-'+ids[index]).addClass('selected');
                        });
                    } else{
                        iframe.find(".check-input").prop("checked", false);
                        iframe.find('li.image-li').removeClass('add-background');
                        iframe.find('li.image-li').removeClass('selected');
                    }

                    $('#multiple-img').val('true');
                }
            }
        }

        var iframe = $('#file_manager').contents();
        if(type == 'image'){
            iframe.find(".checkb-video").attr("disabled", true);
        }
        $('#fileManagerModal').modal('toggle');
        var iframe = $('iframe');
        $(iframe).on('load', function() {
            $('#loader').hide();
        });

    }

    $(document).ready(function () {
        $('.close').click(function () {
            $('#fileManagerModal').fadeOut();
        });

    });
    $('#file_manager').on('load', function () {
        var iframe = $('#file_manager').contents();
        iframe.find("#mySelected").click(function () {
            let current = $('#fileManagerModal').attr('current');
            var images = [];
            var ids = [];
            var size = [];
            var height = [];
            var width = [];
            iframe.find('ul.img-gallery li').each(function () {
                if ($(this).hasClass('selected')) {
                    var type = $(this).attr("type");
                    var src = $(this).find('img').attr('src');
                    var client_id = $('#folder-id').val();
                    if(type == 'image' ){
                        images.push($(this).find('img').data('value'));
                        height.push($(this).find('img')[0].naturalHeight);
                        width.push($(this).find('img')[0].naturalWidth);
                        ids.push($(this).find('img').data('id'));
                        size.push($(this).find('img').data('size'));
                    }
                    if(type == 'pdf' ){
                        images.push($(this).find('img').data('value'));
                        ids.push($(this).find('img').data('id'));
                        size.push($(this).find('img').data('size'));
                    }
                    if(type == 'video'){
                        images.push($(this).find('video').data('value'));
                        ids.push($(this).find('video').data('id'));
                        size.push($(this).find('video').data('size'));
                    }


                }
            });
            if(current == '.trumbowyg-editor') {
                var client_id = $('#folder-id').val();
                var img = '{{ env('AWS_URL')}}'+ ids +'/' + images ;
                $('input[name="url"]').val(img);
            } else {
                $(current).val(images);

                @if(!empty($validateSize))
                if(width >= 640 && height <= 1920){
                    $('#image-upload').val(images);
                    $('form#img-upload').submit();
                } else{
                    alert("Please upload a bigger image.");
                    return false;
                }

                @endif
                $('.s3-upload').each(function() {
                    if($(this).attr('data-click') == 'set'){
                        $(this).parent().find('.fm-image').val(images);
                        $(this).parent().find('.image-ids').val(ids);
                    }
                });
            }

            $('#fileManagerModal').modal('toggle');
        });
    });
</script>