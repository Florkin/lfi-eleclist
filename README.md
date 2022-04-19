# LFI Eleclist
## Outil d'import de listes electorales et de regroupement d'addresses

### Importer CSV :
- Placer le fichier dans /files
- Executer `bin/console eleclist:import-csv files/filename.csv`
- Pour effacer les données déjà présentes, ajouter l'argument "--clear" ou "-c"  
exemple : `bin/console eleclist:import-csv files/filename.csv --clear`

### Setup Docker (linux) :
- sudo docker-compose up -d --build
- sudo docker exec -it -u1000 php74-eleclist-container bash
- composer install
- connect database : DATABASE_URL="mysql://root:@mariadb-service:3306/eleclist?serverVersion=10.8&charset=utf8mb4"
- connect pma : root / emptyPassword
