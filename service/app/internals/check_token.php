<?php

/**
 * Checks the token, display an error page if absent.
 * 
 * Refresh the token.
 * Create $working_dir and $full_dir and check if the directory exists
 */

// Is token is not present
if (!isset($_COOKIE['token'])) {
    ?>
    <html>
        <body>
            <h1>Token absent.</h1><br/>
            <a href="/">Retour a l'accueil</a><br/>
        </body>
    </html>
    <?php
    exit(0);
}

// If token is not correct
$token_part1 = substr($_COOKIE['token'], 0, 24);
$token = strtolower(substr($token_part1 . hash('sha256', getenv('XMLINT_SANDBOX_SEED') . $token_part1), 0, 32));
if ($token !== $_COOKIE['token']) {
    ?>
    <html>
        <body>
            <h1>Token hash invalide.</h1><br/>
            <a href="/">Retour a l'accueil</a><br/>
        </body>
    </html>
    <?php
    exit(0);
}

// If working dir doesn't exist
$working_dir = substr($token, 0, 8);
$full_dir = getenv('XMLINT_SANDBOX_DIR') . '/' . $working_dir;
if (!file_exists($full_dir) || !is_dir($full_dir)) {
    ?>
    <html>
        <body>
            <h1>Ce projet n'est plus disponible.</h1><br/>
            <a href="/">Retour a l'accueil</a>
        </body>
    </html>
    <?php
    exit(0);
}

// Refresh token Cookie
setcookie("token", $token, time() + (365 * 24 * 60 * 60), "/");
