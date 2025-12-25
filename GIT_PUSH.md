# Инструкция по отправке проекта в Git репозиторий

## Шаг 1: Инициализация Git (если еще не сделано)

```bash
cd williams-indicators-project
git init
```

## Шаг 2: Добавление всех файлов

```bash
git add .
```

## Шаг 3: Создание первого коммита

```bash
git commit -m "Initial commit: Bill Williams Indicators project"
```

## Шаг 4: Создание репозитория на GitHub/GitLab/Bitbucket

1. Зайдите на GitHub (github.com) или другой Git-хостинг
2. Нажмите "New repository" (Создать репозиторий)
3. Укажите имя репозитория (например: `williams-indicators`)
4. НЕ добавляйте README, .gitignore или лицензию (они уже есть)
5. Нажмите "Create repository"

## Шаг 5: Подключение к удаленному репозиторию

После создания репозитория GitHub покажет команды. Выполните:

```bash
git remote add origin https://github.com/ВАШ_USERNAME/williams-indicators.git
```

Или если используете SSH:
```bash
git remote add origin git@github.com:ВАШ_USERNAME/williams-indicators.git
```

## Шаг 6: Отправка кода

```bash
git branch -M main
git push -u origin main
```

## Если нужно обновить код позже

```bash
git add .
git commit -m "Описание изменений"
git push
```

## Проверка статуса

```bash
git status
```

## Просмотр удаленных репозиториев

```bash
git remote -v
```

