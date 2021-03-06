<template>
    <studip-messagebox v-if="dates.length < 1" message="Es sind noch keine Medien vorhanden!"
                        type="info"></studip-messagebox>
    <div v-else>
        <div v-if="dateGrouping">
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
                <div v-if="openDates.includes(date.id)">
                    <template v-for="video in date.videos">
                        <shared-video v-if="video.type == 'share'" :key="video.id" :video="video"
                                      :get-src-url="getVideoSrcUrl" :edit-url="editUrl"
                                      :delete-url="deleteUrl"></shared-video>
                        <vimeo-video v-if="video.type == 'vimeo'" :key="video.id" :video="video"
                                     :edit-url="editUrl" :delete-url="deleteUrl"></vimeo-video>
                    </template>
                </div>
            </section>
        </div>
        <div v-else class="date">
            <template v-for="video in dates[0].videos">
                <shared-video v-if="video.type == 'share'" :key="video.id" :video="video"
                              :get-src-url="getVideoSrcUrl" :edit-url="editUrl"
                              :delete-url="deleteUrl"></shared-video>
                <vimeo-video v-if="video.type == 'vimeo'" :key="video.id" :video="video"
                             :edit-url="editUrl" :delete-url="deleteUrl"></vimeo-video>
            </template>
        </div>
    </div>
</template>

<script>
    import StudipMessagebox from './StudipMessagebox'
    import SharedVideo from './SharedVideo'
    import VimeoVideo from './VimeoVideo'
    import StudipIcon from './StudipIcon'

    export default {
        name: 'VideoList',
        components: {
            SharedVideo,
            StudipIcon,
            StudipMessagebox,
            VimeoVideo
        },
        props: {
            dates: {
                type: Object,
                default: () => {}
            },
            getVideoSrcUrl: {
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
        mounted() {
            const keys = Object.keys(this.dates)
            if (keys.length == 1) {
                this.openDates.push(keys[0])
            }
        },
        computed: {
            dateGrouping: function() {
                const keys = Object.keys(this.dates)
                if (keys.length == 1 && keys.filter(key => key != '0').length == 0) {
                    return false
                } else {
                    return true
                }
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
    @import "~video.js/src/css/video-js";

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

        & > div {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        .video {
            -moz-transition: width 300ms ease-in-out, height 300ms ease-in-out;
            -o-transition: width 300ms ease-in-out, height 300ms ease-in-out;
            -webkit-transition: width 300ms ease-in-out, height 300ms ease-in-out;
            border: 1px solid #28497c;
            height: 250px;
            margin: 5px;
            position: relative;
            transition: width 300ms ease-in-out, height 300ms ease-in-out;
            width: 300px;

            header {
                background-color: #28497c;
                color: white;
                display: flex;
                flex-direction: row;
                padding: 5px;

                div {
                    flex: 1;

                    div.visibility {
                        font-style: italic;
                        font-size: smaller;

                        img, svg {
                            vertical-align: bottom;
                        }
                    }
                }
            }

            iframe {
                height: unset !important;
            }

            &.playing {
                height: auto;
                width: 100%;

                iframe {
                    height: 100% !important;
                }
            }

            .play-me, .loading, .cannot-play, .in-progress {
                margin-top: 15px;
                padding: 10px;
                text-align: center;

                a {
                    cursor: pointer;

                    img, svg {
                        vertical-align: bottom;
                    }

                    div {
                        display: inline-block;
                        margin-bottom: 13px;
                    }
                }
            }

            .play-me {
                margin-top: calc(25% - 15px);
            }

            .loading {
                font-size: large;
                line-height: 54px;
                text-align: center;
            }

            .cannot-play, .in-progress {
                font-style: italic;
                font-size: small;
            }

            .video-js {
                max-width: calc(100% - 10px);
                margin: 5px;
            }

            &.hidden {
                border-color: mix(#28497c, #ffffff, 40%);

                header {
                    background-color: mix(#28497c, #ffffff, 40%);
                }
            }

            footer {
                background-color: #28497c;
                bottom: 0;
                color: white;
                flex-direction: row;
                font-size: smaller;
                font-style: italic;
                padding: 2px;
                padding-left: 5px;
                padding-right: 5px;
                position: absolute;
                width: calc(100% - 10px);
            }
        }
    }
</style>
