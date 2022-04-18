# LFI Eleclist
## Outil d'import de listes electorales et de regroupement d'addresses

### Importer CSV :
- Placer le fichier dans /files
- Executer `bin/console eleclist:import-csv files/filename.csv`
- Pour effacer les données déjà présentes, ajouter l'argument "--clear" ou "-c"  
exemple : `bin/console eleclist:import-csv files/filename.csv --clear`
