<div id="media-content">
    <media-list :media='<?php echo studip_json_encode($media) ?>'></media-list>
</div>

<script>
    new Vue({
        el: '#media-content'
    })
</script>
