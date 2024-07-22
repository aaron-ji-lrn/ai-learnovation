# Assessment feedback
- An App for graders/teacher to help them to generates a hollistic feedback for a student or the entire class assessments
- Project document: https://learnosity.atlassian.net/wiki/spaces/PT/pages/3006824651/Moving+from+AI+generated+question+level+feedback+to+holistic+assessment+feedback

## Getting started
- Run `composer install`
- yarn install
- yarn dev
- ./vendor/bin/sail artisan migrate
- ./vendor/bin/sail up -d
- .env configuration → openai_key
- http://localhost:8085/assessments/1/feedback

### Running UI
- yarn install
- yarn dev
- http://localhost:8085/generate/

### install
```
curl -s https://laravel.build/assessment-feedback | bash
cd assessment-feedback && ./vendor/bin/sail up
```

### Technology
- Backend: PHP + Laravel + Blde + OpenAI
- Frontend : ReactJS + Tailwind + Typescript
