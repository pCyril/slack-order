# Slack order

Il est souvent laborieux de passer une commande groupée, c'est pourquoi j'ai décidé de developper cette commande slack

## Comment ça fonctionne ?

Tout est dans la configuration

    - `order_command_name` Il s'agit de la commande que vous avez configuré dans Slack. 
        - Exemple: "/bagel" "/pizza"
        
    - `order_example` Il s'agit de la commande que vous avez configuré dans Slack. 
        - Exemple: "Savoyarde" "4 fromages"
        
    - `order_restaurant_name` Le nom du restaurant où vous souhaité passer commande.
        - Exemple: "McDo" "Mamamia Pizza"
            
    - `order_restaurant_phone_number` Le numéro de téléphone du restaurant où vous souhaité passer commande.
        - Exemple: Vraiment ?
                
    - `order_start_hour` L'heure à laquelle les commandes peuvent commencer.
        - Exemple: "09:00"
            
    - `order_end_hour` L'heure à laquelle les commandes ne sont plus accéptées.
        - Exemple: "09:10" (Il faut être rapide)
            
    - `order_send_by_mail_activate` Si vous souhaiter autoriser l'envoi d'un email automatique de la commande
        - Exemple: 0 ou 1
            
    - `order_restaurant_email` Du coup si vous avez activé l'envoi de l'email vous faut un email
        - Exemple: jean@dupont.fr
            
## Le petit plus: 
 
    - Tu peux facilement créer une commande "/bonjour" pour dire bonjour à madame :)