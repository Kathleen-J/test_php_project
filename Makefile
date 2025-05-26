.PHONY: up migrate seed all

up:
	docker compose up -d --build

migrate:
	docker exec -ti apiservice sh -c "vendor/bin/phinx migrate"

down:
	docker exec -ti apiservice sh -c "vendor/bin/phinx rollback -t 0"

seed:
	docker exec -i apiservice env $(cat .env | xargs) php vendor/bin/phinx seed:run -s AdminSeed -s UsersSeed -s PostsSeed -s CommentsSeed

firstStart: up migrate seed