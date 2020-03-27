<?php

/**
 * Creates database tables for storing external media file data.
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

class Setup extends Migration {

    public function description()
    {
        return 'Creates database tables for storing external media file data.';
    }

    public function up()
    {
        // Request: one per course, preferred room, cycle data, comments
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `external_media_files`
        (
            `file_id` INT NOT NULL AUTO_INCREMENT,
            `sharelink` VARCHAR(2048) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `position` INT NOT NULL DEFAULT 0,
            `course_id` VARCHAR(32) NOT NULL REFERENCES `seminare`.`Seminar_id`,
            `user_id` VARCHAR(32) NOT NULL REFERENCES `auth_user_md5`.`user_id`,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`file_id`),
            INDEX course_id (`course_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `external_media_files`");
    }

}
