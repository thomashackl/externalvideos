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

class VimeoOAuth extends Migration {

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
        // Client ID of Vimeo app
        Config::get()->create('VIMEO_CLIENT_ID', [
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'externalvideos',
            'description' => 'Client-ID der in Vimeo erstellten App'
        ]);
        // Client ID of Vimeo app
        Config::get()->create('VIMEO_CLIENT_SECRET', [
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'externalvideos',
            'description' => 'Client Secret der in Vimeo erstellten App'
        ]);
        // Authorization code as provided by Vimeo after OAuth authorization
        Config::get()->create('VIMEO_AUTHORIZATION_CODE', [
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'externalvideos',
            'description' => 'OAuth-Code bei erfolgter Authentifizierung'
        ]);
        // Authorization code as provided by Vimeo after OAuth authorization
        Config::get()->create('VIMEO_ACCESS_TOKEN', [
            'value' => '',
            'type' => 'string',
            'range' => 'global',
            'section' => 'externalvideos',
            'description' => 'Access Token zur Authentifizierung bei Vimeo'
        ]);
        // Callback URL for authentication requests
        Config::get()->create('VIMEO_CALLBACK_URL', [
            'value' => URLHelper::getLink('plugins.php/externalvideos/vimeo/callback'),
            'type' => 'string',
            'range' => 'global',
            'section' => 'externalvideos',
            'description' => 'Callback URL nach erfolgter Authentifizierung'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove config entries.
        foreach (words('CLIENT_ID CLIENT_SECRET AUTHORIZATION_CODE ACCESS_TOKEN CALLBACK_URL') as $entry) {
            Config::get()->delete('VIMEO_' . $entry);
        }
    }

}
