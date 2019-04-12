{{--css--}}
<link rel="stylesheet" href="/css/filemanager.css?v=1.2">

{{--start model for S3 file manager --}}
<div class="modal filemanager-iframe" id="fileManagerModal" tabindex="-1" role="dialog" aria-labelledby="fileManagerModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <h3 id="fileManagerModalLabel">File Manager</h3>
    </div>
    <div class="modal-body">
        <div id="loadingMessage" class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <iframe src="" id="file_manager" style="width: 100%;height: 100%"></iframe>
        <div id="err_message" class="text-center"></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-lg btn-theme-color waves-effect" data-dismiss="modal" aria-hidden="true" style="float: right;">Cancel</button>
    </div>
</div>
{{--end model for S3 file manager --}}

{{--scripts--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>
<script>
    function getS3Images() {
        var client_id = $('#folder-id').val();
        if(client_id === undefined || client_id === ''){
            client_id = null;
            $('#file_manager').hide();
            $('#loadingMessage').hide();
            $('#err_message').html('Please add data-client attribute to upload button and set folder value !!');
        }

        if(client_id != null || client_id == 'cms'){
            var url = '/filemanager?path='+client_id;
            $('#file_manager').attr('src',url);
        }
        $('#fileManagerModal').modal('toggle');
        var iframe = $('iframe');
        $(iframe).on('load', function() {
            $('#loadingMessage').hide();
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
                var img = '{{ env('AWS_URL').config('path.folder_name').'/' }}' + images ;
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