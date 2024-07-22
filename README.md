# Assessment feedback
- An App for graders (teachers) to assisst them for generating a hollistic feedback of a student or class assessments
- Project document: https://learnosity.atlassian.net/wiki/spaces/PT/pages/3006824651/Moving+from+AI+generated+question+level+feedback+to+holistic+assessment+feedback

## Getting started
- Run `composer install`
- `yarn install`
- ./vendor/bin/sail artisan migrate
- ./vendor/bin/sail up -d
- .env configuration → openai_key
- http://localhost:8085/assessments/1/feedback

### Running the frontend
- yarn install
- yarn dev
- http://localhost:8085/generate/

### Running the banckend
```
curl -s https://laravel.build/assessment-feedback | bash
cd assessment-feedback && ./vendor/bin/sail up
```

## Tech Stack
- Backend: PHP + Laravel + Blade + OpenAI
- Frontend : ReactJS + Tailwind + Typescript
