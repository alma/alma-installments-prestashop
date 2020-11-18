.PHONY: dist
dist: clean install-tools
	./scripts/build-dist.sh

.PHONY: clean
clean:
	rm -rf ./dist
	rm -rf ./tmp/build

.PHONY: install-tools
install-tools:
	composer install --working-dir=tools

.PHONY: lint-fix
lint:
	./tools/vendor/bin/php-cs-fixer fix --dry-run --diff alma

.PHONY: lint-fix
lint-fix:
	./tools/vendor/bin/php-cs-fixer fix alma
