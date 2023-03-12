phpunit:
	vendor/bin/phpunit

phpspec:
	vendor/bin/phpspec run --ansi --no-interaction -f dot

phpstan:
	vendor/bin/phpstan analyse

psalm:
	vendor/bin/psalm

behat-js:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

install:
	composer install --no-interaction --no-scripts

backend:
	tests/Application/bin/console sylius:install --no-interaction
	tests/Application/bin/console sylius:fixtures:load default --no-interaction

frontend:
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

behat:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv 

ecs:
	vendor/bin/ecs check src

init: install backend frontend

ci: init ecs phpstan psalm phpunit phpspec behat

integration: init phpunit behat

static: install esc phpspec phpstan psalm

test: reload ecs phpstan psalm phpunit phpspec behat

reload: 
	composer dump-autoload

symfony:
	tests/Application/bin/console $(filter-out $@,$(MAKECMDGOALS))
%:      
	@:    