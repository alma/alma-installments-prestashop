# Dockerfiles

## Dockerfile

Used to run the tests with the command

```task test```

*Tests need MySQL to run. The MySQL container is started by the `docker-compose.yml` file.*

## PHPCS Dockerfile

Used to run the `phpcs` command by the pre-commit hook or manually with the command

```task lint```

## PHPCBF Dockerfile

Used to run the `phpcbf` command by the pre-commit hook or manually with the command

```task lint:fix```