<?php

/**
 * Unsecure, @TODO make it secure!
 */

// Token check
$token_part1 = substr($_POST['token'], 0, 24);
$token = strtolower(substr($token_part1 . hash('sha256', getenv('XMLINT_SANDBOX_SEED') . $token_part1), 0, 32));

if ($token !== $_POST['token']) {
    echo "Invalid token";
    exit(0);
}

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 365);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 365);
session_start();
$_SESSION['token'] = $_POST['token'];

?>
<html>
    <body>
        <h1>Token activé, au travail!</h1>
        <a href="/app/index.php">Ouvrez l'App</a>
    </body>
</html>
