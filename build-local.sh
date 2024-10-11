#!/bin/bash

# Run ddev composer install
ddev composer install

# Run ddev drush commands
ddev drush cim -y
ddev drush cim -y
ddev drush updb -y
ddev drush sitestudio:package:multi-import --path=config/site_studio_sync/site_studio.packages.yml
ddev drush cohesion:rebuild
ddev drush cr
