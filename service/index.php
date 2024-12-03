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

?>
<html>
    <head>
        <title>XMLint Sandbox</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial;
            }
        </style>
        <link rel="stylesheet" href="/app/css/homepage.css">
    </head>
    <body>
<?php

if ($message !== '') {
?>
        <div class="container">
            <div id="container-message">
                <p><?= $message ?></p>
            </div>
            <div id="container-create">
                <p>Nouveau sur XMLint Sandbox?</p>
                <p><a href="/app/create.php">Creez votre projet</a></p>
            </div>  
            <div id="container-token">
                <p>Vous avez un token?</p>
                <p>
                    <form action="/app/token.php" method="post">
                        <input type="text" name="token" value="" />
                        <input type="submit" value="Envoyer" />
                    </form>
                </p>
            </div>
        </div>
<?php
} else {
?>
        <div class="container">
            <div id="container-project">
                <p>Acceder a votre projet "<?= $working_dir ?>" : <a href="/app">XMLint Sandbox</a></p>
            </div>
            <div id="container-token">
                <p>Vous avez un token pour acceder a un autre projet?</p>
                <p>
                    <form action="/app/token.php" method="post">
                        <input type="text" name="token" value="" />
                        <input type="submit" value="Envoyer" />
                    </form>
                </p>
            </div>
            <div id="container-create">
                <a href="/app/create.php">Creer un nouveau projet</a><br/><br/>
            </div>
        </div>
<?php
}

?>
        <!-- fin commune à tous -->
        <div class="container">
            <div id="container-about">
                <p>
                    XMLint Sandbox est un <em>prototype</em> de site Web permettant a tout-un-chacun de creer un service Minitel pour XMLint et MiniPavi.<br/>
                    Vous n'avez pas besoin de serveur, d'ouvrir des ports à Internet sur votre routeur ou de quoi que ce soit de technique.<br/>
                    XMLint Sandbox est gratuit. Mais c'est un prototype. Les donnees peuvent etre effacees volontairement ou perdues a tout moment. Le service s'arrêter<br/>
                    Pensez a sauvegarder votre travail via l'option de telechargement au format ZIP.
                </p>
                <p>
                    Vous pouvez créer des pages Minitel avec <a href="https://minitel.cquest.org/">MiEdit</a>.<br/>
                    Vous pouvez modifier le fichier XML de votre service Minitel simplement en le téléchargeant, en l'éditant localement et en le téléversant depuis l'App Web.<br/>
                    La <a href="https://raw.githubusercontent.com/ludosevilla/minipaviCli/master/XMLint/XMLint-doc.pdf">documentation de XMLint est disponible en PDF ici</a>.<br/>
                    <a href="https://minipavi.fr">Minipavi.fr</a> est un excellent point de départ global, intégrant XMLint (XML), MiniPavi et MiniPaviCli (PHP), en vous offrant de multiples possibilites.<br/>
                    J'ai aussi realise MiniPaviFwk, micro-framework PHP de programmation de services Minitel en liaison avec MiniPaviCli et MiniPavi.<br/>
                    <br/>
                    Pour finir, je recommande de télécharger <a href="https://www.minipavi.fr/stum1b.pdf">les STUM1b en PDF</a>, ainsi que <a href="https://www.minipavi.fr/videotex-codes.pdf">ce resume PDF des codes Videotex du Minitel<a/>.
                </p>
            </div>

            <div id="container-sources">
                <p>
                    Les sources de ce prototype sont disponibles sur <a href="https://github.com/iAPX/xmlint-sandbox">https://github.com/iAPX/xmlint-sandbox</a>.
                </p>
                <p>
                    Sources externes:<br/>
                    Les sources de MiEdit sont disponibles sur <a href="https://github.com/Zigazou/miedit">https://github.com/Zigazou/miedit</a>.<br/>
                    Les sources de MiniPavi sont disponibles sur <a href="https://github.com/ludosevilla/minipavi">https://github.com/ludosevilla/minipavi</a>.<br/>
                    Les sources de MiniPaviCli sont disponibles sur <a href="https://github.com/ludosevilla/minipaviCli">https://github.com/ludosevilla/minipaviCli</a>.<br/>
                    Les sources de XMLint sont disponibles sur <a href="https://github.com/ludosevilla/xmlint">https://github.com/ludosevilla/xmlint</a>.<br/>
                    Les sources de MiniPaviFwk sont disponibles sur <a href="https://github.com/iAPX/minipavifwk">https://github.com/iapx/minipavifwk</a>.
                </p>
            </div>

            <div id="container-gdpr">
                <p>Cote GDPR/RGPD et protection des donnees et de la vie privee:</p>
                <p>Les fichiers sont stockes au Canada, hors UE. N'utilisez pas ce prototype si vos données sont sensibles ou nécessitent de rester en UE.</p>
                <p>Les donnees restent sous notre controle complet, a l'exception de leur acces en HTTP, elles sont donc considerees comme public et non confidentielles.</p>
                <p>Nous ne stockons aucune donnee personnelle, ce prototype a ete concu en ce sens. Ni adresse IP ni email.</p>
                <p>Un seul cookie est utilisé: "token" qui stocke votre token courant, il ne comporte aucune PII.</p>
            </div>

            <div id="container-license">
                <p>
                    Ce prototype est realise sous <a href="https://mit-license.org/">licence MIT</a>. Pas de "Copyright" reclame.<br/>
                    Vous faites ce que vous voulez. Pas d'autorisation a demander. Pas besoin de me citer. Agissez librement!
                </p>
            </div>
        </div>

    </body>
</html>
