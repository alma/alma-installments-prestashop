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


.PHONY: lint
lint: install-tools
	./tools/vendor/bin/phpcs alma
	./tools/vendor/bin/php-cs-fixer fix --dry-run --diff alma

.PHONY: lint-fix
lint-fix: install-tools
	./tools/vendor/bin/php-cs-fixer fix alma

.PHONY: php-compatibililty
php-compatibility: install-tools
	./tools/vendor/bin/phpcs -p alma --standard=PHPCompatibility -s --runtime-set testVersion 5.5-8.0 --ignore=\*/vendor/\*

.PHONY: autoindex
autoindex:
	./tools/vendor/bin/autoindex prestashop:add:index alma/

.PHONY: remove-blank-line-index
remove-blank-line-index:
	./scripts/remove-blank-line-index.sh

.PHONY: crowdin-download
crowdin-download:
	crowdin download

.PHONY: crowdin-upload
crowdin-upload:
	crowdin upload sources
