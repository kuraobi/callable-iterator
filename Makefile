.PHONY: test install cs-fixer phpstan phpunit composer-update

install: vendor

test: vendor cs-fixer phpstan phpunit

composer.lock:
	rm -rf ./vendor
	docker run -t --name=tmp_composer_install \
		-v ${PWD}/composer.json:/app/composer.json \
		-e COMPOSER_MEMORY_LIMIT=-1 \
		composer:latest \
		composer install --prefer-dist --no-scripts --no-suggest --ignore-platform-reqs
	docker cp tmp_composer_install:/app/composer.lock .
	docker cp tmp_composer_install:/app/vendor .
	docker rm -f tmp_composer_install

vendor: composer.lock

composer-update: composer.lock
	[ -d vendor ] || mkdir vendor
	./bin/composer update --prefer-dist --no-scripts --no-suggest

cs-fixer:
	./bin/cs-fixer fix --allow-risky=yes

phpstan:
	./bin/phpstan analyze -n --no-progress -l max src

phpunit: vendor
	./bin/phpunit
