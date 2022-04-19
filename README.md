# LFI Eleclist
## Outil d'import de listes electorales et de regroupement d'addresses

### Workflow
#### Issues
- Si vous avez une idée, formalisez la dans une issue avant de travailler. **Pas d'issue, pas de dev** !
- Mettez des labels : evolution, bug...

#### Branches
Format de nom de branche : feat/[NUMERO D'ISSUE]/description-courte  
ex: `feat/1/install-docker`

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


### Importer CSV :
- Placer le fichier dans /files
- Executer `bin/console eleclist:import-csv files/filename.csv`
- Pour effacer les données déjà présentes, ajouter l'argument "--clear" ou "-c"  
exemple : `bin/console eleclist:import-csv files/filename.csv --clear`
