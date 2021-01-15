<?php

/**
 * Changes database for adding videos to institutes
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

class VideosAtInstitutes extends Migration {

    public function description()
    {
        return 'Changes database for adding videos to institutes.';
    }

    public function up()
    {
        // rename column and adjust index
        DBManager::get()->execute("ALTER TABLE `external_videos`
            CHANGE `course_id` `range_id` CHAR(32) NOT NULL,
            DROP INDEX `course_id`, ADD INDEX `range_id` (`range_id`)");

        DBManager::get()->execute("ALTER TABLE `vimeo_folders`
            CHANGE `course_id` `range_id` CHAR(32) NOT NULL,
            DROP INDEX `course_id`, ADD INDEX `range_id` (`range_id`)");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `vimeo_folders`
            CHANGE `range_id` `course_id` CHAR(32) NOT NULL,
            DROP INDEX `range_id`, ADD INDEX `course_id` (`course_id`)");

        DBManager::get()->execute("ALTER TABLE `external_videos`
            CHANGE `range_id` `course_id` CHAR(32) NOT NULL,
            DROP INDEX `range_id`, ADD INDEX `course_id` (`course_id`)");
    }

}
