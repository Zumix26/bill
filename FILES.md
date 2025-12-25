# Структура файлов проекта

## Frontend

```
frontend/
├── index.html                    # Главный HTML файл
├── package.json                  # Зависимости npm
├── vite.config.ts               # Конфигурация Vite
├── tsconfig.json                # Конфигурация TypeScript
├── tsconfig.node.json           # Конфигурация TypeScript для Node
├── .env.example                 # Пример переменных окружения
└── src/
    ├── main.ts                  # Точка входа приложения
    ├── App.vue                  # Главный компонент
    ├── router/
    │   └── index.ts            # Роутинг
    ├── plugins/
    │   └── vuetify.ts          # Конфигурация Vuetify
    ├── utils/
    │   └── axios.ts            # Настройка Axios
    └── views/
        └── WilliamsIndicators.vue  # Главная страница с индикаторами
```

## Backend

```
backend/
├── composer.json                # Зависимости Composer
├── .env.example                 # Пример переменных окружения
├── routes/
│   ├── api.php                  # API роуты
│   └── routes-example.php      # Пример полной регистрации роутов
└── app/
    ├── Http/
    │   └── Controllers/
    │       └── API/
    │           └── Moex/
    │               └── BillWilliamsIndicatorsController.php
    └── Services/
        ├── MoexService.php      # Основной сервис для работы с MOEX API
        └── Moex/
            ├── AlligatorIndicatorService.php
            ├── AwesomeOscillatorService.php
            ├── BillWilliamsIndicatorsService.php
            ├── MarketAnalysisService.php
            ├── ReversalBarsService.php
            ├── SMMAService.php
            └── TradingSignalsService.php
```

## Корневые файлы

```
├── README.md                    # Основная документация
├── INSTALL.md                   # Инструкция по установке
├── FILES.md                     # Этот файл
└── .gitignore                   # Игнорируемые файлы Git
```

