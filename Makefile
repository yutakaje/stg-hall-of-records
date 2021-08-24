all: test analyse cs

test:
	vendor/bin/phing test

analyse:
	vendor/bin/phing analyse

cs:
	vendor/bin/phing cs

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text

exportDir = "export/`cat export/current.date`"
export:
	date '+%Y-%m-%d-%H%M%S' > export/current.date
	mkdir ${exportDir}
	cp -a .git ${exportDir}
	cd ${exportDir} && ls -la
	cd ${exportDir} && git reset --hard
	cd ${exportDir} && rm -rf .git
	cd ${exportDir} && rm -rf .github
	cd ${exportDir} && rm logs/.gitignore
	cd ${exportDir} && rm -rf tests
	cd ${exportDir} && rm .gitignore
	cd ${exportDir} && rm build.xml
	cd ${exportDir} && rm phpcs.xml
	cd ${exportDir} && rm phpstan.neon
	cd ${exportDir} && rm phpunit.xml
	cd ${exportDir} && composer install --no-dev
	cd ${exportDir} && rm composer.json
	cd ${exportDir} && rm composer.lock
	cd ${exportDir} && git log -1 | head -n1 | sed 's/commit /Build: /' > VERSION
	cd ${exportDir} && echo "Date: `git log -1 | head -n3 | tail -n1 | cut -d' ' -f'4-8'`" >> VERSION
	cd ${exportDir} && echo "" >> VERSION
	cd ${exportDir} && git log --oneline -1 | cut -d' ' -f'2-' >> VERSION
	rm export/current.date
	@echo "DONE"

.PHONY: test analyse style coverage export
