# Development
## Requirements
- [Have docker installed on your local machine](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-20-04)
- [Have docker-compose installed on your local machine](https://docs.docker.com/compose/install/)
- cp .env.example .env
  - update APP_URL to match the local url you give to the project
  - update DB_PASSWORD DB_ROOT_PASSWORD to some sensible values
## Run
`` docker-compose up -d``

(optional after containers have started) 

``docker-compose exec api php artisan jwt:secret``
## Frontend
#### Purgecss
Before deploying make sure you run:
``
docker-compose exec ui npm run prod
``
to check for any unintentional css being removed by Purgecss

## Testing

For Cypress E2E test run the command
``
bash cy.sh
``

For Laravel tests run
``
docker-compose exec api php artisan test
``
# Development deploy
``
docker-compose -f docker-compose.dev.yml up -d --build
``

# Production deploy
``
docker-compose -f docker-compose.prod.yml up -d --build
``