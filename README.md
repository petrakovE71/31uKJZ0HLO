# StoryVault

StoryVault - приложение для публикации коротких сообщений с возможностью управления через email.

## Описание

Приложение позволяет любому пользователю оставить своё сообщение на сайте. После публикации автор получает на email приватные ссылки для редактирования (12 часов) и удаления (14 дней) своего поста.

## Особенности

- Публикация сообщений без регистрации
- Защита от спама (капча + rate limiting)
- Email-уведомления с приватными ссылками управления
- Редактирование в течение 12 часов
- Удаление в течение 14 дней
- Маскировка IP-адресов (конфиденциальность)
- Нормализованная БД (authors, posts)

## Архитектура

Проект реализован с использованием **Clean Architecture** и **SOLID** принципов:

### Структура проекта

```
storyvalut/
├── config/              # Конфигурация приложения
├── controllers/         # Контроллеры
│   ├── SiteController.php
│   └── PostController.php
├── models/              # Модели (Active Record + Form Models)
│   ├── Author.php
│   ├── Post.php
│   ├── PostForm.php
│   └── PostEditForm.php
├── repositories/        # Repository Pattern (Data Layer)
│   ├── AuthorRepository.php
│   └── PostRepository.php
├── services/            # Service Layer (Business Logic)
│   ├── AuthorService.php
│   ├── PostService.php
│   ├── EmailService.php
│   ├── IpService.php
│   └── HtmlSanitizerService.php
├── helpers/             # Вспомогательные классы
│   ├── TimeHelper.php
│   └── PluralHelper.php
├── views/               # Представления
│   ├── layouts/
│   └── post/
├── mail/                # Email шаблоны
├── migrations/          # Миграции БД
├── docker/              # Docker конфигурация
│   ├── nginx/
│   └── php/
└── docker-compose.yml   # Docker Compose
```

### Паттерны проектирования

- **Repository Pattern** - абстракция работы с данными
- **Service Layer** - бизнес-логика отделена от контроллеров
- **Form Models** - валидация данных
- **Active Record** - маппинг БД
- **Dependency Injection** - через конфигурацию Yii2

## Технологии

- **PHP 8.1+**
- **Yii2 Framework** 2.0.45+
- **MySQL 8.0** (нормализованная БД)
- **Bootstrap 5** (UI)
- **Docker & Docker Compose** (окружение)
- **HtmlPurifier** (безопасность)
- **Codeception** (тестирование)

## Установка и запуск

### Вариант 1: Docker (рекомендуется)

```bash
# Клонировать репозиторий
git clone <repo-url>
cd storyvalut

# Запустить Docker окружение
docker-compose up -d

# Установить зависимости
docker-compose exec php composer install

# Применить миграции
docker-compose exec php php yii migrate

# Приложение доступно по адресу:
# http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

### Вариант 2: Локальная установка

**Требования:**
- PHP 8.1+
- MySQL 8.0+
- Composer

```bash
# Установить зависимости
composer install

# Настроить БД в config/db.php
# Создать базу данных
mysql -u root -p
CREATE DATABASE storyvalut CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Применить миграции
php yii migrate

# Запустить встроенный сервер
php yii serve

# Приложение доступно: http://localhost:8080
```

## Конфигурация

### База данных (config/db.php)

```php
return [
    'dsn' => 'mysql:host=mysql;dbname=storyvalut',
    'username' => 'storyvalut_user',
    'password' => 'storyvalut_pass',
    'charset' => 'utf8mb4',
];
```

Для локальной установки используйте environment variables:
```bash
export DB_DSN='mysql:host=localhost;dbname=storyvalut'
export DB_USERNAME='your_user'
export DB_PASSWORD='your_password'
```

### Email (config/params.php)

```php
return [
    'senderEmail' => 'noreply@storyvalut.local',
    'senderName' => 'StoryVault',
];
```

**Отладка email:** По умолчанию письма сохраняются в `runtime/mail/` (useFileTransport=true)

## База данных

### Схема БД

**Таблица `authors`:**
- id (PK)
- email (UNIQUE) - идентификатор автора
- name - текущее имя
- ip_address - текущий IP
- created_at - unixtime регистрации
- updated_at - unixtime обновления
- last_post_at - для rate limiting

**Таблица `posts`:**
- id (PK)
- author_id (FK -> authors.id)
- message - текст сообщения
- created_at - unixtime публикации
- updated_at - unixtime редактирования
- deleted_at - soft delete
- edit_token (UNIQUE) - токен редактирования
- delete_token (UNIQUE) - токен удаления

## Безопасность

Реализованные защиты:

- **SQL Injection** - Prepared Statements
- **CSRF** - токены Yii2
- **Rate Limiting** - 1 пост/3 минуты на автора
- **Input Validation** - строгая валидация всех полей
- **Secure Tokens** - SHA256 для edit/delete ссылок
- **Time-based Access** - проверка таймаутов (12ч/14дней)
- **Email Flood** - rate limit защита
- **IP Masking** - конфиденциальность пользователей

## Функциональность

### Создание поста

1. Пользователь заполняет форму (имя, email, сообщение, капча)
2. Валидация данных (2-15 символов имя, 5-1000 символов сообщение)
3. Проверка rate limit (1 пост в 3 минуты)
4. Создание/обновление автора (по email)
5. Создание поста с уникальными токенами
6. Отправка email с ссылками управления

### Редактирование поста

- Доступно **12 часов** после публикации
- По приватной ссылке с edit_token
- Можно изменить только текст сообщения
- Валидация как при создании

### Удаление поста

- Доступно **14 дней** после публикации
- По приватной ссылке с delete_token
- Страница подтверждения
- Soft delete (deleted_at)

### Дополнительно

- **Пагинация** - 20 постов на страницу
- **Относительное время** - "5 минут назад"
- **Счетчик постов** - по автору (email)
- **Маскировка IP** - IPv4: `46.211.**.**`, IPv6: `2001:0db8:****:****:****:****`
- **HTML в сообщениях** - только безопасные теги

## Тестирование

```bash
# Запустить все тесты
composer test

# Unit тесты
vendor/bin/codecept run unit

# Functional тесты
vendor/bin/codecept run functional
```

## Docker команды

```bash
# Запустить окружение
docker-compose up -d

# Остановить
docker-compose down

# Просмотр логов
docker-compose logs -f

# Перезапустить сервис
docker-compose restart php

# Выполнить команду в контейнере
docker-compose exec php php yii migrate
docker-compose exec php composer install
```

## Разработка

### Структура кода

1. **Controllers** - только обработка HTTP запросов
2. **Services** - вся бизнес-логика
3. **Repositories** - работа с БД
4. **Models** - маппинг данных
5. **Form Models** - валидация

### Добавление новой функциональности

1. Создать миграцию (если нужно)
2. Обновить модели
3. Добавить методы в Repository
4. Реализовать логику в Service
5. Добавить действия в Controller
6. Создать Views
7. Написать тесты

## Проверка email

Письма сохраняются в `runtime/mail/`:

```bash
# Просмотр последнего письма
ls -lt runtime/mail/ | head -5
cat runtime/mail/<filename>
```

Или используйте Yii2 Debug Toolbar (внизу страницы при YII_DEBUG=true).

## Production

Для production окружения:

1. Отключить debug: `YII_DEBUG=false` в `web/index.php`
2. Включить schema cache в `config/db.php`
3. Настроить реальную отправку email (`useFileTransport=false`)
4. Настроить SMTP в `config/web.php`
5. Использовать HTTPS
6. Настроить backup БД