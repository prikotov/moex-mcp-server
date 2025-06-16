include .env
-include .env.local

IMAGE = $(IMAGE_REPO)/$(IMAGE_NAME):$(IMAGE_TAG)

# Выбираем compose команду в зависимости от CONTAINER_SYSTEM
ifeq ($(CONTAINER_SYSTEM),podman)
	COMPOSE = podman-compose
else
	COMPOSE = docker-compose
endif

.PHONY: help
help: ## Показать помощь
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

############
#  Docker  #
############
.PHONY: build
build: ## Сборка контейнера
	docker build -t $(IMAGE) .

push: ## Публикация контейнера
	docker push $(IMAGE)

#########
#  Кэш  #
#########
.PHONY: cache-clear
cache-clear: ## Сбросить кэш
	bin/console cache:clear

###########
#  Тесты  #
###########
.PHONY: test
test: ## Запустить тесты
	$(COMPOSE) run --rm mcp-server bin/console cache:clear
	$(COMPOSE) run --rm mcp-server bin/console -vv app:test
	$(COMPOSE) run --rm mcp-server bin/phpunit
	$(COMPOSE) run --rm mcp-server bin/console -vv app:mcp-client --via=console

