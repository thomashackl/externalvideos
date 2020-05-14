let VimeoAPI = {
    BASE_URL: 'https://api.vimeo.com',
    VERSION_STRING: 'application/vnd.vimeo.*+json; version=3.4',
    USER_AGENT: 'vimeo.php 3.0.5; (http://developer.vimeo.com/api/docs)',
    CONTENT_TYPE: 'application/json',
    PLUGIN_URL: 'plugins.php/externalvideos',
    prepareUpload: async function(name, description, fileSize, password) {

        // Prepare file upload
        const initialize = STUDIP.URLHelper.getURL(this.PLUGIN_URL + '/vimeo/initialize_upload')

        let formData = new FormData();
        formData.append('name', name)
        formData.append('description', description)
        if (password != '') {
            formData.append('password', password)
        }
        formData.append('filesize', fileSize)

        // Call upload initialization to get a target URL for the video file
        const response = await fetch(initialize, {
            method: 'POST',
            body: formData
        })

        let json = null
        if (response.ok) {
            json = await response.json()
        } else {
            json = null
            console.log('Error on preparing video')
            console.log(response)
        }

        return json
    },
    moveVideoToCourseFolder: async function(videoId) {
        const initialize = STUDIP.URLHelper.getURL(this.PLUGIN_URL + '/vimeo/move_to_folder')

        let formData = new FormData()
        formData.append('video_id', videoId)

        // Call upload initialization to get a target URL for the video file
        return fetch(initialize, {
            method: 'POST',
            body: formData
        })
    }
}

export default VimeoAPI
