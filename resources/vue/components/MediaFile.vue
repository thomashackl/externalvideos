<template>
    <section class="medium">
        <header>
            {{ medium.title }}
        </header>
        <video v-if="medium.mediumurl != null" ref="videoPlayer" :src="medium.mediumurl" controls></video>
        <studip-icon v-if="medium.mediumurl == null" shape="play" size="48" @click="openLink"></studip-icon>
        <footer>
            <a v-if="medium.mediumurl != null" :href="medium.sharelink" target="_blank">
                Wird das Video nicht abgespielt? Klicken Sie hier, um den originalen Link zu öffnen.
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
            }
        },
        data() {
            return {
                player: null,
                options: {
                    autoplay: false,
                    controls: true,
                    sources: [
                        {
                            src: this.medium.mediumurl,
                            type: "video/mp4",
                            fluid: true
                        }
                    ]
                }
            }
        },
        mounted() {
            if (this.medium.mediumurl != null) {
                let formData = new FormData()
                formData.append('id', this.medium.id)

                /*const sharelink = window.open(this.medium.sharelink, 'sharelink',
                    'height=100,location=no,menubar=no,resizable=no,scrollbars=no,status=no,titlebar=no,toolbar=no,width=100')

                setTimeout(() => {
                    this.player = videojs(this.$refs.videoPlayer, this.options)
                    sharelink.close()
                }, 3000)*/
            }
        },
        methods: {
            openLink: function() {
                window.open(this.medium.sharelink)
            }
        }
    }
</script>

<style lang="scss">
    .medium {
        border: 1px solid #28497c;
        float: left;
        height: 250px;
        margin: 5px;
        padding: 2px;
        position: relative;
        text-align: center;
        width: 300px;

        iframe {
            display: none;
            height: 20px;
            max-height: 20px;
            max-width: 20px;
            width: 20px;
        }

        header, footer {
            background-color: #28497c;
            color: white;
            padding: 5px;

            a {
                color: white;
            }
        }

        header {
            margin-bottom: 10px;
        }

        footer {
            bottom: 0;
            margin: 2px;
            margin-left: 0;
            padding-left: 0;
            position: absolute;
        }

        img, svg {
            margin-top: 30px;
        }

        video {
            max-height: 150px !important;
            max-width: 300px !important;
        }
    }
</style>
