<?php

/**
 * Page principale
 * 
 * - Génération Token et affichage
 * - Liste des fichiers, lien delete (refresh), lien afficher (nouvel onglet) suivant XML ou page.
 * - Upload file (refresh)
 * - Lien pour chaque fichier XML pour ouvrir le fichier (nouvel onglet)
 */


// @TODO remove them, prototype-only
$links = ['Démo' => 'https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/xml.xml'];
$test_token = "";

require_once 'internals/bootstrap.php';
require_once 'internals/get_files.php';

?>
<html>
    <head>
        <title>XMLint sandbox - <?= $working_dir ?></title>
    </head>
    <body>
        <h1>XMLint sandbox - <?= $working_dir ?></h1>

        <!-- Token informations -->
        <div id="token">
            <div id="token-summary">
                <p>
                    Token : <?= $token ?><br/>
                    Ça ne vous sert à rien pour le moment, ça viendra plus tard!
                </p>
            </div>        
        </div>

        <!-- Lists the files in the working directory -->
        <div id="files">
            <div id="file-summary">
                <p>
                    <?= count($working_dir_files) ?> Fichiers, <?= round($files_spaces / 1000.0, 1) ?> Ko.
                </p>
            </div>
            <div id="file-list">
                <?php foreach($working_dir_files as $filename => $filesize): ?>
                    <p>
                        <?= $filename . ' (' . $filesize . ' bytes)' ?>
                        <form action="/app/delete.php" method="post">
                            <input type="hidden" name="filename" value="<?= $filename ?>" />
                            <input type="submit" name="delete" value="Effacer <?= $filename ?>" />
                        </form>
                        <?php if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'xml'): ?>
                            <a href="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename . '.visu') ?>" target="_blank">
                                Afficher la page
                            </a>
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- uplopad form to add a file -->
        <div id="upload">
            <div id="upload-summary">
            <p>
                Téléversez un fichier local vers votre Sandbox XMLint.<br/>
            </p>
                </div>
            <div id="upload-form">
                <form action="/app/upload.php" method="POST" enctype="multipart/form-data">
                    <input type="file" id="myFile" name="upfile">
                    <input type="submit">
                </form>
            </div>
        </div>


        <!-- links to Démo(s) -->
        <div id="demos">
            <div id="demo-summary">
                <p>
                <?= count($working_dir_xml) ?> Fichiers XML.
                </p>
            </div>
            <div id="demo-list">
                <p><a href="https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/xml.xml" target="_blank">Fake Démo XML @todo remove</a></p>
                <br/><br/>
                <?php foreach($working_dir_xml as $filename => $filesize): ?>
                    <p>
                        <a href="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename) ?>" target="_blank">
                            <?= $filename . ' (' . $filesize . ' bytes)' ?>
                        </a>
                        <br/>
                        <a href="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename) ?>" target="_blank">
                            Afficher votre service Minitel sur minipavi.fr
                        </a>
                        <br/>
                        <a href="<?= getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename ?>" target="_blank">Afficher le fichier XML</a>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- Download your project as a ZIP file -->
        <div id="zip">
            <div id="zip-download">
            <p>
                <form action="/app/zip.php" method="post">
                    <input type="submit" name="zip" value="Sauvez votre projet en .ZIP" />
                </form>
            </p>
        </div>
    </body>
</html>
