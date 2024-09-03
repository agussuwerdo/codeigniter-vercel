# Define variables
DOCKER_COMPOSE = docker-compose
PHP_CONTAINER = codeigniter_vercel_app
POSTGRES_CONTAINER = codeigniter_vercel_postgres

# Default target
all: help

# Start services
up:
	$(DOCKER_COMPOSE) up -d

# Stop services
down:
	$(DOCKER_COMPOSE) down

# Access the PHP container's shell
shell:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) bash

# Run Composer install
composer-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install

# Run Composer update
composer-update:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer update

# Run migrations
migrate:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php index.php migrate

# Generate a new migration
generate-migration:
	./generate_migration.sh $(name)

# View logs
logs:
	$(DOCKER_COMPOSE) logs -f

# Restart services
restart: down up

# Access the Postgres container's shell
postgres-shell:
	$(DOCKER_COMPOSE) exec $(POSTGRES_CONTAINER) bash

# Display help
help:
	@echo "Makefile commands:"
	@echo "  up                Start Docker containers"
	@echo "  down              Stop Docker containers"
	@echo "  shell             Access the PHP container's shell"
	@echo "  composer-install  Run 'composer install' inside the PHP container"
	@echo "  composer-update   Run 'composer update' inside the PHP container"
	@echo "  migrate           Run migrations inside the PHP container"
	@echo "  generate-migration Generate a new migration file with a timestamp"
	@echo "  logs              View logs from Docker containers"
	@echo "  restart           Restart Docker containers"
	@echo "  postgres-shell    Access the PostgreSQL container's shell"
