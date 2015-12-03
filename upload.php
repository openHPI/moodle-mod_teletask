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
 * upload.php file to handle chunked upload
 *
 * Copyright 2013, Moxiecode Systems AB (Original Author)
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 * 
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error(get_string('misconfigured', 'teletask'));
}

require_course_login($course, false);

$coursecontext = context_course::instance($course->id);
if (has_capability('mod/teletask:addinstance', $coursecontext)) {


    // Make sure file is not cached (as it happens for example on iOS devices).
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    /*
    // Support CORS
    header("Access-Control-Allow-Origin: *");
    // other CORS headers if any...
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    	exit; // finish preflight CORS requests here
    }
    */

    // 5 minutes execution time.
    @set_time_limit(5 * 60);

    // Uncomment this one to fake upload time.

    // Settings.
    $targetdir = 'uploads';
    $cleanuptargetdir = true; // Remove old files.
    $maxfileage = 5 * 3600; // Temp file age in seconds.


    // Create target dir.
    if (!file_exists($targetdir)) {
        @mkdir($targetdir);
    }

    // Get a file name.
    if (isset($_REQUEST["name"])) {
        $filename = $_REQUEST["name"];
    } else if (!empty($_FILES)) {
        $filename = $_FILES["file"]["name"];
    } else {
        $filename = uniqid("file_");
    }

    // Normalize file name to avoid directory traversal attacks.
    // Always strip any paths.
    $filename = basename($filename);

    $filepath = $targetdir . DIRECTORY_SEPARATOR . $filename;

    // Chunking might be enabled.
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


    // Remove old temp files.
    if ($cleanuptargetdir) {
        if (!is_dir($targetdir) || !$dir = opendir($targetdir)) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
        }

        while (($file = readdir($dir)) !== false) {
            $tmpfilepath = $targetdir . DIRECTORY_SEPARATOR . $file;

            // If temp file is current file proceed to the next.
            if ($tmpfilepath == "{$filepath}.part") {
                continue;
            }

            // Remove temp file if it is older than the max age and is not the current file.
            if (preg_match('/\.part$/', $file) && (filemtime($tmpfilepath) < time() - $maxfileage)) {
                @unlink($tmpfilepath);
            }
        }
        closedir($dir);
    }


    // Open temp file.
    if (!$out = @fopen("{$filepath}.part", $chunks ? "ab" : "wb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    }

    if (!empty($_FILES)) {
        if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        }

        // Read binary input stream and append it to temp file.
        if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
        }
    } else {
        if (!$in = @fopen("php://input", "rb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
        }
    }

    while ($buff = fread($in, 4096)) {
        fwrite($out, $buff);
    }

    @fclose($out);
    @fclose($in);

    // Check if file has been uploaded.
    if (!$chunks || $chunk == $chunks - 1) {
        // Strip the temp .part suffix off.
        rename("{$filepath}.part", $filepath);
    }

    // Return Success JSON-RPC response.
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
} else {
    print_error(get_string('misconfigured', 'teletask'));
}
