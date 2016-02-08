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
 * 
 * File to serve video securly from moodle data Folder.
 * 
 * @package   mod_teletask
 * @copyright 2016 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('vendor/VideoStream.php');
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/completionlib.php');

$teletaskvideodir = $CFG->dataroot.'/mod_teletask/';

$id         = required_param('id', PARAM_INT);                          // Course Module ID.
$action     = optional_param('action', '', PARAM_ALPHA);
$attemptids = optional_param_array('attemptid', array(), PARAM_INT);    // Array of attempt ids for delete action.
$notify     = optional_param('notify', '', PARAM_ALPHA);

$url = new moodle_url('/mod/teletask/view.php', array('id' => $id));
if ($action !== '') {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (!$cm = get_coursemodule_from_id('teletask', $id)) {
    print_error(get_string('incorrectcoursemoduleid', 'teletask'));
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error(get_string('misconfigured', 'teletask'));
}

require_course_login($course, false, $cm);

if (!$teletask = $DB->get_record('teletask', array('id' => $cm->instance))) {
    print_error(get_string('incorrectcoursemodule', 'teletask'));
}

if ($_GET['type'] == 'speaker') {
    $stream = new VideoStream($teletaskvideodir.$teletask->video_url_speaker);
    $stream->start();
} else if ($_GET['type'] == 'desktop') {
    $stream = new VideoStream($teletaskvideodir.$teletask->video_url_desktop);
    $stream->start();
}