<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines backup_teletask_activity_task class
 *
 * @package     mod_teletask
 * @category    backup
 * @copyright   2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/teletask/backup/moodle2/backup_teletask_stepslib.php'); // Because it exists (must).
require_once($CFG->dirroot . '/mod/teletask/backup/moodle2/backup_teletask_settingslib.php'); // Because it exists (optional).

/**
 * Provides all the settings and steps to perform one complete backup of the activity
 */
class backup_teletask_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // In teletask only one structure step is defined.
        $this->add_step(new backup_teletask_activity_structure_step('teletask', 'teletask.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     * 
     * @param string $content The content that should be encoded.
     * 
     */
    static public function encode_content_links($content) {
        return $content;
    }
}