#!/bin/bash

docker-compose exec --env="XDEBUG_MODE=off" -T app php ./bin/console.php $*;
