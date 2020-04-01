<?php

/**
 * ExternalMediaFile.php
 * model class for external media files that shall be integrated in Stud.IP courses.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    MediaContent
 *
 * @property int file_id database column
 * @property string title database column
 * @property string course_id database column
 * @property string user_id database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ExternalMediaFile extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'external_media_files';
        $config['has_one']['creator'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];

        parent::configure($config);
    }

    public static function extractVideoSources($url)
    {

        // OneDrive Business
        if (mb_strpos($url, 'my.sharepoint.com') !== false) {

            require_once(__DIR__ . '/../vendor/autoload.php');

            try {
                $puppeteer = new Nesk\Puphpeteer\Puppeteer;

                $browser = $puppeteer->launch(['executablePath' => Config::get()->MEDIACONTENT_CHROME_PATH]);

                $page = $browser->newPage();
                $page->goto($url);
                $page->waitForSelector('video', ['timeout' => 5000]);
                $pageUrl = $page->url();
                $content = $page->content();

                $matches = [];
                preg_match('/<video.*src="(.*)".*<\/video>/', $page->content(), $matches);

                /*$start = mb_strpos($pageUrl, 'id=') + 3;
                $end = mb_strpos($pageUrl, '&', $start);

                $domain = mb_substr($pageUrl, 0, mb_strpos($pageUrl, '/', 8));*/

                //return $domain . urldecode(mb_substr($pageUrl, $start, ($end - $start)));

                $browser->close();

                return $matches[1];

            } catch (Exception $e) {
                return null;
            }

        } else {
            return null;
        }
    }

}
