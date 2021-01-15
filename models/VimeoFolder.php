<?php

/**
 * VimeoFolder.php
 * model class for storing which folders exist in Vimeo.
 * This is necessary because you cannot get a folder(project) by name from Vimeo,
 * thus making it quite impossible to live-check if a folder with a given name
 * needs to be created.
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
 * @property int folder_id database column
 * @property int vimeo_id database column
 * @property int range_id database column
 * @property string name database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class VimeoFolder extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'vimeo_folders';

        parent::configure($config);
    }

    /**
     * Generates a folder name based on given semester and course.
     *
     * @param Course|Institute $range
     * @return string
     */
    public static function generateName($range)
    {
        if ($range instanceof Course) {
            $name = ($range->veranstaltungsnummer ? $range->veranstaltungsnummer . ' ' : '') . $range->name;

            // Max length for Vimeo titles is 128 characters.
            return mb_substr('Stud.IP_' . $range->start_semester->name . '_' . $name, 0, 128);
        } else {
            // Max length for Vimeo titles is 128 characters.
            return mb_substr('Stud.IP_' . $range->name, 0, 128);
        }

    }

}
