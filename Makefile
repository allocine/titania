-include .env

TB = docker-compose run --rm test

export COMPOSE_PATH_SEPARATOR=:

login-gcp:
	# Login to GCP
	@gcloud auth print-access-token | docker login -u oauth2accesstoken --password-stdin https://eu.gcr.io

install:
	$(TB) composer install --no-progress

test:
	$(TB) bin/phpunit --verbose

cleanup:
	git reset --hard
	git clean -xdf

