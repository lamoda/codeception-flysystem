WORKING_DIR=$(CURDIR)

php-cs-check:
	$(WORKING_DIR)/vendor/bin/php-cs-fixer fix --dry-run --format=junit --diff

php-cs-fix:
	$(WORKING_DIR)/vendor/bin/php-cs-fixer fix