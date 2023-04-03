#!/bin/bash

mysql -u root -p$MYSQL_ROOT_PASSWORD --execute \
"CREATE DATABASE IF NOT EXISTS sylius_test;
CREATE DATABASE IF NOT EXISTS sylius_dev;"
