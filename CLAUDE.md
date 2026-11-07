<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
<application-guidelines>
# Application
## Добавочные папки приложения
- В папке app создай папку Library
- По указанному пути app/Library/Functions/Functions.php создай файл и все папки, что есть в этом пути если их нет.
- В папке app создай папку Services
- В папке app создай папку Pagings
- В папке app создай папку Repositories
- В папке app создай папку API
## Добавочные понятия приложения
- API является классом, который содержит в себе код для http запросов на сторонние сервера, классы API размещай в папке app/API
- Пейджинги являются классами, которые содержат в себе код запросов в базу данных для постраничного вывода данных, пейджинги размещай в папке app/Pagings
- Репозитории являются классами, которые содержат в себе код запросов в базу данных, репозитории размещай в папке app/Repositories
- Сервисы являются классами, которые содержат в себе бизнес логику, сервисы размещай в папке app/Services
## Добавочные правила именования
- В именах контроллеров делай имя сущности во множественном числе с суффиксом Controller, например UsersController
- В именах сервисов делай имя сущности во множественном числе с суффиксом Service, например UsersService
- В именах API делай имя сущности во множественном числе с суффиксом API, например UsersAPI
- В именах моделей делай только имя сущности в единсвенном числе, например User
- В именах классов для валидации данных делай имя сущности в единственном числе с суффиксом Request, например UserRequest
- Глобальные функции добавляй в файлы по следующему пути: app/Library/Functions/Functions.php
- В именах пейджингов используй имя сущности во множественном числе с суффиксом Paging, например UsersPaging
- В именах репозиториев используй имя сущности во множественном числе с суффиксом Repository, например UsersRepository
- Роуты добавляй в файл routes/web.php, если это роуты для ajax запросов с фронтенда то добавляй роуты в файл routes/api.php, роуты должны ссылаться на контроллеры
  </application-guidelines>
