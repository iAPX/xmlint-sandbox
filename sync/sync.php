<?php

/**
 * Sync local directory and XMLint sandbox
 * 
 * Expect token as unique parameter
 * Exchange with https://xs-api.pvigier.com/index.php
 * 
 * - POST &op=LIST
 * - POST &op=DELETE&filename={filename}
 * - POST &op=UPLOAD&filename={filename}
 *   "upfile" = content
 */

const SYNC_VERSION = "0.2";
const ENDPOINT = "https://xs-api.pvigier.com/index.php";
const MAX_FILES = 100;

// Get the Token
if ($argc !== 2) {
    echo 'Usage: ' . $argv[0] . ' <token>' . PHP_EOL;
    exit(1);
}
$token = $argv[1];
$dirname = substr($token, 0, 8);


function apiQuery(string $op, string $token, string $local_filename = '', string $content_filename = ''): array
{
    $curl = curl_init();
    $headers = [
        'Xmlint-Sandbox-Token: ' . $token,
        'Xmlint-Sandbox-Sync-Version: ' . SYNC_VERSION,
        'Accept: application/json',
    ];

    $curl_options = [
        CURLOPT_URL => ENDPOINT,
        CURLOPT_RETURNTRANSFER => true, // Return the response as a string
        CURLOPT_HTTPHEADER => $headers, // Set the headers
        CURLOPT_CUSTOMREQUEST => 'POST', // Set the HTTP method to GET
        CURLOPT_HEADER => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => ['op' => $op, 'filename' => $local_filename],
    ];

    // File upload in POST
    if ($op === 'UPLOAD' && file_exists($content_filename) && !is_dir($content_filename)) {
        echo "[UPLOAD FILE $local_filename / $content_filename]\n";
        $mime_type = strtolower(pathinfo($local_filename, PATHINFO_EXTENSION)) === 'xml' ? 'application/xml' : 'text/plain';
        $curl_file = curl_file_create($content_filename, $mime_type, 'upfile');
        $curl_options[CURLOPT_POSTFIELDS]['upfile'] = $curl_file;
    }

    curl_setopt_array($curl, $curl_options);

    // Execute the request
    $response = curl_exec($curl);

    // Check for errors
    if ($response === false) {
        echo 'cURL error: ' . curl_error($curl);
        curl_close($curl);
        exit;
    }

    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize); // Extract headers
    $body = substr($response, $headerSize);      // Extract body

    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // Close the cURL session
    curl_close($curl);

    echo "HTTP Status Code: $httpStatusCode\n";
    echo "Response: $body\n";
    if ($httpStatusCode < 200 || ($httpStatusCode >= 300 && $httpStatusCode != 404)) {
        echo "Sync stopped!\n";
        exit(0);
    }

    return json_decode($body, true);
}

function processXml(string $filename, string $serve_url, string $dirname): string
{
    $xml = simplexml_load_file($filename);

    // Correct XML?
    if ($xml === false) {
        echo "XML error(s) in $filename\n";
        exit(0);
    }

    // Change the page url in XML
    foreach ($xml->xpath('//affiche[@url]') as $page) {
        $currentUrl = (string)$page['url'];
        $basename = pathinfo($currentUrl, PATHINFO_BASENAME);
        $page['url'] = $serve_url . '/' . $dirname . '/' . $basename;
    }

    // Output the modified XML as a string
    $modifiedXmlString = $xml->asXML();
    return $modifiedXmlString;
}

// get the file list
$response = apiQuery('LIST', $token);
$remote_files = $response['files'];
$serve_url = $response['SERVE_URL'];
$max_files = $response['MAX_FILES'];
$max_xml_length = $response['MAX_XML_LENGTH'];
$max_page_length = $response['MAX_PAGE_LENGTH'];

echo "Max number of remote files : $max_files\n";
echo "Max XML length : $max_xml_length bytes\n";
echo "Max page length : $max_page_length bytes\n";
echo "\n";

echo count($remote_files) . " remote files\n";
var_dump($remote_files);

// get the local file list
$local_files = [];
foreach (scandir(".") as $file) {
    if (is_dir($file)) {
        echo "Skipping directory " . $file . "\n";
        continue;
    }
    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['php', 'perl', 'pl', 'py', 'rb', 'sh', 'md'])) {
        echo "Skipping file " . $file . "\n";
        continue;
    }
    $local_files[$file] = filectime('./' . $file);
}
echo count($local_files) . " local files\n";
var_dump($local_files);

// Files to delete
$delete_files = [];
foreach ($remote_files as $filename => $timestamp) {
    if (!array_key_exists($filename, $local_files)) {
        $delete_files[] = $filename;
    }
}
echo count($delete_files) . " files to delete\n";
var_dump($delete_files);

// Files to create or upload
$new_files = [];
foreach ($local_files as $filename => $timestamp) {
    if (!array_key_exists($filename, $remote_files)) {
        $new_files[] = $filename;
    }
}
echo count($new_files) . " files to create\n";
var_dump($new_files);

// Files to update
$update_files = [];
foreach ($local_files as $filename => $timestamp) {
    if (array_key_exists($filename, $remote_files) && $timestamp > $remote_files[$filename]) {
        $update_files[] = $filename;
    }
}
echo count($update_files) . " files to update\n";
var_dump($update_files);

// Cheks number of files; number of XML files
if (count($new_files) - count($delete_files) > MAX_FILES) {
    echo "Too many files after sync, limit is " . MAX_FILES . ".\n";
    exit(0);
}

// Action?
if (count($update_files) + count($new_files) + count($delete_files) === 0) {
    echo "No files to sync.\n";
    exit(0);
}
readline("Press enter to continue, or CTL-C to stop the sync\n");

foreach ($delete_files as $filename) {
    echo " - DELETE $filename\n";
    apiQuery('DELETE', $token, $filename);
}
$upload_files = array_merge($new_files, $update_files);
foreach ($upload_files as $filename) {
    echo " - UPLOAD $filename\n";
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml') {
        if (filesize($filename) > $max_xml_length) {
            echo "Skipping file $filename, too big for XML, limit is $max_xml_length\n";
            continue;
        }
        $content = processXml($filename, $serve_url, $dirname);
        echo "Nouveau XML: \n";
        var_dump($content);
        $tmpfile = tmpfile();
        fwrite($tmpfile, $content);
        $tmp_filename = stream_get_meta_data($tmpfile)['uri'];
        apiQuery('UPLOAD', $token, $filename, $tmp_filename);
        fclose($tmpfile);
    } else {
        if (filesize($filename) > $max_page_length) {
            echo "Skipping file $filename, too big for Videotex Pages, limit is $max_page_length\n";
            continue;
        }
        apiQuery('UPLOAD', $token, $filename, $filename);
    }
}

echo "Sync done!\n";
