<?php

/**
 * Class VimeoAPI
 * Helper class for collecting communication with Vimeo API in one place.
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

require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

use Vimeo\Vimeo;

class VimeoAPI {

    // Some random value for verifying auth state.
    const STATE = 'Stud.IP iz in da house!';
    // Needed scope entries for generated access token
    const SCOPE = 'create delete edit interact public private upload video_files';

    /**
     * Creates the correct URL for OAuth authentication.
     *
     * @param string $clientId ID of your Vimeo app
     * @param string $secret Secret of your Vimeo app
     * @return string Vimeo URL to call for OAuth2
     */
    public static function getOAuthUrl($clientId, $secret)
    {
        Config::get()->store('VIMEO_CLIENT_ID', $clientId);
        Config::get()->store('VIMEO_CLIENT_SECRET', $secret);

        $vimeo = new Vimeo($clientId, $secret);

        return $vimeo->buildAuthorizationEndpoint(
            Config::get()->VIMEO_CALLBACK_URL,
            words(self::SCOPE),
            hash(in_array('sha256', hash_algos()) ? 'sha256' : 'md5', self::STATE)
        );
    }

    public static function getAccessToken()
    {
        $vimeo = new Vimeo(Config::get()->VIMEO_CLIENT_ID, Config::get()->VIMEO_CLIENT_SECRET);

        $params = [
            'grant_type' => 'authorization_code',
            'code' => Config::get()->VIMEO_AUTHORIZATION_CODE,
            'redirect_uri' => Config::get()->VIMEO_CALLBACK_URL,
            'scope' => self::SCOPE
        ];

        $response = $vimeo->request(Vimeo::ACCESS_TOKEN_ENDPOINT, $params, 'POST');

        // Yay, we got an access code! Write it to global config as fast as possible!
        if ($response['status'] == 200) {
            Config::get()->store('VIMEO_ACCESS_TOKEN', $response['body']['access_token']);

            return true;
        } else {
            return false;
        }
    }

    public static function prepareFileUpload($name, $description, $filesize, $password)
    {
        $vimeo = new Vimeo(
            Config::get()->VIMEO_CLIENT_ID,
            Config::get()->VIMEO_CLIENT_SECRET,
            Config::get()->VIMEO_ACCESS_TOKEN
        );

        $body = [
            'upload' => [
                'approach' => 'tus',
                'size' => $filesize
            ],
            'name' => $name,
            'description' => $description
        ];

        if ($password != '') {
            $body['password'] = $password;
            $body['privacy'] = ['view' => 'password'];
        }

        return $vimeo->request('/me/videos', $body, 'POST');
    }

    public static function createProject($name)
    {
        $vimeo = new Vimeo(
            Config::get()->VIMEO_CLIENT_ID,
            Config::get()->VIMEO_CLIENT_SECRET,
            Config::get()->VIMEO_ACCESS_TOKEN
        );

        $body = [
            'name' => $name
        ];

        return $vimeo->request('/me/projects', $body, 'POST');
    }

    public static function addVideoToProject($videoId, $projectId)
    {
        $vimeo = new Vimeo(
            Config::get()->VIMEO_CLIENT_ID,
            Config::get()->VIMEO_CLIENT_SECRET,
            Config::get()->VIMEO_ACCESS_TOKEN
        );

        return $vimeo->request('/me/projects/' . $projectId . '/videos/' . $videoId, [], 'PUT');
    }

    public static function getOEmbed($url)
    {
        $params = [
            'url' => $url
        ];

        $query = http_build_query($params);
        $target = 'https://vimeo.com/api/oembed.json?' . $query;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $target,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ]);

        $response = json_decode(curl_exec($curl));
        $error = curl_error($curl);
        $statuscode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        return [
            'response' => $response,
            'statuscode' => $statuscode
        ];
    }

}
