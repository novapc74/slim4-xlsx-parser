init: docker-down docker-pull docker-build docker-up docker-composer
up: docker-up
down: docker-down
restart: down up
composer: docker-composer

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

docker-composer:
	docker-compose run --rm api-php-cli composer install

app-php-cli:
	docker-compose run --rm api-php-cli bash

parse-multiply:
	docker-compose run --rm api-php-cli php bin/app.php import-csv-ads true

parse-single:
	docker-compose run --rm api-php-cli php bin/app.php import-csv-ads false