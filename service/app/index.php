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

require_once 'internals/check_token.php';
require_once 'internals/bootstrap.php';
require_once 'internals/get_files.php';
require_once 'internals/thumb.php';

// Generate Thumbnails when needed
foreach ($working_dir_files as $filename => $filesize) {
    $png_filename = $full_dir . '/.' . $filename . '.png';
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml' || file_exists($png_filename)) {
        continue;
    }

    // Read the Videotex file as a string, then create and store the image, protect execution by a try/catch
    try {
        $videotex = file_get_contents($full_dir . '/' . $filename);
        $image = thumb($videotex);
        imagepng($image, $png_filename);
        imagedestroy($image);
    } catch (Exception $e) {
        continue;
    }
}

// Analyze the XML files
$projects = [];
foreach($working_dir_xml as $filename => $xml_filesize) {
    $filesize = $xml_filesize;
    $xml = simplexml_load_file($full_dir . '/' . $filename);
    $pages = [];
    $missing_pages = [];
    foreach($xml->xpath('//affiche') as $affiche) {
        $url = (string)$affiche['url'];
        $basename = pathinfo($url, PATHINFO_BASENAME);
        if (isset($working_dir_files[$basename])) {
            $pages[$basename] = filesize($full_dir . '/' . $basename);
        } else {
            $missing_pages[$basename] = true;
        }
        $pages[$basename] = $url;
    }
    // Complete filesize
    foreach($pages as $filename => $vdt_filesize) {
        $filesize += $vdt_filesize;
    }

    $projects[$filename] = [
        'nb_pages' => count($xml->page),
        'nb_vdt' => count($pages) + count($missing_pages),
        'missing_pages' => $missing_pages,
        'filesize' => $filesize,
    ];
}

$working_dir_sorted = array_merge($working_dir_xml, $working_dir_pages);

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>XMLint sandbox - <?= $working_dir ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial;
            }
        </style>
        <link rel="stylesheet" href="/app/css/main.css">
        <link rel="stylesheet" href="/app/css/tabs.css">
        <link rel="stylesheet" href="/app/css/project.css">
        <link rel="stylesheet" href="/app/css/preview.css">
    </head>
    <body>
        <h3>XMLint sandbox - <?= $working_dir ?></h3>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-button" data-tab="project">Projet "<?= $working_dir ?>"</button>
            <button class="tab-button" data-tab="preview">Previsualiser</button>
            <button class="tab-button" data-tab="edit">Ajouter un fichier</button>
            <button class="tab-button" data-tab="doc">Documentation</button>
        </div>

        <!-- Lists the files in the working directory -->
        <div id="project" class="tab-content">
            <!-- Token informations -->
            <div id="token">
                <div id="token-summary">
                    <p>
                        Token : <?= $token ?><br/>
                        Ça peut vous permettre de retourner sur ce projet en cas de changement de navigateur ou d'appareil.
                    </p>
                </div>        
            </div>

            <div id="demo-summary">
                <p>
                <?= count($working_dir_xml) ?> Service(s) Minitel, <?= count($working_dir_files) ?> fichier(s), <?= round($files_spaces / 1000.0, 1) ?> Ko.
                </p>
            </div>

            <!-- les services minitel -->
            <div id="demo-list" class="list-container">
                <?php foreach($projects as $filename => $project): ?>
                    <div class="list-item list-item-xml" data-filename="<?= $filename ?>">
                        <img src="https://xs.pvigier.com/minipavi.png" width="160" height="125" class="thumbnail" />
                        <div class="list-item-details">
                            <div class="list-item-filename"><?= ucfirst(substr($filename, -0, 4)) ?></div>
                            <div class="list-item-filesize">
                                <?= $project['nb_pages'] ?> page(s), <?= $project['nb_vdt'] ?> fichier(s) Videotex, <?= round($project['filesize'] / 1000.0, 1) ?> Ko.
                                <?php if (count($project['missing_pages']) > 0): ?>
                                    <br/>
                                    <p class="error">
                                        <?= count($project['missing_pages']) ?> fichier(s) manquants: <?= implode(', ', array_keys($project['missing_pages'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="list-item-actions">
                                <a href="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename) ?>" target="_blank">
                                    Affichez votre service "<?= ucfirst(substr($filename, -0, 4)) ?>" avec MiniPavi.fr, dans un nouvel onglet.
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Download your project as a ZIP file -->
                <div id="zip">
                    <div id="zip-download">
                        <p>
                            <form action="/app/zip.php" method="post">
                                <input type="submit" name="zip" value="Exportez votre projet en .ZIP" />
                            </form>
                        </p>
                    </div>
                </div>

            <div id="file-list" class="list-container">
                <?php foreach($working_dir_sorted as $filename => $filesize): ?>
                    <div class="list-item <?= strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml' ? 'list-item-xml' : 'list-item-page' ?>" data-filename="<?= $filename ?>">
                        <?php if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xml'): ?>
                            <img src="https://xs.pvigier.com/xml.png" width="160" height="125" class="thumbnail" />
                        <?php else: ?>
                            <img src="https://xs.pvigier.com/<?= $working_dir ?>/.<?= $filename ?>.png" width="160" height="125" class="thumbnail" />
                        <?php endif; ?>
                        <div class="list-item-details">
                            <div class="list-item-filename"><?= $filename ?></div>
                            <div class="list-item-filesize"><?= $filesize . ' octets' ?></div>
                            <div class="list-item-actions">
                                <?php if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'xml'): ?>
                                    <!-- Preview button for Pages -->
                                    <button 
                                        class="preview-button"
                                        infos="Page Minitel <?= $filename ?>"
                                        preview="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename . '.visu') ?>"
                                    >
                                        Prévisualiser la page
                                    </button>
                                <?php else: ?>
                                    <!-- Preview button for XML Services -->
                                    <button 
                                        class="preview-button"
                                        infos="Service Minitel du fichier XML <?= $filename ?>"
                                        preview="https://www.minipavi.fr/emulminitel/index.php?url=<?= urlencode(getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename) ?>"
                                    >
                                        Previsualisez ce service Minitel grace a MiniPavi.fr
                                    </button>

                                    <!-- Affichage du source XMl -->
                                    <form action="<?= getenv('XMLINT_SERVE_URL') . $working_dir . '/' . $filename ?>" method="GET" target="_blank">
                                        <input type="submit" class="source-button" name="source" value="Afficher le source du XML" />
                                    </form>
                                <?php endif; ?>

                                <!-- delete action -->
                                <button class="delete-button">Effacer <?= $filename ?></button>

                            </div>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Edition -->
        <div id="edit" class="tab-content" style="display: none;">
            <!-- uplopad form to add a file -->
            <div id="edit-upload">
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
        </div>

        <!-- Previews -->
        <div id="preview" class="tab-content" style="display: none;">
            <p id="preview-info">Rien à afficher pour l'instant.</p>
            <div id="preview-content">
                &nbsp;
            </div>
        </div>

        <!-- Documentation -->
        <div id="doc" class="tab-content" style="display: none;">
            <p id="doc-info">Rien à afficher pour l'instant.</p>
            <div id="doc-content">
                &nbsp;
            </div>
        </div>


        <!-- Script for the tabs -->
        <script src="/app/js/tabs.js"></script>
    </body>
</html>
