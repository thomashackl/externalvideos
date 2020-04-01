<?php

/**
 * Creates a config entry for using a local Chrome installation for scraping data.
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

class HeadlessChrome extends Migration {

    public function description()
    {
        return 'Creates a config entry for using a local Chrome installation for scraping data.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Provide config options for weekdays and times for planning and statistics
        Config::get()->create('MEDIACONTENT_CHROME_PATH', [
            'value' => '/usr/bin/chrome',
            'type' => 'string',
            'range' => 'global',
            'section' => 'mediacontent',
            'description' => 'Pfad zum Starten von Chrome'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entry.
        Config::get()->delete('MEDIACONTENT_CHROME_PATH');
    }

}
