<template>
    <div>
        <div class="overlay"></div>
        <article>
            <header>
                <h2>Status</h2>
            </header>
            <div v-if="prepareStatus != ''" :key="prepareSuccess" :class="'messagebox messagebox_' + prepareSuccess">
                {{ prepareStatus }}
            </div>
            <div v-if="uploadStatus != ''" :key="uploadSuccess" :class="'messagebox messagebox_' + uploadSuccess">
                {{ uploadStatus }} {{ progress }} %
                <progress max="100" :value="progress"></progress>
            </div>
            <div v-if="moveStatus != ''" :key="moveSuccess" :class="'messagebox messagebox_' + moveSuccess">
                {{ moveStatus }}
            </div>
        </article>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'
    import VimeoAPI from 'jsassets/vimeo'
    import * as tus from 'tus-js-client'

    export default {
        name: 'VimeoUploadStatus',
        props: {
            initializeUploadUrl: {
                type: String
            },
            files: {
                type: Array,
                default: () => []
            }
        },
        data() {
            return {
                prepareStatus: '',
                prepareSuccess: 'warning',
                uploadStatus: '',
                uploadSuccess: 'warning',
                progress: 0,
                moveStatus: '',
                moveSuccess: 'warning'
            }
        },
        mounted() {

            const file = this.files[0]

            this.prepareStatus = 'Bereite den Video-Upload vor...'

            VimeoAPI.prepareUpload(
                document.querySelector('#title').value,
                document.querySelector('#description').value,
                file.size,
                document.querySelector('#password').value
            ).then((result) => {
                if (result !== null) {
                    this.prepareStatus += ' abgeschlossen'
                    this.prepareSuccess = 'success'
                    let videoId = result.uri.substring(result.uri.lastIndexOf('/') + 1)
                    let url = result.link
                    // Create a new tus upload
                    var upload = new tus.Upload(file, {
                        uploadUrl: result.upload.upload_link,
                        retryDelays: [0, 3000, 5000, 10000, 20000],
                        resume: true,
                        removeFingerprintOnSuccess: true,
                        headers: {
                            'Accept': VimeoAPI.VERSION_STRING
                        },
                        onSuccess: () => {
                            this.uploadSuccess = 'success'
                            this.moveStatus = 'Bewege Video in den entsprechenden Veranstaltungsordner'
                            VimeoAPI.moveVideoToCourseFolder(videoId)
                                .then((response) => {
                                    if (!response.ok) {
                                        this.moveSuccess = 'error'
                                        throw response
                                    }
                                    this.moveStatus += ' verschoben.'
                                    this.moveSuccess = 'success'
                                    setTimeout(() => {
                                        bus.$emit('vimeo-upload-success', {id: videoId, url: url})
                                    }, 500)
                                }).catch((error) => {
                                    this.moveStatus += ' fehlgeschlagen\n' + error.statusText
                                    this.moveSuccess = 'error'
                                    console.log('Error')
                                    console.log(error)
                                    bus.$emit('vimeo-upload-error', {id: videoId, url: url})
                                })
                        },
                        onError: (error) => {
                            this.uploadSuccess = 'error'
                            this.uploadStatus += ' Fehler.\n' + error.statusText
                            console.log('Error on tus upload')
                            console.log(error)
                        },
                        onProgress: (bytesUploaded, bytesTotal) => {
                            const percentage = (bytesUploaded / bytesTotal * 100).toFixed(0)
                            this.progress = percentage
                        }
                    })

                    this.uploadStatus = 'Video wird hochgeladen... '
                    upload.start()
                } else {
                    this.prepareSuccess = 'error'
                }
            })
        }
    }
</script>

<style lang="scss">
    .overlay {
        background-color: rgba(40, 73, 124, 0.5);
        height: 100%;
        left: 0;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 9998;
    }
    article {
        background-color: #ffffff;
        box-shadow: 2px 2px 10px #000000;
        left: 25%;
        position: absolute;
        top: calc(50% - 100px);
        width: 50%;
        z-index: 9999;

        header {
            background-color: #28497c;
            color: #ffffff;
            margin: 2px;
            padding: 5px;

            h2 {
                border: 0;
                color: #ffffff;
                margin: 0;
            }
        }

        div {
            font-weight: normal !important;
            margin: 10px !important;

            progress {
                background-color: #28497c;
                border: 1px solid #28497c;
                display: block;
                height: 25px;
                text-align: center;
                width: 100%;

                &::-webkit-progress-bar {
                    background-color: #ffffff;
                }
                &::-webkit-progress-value {
                    background-color: #28497c;
                }
            }
        }
    }
</style>
