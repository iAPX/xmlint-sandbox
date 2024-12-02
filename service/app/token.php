<?php

/**
 * Get a POSTed or GETed token, check it, check the project's directory
 * 
 * Expect $_POST['token'] ou $_GET['token']
 */

$access_token = $_POST['token'] ?? $_GET['token'] ?? '';

// Token absent
if ($access_token === '') {
    ?>
    <html>
        <body>
            <h1>Token absent.</h1><br/>
            <a href="/">Retour a l'accueil</a>
        </body>
    </html>
    <?php
    exit(0);
}

// Token check
$token_part1 = substr($access_token, 0, 24);
$token = strtolower(substr($token_part1 . hash('sha256', getenv('XMLINT_SANDBOX_SEED') . $token_part1), 0, 32));

// Not a token
if ($token !== $access_token) {
    ?>
    <html>
        <body>
            <h1>Token invalide.</h1><br/>
            <a href="/">Retour a l'accueil</a>
        </body>
    </html>
    <?php
    exit(0);
}

// Not a directory
$working_dir = substr($token, 0, 8);
$full_dir = getenv('XMLINT_SANDBOX_DIR') . '/' . $working_dir;
if (!file_exists($full_dir) || !is_dir($full_dir)) {
    ?>
    <html>
        <body>
            <h1>Token valide, mais ce projet n'est plus disponible.</h1><br/>
            <a href="/">Retour a l'accueil</a>
        </body>
    </html>
    <?php
    exit(0);
}

// Token activation
setcookie("token", $token, time() + (365 * 24 * 60 * 60), "/");

// Redirect to /app/index.php
header('Location: /app/');

