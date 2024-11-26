<?php

/**
 * Sync local directory and XMLint sandbox
 * 
 * Expect token as unique parameter
 * Exchange with https://xs-api.pvigier.com
 * 
 * - GET /{directory}/ : liste le répertoire courant
 * - DELETE /{directory}/{filename} : efface le fichier
 * - POST or PUT /{directory}/{filename} : créer ou remplace le fichier
 */

const SYNC_VERSION = "0.1";
const ENDPOINT = "https://xs-api.pvigier.com";

// Get the Token
$token = $argv[1];
$dirname = substr($token, 0, 8);


function apiQuery(string $method, string $path, string $token, ?string $filename = null): array
{
    $curl = curl_init();
    $headers = [
        'Xmlint-Sandbox-Token: ' . $token,
        'Xmlint-Sandbox-Sync-Version: ' . SYNC_VERSION,
        'Accept: application/json',
    ];

    // Configure cURL options
    $url = ENDPOINT . '/' . $path;
    echo "URL : $url\n";

    $curl_options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true, // Return the response as a string
        CURLOPT_HTTPHEADER => $headers, // Set the headers
        CURLOPT_CUSTOMREQUEST => $method, // Set the HTTP method to GET
        CURLOPT_HEADER => true,
    ];

    // File upload in POST
    if ($filename !== null) {
        $curl_file = curl_file_create($filename);
        $post = array('file_contents'=> $curl_file);
        curl_setopt($curl, CURLOPT_POST,1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
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

    var_dump($headers);
    echo "HTTP Status Code: $httpStatusCode\n";
    echo "Response: $body\n";
    if ($httpStatusCode < 200 || ($httpStatusCode >= 300 && $httpStatusCode != 404)) {
        echo "Sync stopped!\n";
        exit(0);
    }

    return json_decode($body, true);
}

// get the file list
$response = apiQuery('GET', $dirname, $token);
$remote_files = $response['files'];
var_dump($remote_files);

// get the local file list
$scanned_directory = array_diff(scandir("."), array('..', '.'));
$local_files = [];
foreach ($scanned_directory as $file) {
    if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['php', 'perl', 'pl', 'py', 'rb', 'sh', 'md'])) {
        $local_files[$file] = filectime('./' . $file);
    }
}
var_dump($local_files);

// Files to delete
$delete_files = [];
foreach ($remote_files as $filename => $timestamp) {
    if (!array_key_exists($filename, $local_files)) {
        $delete_files[] = $filename;
    }
}
echo "Files to delete\n";
var_dump($delete_files);

// Files to create or upload
$new_files = [];
foreach ($local_files as $filename => $timestamp) {
    if (!array_key_exists($filename, $remote_files)) {
        $new_files[] = $filename;
    }
}
echo "Files to create\n";
var_dump($new_files);

// Files to update
$update_files = [];
foreach ($local_files as $filename => $timestamp) {
    if (array_key_exists($filename, $remote_files) && $timestamp > $remote_files[$filename]) {
        $update_files[] = $filename;
    }
}
echo "Files to update\n";
var_dump($update_files);

// Action!
readline("Press enter to continue, or CTL-C to stop the sync\n");

foreach ($delete_files as $filename) {
    echo " - DELETE $filename\n";
    // apiQuery('DELETE', $dirname . '/' . $filename, $token);
}
foreach ($new_files as $filename) {
    echo " - POST $filename\n";
    apiQuery('POST', $dirname . '/' . $filename, $token, $filename);
    exit(0);
}
foreach ($update_files as $filename) {
    echo " - PUT $filename\n";
    // apiQuery('PUT', $dirname . '/' . $filename, $token, $filename);
}

echo "Sync done!\n";
