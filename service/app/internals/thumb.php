<?php

/**
 * Convert a Videotex stream into a 320x250 PNG image, for preview on image list.
 */

function thumb(string $videotex) {
    $image = imagecreatetruecolor(320, 250);

    // Init the palette for Minitel
    $palette = [];
    $palette[0] = imagecolorallocate($image, 0, 0, 0);
    $palette[1] = imagecolorallocate($image, 255, 0, 0);
    $palette[2] = imagecolorallocate($image, 0, 255, 0);
    $palette[3] = imagecolorallocate($image, 0, 255, 255);
    $palette[4] = imagecolorallocate($image, 0, 0, 255);
    $palette[5] = imagecolorallocate($image, 255, 0, 255);
    $palette[6] = imagecolorallocate($image, 0, 255, 255);
    $palette[7] = imagecolorallocate($image, 255, 255, 255);

    // init the registers
    $ligne = 1;
    $colonne = 1;
    $last_ligne = 1;
    $last_colonne = 1;
    $couleur_texte = 7;
    $couleur_fond = 0;
    $last_couleur_texte = 7;
    $last_couleur_fond = 0;
    $double_largeur = false;
    $double_hauteur = false;
    $inversion = false;
    $mode_alphamosaique = false;
    $last_car = ' ';

    // Au cas où, ça enlève beaucoup de cas d'erreurs et donc de tests!
    $videotex .= "\x00\x00\x00\x00";

    // Let's go!
    while ($videotex !== '') {
        $car = ord($videotex[0]);
        $videotex = substr($videotex, 1);
        switch ($car) {
            case 0x00:
                break;
            case 0x08:
                $colonne--;
                reposition($ligne, $colonne);
                break;
            case 0x09:
                $colonne++;
                reposition($ligne, $colonne);
                break;
            case 0x0A:
                if ($ligne === 0) {
                    $ligne = $last_ligne;
                    $colonne = $last_colonne;
                    $couleur_texte = $last_couleur_texte;
                    $couleur_fond = $last_couleur_fond;
                    $double_largeur = false;
                    $double_hauteur = false;
                    $inversion = false;
                    $mode_alphamosaique = false;
                } else {
                    $ligne++;
                }
                reposition($ligne, $colonne);
                break;
            case 0x0B:
                $ligne--;
                reposition($ligne, $colonne);
                break;
            case 0x0C:
                // Clear screen
                imagefill($image, 0, 0, $palette[0]);
                // No break, this is intentional, Clear screen, Home and Position share a lot!
            case 0x1E:
                // Home, implicit position (1, 1)
            case 0x1F:
                // Reposition explicitly
                $last_ligne = $ligne;
                $last_colonne = $colonne;
                $last_couleur_texte = $couleur_texte;
                $last_couleur_fond = $couleur_fond;

                // And reinit!
                $ligne = 1;
                $colonne = 1;
                $couleur_texte = 7;
                $couleur_fond = 0;
                $double_largeur = false;
                $double_hauteur = false;
                $inversion = false;
                $mode_alphamosaique = false;

                // Si positionnement, effectue-le!
                if ($car === 0x1F) {
                    $car2 = ord($videotex[0]);
                    $car3 = ord($videotex[1]);
                    $videotex = substr($videotex, 2);

                    if ($car2 < 0x40) {
                        $ligne = ($car2 - 0x30) * 10 + ($car3 - 0x30);
                        $colonne = 1;
                    } else {
                        $ligne = $car2 & 63;
                        $colonne = $car3 & 63;
                    }
                    reposition($ligne, $colonne);
                }
                break;
            case 0x0D:
                $colonne = 1;
                break;
            case 0x0E:
                $mode_alphamosaique = true;
                break;
            case 0x0F:
                $mode_alphamosaique = false;
                break;
            case 0x12:
                // répétition du dernier caractère affichable!
                $repetition = ord($videotex) & 63;
                $videotex = str_repeat(chr($last_car), $repetition) . substr($videotex, 1);
                break;
            case 0x18:
                // Efface fin de ligne, simulé (!!!)
                for ($x = $colonne; $x <= 40; $x++) {
                    thumb_draw_alphamosaic($image, $palette, $ligne, $x, $couleur_texte, $couleur_fond, 32);
                }
                break;
            case 0x19:
                // Accentué, on jette!
                $videotex = substr($videotex, 1);
                break;
            case 0x1B:
                // Échappements!
                $car2 = ord($videotex);
                $videotex = substr($videotex, 1);
                 if ($car2 >= 0x40 && $car2 <= 0x47) {
                     $couleur_texte = $car2 & 7;
                 } elseif ($car2 >=0x50 && $car2 <= 0x57) {
                     $couleur_fond = $car2 & 7;
                 } elseif ($car2 >= 0x4C && $car2 <= 0x4F) {
                    $double_hauteur = ($car2 & 1) == 1;
                    $double_largeur = ($car2 & 2) == 2;
                 } elseif ($car2 === 0x5C) {
                     $inversion = false;
                 } elseif ($car2 === 0x5D) {
                     $inversion = true;
                 }
                 break;
            default:
                if ($car >= 32) {
                    $last_car = $car;
                    if ($mode_alphamosaique) {
                        thumb_draw_alphamosaic($image, $palette, $ligne, $colonne, $couleur_texte, $couleur_fond, $car);
                        $colonne++;
                        reposition($ligne, $colonne);                            
                    } else {
                        thumb_draw_char($image, $palette, $ligne, $colonne, $couleur_texte, $couleur_fond, $double_largeur, $double_hauteur, $inversion, $car);
                        $colonne += $double_largeur ? 2 : 1;
                        reposition($ligne, $colonne, $double_hauteur);
                    }
                }
                break;
        }
    }

    // Et la touche finale!
    thumb_draw_char($image, $palette, 0, 39, 7, 0, false, false, true, ord('C'));

    return $image;
}

