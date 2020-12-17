@foreach($final['directories'] as $k)
    <li class="folders">
                    <span class="folders">
                        <span onclick="location.href = '/filemanager?path={{ $path.'/'.$k['id'] }}'"
                              data-clientid="{{$k['client_id']}}"
                              class="icon folder full folder-details icon-font"></span>
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
    if ($multiple == 'true') {
        $checked = 'checked';
        $li_class = 'add-background selected';
    } else {
        if ($count == 0) {
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
    <li class="image-li check-{{ $k['id'] }} {{ $li_class }}" data-type="{{$k['type']}}"
        data-action="no" data-id="{!! $k['id'] !!}" id="li-{!! $k['id'] !!}">
                    <span class="image">
                        @if($k['type'] == 'image' || $k['type'] == 'pdf')
                            <img class="img-select" id="img-select"
                                 src="{{($k['type'] == 'pdf' ? 'images/pdf-icon.png' : $k['src'] )}}"
                                 data-width="{{$k['width']}}"
                                 data-height="{{$k['height']}}"
                                 data-value="{{ $k['name'] }}" data-id="{{ $k['id'] }}" data-size="{{ $k['size'] }}"
                                 value="this.naturalHeight">
                        @endif

                        @if($k['type'] == 'video')
                            <video class="img-select" id="img-select"
                                   data-value="{{ $k['name'] }}" data-id="{{ $k['id'] }}" data-size="{{ $k['size'] }}"
                                   value="this.naturalHeight">
                                <source src="{{$k['src']}}" type="video/mp4">
                            </video>
                        @endif
                    </span>
        <div id="outer-{!! $k['id'] !!}" class="outer-div">
                        <span class="inputGroup">
                            <input {{ $checked }} class="check-input check-{{ $k['id'] }} {{(($k['type'] == 'image') ? 'checkb-image' : 'checkb-video' )}}"
                                   data-id="{!! $k['id'] !!}" data-type="{{$k['type']}}" data-action="box"
                                   id="option-{!! $k['id'] !!}" data-alt="{!! $k['alt'] !!}"
                                   data-title="{!! $k['title'] !!}" data-desc="{!! $k['desc'] !!}"
                                   name="option{!! $k['id'] !!}" type="checkbox" disabled/>
                            <label for="option-{!! $k['name'] !!}"></label>
                        </span>
            <span class="name" value="{{$k['name']}}">{{$k['name']}}</span>
            <div class="box-bottom">
                                <span id="copyClipboard" data-toggle="tooltip" data-placement="top"
                                      title="Copy to clipboard" class="copy_clipboard fa fa-2x fa-copy"
                                      onclick="copyToClipboard(this)" copyval="{{$k['copySrc']}}"></span>
                <span class="image-size" value="{!! $k['size'] !!}">{!! $k['size'].' KB' !!}</span>
                <span class="delbtn" data-value="{{$k['name']}}" data-id="{{ $k['id'] }}"
                      data-type="{{$k['type']}}" data-action="del" data-name="file">
                            <i class="fas fa-trash del-icon"></i>
                        </span>
            </div>
        </div>
    </li>
@endforeach