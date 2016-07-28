# Веб-сервис QuickStat

Позволяет узнать рейтинг(количество упоминаний) личностей на новостных сайтах.

# Описание запросов к веб-сервису

Работа с сервисом требует авторизации!  
Допустимые значения для логина и пароля: символы a-z, A-Z, 0-9, "_", "-", длина 4-32.  
Пользователь получает доступ только к тем сайтам, персонам, ключевым словам, которые он сам добавил.  
Администратор получает полный доступ ко всем данным.  

При успешной обработке запроса возвращается ответ "200 OK".  
При возникновении ошибки базы данных возвращается ответ "500 Database error".

**Регистрация:**
```
Запрос: POST /api/auth/register
Параметры: login, password
    login    - логин пользователя
	password - пароль пользователя
Коды ошибок:
	400 Already authorized, logout first
	    - пользователь авторизоан, сначала нужно произвести выход(logout)
	400 User already exists
	    - пользователь с указанным логином уже существует
	400 Login not specified
	    - логин не указан или является пустой строкой
	400 Password not specified
	    - пароль не указан или является пустой строкой
	400 Invalid login
	    - неверный логин, содержит недопустимые символы или недопустимую длину
	400 Invalid password
	    - неверный пароль, содержит недопустимые символы или недопустимую длину
```

**Авторизация:**
```
Запрос: POST /api/auth/login
Параметры: login, password
    login    - логин пользователя
	password - пароль пользователя

Результат:
    Токен в виде строки "abedc23ad45c5ad68c7a9b78dcbc", также сохраняет токен в cookie.
Коды ошибок:
	400 Already authorized, logout first
	    - пользователь авторизоан, сначала нужно произвести выход(logout)
	400 User not found
	    - пользователь с указанным логином не найден
	400 Login not specified
	    - логин не указан или является пустой строкой
	400 Password not specified
	    - пароль не указан или является пустой строкой
	400 Invalid login
	    - неверный логин, содержит недопустимые символы или недопустимую длину
	400 Invalid password
	    - неверный пароль, содержит недопустимые символы или недопустимую длину, либо не соответствует паролю пользователя
```

**Выход:**
```
Запрос: POST /api/auth/logout
Коды ошибок:
	401 Unauthorized
	    - пользователь не авторизован
```

**Зарегистрировать пользователя(только для администратора):**
```
Запрос: POST /api/auth/register
Параметры: login, password, role
	role     - роль пользователя(0 - пользователь, 1 - администратор)
    login    - логин пользователя
	password - пароль пользователя
Коды ошибок:
	400 Already authorized, logout first
	    - пользователь авторизоан, сначала нужно произвести выход(logout)
	400 User with specified login already exists
	    - пользователь с указанным логином уже существует
	403 Registration of a new administrator allowed by another administrator only
	    - попытка регистрации нового администратора пользователем не имеющим прав администратора
	400 Login not specified
	    - логин не указан или является пустой строкой
	400 Password not specified
	    - пароль не указан или является пустой строкой
	400 Invalid login
	    - неверный логин, содержит недопустимые символы или недопустимую длину
	400 Invalid password
	    - неверный пароль, содержит недопустимые символы или недопустимую длину
```

## Списки

**Получить список сайтов:**
```
Запрос: GET /api/sites

Результат:
    [
        {"id":1, "name":"1tv.ru"},
        {"id":2, "name":"lenta.ru"},
        {"id":3, "name":"gazeta.ru"}
    ]
```

**Получить список персон:**
```
Запрос: GET /api/persons

Результат:
    [
        {"id":1, "name":"Путин"},
        {"id":2, "name":"Медведев"},
        {"id":3, "name":"Навальный"}
    ]
```

