default:
	./vendor/bin/phpcs --standard=PSR2  src/ tests/
	./vendor/bin/phpunit
	vendor/bin/phpstan analyse src tests --level 5
