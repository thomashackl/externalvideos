<?php

/**
 * Class VimeoController
 * Helper Controller for stuff related to Vimeo API.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Videos
 */

class VimeoController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->current_plugin;

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();
    }

    /**
     * Show authentication form for Vimeo OAuth
     */
    public function index_action()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/admin/locations/vimeo');

        if (Config::get()->VIMEO_ACCESS_TOKEN) {
            PageLayout::postSuccess(dgettext('videos', 'Ihr Stud.IP ist bereits gegenüber Vimeo authentifiziert.'));
        }
    }

    /**
     * Redirects to Vimeo authentication page in order to perform OAuth call.
     */
    public function authorize_action()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        CSRFProtection::verifyUnsafeRequest();

        $this->relocate(VimeoAPI::getOAuthUrl(Request::get('client_id'), Request::get('client_secret')));
    }

    /**
     * Callback after successful OAuth authentication at Vimeo
     */
    public function callback_action()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        $state = hash(in_array('sha256', hash_algos()) ? 'sha256' : 'md5', VimeoAPI::STATE);

        // States match, success. Proceed to get auth token.
        if (Request::get('state') === $state) {

            // Store authorization code to global config.
            Config::get()->store('VIMEO_AUTHORIZATION_CODE', Request::get('code'));

            // Now that we have the authorization code, get an auth token that we can use subsequently.
            if (VimeoAPI::getAccessToken()) {
                PageLayout::postSuccess(
                    dgettext('videos', 'Dieses Stud.IP wurde erfolgreich bei Vimeo authentifiziert.'));
            } else {
                PageLayout::postError(
                    dgettext('videos', 'Dieses Stud.IP konnte nicht bei Vimeo authentifiziert werden.'));
            }


        // Something went wrong.
        } else {
            PageLayout::postInfo(dgettext('videos',
                'Der zurückgegebene Authentifizierungscode konnte nicht verifiziert werden.'));
        }

        $this->relocate('vimeo');
    }

    public function initialize_upload_action()
    {
        $course = Course::findCurrent();

        if (!$GLOBALS['perm']->have_studip_perm('dozent', $course->id)) {
            throw new AccessDeniedException();
        }

        // First check if folder needs to be created.
        if ($folder = VimeoFolder::findOneByCourse_id($course->id)) {

            $uploadLink = VimeoAPI::prepareFileUpload(Request::get('name'),
                Request::get('description'), Request::int('filesize'), Request::get('password'));

            $this->set_status($uploadLink['status']);
            $this->render_json($uploadLink['body']);

        } else {

            $vimeo = VimeoAPI::createProject(
                VimeoFolder::generateName($course)
            );

            // Folder created successfully
            if ($vimeo['status'] == 201) {

                $folder = new VimeoFolder();
                $folder->course_id = $course->id;

                $folder->vimeo_id = mb_substr($vimeo['body']['uri'], mb_strrpos($vimeo['body']['uri'], '/') + 1);
                $folder->mkdate = date('Y-m-d H:i:s');
                $folder->chdate = date('Y-m-d H:i:s');

                if ($folder->store()) {
                    $uploadLink = VimeoAPI::prepareFileUpload(Request::get('name'),
                        Request::get('description'), Request::int('filesize'), Request::get('password'));

                    $this->set_status($uploadLink['status']);
                    $this->render_json($uploadLink['body']);
                } else {
                    $this->set_status(500);
                    $this->render_json(['error' => dgettext('videos',
                        'Die Daten des Vimeo-Ordners konnten nicht in Stud.IP gespeichert werden.')]);
                }

            } else {
                $this->set_status($vimeo['status']);
                $this->render_json($vimeo['body']);
            }

        }

    }

    public function move_to_folder_action()
    {
        $course = Course::findCurrent();
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $course->id)) {
            throw new AccessDeniedException();
        }

        $folder = VimeoFolder::findOneByCourse_id($course->id);

        $result = VimeoAPI::addVideoToProject(Request::int('video_id'), $folder->vimeo_id);
        $this->set_status($result['status']);
        $this->render_json($result['body']);
    }

    public function get_video_action()
    {
        $cache = StudipCacheFactory::getCache();

        if ($cached = $cache->read('external-video-' . Request::int('video_id'))) {

            $result['status'] = 200;
            $result['body'] = studip_json_decode($cache);

        } else {

            $result = VimeoAPI::getVideo(Request::int('video_id'));

            if ($result['status'] == 200) {
                $cache->write('external-video-' . Request::int('video_id'),
                    studip_json_encode($result['body']), 86400);
            }

        }

        $this->set_status($result['status']);
        $this->render_json($result['body']);
    }

}
