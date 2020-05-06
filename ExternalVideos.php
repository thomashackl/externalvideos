<?php
/**
 * ExternalVideos.class.php
 *
 * Plugin for embedding video shares from external clouds.
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

class ExternalVideos extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('videos', realpath(__DIR__.'/locale'));

        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation = new Navigation(dgettext('videos', 'OAuth-Authentifizierung bei Vimeo'),
                PluginEngine::getURL($this, [], 'vimeo'));
            Navigation::addItem('/admin/locations/vimeo', $navigation);
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName()
    {
        return dgettext('videos', 'Videos');
    }

    public function getVersion()
    {
        $metadata = $this->getMetadata();
        return $metadata['version'];
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        return null;
    }

    public function getTabNavigation($course_id)
    {
        if ($GLOBALS['user']->id == 'nobody') {
            return [];
        }

        $videos = new Navigation($this->getDisplayName());
        $videos->addSubNavigation('videos', new Navigation($this->getDisplayName(),
            PluginEngine::getURL($this, [], 'videos')));

        return compact('videos');
    }

    /**
     * @see StudipModule::getMetadata()
     */
    public function getMetadata()
    {
        $data = parent::getMetadata();

        $data['summary'] = dgettext('videos', 'Einbindung von externen Videos aus Vimeo und Cloudfreigaben');
        $data['description'] = dgettext('videos', 'Hier können Sie eigene Videos auf mehrere Arten ' .
            'in Ihre Veranstaltungen einbinden. Laden Sie direkt aus Stud.IP Videos zu Vimeo hoch oder ' .
            'speichern Sie sie in einen Cloud wie OneDrive oder iCloud Drive und binden Sie die Freigaben ' .
            'direkt hier als Video ein. Sie können selbst festlegen, von wann bis wann ein Video für die ' .
            'Teilnehmenden sichtbar sein soll und es zur Strukturierung einem Veranstaltungstermin zuordnen.');
        $data['category'] = dgettext('videos', 'Medien');
        $data['keywords'] = dgettext('videos', 'Videos direkt aus Stud.IP zu Vimeo hochladen;' .
            'Videos aus Cloudfreigaben einbinden;zeitlich gesteuerte Sichtbarkeit;Zuordnung zu ' .
            'Veranstaltungsterminen');
        $data['icon'] = Icon::create('video', 'info');
        $data['screenshot'] = 'assets/images/Videoliste.jpg';

        return $data;
    }

    /**
     * @see StandardPlugin::getInfoTemplate()
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    public function perform($unconsumed_path) {
        $range_id = Request::option('cid', Context::get()->id);

        URLHelper::removeLinkParam('cid');
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'videos'
        );
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher->current_plugin = $this;
        $dispatcher->range_id       = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }

}