<?php

/**
 * Upload d'un fichier local vers le serveur XMLint Sandbox
 * 
 * - vérification filename
 * - vérification taille et contenu
 * - vérification nb fichiers (si pas déjà présent)
 */

require_once "internals/check_token.php";
require_once "internals/bootstrap.php";
require_once "internals/get_files.php";
require_once "internals/check_filename.php";

// Just in case
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['upfile'])) {
    ?>
    <html><body>Erreur d'appel.</body></html>
    <?php
    exit(0);
}

$filename = $_FILES['upfile']['name'];

function processUploadedFile(string $filename, string $working_dir, string $full_dir, array $working_dir_files)
{
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);

    if (!isset($_FILES['upfile'])) {
        return "Pas de fichier téléversé";
    }
    if($_FILES['upfile']['error'] !== UPLOAD_ERR_OK) {
        return "Erreur dans le téléversement.";
    }

    // Empty file?
    if ($_FILES['upfile']['size'] === 0) {
        return "Fichier vide";
    }

/*
    // too heavy for XML? With a 4KB margin to enable page URL transposition!
    if (strtolower($extension) === 'xml' && $_FILES['upfile']['size'] > REAL_MAX_XML_LENGTH) {
        return "Fichier XML trop lourd, la limite est de " . MAX_XML_LENGTH . " octets.";
    }

    // Too heavy for Pages
    if (strtolower($extension) !== 'xml' && $_FILES['upfile']['size'] > MAX_PAGE_LENGTH) {
        return "Fichier page trop lourd, la limite est de " . MAX_PAGE_LENGTH . " octets.";
    }
*/

    // Begins with <? or #! ??? Security problem!
    $content = file_get_contents($_FILES['upfile']['tmp_name']);
    if ($content === false) {
        return "Le fichier téléversé n'est pas accessible?!?";
    }
    if (substr($content, 0, 5) !== '<?xml' && in_array(substr($content, 0, 2), ['<?', '#!'])) {
        return "le fichier semble suspect. Vérifiez son contenu avant de réessayer.";
    }

    // Correct XML file?
    if (strtolower($extension) === 'xml') {
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            return "Fichier XML invalide, probablement mal formé.";
        }
        // libère la mémoire
        unset($xml);
    }

    // On le stocke (ou pas) en fonction du nombre de fichiers.
    $full_filename = $full_dir . '/' . $filename;
    if (!file_exists($full_filename)) {
        // File adding
        if (count($working_dir_files) >= MAX_FILES) {
            return "Le nombre de fichier maximum, " . MAX_FILES. " est atteint.";
        }
    } else {
        // File overwriting
        unlink($full_filename);
    }
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $full_filename)) {
        return "Problème lors du déplacement du fichier!?!";
    } else {
        // Xml transcrit!
        if (strtolower($extension) === 'xml') {
            $xml = simplexml_load_file($full_filename);
            foreach ($xml->xpath('//affiche[@url]') as $page) {

                $currentUrl = (string)$page['url'];
                $basename = pathinfo($currentUrl, PATHINFO_BASENAME);
                // @TODO refaire
                $page['url'] = "https://xs.pvigier.com/" . $working_dir . '/' . $basename;
            }
        
            // Output the modified XML as a string
            $modifiedXmlString = $xml->asXML();
            file_put_contents($full_filename, $modifiedXmlString);
        }

        // RW permissions uniquement pour le Web.
        chmod($full_filename, 0600);
    }

    return true;
}

// Vérifier le nom de fichier, puis le fichier uploadé.
if (!checkFilename($filename)) {
    $message = "Nom de fichier '" . htmlspecialchars($filename) . "' incorrect.";
} else {
    $message = processUploadedFile($filename, $working_dir, $full_dir, $working_dir_files);
    if ($message === true) {
        $message = "Fichier '" . htmlspecialchars($filename) . "' téléversé.";
    }
}

?>
<html>
    <body>
        <div id="uploaded">
            <div id="message">
                <p><?= $message ?></p>
            </div>
            <div id="back">
                <p><a href="/app">Retour à la liste des fichiers.</a></p>
            </div>
        </div>
    </body>
</html> 
