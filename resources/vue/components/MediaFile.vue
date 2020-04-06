<template>
    <section class="medium" :id="'medium-' + medium.id">
        <header>
            {{ medium.title }}
            <template v-if="medium.visible_from != null || medium.visible_until != null">
                <template v-if="medium.visible_from != null && medium.visible_until == null">
                    (sichtbar ab {{ medium.visible_from }})
                </template>
                <template v-if="medium.visible_from == null && medium.visible_until != null">
                    (sichtbar bis {{ medium.visible_until }})
                </template>
                <template v-if="medium.visible_from != null && medium.visible_until != null">
                    (sichtbar von {{ medium.visible_from }} bis {{ medium.visible_until }})
                </template>
            </template>
            <nav class="actions">
                <a v-if="sourceChecked" title="Video ausblenden" @click="closeVideo">
                    <studip-icon shape="decline" size="16" role="info_alt"></studip-icon>
                </a>
                <template v-if="isLecturer">
                    <a :href="realEditUrl" title="Bearbeiten" data-dialog="size=auto">
                        <studip-icon shape="edit" size="16" role="info_alt"></studip-icon>
                    </a>
                    <a :href="realDeleteUrl" title="Löschen" data-confirm="Wollen Sie das Medium wirklich löschen?">
                        <studip-icon shape="trash" size="16" role="info_alt"></studip-icon>
                    </a>
                </template>
            </nav>
        </header>
        <div class="video-error" v-if="playError">
            <a :href="medium.url" title="Link öffnen" target="_blank">
                Kann das Video nicht abgespielt werden?
                Klicken Sie hier, um den Link in einem Fenster/Tab zu öffnen.
            </a>
        </div>
        <div class="play-me">
            <a v-if="!sourceChecked && !loading" @click="getMediumSrc">
                <studip-icon shape="play" size="48"
                             title="Video abspielen"></studip-icon>
                <div>Video abspielen</div>
            </a>
            <div v-if="loading" class="loading">
                Daten werden geladen...
            </div>
        </div>
        <a v-if="sourceChecked && options.sources.length == 0" :href="medium.url" title="Abspielen" target="_blank">
            <studip-icon shape="link-extern" size="64"></studip-icon>
            <br>
            Leider kann das Medium nicht automatisch angezeigt werden.
            <br>
            Klicken Sie hier, um den Link in einem Fenster/Tab zu öffnen.
        </a>
    </section>
</template>

<script>
    import StudipIcon from './StudipIcon'
    import videojs from 'video.js'

    export default {
        name: 'MediaFile',
        components: {
            StudipIcon
        },
        props: {
            medium: {
                type: Object
            },
            getSrcUrl: {
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
                isLecturer: this.editUrl != null && this.deleteUrl != null,
                player: null,
                options: {
                    autoplay: false,
                    controls: true,
                    sources: []
                },
                loading: false,
                sourceChecked: false,
                played: false,
                playError: false
            }
        },
        computed: {
            realSrcUrl: function() {
                if (this.getSrcUrl != null) {
                    return this.createUrlWithId(this.getSrcUrl)
                } else {
                    return null
                }
            },
            realEditUrl: function() {
                if (this.editUrl != null) {
                    return this.createUrlWithId(this.editUrl)
                } else {
                    return null
                }
            },
            realDeleteUrl: function() {
                if (this.deleteUrl != null) {
                    return this.createUrlWithId(this.deleteUrl)
                } else {
                    return null
                }
            },
        },
        beforeDestroy() {
            if (this.player) {
                this.player.dispose()
            }
        },
        methods: {
            getMediumSrc: function() {
                this.loading = true
                fetch(this.createUrlWithId(this.realSrcUrl)).then((response) => {
                    return response.json()
                }).then((json) => {
                    this.sourceChecked = true
                    if (json != null && json.src != null && json.type != null) {
                        this.options.sources.push({
                            src: json.src,
                            type: json.type,
                            fluid: true
                        })
                        let video = document.createElement('video')
                        video.classList.add('video-js')
                        this.$el.appendChild(video)
                        this.$el.querySelector('.play-me').style.display = 'none'
                        this.player = videojs(video, this.options)
                        this.player.on('play', (event) => {
                            this.played = true
                        })
                        this.player.on('error', (event) => {
                            this.playError = true
                        })
                    }
                    this.loading = false
                })
            },
            closeVideo: function() {
                this.player = null
                this.$el.querySelector('.video-js').remove()
                this.sourceChecked = false
                this.$el.querySelector('.play-me').style.display = null
            },
            createUrlWithId: function(url) {
                const parts = url.split('?')
                let fullUrl = parts[0] + '/' + this.medium.id
                if (parts.length > 1) {
                    fullUrl += '?' + parts[1]
                }
                return fullUrl
            }
        }
    }
</script>

<style lang="scss">
    .date {
        .medium {
            margin: 5px;

            header {
                background-color: #28497c;
                color: white;
                padding: 5px;

                nav {
                    float: right;
                }
            }

            .play-me {
                height: 54px;
                vertical-align: center;

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

                .loading {
                    font-size: large;
                    line-height: 54px;
                    text-align: center;
                }
            }

            .video-js {
                max-width: calc(100% - 10px);
                margin: 5px;
            }
        }
    }
</style>
