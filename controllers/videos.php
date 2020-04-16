<?php

/**
 * Class VideosController
 * Controller for listing and adding videos to a course.
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

class VideosController extends AuthenticatedController {

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
     * Show available videos for this course.
     */
    public function index_action()
    {
        SimpleORMap::expireTableScheme();
        // Navigation handling.
        Navigation::activateItem('/course/videos/videos');

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . dgettext('videos', 'Medien'));
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/externalvideos.js');

        $videos = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id) ?
                ExternalVideo::findByCourse_id($this->course->id) :
                ExternalVideo::findByCourseAndVisibility($this->course->id);

        $this->assigned_dates = [];
        foreach ($videos as $video) {
            if (count($video->dates) > 0) {

                $date = $video->dates->first();

                if (!is_array($this->assigned_dates[$date->date_id])) {
                    $this->assigned_dates[$date->date_id] = [
                        'id' => $date->date_id,
                        'name' => $date->datename,
                        'videos' => []
                    ];
                }

                $this->assigned_dates[$date->date_id]['videos'][] = [
                    'id' => $video->id,
                    'url' => $video->url,
                    'title' => $video->title,
                    'visible_from' => $video->visible_from != null ? $video->visible_from->format('d.m.Y H:i') : null,
                    'visible_until' => $video->visible_until != null ? $video->visible_until->format('d.m.Y H:i') : null
                ];

            } else {

                if (!is_array($this->assigned_dates[''])) {
                    $this->assigned_dates[''] = [
                        'id' => '',
                        'name' => dgettext('videos', 'Keinem Termin zugeordnet'),
                        'videos' => []
                    ];
                }

                $this->assigned_dates['']['videos'][] = [
                    'id' => $video->id,
                    'url' => $video->url,
                    'title' => $video->title,
                    'visible_from' => $video->visible_from != null ? $video->visible_from->format('d.m.Y H:i') : null,
                    'visible_until' => $video->visible_until != null ? $video->visible_until->format('d.m.Y H:i') : null
                ];

            }
        }

        $this->permission = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id);

        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            $sidebar = Sidebar::get();
            $actions = new ActionsWidget();
            $actions->addLink(dgettext('videos', 'Video über Freigabelink hinzufügen'),
                $this->link_for('videos/edit_share'),
                Icon::create('add'))->asDialog('size="auto"');
            $sidebar->addWidget($actions);
        }
    }

    public function edit_share_action($id = 0)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle($id == 0 ?
            dgettext('videos', 'Video hinzufügen') :
            dgettext('videos', 'Video bearbeiten'));

        if ($id != 0) {
            $this->video = ExternalVideo::find($id);
        } else {
            $this->video = new ExternalVideo();
        }

        if (count($this->video->dates) > 0) {
            $this->selected_dates = $this->video->dates->pluck('date_id');
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

    public function store_share_action($id = 0)
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($id != 0) {
            $video = ExternalVideo::find($id);
        } else {
            $video = new ExternalVideo();
            $video->mkdate = date('Y-m-d H:i:s');
        }
        $video->course_id = $this->course->id;
        $video->user_id = $GLOBALS['user']->id;
        $video->title = Request::get('title');
        $video->url = Request::get('url');

        $video->visible_from = Request::get('visible_from') ? new DateTime(Request::get('visible_from')) : null;
        $video->visible_until = Request::get('visible_until') ? new DateTime(Request::get('visible_until')) : null;

        $video->chdate = date('Y-m-d H:i:s');

        $newDates = new SimpleCollection();
        foreach (Request::getArray('dates') as $date) {
            if ($date != '') {
                $found = null;
                if (!$video->isNew()) {
                    $found = $video->dates->findOneBy('date_id', $date);
                }
                if ($found) {
                    $newDates->append($found);
                } else {
                    $assign = new ExternalVideoDate();
                    $assign->date_id = $date;
                    $assign->mkdate = date('Y-m-d H:i:s');
                    $newDates->append($assign);
                }
            }
        }

        $video->dates = $newDates;

        if ($video->store()) {
            PageLayout::postSuccess(
                dgettext('videos','Das Video wurde gespeichert.'));
        } else {
            PageLayout::postError(
                dgettext('videos','Das Video konnte nicht gespeichert werden.'));
        }

        $this->relocate('videos');
    }

    public function delete_action($id = 0) {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $this->course->id)) {
            throw new AccessDeniedException();
        }

        $video = ExternalVideo::find($id);

        if ($video->delete()) {
            PageLayout::postSuccess(
                dgettext('videos','Das Medium wurde gelöscht.'));
        } else {
            PageLayout::postError(
                dgettext('videos','Das Medium konnte nicht gelöscht werden.'));
        }

        $this->relocate('videos');
    }

    public function get_src_action($id)
    {
        $video = ExternalVideo::find($id);
        $this->render_json($video->getVideoSource());
    }

}
