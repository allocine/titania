up:
	docker-compose up -it -d app

down:
	docker-compose down

sh:
	docker-compose exec app /bin/bash

install:
	composer install

test: install
	bin/phpunit
