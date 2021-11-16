# Installation de l'infrastructure

## Pré-requis

Avant de monter l'infrastructure, vous devez générer des clef pour le serveur SFTP et pour l'utilisateur leonard:

### Générer les clefs pour le serveur SFTP

Ces clefs sont généralement sauvegardées par les clients SSH / SFTP, et déclenchent des erreurs si elles changent (pour éviter l'usurpation d'identité). Il est donc recommandé de les générer une fois pour toute et de ne plus les changer par la suite.
```
$ ssh-keygen -t dsa -f docker/sftp/host/ssh_host_dsa_key
$ ssh-keygen -t rsa -f docker/sftp/host/ssh_host_rsa_key
```

### Générer la clef pour l'utilisateur leonard

C'est la clef qui sera utilisé par le service Léonard pour nous envoyer des processus.
```
$ ssh-keygen -t rsa -b 4096 -f docker/sftp/leonard/id_rsa
```

## Monter l'infrastructure SPPE de developpement

```
$ docker-compose up -d --build
```

## Test

Vous pouvez maintenant effectuer votre premier transfert de fichiers vers le serveur sftp comme suit:
```
$ scp -P 2222 -i docker/sftp/leonard/id_rsa README.md leonard@localhost:/var/sftp/processus
```


# Installer les dépendances PHP

Installer les dépendances PHP du projet: Symfony, phpunit, phpcs, ...

```
$ ./sppe composer install
```

## Tester

Vous pouvez alors lancer les premiers tests
```
$ ./sppe symfony doctrine:database:create --env=test
$ ./sppe tests
```

