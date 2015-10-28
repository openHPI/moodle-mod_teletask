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
 * Structure step to restore one teletask activity in class restore_teletask_activity_structure_step
 * 
 * @package    mod_teletask
 * @subpackage backup
 * @copyright  2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one teletask activity
 */
class restore_teletask_activity_structure_step extends restore_activity_structure_step {

    /**
     * Function describes the structure for the restore
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('teletask', '/activity/teletask');
        $paths[] = new restore_path_element('teletask_sections', '/activity/teletask/sections/section');
        $paths[] = new restore_path_element('teletask_slides', '/activity/teletask/slides/slide');
        $paths[] = new restore_path_element('teletask_quiz', '/activity/teletask/quizes/quiz');
        $paths[] = new restore_path_element('teletask_quiz_possibleanswer',
                '/activity/teletask/quizes/quiz/quiz_possibleanswers/quiz_possibleanswer');

        if ($userinfo) {
            $paths[] = new restore_path_element('teletask_quiz_user_answer',
                    '/activity/teletask/quizes/quiz/quiz_user_answers/quiz_user_answer');
            $paths[] = new restore_path_element('teletask_quiz_user_givanswer',
                    '/activity/teletask/quizes/quiz/quiz_user_answers/quiz_user_answer/quiz_user_given_answers/quiz_user_given_answer');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process for teletask data structure restore 
     */
    protected function process_teletask($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->date = $this->apply_date_offset($data->date);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the teletask record.
        $newitemid = $DB->insert_record('teletask', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process for teletask section data structure restore
     */
    protected function process_teletask_sections($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->video_id = $this->get_new_parentid('teletask');

        $newitemid = $DB->insert_record('teletask_sections', $data);
        $this->set_mapping('teletask_sections', $oldid, $newitemid);
    }

    /**
     * Process for teletask slides data structure restore
     */
    protected function process_teletask_slides($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->video_id = $this->get_new_parentid('teletask');

        $newitemid = $DB->insert_record('teletask_slides', $data);
        $this->set_mapping('teletask_slides', $oldid, $newitemid);
    }

    /**
     * Process for teletask quiz data structure restore
     */
    protected function process_teletask_quiz($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->video_id = $this->get_new_parentid('teletask');

        $newitemid = $DB->insert_record('teletask_quiz', $data);
        $this->set_mapping('teletask_quiz', $oldid, $newitemid);
    }

    /**
     * Process for teletask quiz possible answer structure restore
     */
    protected function process_teletask_quiz_possibleanswer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->question_id = $this->get_new_parentid('teletask_quiz');

        $newitemid = $DB->insert_record('teletask_quiz_possibleanswer', $data);
        $this->set_mapping('teletask_quiz_possibleanswer', $oldid, $newitemid);
    }

    /**
     * Process for teletask quiz user answer data structure restore
     */
    protected function process_teletask_quiz_user_answer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->question_id = $this->get_new_parentid('teletask_quiz');

        $newitemid = $DB->insert_record('teletask_quiz_user_answer', $data);
        $this->set_mapping('teletask_quiz_user_answer', $oldid, $newitemid);
    }

    /**
     * Process for teletask quiz uder given answer data structure restore
     */
    protected function process_teletask_quiz_user_givanswer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->answer_id = $this->get_new_parentid('teletask_quiz_user_answer');

        $newitemid = $DB->insert_record('teletask_quiz_user_givanswer', $data);
        $this->set_mapping('teletask_quiz_user_givanswer', $oldid, $newitemid);
    }

    /**
     * Restore step after step execution
     */
    protected function after_execute() {
        // Add teletask related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_teletask', 'intro', null);
    }
}