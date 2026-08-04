COMPOSE ?= docker compose

.PHONY: install up down logs shell lint test

install:
	composer install

up:
	$(COMPOSE) up --build

down:
	$(COMPOSE) down

logs:
	$(COMPOSE) logs -f app

shell:
	$(COMPOSE) run --rm app sh

lint:
	@for file in $$(find . -path './vendor' -prune -o -name '*.php' -print); do php -l "$$file" || exit 1; done

test: lint
	@echo "No automated test suite configured yet. PHP syntax checks passed."
