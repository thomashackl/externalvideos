<div id="videos">
    <video-list :dates='<?php echo studip_json_encode($assigned_dates) ?>'
                get-video-src-url="<?php echo $controller->link_for('videos/get_src') ?>"
        <?php if ($permission) : ?>
                edit-url="<?php echo $controller->link_for('videos/edit_share') ?>"
                delete-url="<?php echo $controller->link_for('videos/delete') ?>"
        <?php endif ?>
    ></video-list>
</div>

<script>
    new Vue({
        el: '#videos'
    })
</script>
