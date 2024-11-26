# Service simplifié

En fait renvoie les fichiers page ou XML (.xml) mis statiquement dans des sous-répertoires.<br/>
Nécessite PHP dans une version pas trop obsolète et être actif.<br/>

Le fichier 404.php intercepte les erreurs, pour fournir des informations pratiques.<br/>
Si l'extension demandée est .xml, renvoie un XML correctement formé pour MiniPavi, indiquant l'erreur.<br/>
Sinon, renvoie un flux vidéotex effaçant l'écran et contenant le message d'erreur.

[Source 404.php](./404.php)<br/>
[Serveur direct](https://xs.pvigier.com/exemple/xml.xml)</br>
[Serveur via MiniPavi](https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/xml.xml)<br/>
[Service publique](https://xmlint-sandbox.pvigier.com/)<br/>


## Configuration 404.php pour Apache
Dans votre fichier de configuration dans `/etc/apache2/sites-available/` :<br/>
`   ErrorDocument 404 /404.php`


## Tests
Le sous-répertoire exemple permet de tester:<br/>
- XML et page présents: https://xs.pvigier.com/exemple/xml.xml
- XML présent et page absente: https://xs.pvigier.com/exemple/xml.xml puis 1 + ENVOI
- XMl absent: https://xs.pvigier.com/exemple/noxml.xml


## Tests au travers de MiniPavi:
- ok : https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/xml.xml
- page absente :https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/xml.xml puis 1 + ENVOI
- XML absent : https://www.minipavi.fr/emulminitel/index.php?url=https://xs.pvigier.com/exemple/noxml.xml
