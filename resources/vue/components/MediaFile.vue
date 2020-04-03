<template>
    <section class="medium" :id="'medium-' + medium.id">
        <header>
            {{ medium.title }}
            <span v-if="editUrl != null && deleteUrl != null" class="actions">
                <a :href="realEditUrl" title="Bearbeiten" data-dialog="size=auto">
                    <studip-icon shape="edit" size="16" role="info_alt"></studip-icon>
                </a>
                <a :href="realDeleteUrl" title="Löschen" data-confirm="Wollen Sie das Medium wirklich löschen?">
                    <studip-icon shape="trash" size="16" role="info_alt"></studip-icon>
                </a>
            </span>
        </header>
        <template v-if="!sourceChecked">
            Daten werden geladen...
        </template>
        <a v-if="sourceChecked && options.sources.length == 0" :href="medium.url" title="Abspielen" target="_blank">
            <studip-icon shape="link-extern" size="64"></studip-icon>
            <br>
            Leider kann das Medium nicht automatisch angezeigt werden.
            <br>
            Klicken Sie hier, um den Link in einem Fenster/Tab zu öffnen.
        </a>
        <footer v-show="sourceChecked && options.sources.length > 0">
            <a v-if="sourceChecked && options.sources.length > 0" :href="medium.url" title="Link öffnen" target="_blank">
                Kann das Video nicht abgespielt werden?
                Klicken Sie hier, um den Link in einem Fenster/Tab zu öffnen.
            </a>
        </footer>
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
                player: null,
                options: {
                    autoplay: false,
                    controls: true,
                    sources: []
                },
                loading: false,
                sourceChecked: false
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
        mounted() {
            this.getMediumSrc()
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
                        this.$el.insertBefore(video, this.$el.querySelector('footer'))
                        this.player = videojs(video, this.options)
                    }
                    this.loading = false
                })
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
    .medium {
        border: 1px solid #28497c;
        height: 300px;
        margin: 5px;
        padding: 2px;
        position: relative;
        text-align: center;
        width: 300px;

        header, footer {
            background-color: #28497c;
            color: white;
            padding: 5px;

            a {
                color: white;
            }
        }

        header {
            margin-bottom: 5px;

            .actions {
                float: right;
            }
        }

        .video-js {
            max-height: 190px !important;
            max-width: 300px !important;
        }

        footer {
            bottom: 0;
            margin: 2px;
            margin-left: 0;
            margin-top: 5px;
            padding-left: 0;
            position: absolute;
        }

    }
</style>
