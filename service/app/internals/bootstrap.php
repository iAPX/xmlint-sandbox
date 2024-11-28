<?php

/**
 * Init the environment
 * 
 * - Load the session()
 * - Create token and dir if absent, store token in session
 */

const REAL_MAX_XML_LENGTH = 65000;
const MAX_XML_LENGTH = 60000;
const MAX_PAGE_LENGTH = 4096;
const MAX_FILES = 100;

session_set_cookie_params(86400 * 365);
session_start();

// Token generation and storage
if (!isset($_SESSION['token'])) {
    $timestamp = time();
    while (file_exists(getenv('XMLINT_SANDBOX_DIR') . '/' . strtolower(dechex($timestamp)))) {
        // Just in case, to ensure unicity!
        $timestamp++;
    }
    $token_part1 = strtolower(dechex($timestamp) . bin2hex(random_bytes(8)));
    $token = strtolower(substr($token_part1 . hash('sha256', getenv('XMLINT_SANDBOX_SEED') . $token_part1), 0, 32));
    $_SESSION['token'] = $token;
    $_SESSION['timestamp'] = time();
} else  {
    if (!isset($_SESSION['timestamp']) || $_SESSION['timestamp'] + 86400 < time()) {
        // Regenerate session with new timestamp. One per 24h
        $_SESSION['timestamp'] = time();
        session_regenerate_id(true);
    }
    $token = $_SESSION['token'];
}

// Check if storage exists, create it if not
$working_dir = substr($_SESSION['token'], 0, 8);
$full_dir = getenv('XMLINT_SANDBOX_DIR') . '/' . $working_dir;
if (!file_exists($full_dir)) {
    mkdir($full_dir);

    $page_url = getenv('XMLINT_SERVE_URL') . $working_dir . '/demo.vdt';

    // Create default xml & Videotex
    $xml = <<<XML
<service>
    <interpreteur url="http://www.minipavi.fr/XMLint/?xurl=" /><debut nom="accueil" />
    <page nom="accueil">
        <ecran>
            <affiche url="$page_url" />

            <position ligne="12" col="1" />
            <ecrit texte="Fichier XML : demo.xml" />
            <position ligne="13" col="1" />
            <ecrit texte="Dir : $working_dir" />
            <position ligne="14" col="1" />
            <ecrit texte="Repo GIT:" />
            <position ligne="15" col="1" />
            <ecrit texte="https://github.com/iAPX/xmlint-sandbox" />
        </ecran>
        <entree>
            <zonesaisie ligne="24" col="40" longueur="1" curseur="visible" />
            <validation touche="repetition" />
        </entree>
        <action defaut="Choix non proposé!">
            <saisie touche="repetition" suivant="accueil" />
        </action>
    </page>
</service>
XML;

    file_put_contents($full_dir . '/demo.xml', $xml);
    file_put_contents($full_dir . '/demo.vdt', chr(12) . "Page de demo XMLint Sandbox.\r\nFichier Page : demo.vdt");
}
