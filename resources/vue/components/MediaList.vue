<template>
    <studip-messagebox v-if="dates.length < 1" message="Es sind noch keine Medien vorhanden!"
                        type="info"></studip-messagebox>
    <div v-else>
        <article v-for="date in dates" :key="date.id">
            <header>
                <h1>{{ date.name }}</h1>
            </header>
            <media-file v-for="medium in date.media" :key="medium.id" :medium="medium"
                        :get-src-url="getMediaSrcUrl" :edit-url="editUrl" :delete-url="deleteUrl"></media-file>
        </article>
    </div>
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
            dates: {
                type: Array,
                default: () => []
            },
            getMediaSrcUrl: {
                type: String
            },
            editUrl: {
                type: String,
                default: null
            },
            deleteUrl: {
                type: String,
                default: null
            }
        }
    }
</script>

<style lang="scss">
    article {
        border: 1px solid #d0d7e3;
        clear: both;
        display: flex;
        flex-wrap: wrap;
        margin-top: 10px;

        header {
            background-color: #e7ebf1;
            border-bottom: 1px solid #d0d7e3;
            color: #325182;
            flex: 0 0 100%;

            h1 {
                border-bottom: none;
                font-size: 16px;
            }
        }
    }
</style>
