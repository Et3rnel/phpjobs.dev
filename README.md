# The phpjobs.dev website allows users to find PHP jobs online 
## How to install and start the project
```bash
symfony server:start && \
php bin/console doctrine:schema:create && \
php bin/console doctrine:migrations:migrate && \
yarn install && \
npm run watch
```

## Setup your API credentials
To use the project you need to setup two env. vars with your emploi store credentials, **EMPLOI_STORE_CLIENT_ID** 
and **EMPLOI_STORE_CLIENT_SECRET**.
