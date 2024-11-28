<?php

/**
 * Gets the list of files (and sizes) in the working directory ($working_dir)
 */

$working_dir_files = [];
$working_dir_xml = [];
$files_spaces = 0;
foreach(scandir($full_dir) as $filename) {
    if (substr($filename, 0, 1) === '.') {
        // No hidden files, we will need them later on!
        continue;
    }
    $filesize = filesize($full_dir . '/' . $filename);
    $files_spaces += $filesize;
    $working_dir_files[$filename] = $filesize;
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml') {
        $working_dir_xml[$filename] = $filesize;
    }
};
