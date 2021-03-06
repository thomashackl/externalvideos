<?php

/**
 * ExternalVideoFile.php
 * model class for external videos files that shall be integrated in Stud.IP courses.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Videos
 *
 * @property int video_id database column
 * @property string type database column
 * @property string external_id database column
 * @property string url database column
 * @property string title database column
 * @property string password database column
 * @property int position database column
 * @property string range_id database column
 * @property string user_id database column
 * @property string visible_from database column
 * @property string visible_until database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ExternalVideo extends SimpleORMap
{

    private $vimeoData = null;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'external_videos';
        $config['has_one']['creator'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['institute'] = [
            'class_name' => 'Institute',
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'institut_id'
        ];
        $config['has_many']['dates'] = [
            'class_name' => 'ExternalVideoDate',
            'foreign_key' => 'video_id',
            'assoc_foreign_key' => 'video_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];

        $config['additional_fields']['vimeo_data'] = true;

        $config['registered_callbacks']['before_store'][]     = 'cbDateTimeObject';
        $config['registered_callbacks']['after_store'][]      = 'cbDateTimeObject';
        $config['registered_callbacks']['before_store'][]      = 'cbStoreVimeoData';
        $config['registered_callbacks']['after_initialize'][] = 'cbDateTimeObject';

        parent::configure($config);
    }

    /**
     * Finds all entries that belong to the given course and are visible right now.
     *
     * @param string $range_id
     */
    public static function findByRangeAndVisibility($range_id)
    {
        return self::findBySQL("`range_id` = :range
            AND (
                `visible_from` <= NOW() AND `visible_until` IS NULL
                OR `visible_from` IS NULL AND `visible_until` >= NOW()
                OR NOW() BETWEEN `visible_from` AND `visible_until`
                OR `visible_from` IS NULL AND `visible_until` IS NULL
            )
            ORDER BY `position`, `title`",
            ['range' => $range_id]);
    }

    /**
     * Fetch video data from Vimeo.
     *
     * @return array|null
     */
    public function getVimeo_data()
    {
        if ($this->type === 'vimeo' && $this->vimeoData == null) {
            $data = VimeoAPI::getVideo($this->external_id);

            if ($data['status'] == 200) {
                $this->vimeoData = $data['body'];
            }
        }
        return $this->vimeoData;
    }

    /**
     * Sets new Vimeo data for this video.
     *
     * @param array $data
     */
    public function setVimeo_data($data)
    {
        if ($this->type === 'vimeo') {
            $this->vimeo_data = $data;
        }
    }

    public function setPassword($password)
    {
        if ($password != '') {
            $this->vimeoData['privacy']['view'] = 'password';
            $this->vimeoData['password'] = $password;
        } else {
            $this->vimeoData['privacy']['view'] = 'unlisted';
            unset($this->vimeoData['password']);
        }
    }

    /**
     * Checks if the current video is visible for course participants now.
     */
    public function isVisible()
    {
        $start = $this->visible_from ?: new DateTime('01.01.1970 00:00:00');
        $end = $this->visible_until ?: new DateTime('31.12.2099 23:59:59');

        $now = new DateTime();

        return $now >= $start && $now <= $end;
    }

    public function hasMoreReferences()
    {
        return count(self::findBySQL("`external_id` = :extid AND `video_id` != :id",
                ['extid' => $this->external_id, 'id' => $this->id])) > 0;
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

    /**
     * Store data specific to Vimeo videos if applicable.
     *
     * @param string $type the event
     */
    public function cbStoreVimeoData($type)
    {
        if (!$this->isNew()) {
            if ($this->type === 'vimeo' && $this->vimeo_data != null) {
                $this->vimeoData['name'] = $this->title;
            }
            $result = VimeoAPI::updateVideo($this->external_id, $this->vimeoData);

            // Update video link from Vimeo.
            if ($result['status'] == 200) {
                $this->url = $result['body']['link'];
            }
        }
    }

    /**
     * Helper method for finding the actual video source file
     * contained in a cloud share link. This method makes
     * heavy use of the Puppeteer library for parsing websites.
     *
     * @return array|null
     */
    public function getVideoSource()
    {
        $cache = StudipCacheFactory::getCache();
        $cache_lifetime = null;

        if ($cached = $cache->read('external-video-' . $this->url)) {

            return $cached;

        } else {

            // Sync & Share (Powerfolder)
            if (mb_strpos($this->url, '/getlink/') !== false) {

                $data = [
                    'src' => str_replace('/getlink/', '/dl/', $this->url),
                    'type' => null
                ];

                // Keep in cache for one day, this link should be stable.
                $cache_lifetime = 86400;

            // File is somewhere else: use Puppeteer
            } else {

                require_once(__DIR__ . '/../vendor/autoload.php');

                try {
                    $puppeteer = new Nesk\Puphpeteer\Puppeteer;

                    $browser = $puppeteer->launch();

                    $page = $browser->newPage();
                    $page->goto($this->url);

                    // OneDrive Business
                    if (mb_strpos($this->url, 'my.sharepoint.com') !== false) {

                        $page->goto(mb_substr($this->url, 0, mb_strpos($this->url, '?')));

                        $page->waitForSelector('video', ['timeout' => 5000]);
                        $video = $page->querySelector('video source');

                        $src = $video->getProperty('src')->jsonValue();
                        $type = $video->getProperty('type')->jsonValue();

                        $data = [
                            'src' => $src,
                            'type' => $type
                        ];

                        // OneDrive Business links don't really change, keep in cache for one day.
                        $cache_lifetime = 86400;

                    // iCloud Drive
                    } else if (mb_strpos($this->url, 'icloud.com/iclouddrive') !== false) {

                        $page->waitForSelector('div.page-button-three div[role="button"]', ['timeout' => 5000]);

                        $page->click('div.page-button-three div[role="button"]');

                        $page->waitForSelector('iframe', ['timeout' => 10000]);
                        $src = $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
                            return document.querySelector('iframe').src
                        "));

                        $type = null;

                        $data = [
                            'src' => $src,
                            'type' => $type
                        ];

                        // iCloud Drive links change periodically, keep in cache for one hour.
                        $cache_lifetime = 3600;

                    // iCloud Photos
                    } else if (mb_strpos($this->url, 'icloud.com/photos') !== false) {

                        $handle = $page->waitForSelector('iframe',
                            ['timeout' => 15000, 'visible' => true]);

                        $frame = $handle->contentFrame();
                        //$frame->waitForSelector('.total');
                        $frame->waitForSelector('.derivative-image-container', ['visible' => true]);

                        $frame->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
                            document.querySelector('.derivative-image-container').click()
                        "));

                        $frame->waitForSelector('.pok-video-play-button',
                            ['timeout' => 15000, 'visible' => true]);

                        $page->keyboard->press('Space');

                        $frame->waitForSelector('.pok-video-container video',
                            ['timeout' => 15000, 'visible' => true]);

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

                        $dom = new DOMDocument();
                        @$dom->loadHTML($page->content());
                        $videos = $dom->getElementsByTagName('video');

                        $video = $videos->item(0);

                        $data = [
                            'src' => $video->getAttribute('src'),
                            'type' => $video->getAttribute('type')
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
                $cache->write('external-video-' . $this->url, $data, $cache_lifetime);
            }

            return $data;
        }
    }

}
