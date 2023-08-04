.PHONY: dist
dist: clean install-tools
	./scripts/build-dist.sh

.PHONY: clean
clean:
	rm -rf ./dist

.PHONY: install-tools
install-tools:
	composer install --no-dev --optimize-autoloader --working-dir=alma

.PHONY: update-tools
update-tools: install-tools
	composer update --working-dir=alma

.PHONY: lint
lint: install-tools
	./alma/vendor/bin/php-cs-fixer fix alma --dry-run

.PHONY: lint-fix
lint-fix: install-tools
	./alma/vendor/bin/php-cs-fixer fix alma

.PHONY: php-compatibililty
php-compatibility: install-tools
	./alma/vendor/bin/phpcs -p alma --standard=PHPCompatibility -s --runtime-set testVersion 5.6-8.1 --ignore=\*/vendor/\*

.PHONY: autoindex
autoindex:
	./alma/vendor/bin/autoindex prestashop:add:index alma/

.PHONY: remove-blank-line-index
remove-blank-line-index:
	./scripts/remove-blank-line-index.sh

.PHONY: crowdin-download
crowdin-download:
	crowdin download

.PHONY: crowdin-upload
crowdin-upload:
	crowdin upload sources
