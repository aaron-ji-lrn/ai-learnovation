# Assessment feedback

- An App for graders (teachers) to assisst them for generating a hollistic feedback of a student or class assessments
- Project document: <https://learnosity.atlassian.net/wiki/spaces/PT/pages/3006824651/Moving+from+AI+generated+question+level+feedback+to+holistic+assessment+feedback>

## Getting started

- Run `composer install`
- `yarn install`
- `yarn build`
- .env configuration
- add openai_key to call AI endpoint
- set the session driver to use file

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

- add datasource configurations for ibk, qr, dexter, you can get the consumer 0034 datasource info from central

```env
DEXTER_CONNECTION=mysql
DEXTER_HOST=db-hmhccas.int.lninfra.net
DEXTER_DB_NAME=dev_dexter_ember
DEXTER_DB_USER=dev
DEXTER_DB_PASSWORD=

QR_CONNECTION=pgsql
QR_HOST=int-pgsqla1.int.lninfra.net
QR_PORT=5432
QR_DB_NAME=int_questionresponses_shared
QR_DB_USER=int_questionresponses_shared
QR_DB_PASSWORD=

IBK_CONNECTION=mysql
IBK_HOST=db-ibk-master.int.lninfra.net
IBK_DB_NAME=dev_itembank
IBK_DB_USER=dev
IBK_DB_PASSWORD=
```

### Running the frontend

- yarn install
- yarn dev

### Running the backend

```bash
php artisan serve
```

you will see something like this:

```bash
  INFO  Server running on [http://127.0.0.1:8000].  
```

Now you can access it through url <http://127.0.0.1:8000>

we didn't use sail for the docker environment, because the for php8.3 with sail plugin we couldn't connect to postgres DB

## Tech Stack

- Backend: PHP + Laravel + Blade + OpenAI
- Frontend : ReactJS + Tailwind + Typescript
