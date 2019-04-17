{{--css--}}
<link rel="stylesheet" href="/css/filemanager.css?v=1.2">

{{--start model for S3 file manager --}}
<div class="modal filemanager-iframe" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="fileManagerModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <h3 id="fileManagerModalLabel">File Manager</h3>
    </div>
    <div class="modal-body">
        <div id="loader"></div>
        <iframe src="" id="file_manager" data-select="" style="width: 100%;height: 100%"></iframe>
        <div id="err_message" class="text-center"></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-lg btn-theme-color waves-effect" data-dismiss="modal" aria-hidden="true" style="float: right;">Cancel</button>
    </div>
</div>
<input type="hidden" id="multiple-img" value="">
{{--end model for S3 file manager --}}

{{--scripts--}}
<script>
    $(document).on('click','.s3-upload',function () {
        $('.s3-upload').attr('data-click','');
        $(this).attr('data-click','set');
        var client_id = $('#folder-id').val();
        var multiple = $(this).data('multiple');
        getS3Images(client_id,multiple);
    });
    function getS3Images(client_id,multiple) {
        if(client_id === undefined || client_id === ''){
            client_id = null;
            $('#file_manager').hide();
            $('#loader').hide();
            $('#err_message').html('Please add data-client attribute to upload button and set folder value !!');
        }

        if(client_id != null || client_id == 'cms'){
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
                if(multiple == false) {
                    $('#multiple-img').val('false');
                    var iframe = $('#file_manager').contents();

                    iframe.find(".check-input").prop("checked", false);
                    iframe.find('li.image-li').removeClass('add-background');
                    iframe.find('li.image-li').removeClass('selected');
                }else{
                    $('#multiple-img').val('true');
                }
            }
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
            var size = [];
            var height = [];
            var width = [];

            iframe.find('ul.img-gallery li').each(function () {
                if ($(this).hasClass('selected')) {
                    images.push($(this).find('img').data('value'));
                    size.push($(this).find('img').data('size'));
                    height.push($(this).find('img')[0].naturalHeight);
                    width.push($(this).find('img')[0].naturalWidth);
                }
            });
            if(current == '.trumbowyg-editor') {
                var client_id = $('#folder-id').val();
                var img = '{{ env('AWS_URL')}}'+ client_id +'/' + images ;
                $('input[name="url"]').val(img);
            } else {
                $(current).val(images);
                $('.s3-upload').each(function() {
                    if($(this).attr('data-click') == 'set'){
                        $(this).parent().find('.fm-image').val(images);
                    }
                });
            }

            @if(!empty($validateSize))
            if(width >= 640 && height <= 1920){
                $('#image-upload').val(images);
                $('form#img-upload').submit();
            } else{
                alert("Please upload a bigger image.");
                return false;
            }
            @endif

            $('#fileManagerModal').modal('toggle');
        });
    });
</script>