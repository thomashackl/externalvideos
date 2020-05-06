<template>
    <section class="video" :id="'video-' + video.id" :class="[playing ? 'playing' : '']">
        <header>
            <div>
                {{ video.title }}
                <div class="visibility" v-if="video.visible_from != null || video.visible_until != null">
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
                    <a :href="realDeleteUrl" title="Löschen" data-confirm="Wollen Sie das Video wirklich löschen?">
                        <studip-icon shape="trash" size="16" role="info_alt"></studip-icon>
                    </a>
                </template>
            </nav>
        </header>
        <div :id="'vimeo-video-' + video.id"></div>
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
                playing: false
            }
        },
        mounted() {
            this.player = new Player('vimeo-video-' + this.video.id, this.options)

            this.player.on('play', () => {
                this.playing = true
            })
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
