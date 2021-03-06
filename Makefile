USER = -it -u1000
SYMFO = php74-eleclist-container
PROJECT_PATH = /app
SHELL=/bin/bash
UID := $(shell id -u)

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

down-clear:
	docker-compose down -v

start:
	docker-compose build
	docker-compose up -d

restart:
	docker-compose down
	docker-compose up

restart-clear:
	docker-compose down -v
	docker-compose up --build -d

database:
	docker exec -it $(SYMFO) bin/console doctrine:database:create
	docker exec -it $(SYMFO) bin/console doctrine:migration:migrate

bash:
	docker exec -it $(SYMFO) bash

sh:
	docker exec -it $(SYMFO) sh

vendor: composer.lock
	rm -rf ./vendor
	symfony composer install --no-progress --prefer-dist --optimize-autoloader

cc:
	docker-compose exec -it $(SYMFO) bin/console cache:clear

install-assets:
	docker-compose run --rm encore yarn install

build-assets:
	docker-compose run --rm encore yarn build

fix-permissions:
	sudo chown -R ${UID} ./

