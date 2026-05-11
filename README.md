# back-portuguesando

Backend **Laravel 12** (API REST + Sanctum) e app **Expo** (`mobile/`) para revisão gamificada com repetição espaçada.

## Requisitos

- PHP 8.2+, Composer, Node.js (para o mobile e assets opcionais)
- PostgreSQL (produção) ou SQLite (testes)
- Redis (filas e cache, conforme `.env`)

## Backend

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

API em `http://127.0.0.1:8000` (prefixo `/api`). Testes: `php artisan test`.

Agendador (revisões devidas): `php artisan schedule:work` ou cron que chame `schedule:run`.

## Mobile (Expo)

```bash
cd mobile
npm install
npx expo start
```

Configure `API_BASE_URL` em `mobile/src/config/env.ts` (emulador Android costuma usar `10.0.2.2:8000`).

## Documentação

- Modelo de dados: `docs/database-er.md`

## Licença

MIT (framework Laravel sob [licença MIT](https://opensource.org/licenses/MIT)).
