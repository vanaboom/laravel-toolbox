# Laravel Toolbox

A development toolkit for Laravel applications with built-in Docker scaffolding, Supervisor configurations, and optional services like Horizon, Echo Server, and Scheduler.

## Features

* Laravel Octane + RoadRunner ready
* Preconfigured Supervisor process management
* Optional service configs for Horizon, Echo Server, Scheduler
* Environment-based starter mode (`dev` or `prod`)
* Ready-to-publish Docker scaffold for rapid project setup
* Works seamlessly with the public base image [`vanaboom/laravel-boomkit:base`](https://hub.docker.com/r/vanaboom/laravel-boomkit)

---

## Installation

```bash
composer require vanaboom/laravel-toolbox --dev
```

---

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Vanaboom\\LaravelToolbox\\ToolboxServiceProvider" --tag="config"
```

The config file will be published to `config/toolbox.php`.

**Key options:**

* `mode` → `dev` or `prod`
* `verbose_mode` → Enable verbose logging for starter command
* `octan_watch` → Whether to enable Octane file watching in dev mode

---

## Docker Scaffold

Publish a ready-to-use Docker scaffold into your Laravel app:

```bash
php artisan toolbox:publish-docker
# then:
# docker compose up -d
```

The scaffold includes:

* Dockerfile using `vanaboom/laravel-boomkit:base`
* Supervisor configs:

    * `starter` → Runs `php artisan toolbox:starter`
    * Optional: `horizon`, `echo-server`, `scheduler` (copy from `.ini.example` to enable)
* Configurable environment variables for user/group IDs, app directory, custom commands, and more

### Example docker-compose service

```yaml
services:
  app:
    build:
      context: ./.docker/app
      dockerfile: Dockerfile
      args:
        UID: ${UID:-1000}
        GID: ${GID:-1000}
        APP_INSTALL_DIR: '/code'
        APP_ENV: ${APP_ENV:-production}
        BUILD_TAG: ${BUILD_TAG:-boomrang}
    environment:
      APP_INSTALL_DIR: '/code'
      COMPOSER_MEMORY_LIMIT: -1
      TOOLBOX_STARTER_MODE: ${TOOLBOX_STARTER_MODE:-dev}
      TOOLBOX_VERBOSE_MODE: ${TOOLBOX_VERBOSE_MODE:-false}
    ports:
      - ${APP_PORT:-11000}:8888
    volumes:
      - ./:/code
    healthcheck:
      test: curl --fail -s http://localhost:8888/health || exit 1
      interval: 1m
      timeout: 30s
      retries: 3
```

---

## Healthcheck Route

Add this to your `routes/web.php` to make the Docker healthcheck work:

```php
Route::get('/health', fn() => response()->noContent());
```

---

## Supervisor

The scaffold comes with `/etc/supervisor.d` containing process configs.

### Starter

Runs the Laravel Toolbox starter command based on environment:

```ini
[program:starter]
command=php artisan toolbox:starter
```

### Horizon (optional)

Copy `horizon.ini.example` to `horizon.ini` to enable:

```ini
[program:horizon]
command=php artisan horizon
```

### Echo Server (optional)

Copy `echo-server.ini.example` to `echo-server.ini` to enable:

```ini
[program:echo-server]
command=node /code/echo-server/server.js
```

### Scheduler (optional)

Copy `scheduler.ini.example` to `scheduler.ini` to enable:

```ini
[program:scheduler]
command=php artisan schedule:work
```

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
