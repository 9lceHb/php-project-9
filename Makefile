PORT ?= 8000
start:
    PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public
start2:
	php -S localhost:8081 -t public
lint:
	composer run-script phpcs -- --standard=PSR12 public src tests
lint2:
	composer exec --verbose phpstan

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 src tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

validate:
	composer validate

autoload:
	composer dump-autoload