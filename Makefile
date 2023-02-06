first-init:
	make up
	make docker-exec-php-composer-install

up:
	docker-compose up -d --build

down:
	docker-compose down

docker-exec-php-bash:
	docker-compose exec bot-help-php bash

docker-exec-php-composer-install:
	docker-compose exec bot-help-php composer install

consume-events:
	docker-compose exec bot-help-php bin/console cli:events:consume