**Получить список ключевых слов:**
```
Запрос: GET /api/keywords?person_id=2
    person_id - идентификатор персоны, если не указан, вернет ключевые слова для всех персон

Результат:
    [
        {"id":1, "name":"путин", "person_id":"1"},
        {"id":2, "name":"путина", "person_id":"1"},
        {"id":3, "name":"путину", "person_id":"1"},
    ]
```

**Добавить сайт:**
```
Запрос: POST /api/sites
Параметры: name
    name - имя сайта
Коды ошибок:
    400 Parameter "parameter-name" not specified
	    - параметр запроса parameter-name не указан
	400 Site already exists
	    - сайт уже существует
```

**Добавить персону:**
```
Запрос: POST /api/persons
Параметры: name
    name - имя персоны
Коды ошибок:
    400 Parameter "parameter-name" not specified
	    - параметр запроса parameter-name не указан
	400 Person already exists
	    - персона уже существует
```

**Добавить ключевое слово:**
```
Запрос: POST /api/keywords
Параметры: name, person_id
    name      - ключевое слово
	person_id - идентификатор персоны
Коды ошибок:
    400 Parameter "parameter-name" not specified
	    - параметр запроса parameter-name не указан
	400 Person not found
	    - персона с указанным идентификатором не найдена
	400 Keyword already exists
	    - ключевое слово уже существует
```

**Удалить сайт:**
```
Запрос: DELETE /api/sites/6
Коды ошибок:
	400 Identifier not specified
	    - не указан идентификатор записи
	400 Record not exists
	    - указанная запись не существует
```

**Удалить персону:**
```
Запрос: DELETE /api/persons/12
Коды ошибок:
	400 Identifier not specified
	    - не указан идентификатор записи
	400 Record not exists
	    - указанная запись не существует
```

**Удалить ключевое слово:**
```
Запрос: DELETE /api/keywords/54
Коды ошибок:
	400 Identifier not specified
	    - не указан идентификатор записи
	400 Record not exists
	    - указанная запись не существует
```

**Получить список пользователей(только для администратора):**
```
Запрос: GET /api/users

Результат:
    [
        {"id":1, "login":"admin", "role":"1"},
        {"id":2, "login":"guest", "role":"0"}
    ]
Коды ошибок:
    403 Allowed for administrator only
	    - операция доступна только для администратора
```

**Удалить пользователя(только для администратора):**
```
Запрос: DELETE /api/users/6
Коды ошибок:
	400 Identifier not specified
	    - не указан идентификатор записи
	400 Record not exists
	    - указанная запись не существует
    403 Allowed for administrator only
	    - операция доступна только для администратора
```

## Статистика

**Получить общую статистику для всех персон по сайту:**
```
Запрос: GET /api/stat/common?site_id=1
    site_id - идентификатор сайта

Результат:
    [
        {"id":1, "name":"Путин", "rank":"23"},
        {"id":2, "name":"Медведев", "rank":"18"},
        {"id":3, "name":"Навальный", "rank":"10"}
    ]
Коды ошибок:
    400 Parameter "parameter-name" not specified
	    - параметр запроса parameter-name не указан
```

**Получить ежедневную статистику для конкретной персоны по сайту:**
```
Запрос: GET /api/stat/daily?site_id=2&person_id=3&first_date=01.08.2016&last_date=05.08.2016
    site_id    - идентификатор сайта
    person_id  - идентификатор персоны
    first_date - идентификатор дата начала периода
    last_date  - идентификатор дата конца периода
	
	Без указания дат, вернет статистику за последние 30 дней.

Результат:
    [
        "pagesByDays":[
            {"date":"01.08.2016", "pages":"2"},
            {"date":"02.08.2016", "pages":"0"},
            {"date":"03.08.2016", "pages":"1"},
            {"date":"04.08.2016", "pages":"3"},
            {"date":"05.08.2016", "pages":"0"}
        ],
        "totalPages":"6"
    ]
Коды ошибок:
    400 Parameter "parameter-name" not specified
	    - параметр запроса parameter-name не указан
```