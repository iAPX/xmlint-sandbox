<?php

/**
 * Download your projet as a zip file, named after the project directory and the current date.
 */

require_once 'internals/check_token.php';
require_once 'internals/bootstrap.php';
require_once 'internals/get_files.php';

$directoryToZip = $full_dir;

// Output ZIP file name
$zipFileName = 'xmlint-sandbox-' . date('Y-m-d') . '.zip';

try {
    // Initialize a new ZIP archive
    $zip = new ZipArchive();
    
    // Create a temporary ZIP file
    $tmpZipFile = tempnam(sys_get_temp_dir(), 'zip');

    if ($zip->open($tmpZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Could not create ZIP file.");
    }

    foreach ($working_dir_files as $filename => $filesize) {
        $full_filename = $full_dir . '/' . $filename;
        $zip->addFile($full_filename, $working_dir . '/' . $filename);
    }

    // Close the ZIP archive
    $zip->close();

    // Serve the ZIP file for download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($tmpZipFile));
    readfile($tmpZipFile);

    // Clean up
    unlink($tmpZipFile);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
