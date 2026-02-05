Alma Installments for PrestaShop structure documentation
===================================
This document provides an overview of the Alma Installments module for PrestaShop, detailing its features, installation process, and configuration steps.

## Execute test
You need to connect to the docker ssh
### Run all tests
```bash
vendor/bin/phpunit -c phpunit.ci.xml
```
### Run watcher
```bash
vendor/bin/phpunit-watcher watch
```

### Run watcher with task
I created a task to execute test watcher directly on the docker container

*This command work only if the repo integration-infrastructure is next to the integrations folder*
```bash
task test:local VERSION=1-7-8-9
```
*If you don't add VERSION it will execute 1-7-8-9 by default*
