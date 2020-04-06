<?php
/**
 * MediaContentPlugin.class.php
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
 * @category    MediaContent
 */

class MediaContentPlugin extends StudIPPlugin implements StandardPlugin {

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('mediacontent', realpath(__DIR__.'/locale'));
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName()
    {
        return dgettext('mediacontent', 'Medieninhalte');
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

        $mediacontent = new Navigation($this->getDisplayName());
        $mediacontent->addSubNavigation('media', new Navigation($this->getDisplayName(),
            PluginEngine::getURL($this, [], 'media')));

        return compact('mediacontent');
    }

    /**
     * @see StudipModule::getMetadata()
     */
    public function getMetadata()
    {
        return [
            'summary' => dgettext('mediacontent', 'Einbindung von externen Videos aus Vimeo und Cloudfreigaben'),
            'description' => dgettext('mediacontent', 'Hiermit können Sie Videos einbinden, die Sie in externen Clouds wie OneDrive oder iCloud freigegeben haben.'),
            'category' => dgettext('mediacontent', 'Medien'),
            'icon' => Icon::create('play', 'info')
        ];
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
            'media'
        );
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher->current_plugin = $this;
        $dispatcher->range_id       = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }

}
