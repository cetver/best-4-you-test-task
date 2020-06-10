FROM debian:buster-slim

ARG SOURCES_LIST_COUNTRY_CODE=md

ARG SYSTEM_LOCALE=en_GB.UTF-8

ARG SOCKET_DIRS=php

ENV DEBIAN_FRONTEND=noninteractive \
    SETUP_DIR=/tmp/setup

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
    apt-get update; \
    "${SETUP_DIR}/common/apt-get-install" gnupg2 apt-src git cmake apt-rdepends; \
    "${SETUP_DIR}/install"; \
    apt-get update; \
    apt-get purge --yes gnupg2 apt-src git cmake apt-rdepends; \
    "${SETUP_DIR}/common/clear-fs"; \
    \
    "${SETUP_DIR}/configure"; \
    \
    rm --verbose --recursive --force /usr/share/nginx; \
    rm --verbose --recursive --force "$SETUP_DIR"

WORKDIR /var/www/html

STOPSIGNAL SIGTERM

EXPOSE 80 443

CMD ["nginx"]
