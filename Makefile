default:
	./vendor/bin/phpcs --standard=PSR2  src/ tests/
	./vendor/bin/phpunit --testsuite ${testsuite}
	vendor/bin/phpstan analyse src tests --level 5
