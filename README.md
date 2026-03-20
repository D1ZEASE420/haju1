# Haju1

Täisfunktsionaalne veebirakendus ehitatud Laravel 13 + Vue 3 + Inertia.js baasil.

## Funktsioonid

- 🌤 **Ilmateade** — OpenWeatherMap API integratsioon
- 📝 **Blogi** — postituste CRUD koos kommentaaridega ja admin-moderatsiooniga
- 🗺️ **Kaart** — interaktiivne Leaflet kaart koos markerite haldusega
- 🛍️ **E-pood** — tooted, ostukorv ja Stripe maksed
- 🎵 **Muusika API** — lemmikalbumid koos dokumenteeritud JSON API-ga
- 🔒 **Autentimine** — registreerimine, sisselogimine, 2FA

## Tehnoloogiad

**Backend:** PHP 8.3, Laravel 13, Inertia.js, Laravel Fortify, Stripe SDK  
**Frontend:** Vue 3, TypeScript, Tailwind CSS 4, Leaflet.js, Axios, Vite

## Paigaldamine

```bash
# 1. Klooni repo
git clone <repo-url> haju1
cd haju1

# 2. Installi sõltuvused
composer install
npm install

# 3. Seadista keskkond
cp .env.example .env
php artisan key:generate

# 4. Loo andmebaas ja käivita migratsioonid
touch database/database.sqlite
php artisan migrate

# 5. Laadi testiandmed
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=AlbumSeeder

# 6. Käivita
php artisan serve
npm run dev
```

Rakendus on kättesaadav aadressil **http://localhost:8000**

## Keskkonna muutujad

Lisa järgmised read `.env` faili:

```env
OPENWEATHER_API_KEY=sinu_api_võti

STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx
```

## Admin kasutaja

Pärast seedimist on admin konto:  
**E-post:** `admin@admin.com`  
**Parool:** `password`

## JSON API

Muusika albumite API on kättesaadav `/api/albums` all:

| Endpoint | Kirjeldus |
|---|---|
| `GET /api/albums` | Kõik albumid |
| `GET /api/albums?search=pink&genre=Rock&limit=5` | Filtreerimine |
| `GET /api/albums/{id}` | Üks album |
| `GET /api/albums/meta/genres` | Kõik žanrid |
| `GET /api/albums/meta/stats` | Statistika |

## Andmebaasi struktuur

| Tabel | Kirjeldus |
|---|---|
| `users` | Kasutajad (`is_admin` väli) |
| `blogs` | Blogi postitused |
| `comments` | Kommentaarid |
| `markers` | Kaardi markerid |
| `products` | Poe tooted |
| `orders` | Tellimused (JSON items, Stripe) |
| `my_favorite_subject` | Muusika albumid |