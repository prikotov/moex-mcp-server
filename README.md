# Сервер MCP для Московской биржи

Проект содержит консольное приложение на базе [Symfony](https://symfony.com/), реализующее сервер MCP (Model Context Protocol).
Сервер предоставляет инструменты для работы с [данными информационной системы Московской биржи (ISS)](https://www.moex.com/a2193).

Сервер использует библиотеку [`logiscape/mcp-sdk-php`](https://github.com/logiscape/mcp-sdk-php)

## Конфигурация

```json
{
  "mcpServers": {
    "moex": {
      "command": "docker",
      "args": ["run", "-i", "--rm", "prikotov/moex-mcp-server:latest", "bin/server"]
    }
  }
}
```

## Возможности (Tools)

- `get_security_specification` — возвращает спецификацию указанной ценной бумаги из ISS МОEX;
- `get_security_indices` — показывает индексы Московской биржи, в которые включена бумага;
- `get_security_aggregates` — выводит агрегированные итоги торгов по инструменту за выбранную дату;
- `get_security_trade_data` — предоставляет текущие данные о торгах по инструменту;

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
bin/server
```

Либо традиционным способом:
```bash
php bin/console app:mcp-server
```

Либо с помощью podman:
```bash
podman run --rm -i moex-mcp-server bin/console app:mcp-server
```

Либо с помощью docker:
```bash
docker run --rm -i moex-mcp-server bin/console app:mcp-server
```

Сервер выводит список доступных инструментов и позволяет вызывать каждый из них. Проверить можно с помощью:
```bash
podman-compose run --rm moex-mcp-server bin/console app:mcp-client --via=console
# или
docker-compose run --rm moex-mcp-server bin/console app:mcp-client --via=console
```

Опция `--via` позволяет выбрать способ запуска сервера (`console`, `podman` или `docker`). По умолчанию используется `console`.

### Docker (Podman)

В проекте присутствуют `Dockerfile` и `compose.yaml`. Чтобы собрать и запустить контейнер, выполните:

```bash
podman build -t moex-mcp-server .
# или
docker build -t moex-mcp-server .
```

## Makefile

Для автоматизации типовых задач используется `Make`. Основные цели:

```bash
make help         # вывести справку
make build        # собрать Docker-образ
make push         # отправить образ в репозиторий
make cache-clear  # очистить кэш приложения
make test         # запустить тесты и пример клиента
```

## Тесты

```bash
./bin/phpunit
```

Или

```bash
podman-compose run --rm moex-mcp-server bin/phpunit
# или 
docker-compose run --rm moex-mcp-server bin/phpunit
```

Тесты подключают клиента к серверу и вызывают его инструменты.

## Структура проекта

- `src/` — исходный код приложения;
- `src/Tool` — исходный код Tools;
- `bin/` — консольные скрипты;
- `config/` — конфигурация Symfony;
- `tests/` — интеграционные тесты.
- `var/log` — логи приложения.

## Лицензия

Проект распространяется на условиях лицензии MIT. Полный текст лицензии см. в файле [LICENSE](LICENSE).
