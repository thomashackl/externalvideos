<?php
/**
 * WhakamaherePlugin.class.php
 *
 * Plugin for semester room and time planning of courses.
 * Kudos to the Maori people for having such an awesome culture.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Whakamahere
 */

class MediaContentPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('mediacontent', realpath(__DIR__.'/locale'));

        // Plugin only available if there are corresponding permissions.
        if (Navigation::hasItem('/course')) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, [], 'media'));

            Navigation::addItem('/course/mediacontent', $navigation);
        }
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
