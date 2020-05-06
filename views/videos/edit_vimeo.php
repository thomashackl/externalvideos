<div id="edit-vimeo-video">
    <edit-vimeo-video :video='<?php echo studip_json_encode($video) ?>'
                      :dates='<?php echo studip_json_encode($dates) ?>'
                      overview-url="<?php echo $controller->link_for('videos') ?>"
                      store-url="<?php echo $controller->link_for('videos/store_vimeo') ?>"
                      initialize-upload-url="<?php echo $controller->link_for('vimeo/initialize_upload') ?>">
    </edit-vimeo-video>
</div>

<script>
    new Vue({
        el: '#edit-vimeo-video'
    })
</script>
