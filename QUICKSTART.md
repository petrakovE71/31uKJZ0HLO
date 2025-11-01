# Быстрый старт StoryVault

## Запуск через Docker (рекомендуется)

```bash
# 1. Запустить Docker окружение
docker-compose up -d

# 2. Применить миграции
docker-compose exec php php yii migrate --interactive=0

# 3. Открыть в браузере
# http://localhost:8080
```

Готово! Приложение запущено.

**Дополнительно:**
- phpMyAdmin: http://localhost:8080
- Логи: `docker-compose logs -f`
- Email проверка: `runtime/mail/`

## Тестирование функциональности

1. Откройте http://localhost:8080
2. Заполните форму справа:
   - Имя автора: Андрей
   - Email: test@example.com
   - Сообщение: Это <b>тестовое</b> сообщение!
   - Капча: введите код с картинки
3. Нажмите "Отправить"
4. Проверьте email в `runtime/mail/` - там будут ссылки для редактирования и удаления
5. Попробуйте создать еще один пост - должна сработать защита rate limit (1 пост/3 мин)

## Проверка email

```bash
# Последнее письмо
docker-compose exec php ls -lt runtime/mail/ | head -2

# Просмотр письма
docker-compose exec php cat runtime/mail/<имя_файла>.eml
```

## Остановка

```bash
docker-compose down
```

## Проблемы?

1. **Порт 8080 занят** - измените в docker-compose.yml: `"8080:80"` на другой порт
2. **MySQL не стартует** - проверьте свободное место на диске
3. **Permission denied** - выполните `chmod -R 777 runtime web/assets`

## Структура проекта

- `controllers/PostController.php` - основной контроллер
- `services/` - бизнес-логика
- `repositories/` - работа с БД
- `views/post/index.php` - главная страница
- `migrations/` - миграции БД

Полная документация в [README.md](README.md)
