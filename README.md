# Slack order

Il est souvent laborieux de passer une commande groupée, c'est pourquoi j'ai décidé de développer cette commande slack

## Comment ça s'installe ?

* Créer la base de données : `.bin/doctrine orm:schema-tool:create`
* Il vous faudra ensuite installer l'application sur un serveur web classique avec une petite base de données et pouvoir appeler l'url en HTTPS
* Ensuite vous devrez configurer votre [commande dans l'interface de Slack](https://my.slack.com/services/new/slash-commands) (les droits admin sont nécessaires)
    * Appeler l'url https://yourdomain.tld/{_locale}/order
    * Choisir de l'appeler en GET

## Configuration

* Configurer votre restaurant préféré : `.bin/console order:restaurant:create`

## Comment ça fonctionne ?

    - Une fois installé et configuré vous aurez ceci :

![alt text](https://www.devexcuses.fr/images/slack-order.jpg "Exemple")

