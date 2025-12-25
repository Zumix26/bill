# Инструкция по установке

## Требования

- PHP 8.2+
- Composer
- Node.js 18+
- npm или yarn
- MySQL (опционально, только если нужна БД)

## Установка Backend

1. Перейдите в папку backend:
```bash
cd backend
```

2. Установите зависимости:
```bash
composer install
```

3. Создайте файл `.env`:
```bash
cp .env.example .env
```

4. Сгенерируйте ключ приложения:
```bash
php artisan key:generate
```

5. Настройте кеш (в `.env`):
```
CACHE_DRIVER=file
```

6. Зарегистрируйте роуты в `routes/api.php` (см. `routes-example.php`)

7. Запустите сервер:
```bash
php artisan serve
```

API будет доступен по адресу: `http://localhost:8000`

## Установка Frontend

1. Перейдите в папку frontend:
```bash
cd frontend
```

2. Установите зависимости:
```bash
npm install
```

3. Создайте файл `.env`:
```bash
cp .env.example .env
```

4. Настройте URL API в `.env`:
```
VITE_SERVER_URL=http://localhost:8000/api/
```

5. Запустите dev сервер:
```bash
npm run dev
```

6. Откройте в браузере: `http://localhost:5173`

## Сборка для production

### Frontend:
```bash
cd frontend
npm run build
```

Собранные файлы будут в папке `frontend/dist/`

### Backend:
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
```

## Настройка API токена

Если ваш API требует аутентификации, установите токен в localStorage браузера:

```javascript
localStorage.setItem('token', 'your-api-token-here');
```

Или измените код в `frontend/src/utils/axios.ts` для использования другого метода аутентификации.

