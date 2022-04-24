USER = -it -u1000
SYMFO = php74-eleclist-container

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

bash:
	docker exec $(USER) $(SYMFO) bash

vendor: composer.lock
	rm -rf ./vendor
	symfony composer install --no-progress --prefer-dist --optimize-autoloader

cc:
	docker-compose exec $(USER) $(SYMFO) bin/console cache:clear
