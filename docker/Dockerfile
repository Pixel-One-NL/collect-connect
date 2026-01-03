FROM serversideup/php:8.5-frankenphp AS base

USER root
RUN install-php-extensions intl

FROM base AS development

ARG USER_ID
ARG GROUP_ID

USER root
RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID  && \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID
USER www-data

