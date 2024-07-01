# Initialiser le Template Symfony 5.8.3 :
- Supprimer le fichier ".git"
- Commande à executer dans le terminal du projet : 
  - composer install
  - composer update
  - yarn install
  - yarn dev

# Base de donnée :
- modifier ".env" : DATABASE_URL="mysql://root:@127.0.0.1:3306/db_name"
  - Modifier si besoin l'utilisateur
  - Modifier si besoin son mot de passe
  - Modifier le nom de la base de donnée
- symfony console doctrine:database:create (d:d:c)
- php bin/console make:entity
- php bin/console make:migration
- php bin/console d:m:m
