<?php

/**
 * Adds datbase columns for videos stored at Vimeo.
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

class VimeoData extends Migration {

    public function description()
    {
        return 'Adds datbase columns for videos stored at Vimeo.';
    }

    public function up()
    {
        // Differentiate between cloud shares and Vimeo videos.
        DBManager::get()->execute("ALTER TABLE `external_videos`
            ADD `type` ENUM('share','vimeo') NOT NULL DEFAULT 'share' AFTER `video_id`,
            ADD `external_id` VARCHAR(255) NULL AFTER `type`");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `external_videos` DROP `type`");
    }

}
