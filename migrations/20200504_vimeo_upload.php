<?php

/**
 * Creates database tables for storing Vimeo data, like folders etc.
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

class VimeoUpload extends Migration {

    public function description()
    {
        return 'Creates database tables for storing Vimeo data, like folders etc.';
    }

    public function up()
    {
        // Existing Stud.IP folders in Vimeo account
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `vimeo_folders`
        (
            `folder_id` INT NOT NULL AUTO_INCREMENT,
            `vimeo_id` INT NOT NULL,
            `course_id` VARCHAR(32) NOT NULL REFERENCES `seminare`.`Seminar_id`,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`folder_id`),
            UNIQUE KEY vimeo_id (`vimeo_id`),
            UNIQUE KEY course_id (`course_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `vimeo_folders`");
    }

}
