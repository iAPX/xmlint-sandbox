<?php

/**
 * File not found.
 * 
 * Used to display the error through MiniPavi!
 */


// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];

// Parse the URI to extract the file path and name
$pathInfo = pathinfo($requestUri);

// Extract parts of the path
$dirname = trim($pathInfo['dirname'], '/'); // Subdirectories (without leading or trailing slashes)
$basename = $pathInfo['basename'];          // Full file name (e.g., 'file.html')
$filename = $pathInfo['filename'] ?? '';    // File name without extension (e.g., 'file')
$extension = $pathInfo['extension'] ?? '';  // File extension (e.g., 'html')

// What is the error?
$error = "Fichier " . $filename . "." . $extension . " introuvable.";
if (!file_exists($dirname) || !is_dir($dirname)) {
    $error = "Dossier " . $dirname . " introuvable.";
}

// XML?
if ($extension === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $output = <<<XML
<service>
    <interpreteur url="http://www.minipavi.fr/XMLint/?xurl=" />
    <debut nom="erreur" />
    <page nom="erreur">
        <ecran>
            <efface />
            <couleur texte="rouge" />
            <ecrit texte="$error" />

            <position ligne="4" col="1" />
            <ecrit texte="Site:" />
            <position ligne="5" col="1" />
            <ecrit texte="https://xmlint-sandbox.pvigier.com/" />

            <position ligne="8" col="1" />
            <ecrit texte="Repo Git:" />
            <position ligne="9" col="1" />
            <ecrit texte="https://github.com/iAPX/xmlint-sandbox" />
        </ecran>
        <entree>
            <zonesaisie ligne="24" col="40" longueur="1" curseur="visible" />
            <validation touche="repetition" />
        </entree>
        <action defaut="Choix non proposé!">
            <saisie touche="repetition" suivant="erreur" />
        </action>
    </page>
</service>
XML;
} elseif ($extension === 'png') {
    // Vignette manquante, pour du vidéotex.
    $png_filename = "./vdt.png";
    header("Content-Type: image/png");
    header("Content-Length: " . filesize($png_filename));
    header('Expires: 0');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    readfile($png_filename);
    exit(0);
} elseif ($extension === 'visu') {
    // Affiche la page concernée appelée {pagename}.{extension}.visu
    header('Content-Type: application/xml; charset=utf-8');
    $output = <<<XML
<service>
    <interpreteur url="http://www.minipavi.fr/XMLint/?xurl=" />
    <debut nom="visu" />
    <page nom="visu">
        <ecran>
            <efface />
            <position ligne="0" col="1" />
            <ecrit texte="Fichier : $filename                      " />
            <position ligne="1" col="1" />
            <affiche url="https://xs.pvigier.com/$dirname/$filename" />
        </ecran>
        <entree>
            <zonesaisie ligne="24" col="40" longueur="1" curseur="visible" />
            <validation touche="repetition" />
        </entree>
        <action defaut="Choix non proposé!">
            <saisie touche="repetition" suivant="erreur" />
        </action>
    </page>
</service>
XML;
} else {
    $output = chr(12) . "\x1B\x41" . $error;
}

echo $output;
