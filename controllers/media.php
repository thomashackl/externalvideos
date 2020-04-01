<?php

/**
 * Class MediaController
 * Controller for listing and adding media to a course.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    MediaContent
 */

class MediaController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->current_plugin;

        $this->course = Course::findCurrent();

        if (!$GLOBALS['perm']->have_studip_perm('user', $this->course->id)) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();
    }

    /**
     * Show available media for this course.
     */
    public function index_action()
    {
        // Navigation handling.
        Navigation::activateItem('/course/mediacontent');

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . dgettext('mediacontent', 'Medien'));
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/mediacontent.js');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/mediacontent.css');

        $this->media = array_map(function($medium) {
            return $medium->toArray();
        },
        ExternalMediaFile::findByCourse_id($this->course->id));

        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            $sidebar = Sidebar::get();
            $actions = new ActionsWidget();
            $actions->addLink(dgettext('mediacontent', 'Audio/Video hinzufügen'),
                $this->link_for('media/add'),
                Icon::create('add'))->asDialog('size="auto"');
            $sidebar->addWidget($actions);
        }
    }

    public function add_action($id = 0)
    {
        PageLayout::setTitle(dgettext('mediacontent', 'Audio/Video hinzufügen'));
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/mediacontent.js');

        if ($id > 0) {
            $this->medium = ExternalMediaFile::find($id);
        } else {
            $this->medium = new ExternalMediaFile();
        }
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($id = Request::int('id', 0) != 0) {
            $medium = ExternalMediaFile::find($id);
        } else {
            $medium = new ExternalMediaFile();
            $medium->mkdate = date('Y-m-d H:i:s');
        }
        $medium->course_id = $this->course->id;
        $medium->user_id = $GLOBALS['user']->id;
        $medium->title = Request::get('title');
        $medium->sharelink = Request::get('url');

        $medium->chdate = date('Y-m-d H:i:s');

        if (($url = ExternalMediaFile::extractVideoSources(Request::get('url'))) != null) {
            $medium->mediumurl = $url;
        }

        if ($medium->store()) {
            PageLayout::postSuccess(
                dgettext('mediacontent','Das Medium wurde erfolgreich hinzugefügt.'));
        } else {
            PageLayout::postError(
                dgettext('mediacontent','Das Medium konnte nicht hinzugefügt werden.'));
        }

        $this->relocate('media');
    }

}
