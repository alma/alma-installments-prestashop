# Dockerfiles

## Dockerfile

Used to run the tests with the command

```task test```

*Tests need MySQL to run. The MySQL container is started by the `docker-compose.yml` file.*

## Lint Dockerfile

Used to run the `php-cs-fixer` command by the pre-commit hook or manually with the commands

- ```task lint```
- ```task lint:fix```

Used to run the `phpcs` command to check PHP compatibility by the pre-commit hook or manually with the command

- ```task php-compatibility```