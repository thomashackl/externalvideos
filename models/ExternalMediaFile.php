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
 * @property string sharelink database column
 * @property string mediumurl database column
 * @property string mimetype database column
 * @property string title database column
 * @property int position database column
 * @property string course_id database column
 * @property string user_id database column
 * @property string visible_from database column
 * @property string visible_until database column
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
        $config['has_many']['dates'] = [
            'class_name' => 'ExternalMediaFileDate',
            'foreign_key' => 'file_id',
            'assoc_foreign_key' => 'file_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        $config['registered_callbacks']['before_store'][]     = 'cbDateTimeObject';
        $config['registered_callbacks']['after_store'][]      = 'cbDateTimeObject';
        $config['registered_callbacks']['after_initialize'][] = 'cbDateTimeObject';

        parent::configure($config);
    }

    /**
     * Finds all entries that belong to the given course and are visible right now.
     *
     * @param string $course_id
     */
    public static function findByCourseAndVisibility($course_id)
    {
        return self::findBySQL("`course_id` = :course
            AND (
                `visible_from` <= NOW() AND `visible_until` IS NULL
                OR `visible_from` IS NULL AND `visible_until` >= NOW()
                OR NOW() BETWEEN `visible_from` AND `visible_until`
            )
            ORDER BY `position`",
            ['course' => $course_id]);
    }

    /**
     * Visibilities are stored as strings to database (YYYY-MM-DD HH:ii:ss).
     * Internally, the model class uses DateTime objects for better handling.
     *
     * @param string $type the event
     */
    public function cbDateTimeObject($type)
    {
        foreach (words('visible_from visible_until') as $one) {
            if ($type === 'before_store' && $this->$one != null) {
                $this->$one = $this->$one->format('Y-m-d H:i:s');
            }
            if (in_array($type, ['after_initialize', 'after_store']) && $this->$one != null) {
                $this->$one = new DateTime($this->$one);
            }
        }
    }

    public function getVideoSource()
    {
        $cache = StudipCacheFactory::getCache();
        $cache_lifetime = null;

        if ($cached = $cache->read('external_media_file_' . $this->id)) {

            return $cached;

        } else {

            // OneDrive Business
            if (mb_strpos($this->url, 'my.sharepoint.com') !== false) {

                $data = [
                    'src' => mb_substr($this->url, 0, mb_strpos($this->url, '?')) . '?download=1',
                    'type' => 'video/mp4'
                ];

                // OneDrive Business links don't really change, keep in cache for one day.
                $cache_lifetime = 86400;

            // File is somewhere else: use Puppeteer
            } else {

                require_once(__DIR__ . '/../vendor/autoload.php');

                try {
                    $puppeteer = new Nesk\Puphpeteer\Puppeteer;

                    $browser = $puppeteer->launch(
                        ['executablePath' => Config::get()->MEDIACONTENT_CHROME_PATH]);

                    $page = $browser->newPage();
                    $page->goto($this->url);

                    // iCloud Drive
                    if (mb_strpos($this->url, 'icloud.com/iclouddrive') !== false) {

                        $page->waitForSelector('div.page-button-three div[role="button"]', ['timeout' => 5000]);

                        $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
                            document.querySelector('div.page-button-three div[role=\"button\"]').click()
                        "));

                        $page->waitForSelector('iframe', ['timeout' => 10000]);
                        $src = $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
                            return document.querySelector('iframe').src
                        "));

                        $type = 'video/mp4';

                        $data = [
                            'src' => $src,
                            'type' => $type
                        ];

                        // iCloud Drive links change periodically, keep in cache for one hour.
                        $cache_lifetime = 3600;

                    // iCloud Photos
                    } else if (mb_strpos($this->url, 'icloud.com/photos') !== false) {

                        $handle = $page->waitForSelector('iframe',
                            ['timeout' => 10000, 'visible' => true]);
                        $frame = $handle->contentFrame();
                        $frame->waitForSelector('.total');

                        $frame->click('.derivative-image-container');

                        $frame->waitForSelector('.pok-video-play-button',
                            ['timeout' => 10000, 'visible' => true]);

                        $page->keyboard->press('Space');

                        $frame->waitForSelector('.pok-video-container video',
                            ['timeout' => 10000, 'visible' => true]);

                        $video = $frame->querySelector('.pok-video-container video');

                        $data = [
                            'src' => $video->getProperty('src')->jsonValue(),
                            'type' => $video->getProperty('type')->jsonValue()
                        ];

                        // iCloud Photo links change periodically, keep in cache for three hours.
                        $cache_lifetime = 10800;

                    // Other links -> try to call via Puppeteer and extract video source.
                    } else {

                        $page->waitForSelector('video', ['timeout' => 5000]);

                        $matches = [];
                        preg_match('/(<video.+>)/', $page->content(), $matches);

                        // Get video source.
                        $start = mb_strpos($matches[1], 'src="');
                        $src = mb_substr($matches[1], $start, mb_strpos($matches[1], '"', $start + 5));
                        // Get video type.
                        $start = mb_strpos($matches[1], 'type="');
                        $type = mb_substr($matches[1], $start, mb_strpos($matches[1], '"', $start + 6));

                        $data = [
                            'src' => $src,
                            'type' => $type
                        ];

                        // Video links should be stable, keep in cache for one day.
                        $cache_lifetime = 86400;

                    }

                    $browser->close();

                } catch (Exception $e) {

                    $data = null;

                }

            }

            // Cache the direct video link.
            if ($cache_lifetime != null) {
                $cache->write('external_media_file_' . $this->id, $data, $cache_lifetime);
            }

            return $data;
        }
    }

}
