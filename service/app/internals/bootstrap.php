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

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 365);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
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
} else  {
    $token = $_SESSION['token'];
}

// Check if storage exists, create it if not
$working_dir = substr($_SESSION['token'], 0, 8);
$full_dir = getenv('XMLINT_SANDBOX_DIR') . '/' . $working_dir;
if (!file_exists($full_dir)) {
    mkdir($full_dir);

    $page_url = getenv('XMLINT_SERVE_URL') . $working_dir . '/demo.vdt';
    $xml_encoded_url = "https://www.minipavi.fr/emulminitel/index.php?url=" . urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/demo.xml');

    // Create default xml & Videotex
    $xml = <<<XML
<service>
    <!-- consultez la documentation PDF : https://raw.githubusercontent.com/ludosevilla/minipaviCli/master/XMLint/XMLint-doc.pdf  -->
    <!-- Votre service Minitel est visible ici : $xml_encoded_url -->
    <interpreteur url="http://www.minipavi.fr/XMLint/?xurl=" />

    <!-- indique le nom de la première page affichée de votre service -->
    <debut nom="accueil" />

    <!-- Une seule page, rajoutez-en avec des "nom" différents ! -->
    <page nom="accueil">
        <ecran>
            <!-- Ici modifiez l'affichage de votre page ! -->

            <!-- Vous pouvez créer et éditer des fichiers Vidéotex avec : https://minitel.cquest.org/ -->
            <affiche url="$page_url" />

            <!-- Vous pouvez aussi afficher directement depuis le XML ! -->
            <position ligne="8" col="1" />
            <ecrit texte="Fichier XML : demo.xml" />
            <position ligne="10" col="1" />
            <ecrit texte="Dir : $working_dir" />

        </ecran>
        <entree>
            <!-- Indiquez une Zone de Saisie -->
            <zonesaisie ligne="24" col="40" longueur="1" curseur="visible" />

            <!-- Indiquez quelles touches de fonction du Minitel vous voulez gérer -->
            <validation touche="repetition" />
        </entree>
        <action defaut="Choix non proposé!">
            <!-- Ici ajoutez les différentes actions en fonction de la réponse utilisateur -->
            <saisie touche="repetition" suivant="accueil" />
        </action>
    </page>
</service>
XML;

    $page = "\x0C\x1F\x42\x41\x1B\x54  \x18\x1B\x4F  XMLint Sandbox";
    $page .= "\x1F\x43\x41   Votre service Minitel en 1 minute!";
    $page .= "\x1F\x46\x41Fichier Videotex : demo.vdt";

    file_put_contents($full_dir . '/demo.xml', $xml);
    file_put_contents($full_dir . '/demo.vdt', $page);
}
