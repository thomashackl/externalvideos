<template>
    <form class="default" ref="form" :action="storeUrl" method="post" @submit.prevent="uploadVideo">
        <fieldset v-if="!video.id">
            <legend>
                Videodatei
            </legend>
            <section>
                <input type="file" id="video" accept="video/*" @change="setFiles">
            </section>
        </fieldset>
        <fieldset>
            <legend>
                Grunddaten
            </legend>
            <section>
                <label for="title">
                    <span class="required">Name</span>
                </label>
                <input type="text" name="title" id="title" maxlength="255"
                       placeholder="Name des Videos eingeben" :value="video.title">
            </section>
            <section v-if="!video.id">
                <label for="description">
                    <span class="required">Beschreibung</span>
                </label>
                <textarea name="description" id="description" maxlength="1024"
                          placeholder="Beschreibungstext des Videos eingeben" :value="video.description"></textarea>
            </section>
        </fieldset>
        <fieldset>
            <legend>
                Sichtbarkeit in Stud.IP
            </legend>
            <section class="col-3">
                <label for="visible-from" class="undecorated">
                    von
                </label>
                <input type="text" name="visible_from" id="visible-from" maxlength="15"
                       placeholder="unbegrenzt"
                       v-model="video.visible_from"
                       data-datetime-picker>
            </section>
            <section class="col-3">
                <label for="visible-until" class="undecorated">
                    bis
                </label>
                <input type="text" name="visible_until" id="visible-until" maxlength="15"
                       placeholder="unbegrenzt"
                       v-model="video.visible_until"
                       data-datetime-picker='{">=":"#visible-from"}'>
            </section>
        </fieldset>
        <fieldset v-if="dates.length > 0">
            <legend>
                Zuordnung zu Veranstaltungsterminen
            </legend>
            <section>
                <label for="dates">
                    Vorhandene Termine
                </label>
                <select name="dates[]" id="dates" class="nested-select">
                    <option value="">
                        -- keinem Termin zuordnen --
                    </option>
                    <option v-for="date in dates" :key="date.id" :value="date.id">{{ date.name }}</option>
                </select>
            </section>
        </fieldset>
        <footer data-dialog-button>
            <studip-button name="store" class="accept" label="Speichern" :prevent-default="false"></studip-button>
            <studip-link-button name="cancel" class="cancel" label="Abbrechen" :href="overviewUrl"></studip-link-button>
        </footer>
    </form>
</template>

<script>
    import bus from 'jsassets/bus'
    import StudipButton from './StudipButton'
    import StudipLinkButton from './StudipLinkButton'
    import * as tus from 'tus-js-client'
    import VimeoUploadStatus from './VimeoUploadStatus'
    var UploadClass = Vue.extend(VimeoUploadStatus)

    export default {
        name: 'EditVimeoVideo',
        components: {
            StudipButton,
            StudipLinkButton
        },
        props: {
            video: {
                type: Object,
                default: () => {}
            },
            dates: {
                type: Array,
                default: () => []
            },
            overviewUrl: {
                type: String,
            },
            storeUrl: {
                type: String
            },
            initializeUploadUrl: {
                type: String
            }
        },
        data() {
            return {
                files: []
            }
        },
        methods: {
            setFiles: function(event) {
                this.files = event.target.files
                if (!this.video.title) {
                    let filename = this.files[0].name
                    this.video.title = filename.substring(0, filename.lastIndexOf('.')) || filename
                }
            },
            uploadVideo: function(event) {
                if (this.video.id) {
                    this.$refs.form.submit()
                } else {
                    event.preventDefault()

                    let upload = new UploadClass({
                        propsData: {
                            initializeUploadUrl: this.initializeUploadUrl,
                            files: document.querySelector('#video').files
                        }
                    })
                    upload.$mount()
                    this.$el.insertBefore(upload.$el, null)

                    bus.$on('vimeo-upload-success', (video) => {
                        upload.$el.remove()
                        upload.$destroy()
                        const id = document.createElement('input')
                        id.setAttribute('type', 'hidden')
                        id.setAttribute('name', 'external_id')
                        id.setAttribute('value', video.id)
                        this.$refs.form.appendChild(id)
                        const url = document.createElement('input')
                        url.setAttribute('type', 'hidden')
                        url.setAttribute('name', 'url')
                        url.setAttribute('value', video.url)
                        this.$refs.form.appendChild(url)
                        this.$refs.form.submit()
                    })
                    bus.$on('vimeo-upload-error', (videoId) => {
                        console.log('Upload failed, Video ID is ' + videoId + '.')
                    })
                }
            }
        }
    }
</script>
