<?php

/**
 * ExternalMediaFileDate.php
 * model class for external media files assignments to course dates.
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
 * @property string date_id database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ExternalMediaFileDate extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'external_media_dates';
        $config['belongs_to']['file'] = [
            'class_name' => 'ExternalMediaFile',
            'foreign_key' => 'file_id',
            'assoc_foreign_key' => 'file_id'
        ];
        $config['belongs_to']['date'] = [
            'class_name' => 'CourseDate',
            'foreign_key' => 'date_id',
            'assoc_foreign_key' => 'termin_id'
        ];
        $config['additional_fields']['datename'] = true;

        parent::configure($config);
    }

    public function getDatename() {
        $name = date('d.m.Y H:i', $this->date->date) . ' - ' .
            date('H:i', $this->date->end_time);
        if ($room = $this->date->getRoomName()) {
            $name .= ' (' . $room . ')';
        }

        return $name;
    }

}
