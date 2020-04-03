<div id="media-content">
    <media-list :dates='<?php echo studip_json_encode($assigned_dates) ?>'
                get-media-src-url="<?php echo $controller->link_for('media/get_src') ?>"
        <?php if ($permission) : ?>
                edit-url="<?php echo $controller->link_for('media/edit') ?>"
                delete-url="<?php echo $controller->link_for('media/delete') ?>"
        <?php endif ?>
    ></media-list>
</div>

<script>
    new Vue({
        el: '#media-content'
    })
</script>
