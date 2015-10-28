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
 * The functionality of this file handles the unzipping process of teletask recording files
 * 
 * @package   mod_teletask
 * @copyright 2015 Martin Malchow - Hasso Plattner Institute (HPI) {http://www.hpi.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (isset($_POST['fn']) && isset($_POST['action']) && is_file('uploads/'.$_POST['fn'])) {
    if ($_POST['action'] == 'unzip') {
        if (is_dir(explode(".ttpp", 'uploads/'.$_POST['fn'])[0]) || mkdir(explode(".ttpp", 'uploads/'.$_POST['fn'])[0])) {
            $filename = 'uploads/'.$_POST['fn'];
            $archive = zip_open($filename);
            while ($entry = zip_read($archive)) {
                if ($_POST['ufn'] == zip_entry_name($entry)) {
                    $size = zip_entry_filesize($entry);
                    $name = zip_entry_name($entry);
                    $unzipped = fopen(explode(".ttpp", 'uploads/'.$_POST['fn'])[0].'/'.$name, 'wb');
                    while ($size > 0) {
                        $chunksize = ($size > 10240) ? 10240 : $size;
                        $size -= $chunksize;
                        $chunk = zip_entry_read($entry, $chunksize);
                        if ($chunk !== false) {
                            fwrite($unzipped, $chunk);
                        }
                    }
                    fclose($unzipped);
                    echo 'File: ' . $name . ' unzipped!';
                }
            }
        } else {
            // Error no write access.
            echo "You 'uploads' folder have no write access. Contact your moodle administrator!";
        }
    } else if ($_POST['action'] == 'gather') {
        $filename = 'uploads/'.$_POST['fn'];
        $archive = zip_open($filename);
        $outputarray = array();
        while ($entry = zip_read($archive)) {
            if ((is_dir(explode(".ttpp", 'uploads/'.$_POST['fn'])[0]) ||
                    mkdir(explode(".ttpp", 'uploads/'.$_POST['fn'])[0])) &&
                    zip_entry_name($entry) == "Manifest.xml") {
                $size = zip_entry_filesize($entry);
                $name = zip_entry_name($entry);
                $unzipped = fopen(explode(".ttpp", 'uploads/'.$_POST['fn'])[0].'/'.$name, 'wb');
                while ($size > 0) {
                    $chunksize = ($size > 10240) ? 10240 : $size;
                    $size -= $chunksize;
                    $chunk = zip_entry_read($entry, $chunksize);
                    if ($chunk !== false) {
                        fwrite($unzipped, $chunk);
                    }
                }
                fclose($unzipped);

                $xml = simplexml_load_file(explode(".ttpp", 'uploads/'.$_POST['fn'])[0].'/'.$name);

                $outputarray["lectureName"] = (string) $xml->Name;
                $outputarray["lectureDescription"] = (string) $xml->Description;
                $outputarray["lectureSpeaker"] = '';
                foreach ($xml->Speakers->Speaker as $speaker) {
                    if ($outputarray["lectureSpeaker"] == '') {
                        $outputarray["lectureSpeaker"] .= (string) $speaker->Name;
                    } else {
                        $outputarray["lectureSpeaker"] .= ', ' . $speaker->Name;
                    }
                }
                $outputarray["lectureYear"] = date("Y", strtotime($xml->DateTime));
                $outputarray["lectureMonth"] = date("n", strtotime($xml->DateTime));
                $outputarray["lectureDay"] = date("d", strtotime($xml->DateTime));
                $outputarray["files"] = array();

                foreach ($xml->FileAssets->FileAsset as $file) {
                    if ($file->IsEmbedded == 'true') {
                        array_push($outputarray["files"], (string) $file->Path);
                    }
                }

                $outputarray["sections"] = array();

                foreach ($xml->Chapters->Chapter as $chapter) {
                    $chapterarray["name"] = (string) $chapter->Description;
                    $chapterarray["time"] = (string) round($chapter->Time / 1000);
                    array_push($outputarray["sections"], $chapterarray);
                }
            }
        }
        // Remove uploaded file if nothing is found to unpack.
        if (count($outputarray) == 0) {
            unlink('uploads/'.$_POST['fn']);
        }
        echo json_encode($outputarray);
    } else if ($_POST['action'] == 'remove') {
        if (unlink('uploads/'.$_POST['fn'])) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }
} else {
    die('File not found');
}