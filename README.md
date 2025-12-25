# Индикаторы Билла Вильямса

Проект для анализа акций MOEX с использованием индикаторов Билла Вильямса.

## Описание

Проект реализует следующие индикаторы:

- **Alligator** - три скользящие средние (Челюсть 13, Зубы 8, Губы 5)
- **Awesome Oscillator (AO)** - разница между 5-периодной и 34-периодной SMMA медианной цены
- **Reversal Bars** - свечные паттерны разворота тренда
- **Market Analysis** - анализ текущей рыночной ситуации
- **Trading Signals** - торговые сигналы на основе индикаторов

## Быстрый старт

### Запуск в Docker

Требования: Docker и Docker Compose

```bash
git clone <your-repo-url>
cd williams-indicators-project
docker-compose up --build
```

После запуска откройте в браузере:
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000

Подробные инструкции по Docker см. в [DOCKER.md](./DOCKER.md)

### Локальная установка

Подробные инструкции см. в [INSTALL.md](./INSTALL.md)

#### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

#### Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Откройте в браузере: http://localhost:5173

## Структура проекта

```
williams-indicators-project/
├── frontend/          # Vue 3 + Vite приложение
│   ├── src/
│   │   ├── views/
│   │   │   └── WilliamsIndicators.vue
│   │   ├── router/
│   │   ├── plugins/
│   │   └── utils/
│   ├── package.json
│   └── vite.config.ts
├── backend/           # Laravel API
│   ├── app/
│   │   ├── Services/
│   │   │   ├── MoexService.php
│   │   │   └── Moex/
│   │   └── Http/Controllers/
│   └── routes/
│       └── api.php
├── README.md
├── INSTALL.md
└── DOCKER.md
```

## API Endpoints

- `GET /api/moex/stocks` - список акций MOEX
- `GET /api/moex/stocks/{secid}/history?days={days}` - исторические данные акции
- `GET /api/moex/stocks/{secid}/indicators?days={days}` - индикаторы Билла Вильямса

## Технологии

**Backend:**
- Laravel 10+
- PHP 8.2+
- Guzzle HTTP Client

**Frontend:**
- Vue 3 (Composition API)
- TypeScript
- Vite
- Vuetify 3
- ApexCharts
- Axios

## Настройка

### API токен

Если API требует аутентификации, установите токен в localStorage:

```javascript
localStorage.setItem('token', 'your-api-token');
```

### Роуты

Для полной работы также нужны роуты для получения списка акций и истории. См. `backend/routes-example.php`

### MOEX API

Проект использует публичный API MOEX для получения данных об акциях.

## Лицензия

MIT
