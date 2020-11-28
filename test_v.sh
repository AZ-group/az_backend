#!/bin/bash
echo Unit Test ] Validator 
echo
vendor/bin/phpunit --bootstrap ./vendor/autoload.php ./tests/ValidatorTest.php