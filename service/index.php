<?php

/**
 * Display Token status, if present
 * 
 * - Token absent : offers create your project | token
 * - Token present but invalid : message + offers create | token
 * - Token present, valid but without a directory : message + offers create | token
 * - Token present, valid and with a directory : offers Access | token | create new
 */


$access_token = $_COOKIE['token'] ?? '';
$message = 'Token absent.';
if ($access_token !== '') {
    $token_part1 = substr($access_token, 0, 24);
    $token = strtolower(substr($token_part1 . hash('sha256', getenv('XMLINT_SANDBOX_SEED') . $token_part1), 0, 32));
    $message = 'Token present mais invalide.';
    if ($token === $access_token) {
        $working_dir = substr($token, 0, 8);
        $full_dir = getenv('XMLINT_SANDBOX_DIR') . '/' . $working_dir;
        $message = 'Token present mais sans directory.';
        if (file_exists($full_dir) && is_dir($full_dir)) {
            $message = '';
        }
    }
}

if ($message !== '') {
?>
<html>
    <head><title>XMLint Sandbox</title></head>
    <body>
        <?= $message ?><br/>
        Nouveau?<br/>
        <a href="/app/create.php">Creez votre projet</a><br/><br/>
        <br/>
        Vous avez un token?<br/>
        <form action="/app/token.php" method="post">
            <input type="text" name="token" value="" />
            <input type="submit" value="Envoyer" />
        </form>
        <br/>
        @TODO presenter succintement<br/>
        Liens vers les sources : <a href="https://github.com/iAPX/xmlint-sandbox">https://github.com/iAPX/xmlint-sandbox</a><br/>
        @TODO vie privee &amp; GDPR<br/>        
        <br/>
    </body>
</html>
<?php
} else {
?>
    <head><title>XMLint Sandbox</title></head>
    <body>
        Acceder a votre projet "<?= $working_dir ?>" : <a href="/app">XMLint Sandbox</a><br/>
        <br/>
        Vous avez un token pour acceder a un autre projet?<br/>
        <form action="/app/token.php" method="post">
            <input type="text" name="token" value="" />
            <input type="submit" value="Envoyer" />
        </form>
        <br/>
        <a href="/app/create.php">Creer un nouveau projet</a><br/><br/>
        <br/>
        <br/>
        @TODO présenter succintement<br/>
        Liens vers les sources : <a href="https://github.com/iAPX/xmlint-sandbox">https://github.com/iAPX/xmlint-sandbox</a><br/>
        @TODO vie privée &amp; GDPR<br/>        
        <br/>

    </body>
<?php
}
