<?php

/**
 * Micro API
 * 
 * Checks the Token, then act
 * 
 * - POST &op=LIST
 * - POST &op=DELETE&filename={filename}
 * - POST &op=UPLOAD&filename={filename}
 *   "upfile" = content
 */

// Gets the environment datas
define('XMLINT_SANDBOX_DIR', getenv('XMLINT_SANDBOX_DIR'));
define('XMLINT_SANDBOX_SEED', getenv('XMLINT_SANDBOX_SEED'));

const SERVE_URL = "https://xs.pvigier.com";
const MAX_FILES = 100;
const MAX_XML_LENGTH = 60000;
const MAX_PAGE_LENGTH = 4096;
const REAL_MAX_XML_LENGTH = 65500;

$token = getallheaders()['xmlint-sandbox-token'] ?? '';

// @TODO unsafe, but should be ok for now
$dirname = str_replace(['/', '\\', '..'], '', substr($token, 0, 8));
if (!file_exists(XMLINT_SANDBOX_DIR . '/' . $dirname) || !is_dir(XMLINT_SANDBOX_DIR . '/' . $dirname)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Directory "' . $dirname . '" not present.',
    ]);
    exit(0);
}

$op = $_POST['op'] ?? '';
$filename = $_POST['filename'] ?? '';
$basename = pathinfo($filename, PATHINFO_FILENAME);
$extension = pathinfo($filename, PATHINFO_EXTENSION);


function checkSyncVersion(): void 
{
    $headers = getallheaders();
    $version = $headers['xmlint-sandbox-sync-version'] ?? '';
    if (version_compare($version, '0.2', '<')) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Incorrect Sync version, 0.1 or higher expected, "' . $version . '" received.',
        ]);
        exit(0);
    }
}

function checkToken(string $token): void 
{
    $new_token = substr(strtolower($token), 0, 32) . substr(hash('sha256', XMLINT_SANDBOX_SEED . substr(strtolower($token), 0, 32)), 0, 32);
    if (strtolower($new_token) !== strtolower($token)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Invalid token : ' . $token,
        ]);
        exit(0);
    }
}

function checkBasenameAndExtension(string $basename, string $extension): void 
{
    if (empty($basename)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Filename missing',
        ]);
        exit(0);
    }

    // Checks extension first
    $allowed_extensions = ['vdt', 'vdx', 'videotex', 'pag', 'page', 'minitel', 'xml'];
    if (!in_array(strtolower($extension), $allowed_extensions)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Filename extension not allowed : .' . $extension . ', allowed extensions are : .' . implode(', .', $allowed_extensions),
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

function checkUploadedFile(string $extension): void
{
    if (!isset($_FILES['upfile'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'No "upfile" uploaded file found',
            'FILES' => print_r($_FILES, true),
            'SERVER' => print_r($_SERVER, true),
        ]);
        exit(0);
    }
    if($_FILES['upfile']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Upload error : ' . $_FILES['upfile']['error'],
        ]);
        exit(0);
    }

    // Empty file?
    if ($_FILES['upfile']['size'] === 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Empty file',
        ]);
        exit(0);
    }

    // too heavy for XML? With a 4KB margin to enable page URL transposition!
    if (strtolower($extension) === 'xml' && $_FILES['upfile']['size'] > REAL_MAX_XML_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'error' => 'XML files are limited to ' . MAX_XML_LENGTH . ' bytes. This file is too big : ' . $_FILES['upfile']['size'],
        ]);
        exit(0);
    }

    // Too heavy for Pages
    if (strtolower($extension) !== 'xml' && $_FILES['upfile']['size'] > MAX_PAGE_LENGTH) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Page (non-xml) files are limited to ' . MAX_PAGE_LENGTH . ' bytes. This file is too big : ' . $_FILES['upfile']['size'],
        ]);
        exit(0);
    }

    // Begins with <? or #! ??? Security problem!
    $content = file_get_contents($_FILES['upfile']['tmp_name']);
    if ($content === false) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Could not check the uploaded file?!?',
        ]);
        exit(0);
    }
    if (substr($content, 0, 5) !== '<?xml' && in_array(substr($content, 0, 2), ['<?', '#!'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'File starts with "<?" or "#!"',
        ]);
        exit(0);
    }

    // Correct XML file?
    if (strtolower($extension) === 'xml') {
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid XML file',
            ]);
            exit(0);
        }
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
checkToken($token);
switch ($op) {
    case 'LIST':
        // LIST remote files
        http_response_code(200);
        echo json_encode([
            'files' => getFilesAndTimeStamp(XMLINT_SANDBOX_DIR . '/' . $dirname. '/'),
            'SERVE_URL' => SERVE_URL,
            'MAX_FILES' => MAX_FILES,
            'MAX_XML_LENGTH' => MAX_XML_LENGTH,
            'MAX_PAGE_LENGTH' => MAX_PAGE_LENGTH
        ]);
        exit(0);
    case 'DELETE':
        // DELETE remote file
        checkBasenameAndExtension($basename, $extension);
        $filename = XMLINT_SANDBOX_DIR . '/' . $dirname . '/' . $basename . "." . $extension;
        if (!file_exists($filename)) {
            http_response_code(404);
            echo json_encode(['error' => 'File missing for DELETE : ' . $basename]);
        } else {
            unlink($filename);
            http_response_code(200);
            echo json_encode(['ok' => 'File deleted : ' . $basename . '.' . $extension]);
        }
        exit(0);
    case 'UPLOAD':
        // UPLOAD file
        checkBasenameAndExtension($basename, $extension);
        checkUploadedFile($extension);
        $filename = XMLINT_SANDBOX_DIR . '/' . $dirname . '/' . $basename . "." . $extension;
        if (file_exists($filename)) {
            // Delete before adding it again
            unlink($filename);
        } else {
            // Check Max files
            $file_count = count(glob(XMLINT_SANDBOX_DIR . '/' . $dirname . '/*'));
            if ($file_count >= MAX_FILES) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Too many files, limit is ' . MAX_FILES . '.',
                ]);
                exit(0);
            }
        }
        move_uploaded_file($_FILES['upfile']['tmp_name'], $filename);
        chmod($filename, 0600);  // Only owner can read & write, none execution
        http_response_code(200);
        echo json_encode(['ok' => 'File uploaded : ' . $basename . '.' . $extension]);
        exit(0);
    default:
        http_response_code(400);
        echo json_encode([
            'error' => 'Unknown operation : ' . $op,
        ]);
        exit(0);
}
