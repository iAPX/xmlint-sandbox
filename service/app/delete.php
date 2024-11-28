<?php

/**
 * Delete a file by it's filename
 */

require_once 'internals/bootstrap.php';
require_once 'internals/check_filename.php';

$filename = $_POST['filename'];

$full_filename = $full_dir . '/' . $filename;
if (!checkFilename($filename)) {
    $message = "Nom de fichier '" . htmlspecialchars($filename) . "' incorrect.";
} elseif (file_exists($full_filename) && !is_dir($full_filename)) {
    unlink($full_filename);
    $message = "Fichier détruit.";
} else {
    $message = "Le fichier '" . htmlspecialchars($filename) . "' n'existe pas.";
}

?>
<html>
    <body>
        <div id="delete">
            <div id="message">
                <p><?= $message ?></p>
            </div>
            <div id="back">
                <p><a href="/app">Retour à la liste des fichiers.</a></p>
            </div>
        </div>
    </body>
</html>
