<?php

/**
 * Micro API
 * 
 * Checks the Token, then act
 * 
 * - GET /{directory}/
 * - DELETE /{directory}/{filename}
 * - POST or PUT /{directory}/{filename}
 */

// Gets the environment datas
define('XMLINT_SANDBOX_DIR', getenv('XMLINT_SANDBOX_DIR'));
define('XMLINT_SANDBOX_SEED', getenv('XMLINT_SANDBOX_SEED'));


// Get the HTTP method and requested path
if (!empty($_SERVER['REDIRECT_REQUEST_METHOD'])) {
    $method = $_SERVER['REDIRECT_REQUEST_METHOD'];
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$requestUri = $_SERVER['REQUEST_URI'];

// Parse the URI to extract the file path and name
$pathInfo = pathinfo($requestUri);

// Extract parts of the path
$dirname = trim($pathInfo['dirname'], '/'); // Subdirectories (without leading or trailing slashes)
if ($dirname === '') {
    // No file, just a dir.
    $dirname = trim($pathInfo['basename'], '/');
    $basename = '';
    $extension = '';
} else {
    // Dir and file
    $dirname = trim($pathInfo['dirname'], '/'); // Subdirectories (without leading or trailing slashes)
    $basename = $pathInfo['filename'] ?? '';          // Full file name (e.g., 'file.html')
    $extension = $pathInfo['extension'] ?? '';  // File extension (e.g., 'html')
}

function checkDirectoryPermission(string $directory): void 
{
    $headers = apache_request_headers();
    $token = $headers['xmlint-sandbox-token'] ?? '';

    // @TODO check Token integrity

    // Does directory correspond to token
    if (substr($token, 0, 8) !== $directory) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Token "' . $token . '" or Directory "' . $directory . '" incorrect.',
        ]);
        exit(0);
    }

    // Is directory existant
    $dir = XMLINT_SANDBOX_DIR . '/' . $directory;
    if (!file_exists($dir) || !is_dir($dir)) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Directory "' . $directory . '" not present.',
        ]);
        exit(0);
    }
    return;
}

function checkSyncVersion(): void 
{
    $headers = getallheaders();
    // var_dump($headers);
    $version = $headers['xmlint-sandbox-sync-version'] ?? '0';
    if (version_compare($version, '0.1', '<')) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Incorrect Sync version, 0.1 or higher expected, "' . $version . '" received.',
        ]);
        exit(0);
    }
}

function checkBasenameAndExtension(string $basename, string $extension): void 
{
    // Checks extension first
    $allowed_extensions = ['vdt', 'vdx', 'videotex', 'pag', 'page', 'minitel', 'xml'];
    if (!in_array(strtolower($extension), $allowed_extensions)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Filename extension not allowed : ' . $extension,
        ]);
        exit(0);
    }

    // Filename
    $pattern = '/^[a-zA-Z0-9-]+$/';
    if (!preg_match($pattern, $basename)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Only lowercase letters, uppercase letters, digits and dash are allowed in filename. This filename is incorrect : ' . $basename,
        ]);
        exit(0);
    }
}

function checkUploadedFileSize(string $extension): void
{
    // Empty file?
    if ($_FILES['upfile']['size'] === 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Empty file',
        ]);
        exit(0);
    }
    // too heavy for XML?
    if (strtolower($extension) === 'xml' && $_FILES['upfile']['size'] > 60000) {
        http_response_code(400);
        echo json_encode([
            'error' => 'XML files are limited to 60KB (60,000 bytes). This file is too big : ' . $_FILES['upfile']['size'],
        ]);
        exit(0);
    }
    // Too heavy
    if (strtolower($extension) !== 'xml' && $_FILES['upfile']['size'] > 4096) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Page (non-xml) files are limited to 4KiB (4,096 bytes). This file is too big : ' . $_FILES['upfile']['size'],
        ]);
        exit(0);
    }
}

function getFilesAndTimeStamp(string $directory): array
{
    $scanned_directory = array_diff(scandir($directory), array('..', '.'));
    $files = [];
    foreach ($scanned_directory as $file) {
        $files[$file] = filectime($directory . '/' . $file);
    }
    return $files;
}

checkSyncVersion();
checkDirectoryPermission($dirname);
switch ($method) {
    case 'GET':
        // GET file list
        $directory = XMLINT_SANDBOX_DIR . '/' . $dirname. '/';
        http_response_code(200);
        echo json_encode(['files' => getFilesAndTimeStamp($directory)]);
        exit(0);
    case 'DELETE':
        // DELETE file
        checkBasenameAndExtension($basename, $extension);
        $filename = XMLINT_SANDBOX_DIR . '/' . $dirname . '/' . $basename . "." . $extension;
        if (!file_exists($filename)) {
            http_response_code(404);
            echo json_encode(['error' => 'File missing for DELETE : ' . $basename]);
        } else {
            unlink($filename);
            http_response_code(200);
        }
        exit(0);
    case 'POST':
    case 'PUT':
        // Create or replace file
        checkBasenameAndExtension($basename, $extension);
        checkUploadedFileSize($extension);
        $filename = XMLINT_SANDBOX_DIR . '/' . $dirname . '/' . $basename . "." . $extension;
        if (file_exists($filename)) {
            unlink($filename);
        }
        move_uploaded_file($_FILES['upfile']['tmp_name'], $filename);

        // @TODO change the XML page pointers

        http_response_code(200);
        exit(0);
}
