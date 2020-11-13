<template>
    <form class="default" ref="form" :action="createUrlWithId(storeUrl, '')" method="post"
          @submit.prevent="uploadVideo">
        <fieldset v-if="!video.id">
            <legend>
                <span class="required">Videodatei</span>
            </legend>
            <section>
                <input type="file" id="video" accept="video/mp4,video/x-m4v,video/*" @change="setFiles">
            </section>
        </fieldset>
        <fieldset v-if="video.id">
            <legend>
                Daten aus Vimeo
            </legend>
            <section>
                Link zum Video: {{ video.url }}
            </section>
            <section>
                <label for="embed">
                    Embed-Code:
                </label>
                <textarea id="embed">{{ video.embed }}</textarea>
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
                       placeholder="Name des Videos eingeben" v-model="title">
            </section>
            <section v-if="!video.id">
                <label for="description">
                    Beschreibung
                </label>
                <textarea name="description" id="description" maxlength="1024"
                          placeholder="Beschreibungstext des Videos eingeben" v-model="description"></textarea>
            </section>
            <section>
                <label for="password">
                    Passwort (optional)
                </label>
                <template v-if="video.is_mine || !video.id">
                    <input type="password" name="password" id="password" size="75" ref="passwordInput"
                           placeholder="Passwort zur Wiedergabe" v-model="password">
                    <studip-icon shape="visibility-visible" ref="showPassword" size="24" id="show-password-icon"
                                 @click="togglePasswordVisibility"></studip-icon>
                </template>
                <div id="password" v-else>
                    Dieses Video gehört einem anderen Vimeo-Account, daher kann hier kein Passwort gesetzt werden.
                </div>
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
                       placeholder="unbegrenzt" v-model="visibleFrom" data-datetime-picker>
            </section>
            <section class="col-3">
                <label for="visible-until" class="undecorated">
                    bis
                </label>
                <input type="text" name="visible_until" id="visible-until" maxlength="15"
                       placeholder="unbegrenzt" v-model="visibleUntil" data-datetime-picker='{">=":"#visible-from"}'>
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
                    <option v-for="date in dates" :key="date.id" :value="date.id"
                            :selected="video.dates.includes(date.id)">{{ date.name }}</option>
                </select>
            </section>
        </fieldset>
        <footer data-dialog-button>
            <studip-button name="store" class="accept" label="Speichern" :prevent-default="false"
                           :disabled="title == '' || (!video.id && files.length == 0)"></studip-button>
            <studip-link-button name="cancel" class="cancel" label="Abbrechen" :href="overviewUrl"></studip-link-button>
        </footer>
    </form>
</template>

<script>
    import bus from 'jsassets/bus'
    import StudipButton from './StudipButton'
    import StudipIcon from './StudipIcon'
    import StudipLinkButton from './StudipLinkButton'
    import * as tus from 'tus-js-client'
    import VimeoUploadStatus from './VimeoUploadStatus'
    import StudipMessagebox from "./StudipMessagebox";
    var UploadClass = Vue.extend(VimeoUploadStatus)

    export default {
        name: 'EditVimeoVideo',
        components: {
            StudipMessagebox,
            StudipButton,
            StudipIcon,
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
                files: [],
                title: this.video.title,
                description: this.video.description,
                password : this.video.password,
                visibleFrom: this.video.visible_from,
                visibleUntil: this.video.visible_until,
                copied: false
            }
        },
        methods: {
            setFiles: function(event) {
                this.files = event.target.files
                if (this.video.title == '') {
                    let filename = this.files[0].name
                    this.title = filename.substring(0, filename.lastIndexOf('.')) || filename
                }
            },
            togglePasswordVisibility: function(event) {
                const currentClass = this.$refs.passwordInput.getAttribute('type')
                this.$refs.passwordInput.setAttribute('type', currentClass == 'text' ? 'password' : 'text')
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
                        console.log('Upload failed, Video ID is ' + videoId.id + '.')
                    })
                }
            },
            createUrlWithId: function(url, addition) {
                const parts = url.split('?')
                let fullUrl = parts[0]
                if (addition != '') {
                    fullUrl += '_' + addition
                }
                if (this.video.id) {
                    fullUrl += '/' + this.video.id
                }
                if (parts.length > 1) {
                    fullUrl += '?' + parts[1]
                }
                return fullUrl
            }
        }
    }
</script>

<style lang="scss">
    form {
        fieldset {
            section {
                #show-password-icon {
                    left: -32px;
                    position: relative;
                    vertical-align: middle;
                }
            }
        }
    }
</style>
