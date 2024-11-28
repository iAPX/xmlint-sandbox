<?php

/**
 * Checks $filename
 */

function checkFilename(string $filename): bool
{
    // La base!
    if (substr($filename, 0, 1) === '.' || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        return false;
    }

    // Extension dans la liste acceptée
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $allowed_extensions = ['vdt', 'vdx', 'videotex', 'pag', 'page', 'minitel', 'xml'];
    if (!in_array(strtolower($extension), $allowed_extensions)) {
        return false;
    }

    // La base AZ09-
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    if (empty($basename)) {
        return false;
    }
    $pattern = '/^[a-zA-Z0-9-]+$/';
    if (!preg_match($pattern, $basename)) {
        return false;
    }
    
    return true;
}
