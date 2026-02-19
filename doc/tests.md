Tests documentation
===================================

## Context
This document is a guide to execute tests on the integration framework.
It will explain how to execute tests on the docker container and how to use the test watcher.

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
