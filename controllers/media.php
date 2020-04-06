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
        Navigation::activateItem('/course/mediacontent/media');

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . dgettext('mediacontent', 'Medien'));
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/mediacontent.js');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/mediacontent.css');

        $media = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id) ?
                ExternalMediaFile::findByCourse_id($this->course->id) :
                ExternalMediaFile::findByCourseAndVisibility($this->course->id);

        $this->assigned_dates = [];
        foreach ($media as $medium) {
            if (count($medium->dates) > 0) {

                $date = $medium->dates->first();

                if (!is_array($this->assigned_dates[$date->date_id])) {
                    $this->assigned_dates[$date->date_id] = [
                        'id' => $date->date_id,
                        'name' => $date->datename,
                        'media' => []
                    ];
                }

                $this->assigned_dates[$date->date_id]['media'][] = [
                    'id' => $medium->id,
                    'url' => $medium->url,
                    'title' => $medium->title,
                    'visible_from' => $medium->visible_from != null ? $medium->visible_from->format('d.m.Y H:i') : null,
                    'visible_until' => $medium->visible_until != null ? $medium->visible_until->format('d.m.Y H:i') : null
                ];

            } else {

                if (!is_array($this->assigned_dates[''])) {
                    $this->assigned_dates[''] = [
                        'id' => '',
                        'name' => dgettext('mediacontent', 'Keinem Termin zugeordnet'),
                        'media' => []
                    ];
                }

                $this->assigned_dates[$date->date_id]['media'][] = [
                    'id' => $medium->id,
                    'url' => $medium->url,
                    'title' => $medium->title,
                    'visible_from' => $medium->visible_from != null ? $medium->visible_from->format('d.m.Y H:i') : null,
                    'visible_until' => $medium->visible_until != null ? $medium->visible_until->format('d.m.Y H:i') : null
                ];

            }
        }

        $this->permission = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id);

        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            $sidebar = Sidebar::get();
            $actions = new ActionsWidget();
            $actions->addLink(dgettext('mediacontent', 'Audio/Video über Link hinzufügen'),
                $this->link_for('media/edit'),
                Icon::create('add'))->asDialog('size="auto"');
            $sidebar->addWidget($actions);
        }
    }

    public function edit_action($id = 0)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle($id == 0 ?
            dgettext('mediacontent', 'Audio/Video hinzufügen') :
            dgettext('mediacontent', 'Audio/Video bearbeiten'));

        if ($id != 0) {
            $this->medium = ExternalMediaFile::find($id);
        } else {
            $this->medium = new ExternalMediaFile();
        }

        if (count($this->medium->dates) > 0) {
            $this->selected_dates = $this->medium->dates->pluck('date_id');
        } else {
            $this->selected_dates = [];
        }

        $this->dates = [];
        foreach ($this->course->dates as $date) {
            $name = date('d.m.Y H:i', $date->date) . ' - ' .
                date('H:i', $date->end_time);
            if ($room = $date->getRoomName()) {
                $name .= ' (' . $room . ')';
            }

            $this->dates[$date->id] = $name;
        }
    }

    public function store_action($id = 0)
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($id != 0) {
            $medium = ExternalMediaFile::find($id);
        } else {
            $medium = new ExternalMediaFile();
            $medium->mkdate = date('Y-m-d H:i:s');
        }
        $medium->course_id = $this->course->id;
        $medium->user_id = $GLOBALS['user']->id;
        $medium->title = Request::get('title');
        $medium->url = Request::get('url');

        $medium->visible_from = Request::get('visible_from') ? new DateTime(Request::get('visible_from')) : null;
        $medium->visible_until = Request::get('visible_until') ? new DateTime(Request::get('visible_until')) : null;

        $medium->chdate = date('Y-m-d H:i:s');

        $newDates = new SimpleCollection();
        foreach (Request::getArray('dates') as $date) {
            if ($date != '') {
                $found = null;
                if (!$medium->isNew()) {
                    $found = $medium->dates->findOneBy('date_id', $date);
                }
                if ($found) {
                    $newDates->append($found);
                } else {
                    $assign = new ExternalMediaFileDate();
                    $assign->date_id = $date;
                    $assign->mkdate = date('Y-m-d H:i:s');
                    $newDates->append($assign);
                }
            }
        }

        $medium->dates = $newDates;

        if ($medium->store()) {
            PageLayout::postSuccess(
                dgettext('mediacontent','Das Medium wurde gespeichert.'));
        } else {
            PageLayout::postError(
                dgettext('mediacontent','Das Medium konnte nicht gespeichert werden.'));
        }

        $this->relocate('media');
    }

    public function delete_action($id = 0) {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            throw new AccessDeniedException();
        }

        $medium = ExternalMediaFile::find($id);

        if ($medium->delete()) {
            PageLayout::postSuccess(
                dgettext('mediacontent','Das Medium wurde gelöscht.'));
        } else {
            PageLayout::postError(
                dgettext('mediacontent','Das Medium konnte nicht gelöscht werden.'));
        }

        $this->relocate('media');
    }

    public function get_src_action($id)
    {
        $medium = ExternalMediaFile::find($id);
        $this->render_json($medium->getVideoSource());
    }

}
