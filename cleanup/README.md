# Script de nettoyage des répertoires

Efface tous les sous-répertoires qui sont sous la forme de 8 chiffres hexadécimaux.<br/>
C'est un script bash. Je le fais tourner chaque nuit, en attendant mieux!<br/>

`find . -type d -regextype posix-extended -regex './[0-9a-fA-F]{8}' -exec rm -r {} +`

> [!IMPORTANT]
> Attention, suivant votre mouture d'OS, testez toujours avant d'utiliser!<br/>
> Ça ne fonctionne pas sous macOS par exemple, mais bien sous GNU/Linux.<br/>
