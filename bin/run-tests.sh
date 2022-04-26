#!/bin/bash

# Print commands to the screen
# set -x

# Catch Errors
set -euo pipefail

GREEN='\033[0;32m';
RED='\033[0;31m';
NC='\033[0m'; # No Color

export WP_DEVELOP_DIR=./tmp/wordpress/

if [ -d "$WP_DEVELOP_DIR" ] 
then
    printf "${GREEN}WordPress core already installed in $WP_DEVELOP_DIR, running unit tests${NC}\n"
else
	printf "${GREEN}******************************************************************${NC}\n"
    printf "${GREEN}Installing WordPress core to ${WP_DEVELOP_DIR}${NC}\n"
    printf "${GREEN}This will take a few minutes to run git clone and composer install${NC}\n"
	printf "${GREEN}******************************************************************${NC}\n"
	echo ""
    ./bin/install-wp-core.sh
fi

"$WP_DEVELOP_DIR"/vendor/bin/phpunit --verbose ${1:-""}

# Stop printing commands to screen
set +x
