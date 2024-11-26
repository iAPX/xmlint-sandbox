# API pour XMLint Sandbox

C'est actuellement une API minimaliste en PHP 8+.

Notez que chaque requête doit posséder le header `Xmlint-Sandbox-Token` contenant le Token utilisateur.<br/>
Il sert à vérifier que le token est valide et actuel.<br/>
Les entrées d'API appelables par le Synchronisateur "Sync" doivent comporter sa version dans le header `Xmlint-Sandbox-Sync-Version`.


## Variables d'environnement
Les variables `XMLINT_SANDBOX_DIR` et `XMLINT_SANDBOX_SEED` doivent être passées à PHP.<br/>
`XMLINT_SANDBOX_DIR` doit contenir le répertoire de stockage des directories des pages vidéotex et XML, il doit être identique à celui configuré pour le service.<br/>
`XMLINT_SANDBOX_SEED` doit contenir votre seed pour le sha2 et doit être identique à celui passé dans la configuration du service.

Par exemple avec Apache 2 et au sein du virtualhost:
```
        SetEnv XMLINT_SANDBOX_DIR       /var/www/xmlint-sandbox/storage
        SetEnv XMLINT_SANDBOX_SEED      not-my-real-seed
```

## Sync : GET /{directory}/
Vérifie la version du synchronisateur, retourne une erreur si elle ne correspond pas.<br/>
Si la version correspond, renvoie la liste des fichiers présents et leur timestamp EPOCH.

Renvoie un status 404 si le directory n'existe pas, 200 avec les données s'il existe et 403 en cas d'erreur de version de Sync.
Si le nom de directory est incorrect, un status 400 est retourné (exemple: `"/../../../etc/passwd"` ).


## Sync : DELETE /{directory}/{filename}
Efface le fichier indiqué.

Renvoie un status 404 si le fichier n'était pas présent et 200 si il a bien été effacé.<br/>
Si le nom de répertoire ou de fichier est incorrect, un status 400 est retourné (exemple: `"/../../../etc/passwd"` ).


## Sync : POST ou PUT /{directory}/{filename}
Crée ou remplace le fichier indiqué.

Renvoie un status 200 si l'opération est effectuée.<br/>
Renvoie un status 400 dans les cas suivant:<br/>
- Fichier malformé.
- Chargement d'un second fichier XML quand un premier est déjà présent.
- Fichier XML de 60Ko ou plus. (60 000 octets)
- Fichier non-xml (Vidéotex) de 4Ko ou plus.
- Plus de 100 fichiers dans votre répertoire si celui-ci était chargé.
- Nom de répertoire ou de fichier incorrect (voir plus haut)

> [!IMPORTANT]
> Les fichiers XML sont modifiés pour que les pages pointent au bon endroit.<br/>
> Si le fichier fait 60Ko ou plus, il est remplacé par un fichier contenant cette erreur.<br/>
> Si le fichier XML est mal formé, il est aussi remplacé par un fichier indiquand l'erreur.<br/>
