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
 * Formular to create and edit a teletask recording.
 * 
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

$intro = new stdClass();
$introformat = new stdClass();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/teletask/lib.php');

class mod_teletask_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/teletask/upload/plupload.full.min.js') );
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/teletask/upload/uuid.js') );
        $PAGE->requires->js_init_call('M.mod_teletask.init');

        $mform =& $this->_form;

        if (empty($this->current->id)) {
            $mform->addElement('html',
                    '<div id="filelist">Your browser doesn\'t have Flash, Silverlight or HTML5 support.'.
                    ' You can not use the upload functionality.</div><br />'.
                    '<div id="container"><a id="pickfiles" href="javascript:;">[Select tele-TASK moodle file]</a>'.
                    '<a id="uploadfiles" href="javascript:;">[Upload file]</a>(optional)</div>');
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('videoname', 'teletask'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('videodescription', 'teletask'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('text', 'speaker', get_string('videospeaker', 'teletask'));
        $mform->setType('speaker', PARAM_TEXT);

        $mform->addElement('date_selector', 'date', get_string('videorecordingdate', 'teletask'), array('optional'  => false));
        $mform->addRule('date', null, 'required', null, 'client');

        $mform->addElement('text', 'video_url_speaker', get_string('videourlspeaker', 'teletask'));
        $mform->setType('video_url_speaker', PARAM_TEXT);
        $mform->addRule('video_url_speaker', null, 'required', null, 'client');

        $mform->addElement('text', 'video_url_desktop', get_string('videourldesktop', 'teletask'));
        $mform->setType('video_url_desktop', PARAM_TEXT);

        $mform->addElement('header', 'Sections', get_string('videosections', 'teletask'));
        $mform->addElement('html',
                '<p><a id="add_section" style="cursor: pointer;">'.
                get_string('addvideosection', 'teletask').'</a></p><div id="video_sections">');

        // Get Sections.
        $teletasksections = $DB->get_records_sql('SELECT * FROM {teletask_sections} '.
                'WHERE video_id = ? ORDER BY time',
                array($this->current->id));

        foreach ($teletasksections as $section) {
            $mform->addElement('html',
                    '<div>Section: <input type="text" name="sections[]" value="'.
                    $section->name.'"> Time (in s): <input type="text" name="sectiontimes[]" value="'.
                    $section->time.'"> <a class="remove_section" style="cursor: pointer;">remove</a></div>');
        }

        $mform->addElement('html', '</div>');

        // Intro.
        $mform->addElement('header', 'Intro', get_string('intro', 'teletask'));
        if ($CFG->version < 2015051100) {
            $this->add_intro_editor(false, get_string('intro', 'teletask'));
        } else {
            $this->standard_intro_elements(get_string('intro', 'teletask'));
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}