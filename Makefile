.PHONY: dist
dist: clean install-tools
	./scripts/build-dist.sh

.PHONY: clean
clean:
	rm -rf ./dist

.PHONY: install-tools
install-tools:
	composer install --working-dir=tools

.PHONY: update-tools
update-tools: install-tools
	composer update --working-dir=tools


.PHONY: lint-fix
lint: install-tools
	./tools/vendor/bin/php-cs-fixer fix --dry-run --diff alma

.PHONY: lint-fix
lint-fix: install-tools
	./tools/vendor/bin/php-cs-fixer fix alma

.PHONY: php-compatibililty
php-compatibility: install-tools
	./tools/vendor/bin/phpcs -p alma --standard=PHPCompatibility -s --runtime-set testVersion 5.5-8.0 --ignore=\*/vendor/\*
