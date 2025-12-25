# Запуск проекта в Docker

## Требования

- Docker
- Docker Compose

## Быстрый старт

1. Перейдите в папку проекта:
```bash
cd williams-indicators-project
```

2. Запустите контейнеры:
```bash
docker-compose up --build
```

3. Откройте в браузере:
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000

## Остановка

```bash
docker-compose down
```

## Пересборка

Если изменили зависимости или Dockerfile:

```bash
docker-compose up --build --force-recreate
```

## Логи

Просмотр логов всех сервисов:
```bash
docker-compose logs -f
```

Логи только backend:
```bash
docker-compose logs -f backend
```

Логи только frontend:
```bash
docker-compose logs -f frontend
```

## Вход в контейнер

### Backend контейнер:
```bash
docker-compose exec backend sh
```

### Frontend контейнер:
```bash
docker-compose exec frontend sh
```

## Установка зависимостей вручную

Если нужно установить зависимости вручную:

### Backend:
```bash
docker-compose exec backend composer install
```

### Frontend:
```bash
docker-compose exec frontend npm install
```

## Переменные окружения

### Backend
Создайте файл `backend/.env` (опционально, для Laravel):
```env
APP_NAME="Williams Indicators"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Примечание:** Проект работает без полного Laravel, поэтому `.env` не обязателен. Все настройки можно задать через переменные окружения в `docker-compose.yml`.

### Frontend
Создайте файл `frontend/.env`:
```env
VITE_SERVER_URL=http://localhost:8000/api/
```

**Важно:** 
- В Docker контейнере frontend использует `http://backend:8000/api/` для внутренней связи
- В браузере используется `http://localhost:8000/api/` (настроено автоматически)

## Проблемы и решения

### Порт уже занят
Если порты 8000 или 5173 заняты, измените их в `docker-compose.yml`:
```yaml
ports:
  - "8001:8000"  # Вместо 8000
  - "5174:5173"  # Вместо 5173
```

### Ошибки с правами доступа
```bash
sudo chown -R $USER:$USER backend frontend
```

### Очистка и пересборка
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up
```

