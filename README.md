# Сервер MCP для Московской биржи

Проект содержит консольное приложение на базе [Symfony](https://symfony.com/), реализующее сервер MCP (Model Context Protocol).
Сервер предоставляет инструменты для работы с [данными информационной системы Московской биржи (ISS)](https://www.moex.com/a2193).

Сервер использует библиотеку [`logiscape/mcp-sdk-php`](https://github.com/logiscape/mcp-sdk-php)

## Конфигурация

```json
{
  "mcpServers": {
    "moex": {
      "command": "podman",
      "args": ["run", "-i", "--rm", "docker.io/prikotov/moex-mcp-server:latest", "bin/console", "app:mcp-server"]
    }
  }
}
```

## Возможности (Tools)

- `get_security_specification` — спецификация ценной бумаги;
- `get_security_indices` — индексы, в которые входит бумага;
- `get_security_aggregates` — агрегированные результаты торгов;
- `get_security_trade_data` — данные о торгах на фондовом рынке;

# Информация для разработчиков

## Требования

- PHP версии 8.3 и выше;
- Composer.

Приложение можно запустить как локально, так и в Docker.

## Установка

Склонируйте репозиторий и установите зависимости:

```bash
composer install
```

## Запуск

### Локально

```bash
php bin/console app:mcp-server
```

Либо с помощью podman
```bash
podman run --rm -i moex-mcp-server bin/console app:mcp-server
```

Сервер выводит список доступных инструментов и позволяет вызывать каждый из них. Проверить можно с помощью:
```bash
podman-compose run --rm moex-mcp-server bin/console app:mcp-client --via=console
```

Опция `--via` позволяет выбрать способ запуска сервера (`console`, `podman` или `docker`). По умолчанию используется `console`.


### Docker (Podman)

В проекте присутствуют `Dockerfile` и `compose.yaml`. Чтобы собрать и запустить контейнер, выполните:

```bash
podman build -t moex-mcp-server .
```

## Тесты


```bash
./bin/phpunit
```

Или

```bash
podman-compose run --rm moex-mcp-server bin/phpunit
```

Тесты подключают клиента к серверу и вызывают его инструменты.

## Структура проекта

- `src/` — исходный код приложения;
- `bin/` — консольные скрипты;
- `config/` — конфигурация Symfony;
- `tests/` — интеграционные тесты.
- `var/log` — логи приложения.

## Лицензия

Проект распространяется на условиях лицензии MIT. Полный текст лицензии см. в файле [LICENSE](LICENSE).
