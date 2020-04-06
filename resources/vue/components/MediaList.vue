<template>
    <studip-messagebox v-if="dates.length < 1" message="Es sind noch keine Medien vorhanden!"
                        type="info"></studip-messagebox>
    <div v-else>
        <section class="date" v-for="date in dates" :key="date.id" :id="date.id">
            <header>
                <h1>
                    <a href="" @click="toggleDate(date.id, $event)">
                        <studip-icon :shape="openDates.includes(date.id) ? 'arr_1down' : 'arr_1right'"
                                     size="20"></studip-icon>
                        {{ date.name }}
                    </a>
                </h1>
            </header>
            <template v-if="openDates.includes(date.id)">
                <media-file v-for="medium in date.media" :key="medium.id" :medium="medium"
                            :get-src-url="getMediaSrcUrl" :edit-url="editUrl" :delete-url="deleteUrl"></media-file>
            </template>
        </section>
    </div>
</template>

<script>
    import StudipMessagebox from './StudipMessagebox'
    import MediaFile from './MediaFile'
    import StudipIcon from './StudipIcon'

    export default {
        name: 'MediaList',
        components: {
            StudipIcon,
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
        },
        data() {
            return {
                openDates: []
            }
        },
        methods: {
            toggleDate: function(id, event) {
                event.preventDefault()
                if (this.openDates.includes(id)) {
                    for (let i = 0 ; i < this.openDates.length ; i++) {
                        if (this.openDates[i] == id) {
                            this.openDates.splice(i, 1)
                            i--
                        }
                    }
                } else {
                    this.openDates.push(id)
                }
            }
        }
    }
</script>

<style lang="scss">
    .date {
        border: 1px solid #d0d7e3;
        margin-bottom: 10px;
        -webkit-transition: all .3s ease 0s;
        transition: all .3s ease 0s;

        header {
            background-color: #e7ebf1;

            h1 {
                padding: 5px;
                margin: 0;
                color: #28497c;
                border-bottom: none;
                font-size: medium;

                a {
                    img, svg {
                        vertical-align: bottom;
                    }
                }
            }
        }
    }
</style>
