# xmlint-sandbox
XMLint-sandbox vous permet de tester vos services Minitel XMLint sans avoir de serveur, d'hébergement ans modifier les réglages de votre routeur, ou si vous n'y avez pas accès.

En bref, il règle un seul problème: comment tester un service Minitel pour XMLint le plus facilement possible.<br/>
Il ne règle pas tous les problèmes liés au développement, mais il aide à démarrer en quelques minutes!


## Comment faire votre service
- Connectez-vous à l'interface utilisateur [https://xmlint-sandbox.pvigier.com](https://xmlint-sandbox.pvigier.com)
- Activez votre sandbox, en cliquant sur le bouton "Activer"
- Utilisez l'interface, et faites-vous plaisir!

- Rince and repeat!
- Modifiez, ajoutez, effacez, relancez la synchronisation, réessayez!

Consultez [le site MiniPavi](https://www.minipavi.fr/), [les sources de MiniPavi sont ici](https://github.com/ludosevilla/minipavi)<br/>
Lisez [la documentation PDF de XMLint](https://raw.githubusercontent.com/ludosevilla/minipaviCli/master/XMLint/XMLint-doc.pdf)<br/>
Et [plognez-vous dans le PDF des STUMS1b](https://www.minipavi.fr/stum1b.pdf) et [le résumé PDF des codes du Minitel](https://www.minipavi.fr/videotex-codes.pdf).<br/>
Pour créer ou modifier une page Vidéotex, [utilisez MiEdit](https://minitel.cquest.org/), les [sources de MiEdit sont ici](https://github.com/Zigazou/miedit).<br/>


## Limites & contraintes
- Votre service Minitel ne peut avoir de sous-répertoire, le XML et les pages doivent être au même niveau.
- Votre fichier XML doit avoir l'extension .xml (!!!)
- Un fichier XML non-valide ne sera pas accepté, pas plus qu'un fichier XML de plus de 64 Ko
- Les pages seront toutes servies depuis votre répertoire temporaire, quelque-soit l'URL que vous avez assigné à chacune (simplification)
- Votre XML sera modifié avant d'être stocké coté serveur, pour faire pointer chaque page sur votre répertoire du serveur.
- Le nombre de pages est limité à 100. Chaque page ne doit pas dépasser 4Ko.
- Les répertoires temporaires sont effacés après inutilisation, et de toute façon chaque nuit même si activement utilisés. XMLint-sandbox n'est pas de l'hébergement!
- Évidemment certaines extensions comme .php sont interdites, tout comme certains noms de fichier... Coquin!


## Serveur pour MiniPavi : /serve
[X] Premier prototype
[X] Version exploitable
Consultez [la documentation du serveur pour MiniPavi](./serve/README.md)

Service simplifié servant des pages statiques, et interceptant les erreurs pour afficher des messages plus explicite sur l'absence de, page, de XML ou de répertoire.<br/>
C'est agréable d'avoir un message d'erreur explicite!


## Synchronisation des fichiers : /sync
[X] Premier prototype
[ ] Version exploitable
Script PHP qui synchronise vos fichiers avec votre répertoire temporaire, et qui aussi indiquera les erreurs dans le XML téléversé sir votre XML n'est pas correct.<br/>
Consultez [la documentation du synchronisateur de fichiers](./sync/README.md)


## Rest API : /api
[X] Premier prototype
[ ] Version exploitable
Consultez [la documentation de l'API REST](./api/README.md)<br/>
Sert à la synchronisation de fichier, ainsi qu'à l'interface du service xmlint-sandbox, pour la rendre plus dynamique.


## Service xmlint-sandbox : /service
[ ] Premier prototype
[ ] Version exploitaable
Consultez [la documentation du service xmlint-sandbox](./service/README.md)

Essentiellement, permet de créer une sandbox temporaire et d'interagir avec elle, en affichant le service dans un iframe<br/>


## Nettoyage régulier : /cleanup
[X] Premier prototype
[ ] Version opérationnelle
Consultez [la documentation du script de cleanup](./cleanup/README.md)

Efface régulièrement les fichiers et répertoires temporaires des usagers.
