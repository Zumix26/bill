# Настройка Git репозитория

## Инициализация репозитория

1. Перейдите в папку проекта:
```bash
cd williams-indicators-project
```

2. Инициализируйте Git:
```bash
git init
```

3. Добавьте все файлы:
```bash
git add .
```

4. Создайте первый коммит:
```bash
git commit -m "Initial commit: Bill Williams Indicators standalone project"
```

## Подключение к удаленному репозиторию

1. Создайте новый репозиторий на GitHub/GitLab/Bitbucket

2. Добавьте remote:
```bash
git remote add origin https://github.com/yourusername/williams-indicators.git
```

3. Отправьте код:
```bash
git branch -M main
git push -u origin main
```

## Структура для публикации

Проект уже содержит:
- ✅ `.gitignore` - настроен для игнорирования node_modules, vendor, .env и т.д.
- ✅ `README.md` - основная документация
- ✅ `INSTALL.md` - инструкция по установке
- ✅ `FILES.md` - описание структуры файлов
- ✅ Все необходимые конфигурационные файлы

## Что будет в репозитории

```
williams-indicators-project/
├── .gitignore
├── README.md
├── INSTALL.md
├── FILES.md
├── GIT_SETUP.md
├── frontend/
│   ├── package.json
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── .env.example
│   └── src/
└── backend/
    ├── composer.json
    ├── .env.example
    ├── routes/
    └── app/
```

**НЕ будут включены:**
- `node_modules/` (frontend)
- `vendor/` (backend)
- `.env` файлы (только `.env.example`)
- Кеш и временные файлы

