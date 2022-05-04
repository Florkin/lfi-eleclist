# LFI Eleclist

## Outil d'import de listes electorales et de regroupement d'addresses

## Workflow

#### Issues

- Si vous avez une idée, formalisez la dans une issue avant de travailler. **Pas d'issue, pas de dev** !
- Mettez des labels : evolution, bug...
- **Indiquez dans un commentaire si vous commencez le traitement d'une issue**

#### Branches

Format de nom de branche : feat/#[NUMERO D'ISSUE]/description-courte  
ex: `feat/#1/install-docker`

#### Commits

Format de message de commit : [issue #[NUMERO D'ISSUE]] description du commit
ex: `[issue #1] Add phpmyadmin to docker`

Il est aussi possible de faire des commits multilignes :

```
[issue #1]
- Install maria-db
- Add phpmyadmin
```

#### Pull Requests

Ne pas oublier de referencer l'issue dans la Pull Request

## Setup Docker (linux) :

- `export UID=$(id -u) && export GID=$(id -g)` for permissions issues
- `make start` (equivalent to `make build` then `make up`) Build & up l'image docker
- `make vendor` Installe les vendor
- `make database` Crée la base de donnée et execute les migrations
- `make bash` Accéder à la console du conteneur symfony
- Connect phpmyadmin : `http://localhost:8080/` - `root / emptyPassword`

## Importer CSV :

- Placer le fichier dans /files (ou ailleurs)
- Accéder au container `make bash`
- Vérifier le mapping du header dans _**config/app_config/csv_header_mapping.yaml**_ et modifier si nécessaire (ne pas
  versionner si modification!)
- Executer `bin/console eleclist:import-csv files/filename.csv`
- Pour effacer les données déjà présentes, ajouter l'argument "--clear" ou "-c"  
  exemple : `bin/console eleclist:import-csv files/filename.csv --clear`
- Si le delimiteur du csv n'est pas ",", ajouter `--delimiter=";"`

## Grouper les addresses

- Executer `bin/console eleclist:find-groups` va générer des addresses groupées par number/street/city et y associer
  tous les électeurs habitant à cette addresse.

## Générer les PDF
- Compiler le css: `make install-assets` && `make build-assets`
- Executer `bin/console eleclist:generate roubaix wattrelos` génère un PDF par ville dans public/pdf
- Ajouter --zip pour zipper les pdf
