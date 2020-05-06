<template>
    <section class="video" :id="'video-' + video.id" :class="[playing ? 'playing' : '']">
        <header>
            <div>
                {{ video.title }}
                <div class="visibility">
                    <studip-icon shape="visibility-visible" role="info_alt"></studip-icon>
                    <template v-if="video.visible_from != null && video.visible_until == null">
                        ab {{ video.visible_from }}
                    </template>
                    <template v-if="video.visible_from == null && video.visible_until != null">
                        bis {{ video.visible_until }}
                    </template>
                    <template v-if="video.visible_from != null && video.visible_until != null">
                        {{ video.visible_from }} bis {{ video.visible_until }}
                    </template>
                    <template v-if="video.visible_from == null && video.visible_until == null">
                        unbegrenzt
                    </template>
                </div>
            </div>
            <nav class="actions">
                <a v-if="sourceChecked && !playError" title="Video ausblenden" @click="closeVideo">
                    <studip-icon shape="decline" size="16" role="info_alt"></studip-icon>
                </a>
                <template v-if="isLecturer">
                    <a :href="realEditUrl" title="Bearbeiten" data-dialog="size=auto">
                        <studip-icon shape="edit" size="16" role="info_alt"></studip-icon>
                    </a>
                    <a :href="realDeleteUrl" title="Löschen" data-confirm="Wollen Sie das Video wirklich löschen?">
                        <studip-icon shape="trash" size="16" role="info_alt"></studip-icon>
                    </a>
                </template>
            </nav>
        </header>
        <div v-if="!sourceChecked && !loading" class="play-me">
            <a @click="getVideoSrc">
                <studip-icon shape="play" size="48"
                             title="Video abspielen"></studip-icon>
                <div>Video abspielen</div>
            </a>
        </div>
        <div v-if="loading" class="loading">
            Daten werden geladen...
        </div>
        <div v-if="(sourceChecked && options.sources.length == 0) || playError" class="cannot-play">
            <a :href="video.url" title="Abspielen" target="_blank">
                <studip-icon shape="link-extern" size="48"></studip-icon>
                <div>
                    Leider kann das Video nicht automatisch abgespielt werden.
                    Klicken Sie hier, um den Link in einem Fenster/Tab zu öffnen.
                </div>
            </a>
        </div>
    </section>
</template>

<script>
    import StudipIcon from './StudipIcon'
    import videojs from 'video.js'

    export default {
        name: 'SharedVideo',
        components: {
            StudipIcon
        },
        props: {
            video: {
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
                playing: false,
                playError: false
            }
        },
        computed: {
            realSrcUrl: function() {
                if (this.getSrcUrl != null) {
                    return this.createUrlWithId(this.getSrcUrl, '')
                } else {
                    return null
                }
            },
            realEditUrl: function() {
                if (this.editUrl != null) {
                    return this.createUrlWithId(this.editUrl, this.video.type)
                } else {
                    return null
                }
            },
            realDeleteUrl: function() {
                if (this.deleteUrl != null) {
                    return this.createUrlWithId(this.deleteUrl, '')
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
            getVideoSrc: function() {
                this.loading = true
                fetch(this.createUrlWithId(this.realSrcUrl)).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                    return response.json()
                }).then((json) => {
                    this.sourceChecked = true
                    if (json != null && json.src != null) {
                        let source = {
                            src: json.src,
                            fluid: true
                        }
                        if (json.type != null) {
                            source.type = json.type
                        }
                        this.options.sources.push(source)
                        let video = document.createElement('video')
                        video.classList.add('video-js')
                        video.setAttribute('src', json.src)
                        video.setAttribute('controls', true)
                        if (json.type != null) {
                            video.setAttribute('type', json.type)
                        }
                        this.$el.appendChild(video)
                        this.player = videojs(video, this.options)
                        this.playing = true
                        this.player.on('error', (event) => {
                            this.playError = true
                            this.playing = false
                            this.player = null
                            this.$el.querySelector('.video-js').remove()
                        })
                    }
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    this.sourceChecked = true
                })
            },
            closeVideo: function() {
                this.player = null
                const video = this.$el.querySelector('.video-js')
                if (video != null) {
                    video.remove()
                }
                this.sourceChecked = false
                this.loading = false
                this.playing = false
            },
            createUrlWithId: function(url, addition) {
                const parts = url.split('?')
                let fullUrl = parts[0]
                if (addition != '') {
                    fullUrl += '_' + addition
                }
                fullUrl += '/' + this.video.id
                if (parts.length > 1) {
                    fullUrl += '?' + parts[1]
                }
                return fullUrl
            }
        }
    }
</script>
