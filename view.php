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
 * File to view a teletask recording.
 * 
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/completionlib.php');

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

$PAGE->set_title($teletask->name);
$PAGE->set_heading($course->fullname);

$teletask_video_proxy_script = 'serve_video_proxy.php?id='.$id.'&type=';

// Handle Video URL ... local or extern.
if (strpos($teletask->video_url_speaker, '//') === false) {
    $teletask->video_url_speaker = $teletask_video_proxy_script.'speaker';
}
if (strpos($teletask->video_url_desktop, '//') === false && !empty($teletask->video_url_desktop)) {
    $teletask->video_url_desktop = $teletask_video_proxy_script.'desktop';
}

// Handle Sections.
// HTML5.
$html5sections = '';
$teletasksections = $DB->get_records_sql('SELECT * FROM {teletask_sections} WHERE video_id = ? ORDER BY time',
        array($teletask->id));

foreach ($teletasksections as $section) {
    $html5sections .= '<li data-start="'.$section->time.'" data-thumb="">'.$section->name.'</li>';
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($teletask->name), 2, null);

echo '
  <!-- Begin HTML5 player -->
  <link rel="stylesheet" type="text/css" href="html5/css/font-awesome.min.css">
  <link rel="stylesheet" href="html5/css/player.css">
  <script>
	  /*@cc_on document.documentElement.className +=\'ie\';@*/ // detect whether user is using internetexplorer
  </script>

  <div id="content">
	<div class="televideoplayer">
	  <div class="videoPlayer">

	  <div class="videoPlayerInner">
		<div class="main clearfix">
		  <div class="spinner">
			  <i class="fa fa-spin fa-spin-fast fa-spinner"></i>
		  </div>
		  <div class="videoContainer speaker">
			<video class="speaker" autoplay>
			  <source src="'.$teletask->video_url_speaker.'" type="video/mp4" autobuffer>
			</video>
			<div class="aspectRatio speaker">4:3</div>
		  </div>';
if ($teletask->video_url_desktop != "") {
                echo '<div class="videoContainer slides">
				<video class="slides" autoplay>
				  <source src="'.$teletask->video_url_desktop.'" type="video/mp4" autobuffer>
				</video>
				<div class="aspectRatio slides">4:3</div>
				<!-- TODO <div id="link_overlay_container">
				  {% for overlay in link_overlays %}
				  <div class="link overlay" data-start="{{overlay.start}}" data-end="{{overlay.end}}" style="top: {{overlay.y}}%">
					<a class="fill-div" href="{{ overlay.link}}" target="_blank">
					  <span class="link_icon"></span>
					</a>
				  </div>
				  {% endfor %}
				</div>-->
			  </div>
			  <div class="resizer"></div>';
}
echo '<div class="chapterContent">
			<div class="chapters">
			  <ol>
				  '.$html5sections.'
			  </ol>
			</div>
		  </div>
		</div>
		<div class="controlsBox">
		  <div class="controls">
			<a class="play button pause"></a>
			<div class="controlsRight">
			  <div class="timer">0:00</div>
			  <a class="playbackRate button">1.0x</a>
			  <a class="chapter button"></a>
			  <div class="volume-box clearfix">
				<a class="mute button"></a>
				<div class="sliding volume">
				  <div class="slider"></div>
				</div>
			  </div>
			  <a class="fullscreen button"></a>
			</div>
			<div class="seekers">
			  <!-- TODO <ol class="slideContainer">
				{% for slide in slides %}
				  <li data-start="{{ slide.start }}">
					<img src="{{ slide.path }}" alt="">
				  </li>
				{% endfor %}
			  </ol>-->
			  <div class="sliding seek">
				<div class="buffer"></div>
				<div class="slider"></div>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	  </div>
	</div>
  </div>

  <!--[if lt IE 7]>
	  <p class="chromeframe">You are using an outdated browser. '.
      '<a href="http://browsehappy.com/">Upgrade your browser today</a> '.
      'or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a>'.
      ' to better experience this site.</p>
  <![endif]-->

  <!-- Add your site or application content here -->
  <!-- Make the MEDIA_URL variable available to Javascript -->
  <script type="text/javascript">var MEDIA_URL = "";</script>
  <script src="html5/script/vendor/require.js"></script>
  <script src="html5/script/televideoplayer.js"></script>
  <!-- End HTML5 player -->

';

echo $OUTPUT->footer();
die();