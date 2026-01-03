#!/usr/bin/env bash

ncolors=$(tput colors)
if test -n "$ncolors" && test "$ncolors" -ge 8; then
    BOLD="$(tput bold)"
    YELLOW="$(tput setaf 3)"
    GREEN="$(tput setaf 2)"
    NC="$(tput sgr0)"
fi

function display_help {
    echo "SSU - Simple Setup Utility"
    echo
    echo "${YELLOW}Usage:${NC}" >&2
    echo "  ssu COMMAND [options] [arguments]"
    echo
    echo "Unknown commands will be passed to the docker-compose binary"
    echo
    echo "${YELLOW}Docker commands:${NC}"
    echo "  ${GREEN}ssu up -d${NC}                Start the application in the background"
    echo "  ${GREEN}ssu stop${NC}                 Stop the application"
    echo "  ${GREEN}ssu restart${NC}              Restart the application"
    echo
    echo "${YELLOW}Artisan commands:${NC}"
    echo "  ${GREEN}ssu artisan [arguments]${NC}  Run an Artisan command"
    echo "  ${GREEN}ssu artisan queue:work${NC}"
    echo
    echo "${YELLOW}PHP commands:${NC}"
    echo "  ${GREEN}ssu php [arguments]${NC}      Run a snippet of PHP code"
    echo "  ${GREEN}ssu php -v${NC}"
    echo
    echo "${YELLOW}Composer commands:${NC}"
    echo "  ${GREEN}ssu composer [arguments]${NC} Run a Composer command"
    echo
    echo "${YELLOW}Node/NPM commands:${NC}"
    echo "  ${GREEN}sail node [arguments]${NC}    Run a Node command"
    echo "  ${GREEN}sail node --version${NC}"
    echo "  ${GREEN}sail npm [arguments]${NC}     Run a npm command"
    echo "  ${GREEN}sail npx${NC}                 Run a npx command"
    echo
    echo "${YELLOW}Running Tests:${NC}"
    echo "  ${GREEN}sail test${NC}                Run the PHPUnit tests via the Artisan test command"

    exit 1
}

if [ $# -gt 0 ]; then
    if [ "$1" == "help" ] || [ "$1" == "-h" ] || [ "$1" == "-help" ] || [ "$1" == "--help" ]; then
        display_help
    fi
else
    display_help
fi

if [ "$1" == "artisan" ]; then
    shift
    docker-compose exec laravel php artisan "$@"
    exit $?
fi

if [ "$1" == "php" ]; then
    shift
    docker-compose exec laravel php "$@"
    exit $?
fi

if [ "$1" == "composer" ]; then
    shift
    docker-compose exec laravel composer "$@"
    exit $?
fi

if [ "$1" == "node" ]; then
    shift
    docker-compose exec node node "$@"
    exit $?
fi

if [ "$1" == "npm" ]; then
    shift
    docker-compose exec node npm "$@"
    exit $?
fi

if [ "$1" == "npx" ]; then
    shift
    docker-compose exec node npx "$@"
    exit $?
fi

if [ "$1" == "test" ]; then
    shift
    docker-compose exec laravel php artisan test "$@"
    exit $?
fi

if [ "$1" == "up" ] || [ "$1" == "down" ] || [ "$1" == "stop" ] || [ "$1" == "restart" ] || [ "$1" == "start" ] || [ "$1" == "build" ]; then
    docker-compose "$@"
    exit $?
fi

docker-compose exec laravel "$@"
