Alma Prestashop plugin tests
=====================

To be able to launch the tests you would require to copy `phpunit.dist.xml` to `phpunit.xml`
and to fill in `ALMA_API_KEY` and `ALMA_API_ROOT`

---------------------

Before launching the test, you need to connect to the environnment in integration-infrastructure repo
```
make prestashop-X-X-X-X-ssh
```

Place yourself in the alma module folder
```
cd modules/alma/
```

to launch test :
```
./vendor/bin/phpunit
```