function thumb_draw_alphamosaic($image, array $palette, int $ligne, int $colonne, int $couleur_texte, int $couleur_fond, int $car) {
    // Fond
    $px = ($colonne-1) * 8;
    $py = $ligne * 10;
    imagefilledrectangle($image, $px, $py, $px + 7, $py + 9, $palette[$couleur_fond]);

    // Trace le caractère alphamosaïque
    $depy = [0, 3, 7];
    $height = [2, 3, 2];
    $mask = $car - 32;
    for ($deply = 0; $deply < 3; $deply++) {
        $dy = $depy[$deply];
        $sy = $height[$deply];
        for ($dx = 0; $dx < 8; $dx+= 4) {
            $px = ($colonne-1) * 8 + $dx;
            $py = $ligne * 10 + $dy;
            if ($mask & 1) {
                imagefilledrectangle($image, $px, $py, $px + 3 , $py + $sy, $palette[$mask & 1 ? $couleur_texte : $couleur_fond]);
            }
            $mask >>= 1;
        }
    }
}

function thumb_draw_char($image, array $palette, int $ligne, int $colonne, int $couleur_texte, int $couleur_fond, int $double_largeur, int $double_hauteur, int $inversion, int $car) {
    // Inversion gérée
    if ($inversion) {
        $tmp = $couleur_texte;
        $couleur_texte = $couleur_fond;
        $couleur_fond = $tmp;
    }

    // Background drawing
    $px = ($colonne-1) * 8;
    $py = $ligne * 10 - ($double_hauteur ? 10 : 0);
    $sx = $double_largeur ? 16 : 8;
    $sy = $double_hauteur ? 20 : 10;
    imagefilledrectangle($image, $px, $py, $px + $sx - 1, $py + $sy - 1, $palette[$couleur_fond]);

    // Draw the character itself
    $fontsize = 6.0;
    $angle = 0;
    if ($double_largeur &&  $double_hauteur) {
        $fontsize = 12.0;
    } else if($double_hauteur) {
        $fontsize = 9.0;
    } elseif ($double_largeur) {
        $fontsize = 9.0;
        $angle = -10.0;
    }
    imagettftext($image, $fontsize, $angle, $px, $py + $sy - 2, $palette[$couleur_texte], "./minitel.ttf", chr($car));
}

function reposition(&$ligne, &$colonne, bool $double_hauteur = false) {
    // Repositionne le curseur après déplacement, sauf en ligne 00!
    if ($colonne > 40) {
        $colonne = 1;
        $ligne += $double_hauteur ? 2 : 1;
    }
    if ($ligne > 24) {
        $ligne = 1;
    }
}
