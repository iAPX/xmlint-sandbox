# Synchronisateur

Synchronise le répertoire de la sandbox avec le répertoire courant.<br/>
Il peut être stocké dans le répertoire courant, car il ne synchronise aucun fichier PHP.


## Usage
`php ./sync.php {token}`

Synchronise le répertoire pointé par le token avec les données courante.<br/>
Valide le fichier XML et s'assure qu'il est unique et en-dessous de 60 Ko (60 000 octets).<br/>
Efface les fichiers distants qui ne sont plus présents.<br/>
Envoi les fichiers modifiés ou créés qui font moins de 4 Kio (4096 octets).<br/>
