first-init:
	make up
	make docker-exec-php-composer-install
	make messenger-setup-transports
	make create-vhost-rabbitmq

up:
	docker-compose up -d --build

down:
	docker-compose down

docker-exec-php-bash:
	docker-compose exec bot-help-php bash

docker-exec-php-composer-install:
	docker-compose exec bot-help-php composer install

create-vhost-rabbitmq:
	docker-compose exec bot-help-rabbitmq rabbitmqctl add_vhost test
	docker-compose exec bot-help-rabbitmq rabbitmqctl set_permissions -p test root ".*" ".*" ".*"

messenger-setup-transports:
	docker-compose exec bot-help-php bin/console messenger:setup-transports