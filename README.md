Symfony REST API - Тестовое задание

Базовый REST API на Symfony 7 для управления пользователями.

Реализованы методы:

1. Авторизация по почте и паролю (POST /api/user/auth):
- Тело запроса: массив json (email, password)
- Пример тела запроса:
```
{
  "email": "karlfranz@gmail.com",
  "password": "galmaraz"
}
```
- Обязательные поля: email, password
- Возвращает токен аутентификации (бессрочный) - передаётся во все остальные запросы в заголовок "AUTH-TOKEN"

2. Создание нового пользователя (POST /api/user):
- Тело запроса: массив json (firstName, lastName, secondName, email, password, birthdayDate)
- Пример тела запроса:
```
{
    "firstName": "Katarina",
    "lastName": "Kislev",
    "secondName": "Tsarina",
    "email": "katarine@mail.ru",
    "password": "kislev",
    "birthdayDate": "1990-01-01"
}
```
- Обязательные поля: firstName, lastName, email, password, birthdayDate

3. Получения списка пользователей (GET /api/users)

4. Получение пользователя (GET /api/user/{id})
- Параметр пути {id} - id существующего пользователя

5. Обновление пользователя (PUT /api/user/{id})
- Тело запроса: массив json (firstName, lastName, secondName, password, birthdayDate)
- Пример тела запроса:
```
{
    "firstName": "Katarina",
    "lastName": "Kislev",
    "secondName": "Tsarina",
    "password": "kislev",
    "birthdayDate": "1990-01-01"
}
```
- Обязательные поля: нет

6. Удаление пользователя (DELETE /api/user/{id})
- Параметр пути {id} - id существующего пользователя

Важные особенности: 
- Автоматически создаётся админ-пользователь (email: karlfranz@gmail.com, пароль: galmaraz)
- Аутентификацию требуют все методы кроме метода авторизации
- Все пользователи имеют идентичные права (планируется добавление разграничения прав доступа админ пользователя и обычного пользователя)
- В качестве БД используется SQLite
- Для тестирования приложения можно использоваить Docker-контейнер:
```
docker compose build --no-cache --pull

docker compose up -d

docker compose exec php php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate
```
