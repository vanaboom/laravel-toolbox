# ---- Stage 0: RoadRunner binary ----
ARG RR_VERSION=2025.1.4
ARG BUILD_TAG=debian
ARG DOCKER_REGISTRY

FROM ${DOCKER_REGISTRY:-docker.io}/spiralscout/roadrunner:${RR_VERSION} AS rrstage
# ---- Stage 1: App runtime based on boomkit ----
FROM ${DOCKER_REGISTRY:-docker.io}/vanaboom/laravel-boomkit:1.7-${BUILD_TAG} AS app

# ------- Build args / envs -------
ARG CURRENT_USER
ARG CURRENT_GROUP
ARG UID=1000
ARG GID=1000
ARG APP_INSTALL_DIR='/code'
ARG APP_ENV='production'
ARG BUILD_TAG
ARG TIMEZONE
ENV TZ=${TIMEZONE}
RUN echo ${TZ}
# App & Composer envs (inside project)
ENV APP_INSTALL_DIR=${APP_INSTALL_DIR} \
    APP_ENV=${APP_ENV} \
    CURRENT_USER=${CURRENT_USER} \
    CURRENT_GROUP=${CURRENT_GROUP}


ENV HOME=${APP_INSTALL_DIR} \
    COMPOSER_HOME=${APP_INSTALL_DIR}/.composer \
    COMPOSER_CACHE_DIR=${APP_INSTALL_DIR}/.composer/cache \
    SUPERVISOR_DIR=/tmp/supervisor \
    PATH=${APP_INSTALL_DIR}/vendor/bin:${APP_INSTALL_DIR}/.composer/vendor/bin:${PATH}

WORKDIR ${APP_INSTALL_DIR}

# Ensure composer dirs exist and are writable for target UID/GID
RUN mkdir -p "${COMPOSER_CACHE_DIR}" "${SUPERVISOR_DIR}" "${APP_INSTALL_DIR}" && \
    chown -R ${UID}:${GID} "${APP_INSTALL_DIR}" "${SUPERVISOR_DIR}" "${COMPOSER_CACHE_DIR}"

RUN chown -R ${UID}:${GID} /usr/local/etc/php/conf.d
# Use bash with pipefail for reliable RUN steps
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# ------- Align container user/group to host (for dev bind-mounts) -------
RUN set -Eeuo pipefail; \
    if [ -f /etc/alpine-release ]; then \
        if getent group ${GID} >/dev/null 2>&1; then \
            echo "Group with GID ${GID} already exists"; \
        else \
            addgroup -g ${GID} "${CURRENT_GROUP:-app}"; \
        fi; \
        if id -u ${UID} >/dev/null 2>&1; then \
            echo "User with UID ${UID} already exists"; \
        else \
            adduser -D -u ${UID} -G "${CURRENT_GROUP:-app}" -s /bin/bash "${CURRENT_USER:-app}"; \
        fi; \
    else \
        if getent group ${GID} >/dev/null; then \
            groupmod -n "${CURRENT_GROUP:-app}" "$(getent group ${GID} | cut -d: -f1)" || true; \
        else \
            groupadd -g ${GID} "${CURRENT_GROUP:-app}"; \
        fi; \
        if id -u ${UID} >/dev/null 2>&1; then \
            usermod -l "${CURRENT_USER:-app}" "$(getent passwd ${UID} | cut -d: -f1)" || true; \
            usermod -u ${UID} "${CURRENT_USER:-app}" || true; \
            usermod -g ${GID} "${CURRENT_USER:-app}" || true; \
        else \
            useradd -m -u ${UID} -g ${GID} -s /bin/bash "${CURRENT_USER:-app}"; \
        fi; \
    fi

# ------- PHP & Supervisor config -------
COPY conf.d/supervisord.conf /etc/supervisord.conf
COPY supervisor.d/ /etc/supervisor.d/

# ------- Entrypoint -------
COPY start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# ------- RoadRunner (from rrstage) -------
COPY --from=rrstage /usr/bin/rr /usr/local/bin/rr
RUN rr --version

RUN set -Eeuo pipefail; \
    if [ -f /etc/alpine-release ]; then \
        cp "/usr/share/zoneinfo/${TIMEZONE}" /etc/localtime; \
        echo "${TIMEZONE}" > /etc/timezone; \
    else \
        ln -snf "/usr/share/zoneinfo/${TIMEZONE}" /etc/localtime; \
        echo "${TIMEZONE}" > /etc/timezone; \
    fi

# ------- Runtime -------
USER ${CURRENT_USER}
EXPOSE 8888
ENTRYPOINT ["start-container"]
