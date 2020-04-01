<template>
    <studip-messagebox v-if="media.length < 1" message="Es sind noch keine Medien vorhanden!"
                        type="info"></studip-messagebox>
    <article v-else>
        <media-file v-for="medium in media" :key="medium.id" :medium="medium"></media-file>
    </article>
</template>

<script>
    import StudipMessagebox from './StudipMessagebox'
    import MediaFile from './MediaFile'

    export default {
        name: 'MediaList',
        components: {
            StudipMessagebox,
            MediaFile
        },
        props: {
            media: {
                type: Array,
                default: () => []
            }
        }
    }
</script>
