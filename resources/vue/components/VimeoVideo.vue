<template>
    <section class="video" :id="'video-' + video.id" :class="[playing ? 'playing' : '', video.visible ? '' : 'hidden']">
        <header ref="header">
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
                    <template v-if="!video.visible">
                        (wird nicht angezeigt)
                    </template>
                </div>
            </div>
            <nav class="actions">
                <a v-if="playing" title="Video ausblenden" @click="stopVideo">
                    <studip-icon shape="decline" size="16" role="info_alt"></studip-icon>
                </a>
                <template v-if="isLecturer">
                    <a :href="realEditUrl" title="Bearbeiten">
                        <studip-icon shape="edit" size="16" role="info_alt"></studip-icon>
                    </a>
                    <a :href="realDeleteUrl" title="Löschen" data-dialog="size=auto">
                        <studip-icon shape="trash" size="16" role="info_alt"></studip-icon>
                    </a>
                </template>
            </nav>
        </header>
        <div :id="'vimeo-video-' + video.id" ref="playerContainer"
             :class="inProgress ? 'in-progress' : (playError ? 'cannot-play' : '')">
            <template v-if="inProgress">
                <img :src="assetsUrl + 'images/ajax-indicator-black.svg'" height="48" width="48">
                <div>
                    Das Video wird gerade auf Vimeo vorbereitet, bitte haben Sie noch etwas Geduld.
                </div>
            </template>
            <template v-if="playError">
                <studip-icon shape="decline" role="info" width="32" height="32"></studip-icon>
                <div>
                    Das Video wurde nicht auf Vimeo gefunden oder kann nicht abgespielt werden.
                </div>
            </template>
        </div>
    </section>
</template>

<script>
    import StudipIcon from './StudipIcon'
    import Player from '@vimeo/player'

    export default {
        name: 'VimeoVideo',
        components: {
            StudipIcon
        },
        props: {
            video: {
                type: Object
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
                options: {
                    url: this.video.url,
                    byline: false,
                    responsive: true,
                    speed: true,
                    title: false
                },
                player: null,
                playing: false,
                playError: false,
                inProgress: false,
                assetsUrl: STUDIP.ASSETS_URL
            }
        },
        mounted() {
            if (this.video.status == 'in_progress' || this.video.status == 'transcoding' ||
                    this.video.status == 'transcode_starting') {
                this.inProgress = true
            } else {
                this.player = new Player('vimeo-video-' + this.video.id, this.options)
                this.player.ready()
                    .then((response) => {
                    }).catch((error) => {
                    this.playError = true
                })

                this.player.on('play', () => {
                    this.playing = true
                })
            }
        },
        computed: {
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
        methods: {
            stopVideo: function() {
                if (this.player != null) {
                    this.player.pause()
                    this.playing = false
                }
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
