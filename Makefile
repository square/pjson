ci-php80:
	./vendor/bin/phpcs --standard=PSR2  src/ tests/
	./vendor/bin/phpunit --testsuite php80
	vendor/bin/phpstan analyse src tests --level 5 -c phpstan.php80.neon

ci-php81:
	./vendor/bin/phpcs --standard=PSR2  src/ tests/
	./vendor/bin/phpunit --testsuite php81
	vendor/bin/phpstan analyse src tests --level 5 -c phpstan.neon
