fixer-version:
	docker-compose exec php /bin/bash -c "./vendor/bin/php-cs-fixer --version"

fixer-dry:
	docker-compose exec php /bin/bash -c "./vendor/bin/php-cs-fixer fix -v --diff --dry-run"

fixer:
	docker-compose exec php /bin/bash -c "./vendor/bin/php-cs-fixer fix -v --diff"
