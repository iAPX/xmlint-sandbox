# Service Web XMLint-sandbox

Service minimaliste permettant de créer et d'utiliser sa Sandbox XMLint, en PHP 8+.


## Variables d'environnement
Les variables `XMLINT_SANDBOX_DIR` et `XMLINT_SANDBOX_SEED` doivent être passées à PHP.<br/>
`XMLINT_SANDBOX_DIR` doit contenir le répertoire de stockage des directories des pages vidéotex et XML, il doit être identique à celui configuré pour l'API.<br/>
`XMLINT_SANDBOX_SEED` doit contenir votre seed pour le sha2 et doit être identique à celui passé dans la configuration de l'API.

Par exemple avec Apache 2 et au sein du virtualhost:
```
        SetEnv XMLINT_SANDBOX_DIR       /var/www/xmlint-sandbox/storage
        SetEnv XMLINT_SANDBOX_SEED      not-my-real-seed
```
