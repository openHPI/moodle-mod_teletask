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
 * Class backup_teletask_activity_structure_step define all the backup steps used by backup_teletask_activity_task
 * 
 * @package    mod_teletask
 * @subpackage backup
 * @copyright  2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete teletask structure for backup, with file and id annotations
 */
class backup_teletask_activity_structure_step extends backup_activity_structure_step {

    /**
     * Function describes the structure for the backup
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $teletask = new backup_nested_element('teletask', array('id'), array(
                'name', 'description', 'speaker', 'date', 'intro', 'introformat',
                'video_url_speaker', 'video_url_desktop', 'grade', 'timemodified'));

        $teletask = new backup_nested_element('teletask', array('id'), array(
                'name', 'description', 'speaker', 'date', 'intro', 'introformat',
                'video_url_speaker', 'video_url_desktop', 'grade', 'timemodified'));

        $sections = new backup_nested_element('sections');

        $section = new backup_nested_element('section', array('id'), array(
                'name', 'time'));

        $slides = new backup_nested_element('slides');

        $slide = new backup_nested_element('slide', array('id'), array(
                'time', 'image'));

        $quizes = new backup_nested_element('quizes');

        $quiz = new backup_nested_element('quiz', array('id'), array(
                'question', 'type', 'description', 'time'));

        $quizpossibleanswers = new backup_nested_element('quiz_possibleanswers');

        $quizpossibleanswer = new backup_nested_element('quiz_possibleanswer', array('id'), array(
                'answer', 'is_right'));

        $quizuseranswers = new backup_nested_element('quiz_user_answers');

        $quizuseranswer = new backup_nested_element('quiz_user_answer', array('id'), array(
                'user_id', 'correct'));

        $quizusergivenanswers = new backup_nested_element('quiz_user_given_answers');

        $quizusergivenanswer = new backup_nested_element('quiz_user_given_answer', array('id'), array(
                'answer'));

        // Build the tree.
        $teletask->add_child($sections);
            $sections->add_child($section);
        $teletask->add_child($slides);
            $slides->add_child($slide);
        $teletask->add_child($quizes);
            $quizes->add_child($quiz);
                $quiz->add_child($quizpossibleanswers);
                    $quizpossibleanswers->add_child($quizpossibleanswer);
                $quiz->add_child($quizuseranswers);
                    $quizuseranswers->add_child($quizuseranswer);
                        $quizuseranswer->add_child($quizusergivenanswers);
                            $quizusergivenanswers->add_child($quizusergivenanswer);

        // Define sources.
        $teletask->set_source_table('teletask', array('id' => backup::VAR_ACTIVITYID));

        $section->set_source_table('teletask_sections', array('video_id' => backup::VAR_PARENTID));

        $slide->set_source_table('teletask_slides', array('video_id' => backup::VAR_PARENTID));

        $quiz->set_source_table('teletask_quiz', array('video_id' => backup::VAR_PARENTID));

        $quizpossibleanswer->set_source_table('teletask_quiz_possibleanswer', array('question_id' => backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $quizuseranswer->set_source_table('teletask_quiz_user_answer', array('question_id' => backup::VAR_PARENTID));
            $quizusergivenanswer->set_source_table('teletask_quiz_user_givanswer', array('answer_id' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $quizuseranswer->annotate_ids('user', 'user_id');

        // Define file annotations.

        // Return the root element (teletask), wrapped into standard activity structure.
        return $this->prepare_activity_structure($teletask);

    }
}