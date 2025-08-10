# ---- Stage 0: RoadRunner binary (stable via GHCR) ----
ARG RR_VERSION=2025.1.0
FROM ghcr.io/roadrunner-server/roadrunner:${RR_VERSION} AS rrstage

# ---- Stage 1: App runtime based on boomkit ----
FROM vanaboom/laravel-boomkit:base

# ------- Build args / envs -------
ARG WWWUSER
ARG WWWGROUP
ARG UID=1000
ARG GID=1000
ARG APP_INSTALL_DIR='/code'
ARG APP_ENV='production'

# App & Composer envs (inside project)
ENV APP_INSTALL_DIR=${APP_INSTALL_DIR} \
    APP_ENV=${APP_ENV} \
    WWWUSER=${WWWUSER} \
    WWWGROUP=${WWWGROUP} \
    TZ=${APP_TIMEZONE}

ENV HOME=${APP_INSTALL_DIR} \
    COMPOSER_HOME=${APP_INSTALL_DIR}/.composer \
    COMPOSER_CACHE_DIR=${APP_INSTALL_DIR}/.composer/cache \
    PATH=${APP_INSTALL_DIR}/vendor/bin:${APP_INSTALL_DIR}/.composer/vendor/bin:${PATH}

WORKDIR ${APP_INSTALL_DIR}

# Ensure composer dirs exist and are writable for target UID/GID
RUN mkdir -p "${COMPOSER_CACHE_DIR}" "${APP_INSTALL_DIR}" && \
    chown -R ${UID}:${GID} "${APP_INSTALL_DIR}"

# Use bash with pipefail for reliable RUN steps
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# ------- Align container user/group to host (for dev bind-mounts) -------
RUN set -Eeuo pipefail; \
    if getent group ${GID} >/dev/null; then \
        groupmod -n "${WWWGROUP:-app}" "$(getent group ${GID} | cut -d: -f1)" || true; \
    else \
        groupadd -g ${GID} "${WWWGROUP:-app}"; \
    fi; \
    if id -u ${UID} >/dev/null 2>&1; then \
        usermod -l "${WWWUSER:-app}" "$(getent passwd ${UID} | cut -d: -f1)" || true; \
        usermod -u ${UID} "${WWWUSER:-app}" || true; \
        usermod -g ${GID} "${WWWUSER:-app}" || true; \
    else \
        useradd -m -u ${UID} -g ${GID} -s /bin/bash "${WWWUSER:-app}"; \
    fi

# ------- PHP & Supervisor config -------
COPY conf.d/php.ini /usr/local/etc/php/conf.d/98-php.ini
COPY conf.d/supervisord.conf /etc/supervisord.conf
COPY supervisor.d/ /etc/supervisor.d/

# ------- Entrypoint -------
COPY start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# ------- RoadRunner (from rrstage) -------
COPY --from=rrstage /usr/bin/rr /usr/local/bin/rr
RUN rr --version

# ------- Runtime -------
USER ${WWWUSER}
EXPOSE 8888
ENTRYPOINT ["start-container"]
