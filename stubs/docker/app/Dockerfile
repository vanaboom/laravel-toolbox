FROM vanaboom/laravel-boomkit:base

ARG WWWUSER
ARG WWWGROUP
ARG UID=1000
ARG GID=1000
ARG APP_INSTALL_DIR='/code'
ARG APP_ENV='production'
ARG RR_VERSION=2024.3.1

ENV WWWUSER=$WWWUSER \
    WWWGROUP=$WWWGROUP \
    APP_INSTALL_DIR=$APP_INSTALL_DIR \
    APP_ENV=$APP_ENV

WORKDIR $APP_INSTALL_DIR

# Align container user/group to host (for dev bind-mounts)
RUN set -eux; \
    if getent group ${GID} >/dev/null; then groupmod -n ${WWWGROUP:-app} $(getent group ${GID} | cut -d: -f1) || true; \
    else groupadd -g ${GID} ${WWWGROUP:-app}; fi; \
    if id -u ${UID} >/dev/null 2>&1; then usermod -l ${WWWUSER:-app} $(getent passwd ${UID} | cut -d: -f1) || true; \
         usermod -u ${UID} ${WWWUSER:-app} || true; \
         usermod -g ${GID} ${WWWUSER:-app} || true; \
    else useradd -m -u ${UID} -g ${GID} -s /bin/bash ${WWWUSER:-app}; fi

# PHP & Supervisor config
COPY conf.d/php.ini /usr/local/etc/php/conf.d/98-php.ini
COPY supervisord.conf /etc/supervisord.conf
COPY supervisor.d/ /etc/supervisor.d/

# Entrypoint
COPY start-container /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

# RoadRunner (public release)
RUN set -eux; \
    arch="$(uname -m)"; \
    case "$arch" in \
      x86_64) rr_arch=amd64 ;; \
      aarch64) rr_arch=arm64 ;; \
      *) echo "Unsupported arch: $arch" && exit 1 ;; \
    esac; \
    curl -fsSL -o /usr/local/bin/rr "https://github.com/roadrunner-server/roadrunner/releases/download/v${RR_VERSION}/rr-linux-${rr_arch}"; \
    chmod +x /usr/local/bin/rr; \
    rr --version

USER $WWWUSER
EXPOSE 8888
ENTRYPOINT ["start-container"]