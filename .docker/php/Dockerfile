ARG PHP_FPM_VERSION=7.4
FROM php:${PHP_FPM_VERSION}-fpm

ARG NODEJS_MAJOR_VERSION

ARG SOCKET_DIRS=php

ARG SOURCES_LIST_COUNTRY_CODE=md

ARG SYSTEM_LOCALE=en_GB.UTF-8

ARG SYSTEM_USERNAME=skeleton

ARG SYSTEM_PASSWORD=qwe

ENV DEBIAN_FRONTEND=noninteractive \
    SETUP_DIR=/tmp/setup \
    COMPOSER_HOME=/var/www/html/.composer \
    NODEJS_MAJOR_VERSION=${NODEJS_MAJOR_VERSION}

COPY setup ${SETUP_DIR}

RUN set -e; \
    "${SETUP_DIR}/common/rewrite-sources-list" --country-code "${SOURCES_LIST_COUNTRY_CODE}"; \
    apt-get update; \
    apt-get --yes upgrade; \
    "${SETUP_DIR}/common/apt-get-install" nano curl ca-certificates fd-find ripgrep bash-completion procps locales; \
    "${SETUP_DIR}/common/clear-fs"; \
    \
    "${SETUP_DIR}/common/update-locale" --locale "${SYSTEM_LOCALE}"; \
    "${SETUP_DIR}/common/configure-bash"; \
    "${SETUP_DIR}/common/create-socket-dir" --directories "${SOCKET_DIRS}"

RUN set -e; \
    PHP_INI_DEV="${PHP_INI_DIR}/php.ini-development"; \
    PHP_INI_FPM="${PHP_INI_DIR}/php-fpm-fcgi.ini"; \
    PHP_INI_CLI="${PHP_INI_DIR}/php-cli.ini"; \
    PHP_FPM_DIR="/usr/local/etc"; \
    PHP_FPM_D_DIR="${PHP_FPM_DIR}/php-fpm.d"; \
    PHP_FPM_CONF="${PHP_FPM_DIR}/php-fpm.conf"; \
    PHP_FPM_WWW_CONF="${PHP_FPM_D_DIR}/www.conf"; \
    \
    cp --verbose "$PHP_INI_DEV" "$PHP_INI_FPM"; \
    cp --verbose "$PHP_INI_DEV" "$PHP_INI_CLI"; \
    "${SETUP_DIR}/configure-ini" --php-ini-fpm "$PHP_INI_FPM" --php-ini-cli "$PHP_INI_CLI"; \
    \
    "${SETUP_DIR}/configure-fpm" \
        --php-fpm-conf "$PHP_FPM_CONF" \
        --php-fpm-www-conf "$PHP_FPM_WWW_CONF" \
        --php-fpm-www-conf-listen "/var/run/php/php-fpm.sock"; \
    \
    "${SETUP_DIR}/install-ext"; \
    \
    apt-get update; \
    "${SETUP_DIR}/common/apt-get-install" \
        # system user
        sudo \
        # composer
        git unzip; \
    \
    "${SETUP_DIR}/common/add-system-user" --username "${SYSTEM_USERNAME}" --password "${SYSTEM_PASSWORD}"; \
    echo "${SYSTEM_USERNAME}    ALL=NOPASSWD:SETENV: /usr/local/sbin/php-fpm" >> "/etc/sudoers.d/${SYSTEM_USERNAME}"; \
    \
    "${SETUP_DIR}/install-composer" --username "${SYSTEM_USERNAME}"; \
    \
    if [ -n "${NODEJS_MAJOR_VERSION}" ]; then \
        "${SETUP_DIR}/common/apt-get-install" gnupg2; \
        "${SETUP_DIR}/install-nodejs" --version "$NODEJS_MAJOR_VERSION"; \
        apt-get purge --yes gnupg2; \
    fi; \
    \
    "${SETUP_DIR}/common/clear-fs"; \
    fdfind --type file --exclude "$(basename "$PHP_FPM_WWW_CONF")" . "$PHP_FPM_D_DIR" --exec rm --verbose --force {}; \
    rm --verbose --recursive --force /usr/local/php/man; \
    rm --verbose --recursive --force "$SETUP_DIR"

# Work without 'chown -R "$(whoami)": <source code directory>' after each composer / yarn operation
USER ${SYSTEM_USERNAME}

COPY entrypoint /

ENTRYPOINT ["/entrypoint"]

CMD ["start"]
