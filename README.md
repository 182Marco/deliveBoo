# DeliveBoo

Clone di Deliveroo: i clienti scelgono un tipo di cucina, trovano i ristoranti, riempiono il carrello e pagano; i ristoratori gestiscono ristoranti, piatti, ordini e statistiche da un pannello riservato.

> 🗺️ **Apri [`dev/mappa.html`](dev/mappa.html) nel browser** (doppio clic, nessun server) per la mappa interattiva: grafico concettuale, albero di tutti i file con una spiegazione semplice per ognuno, link di avvio con server e Docker, comandi Laravel. È uno strumento solo di sviluppo: segue la skill `skills/project-map-3-tabs` e non finisce nella build.

## Stack

| Parte | Tecnologie |
|---|---|
| Backend | PHP 7.2+/8, Laravel 7, laravel/ui (auth) |
| Sito clienti | Vue 2, Vue Router, Vuex (+ vuex-persistedstate), Bootstrap-Vue, axios |
| Pannello ristoratore | Blade, Bootstrap 4, Chart.js |
| Pagamenti | Braintree (`braintree-web`, hosted fields) |
| Build | Laravel Mix 5 (webpack), Sass |
| Database | MySQL in locale, SQLite su Render |
| Deploy | Docker (php:8.2-apache) su Render |

## Come funziona

```
CLIENTE                                         RISTORATORE
Browser ─► guest/home.blade.php (#root)         Browser ─► /login ─► /admin
            └─ Vue SPA (resources/js)                      └─ Blade (resources/views/admin)
                 │ axios                                         │
                 ▼                                               ▼
           routes/api.php ─► Controllers/Api            routes/web.php ─► Controllers/Admin
                                  └──────────► Modelli Eloquent ◄──────────┘
                                                    │
                                                 Database
```

- Qualsiasi URL che non sia `/admin` o `/login…` viene servito da `routes/web.php` (rotta *catch-all*) con la pagina `guest/home.blade.php`, che avvia la SPA Vue. Il routing delle pagine cliente è in `resources/js/routes.js`.
- Lo stato (tipi scelti, ristoranti, **carrello**) sta in `resources/js/store.js` ed è salvato nel browser.
- Gli ordini arrivano con `POST /api/orders`: validazione, salvataggio, collegamento ai piatti e email di conferma (`app/Mail/OrderedMessage.php`).

## Funzionalità

**Cliente**: scelta di uno o più tipi di cucina · ristoranti migliori (media voti) · ristoranti per tipo (paginati) · menu del ristorante · carrello (un solo ristorante per ordine) · pagamento · email di conferma.

**Ristoratore**: registrazione/login · CRUD ristoranti (con immagine e tipi) · CRUD piatti · lista e dettaglio ordini · statistiche con grafici (spesa per ordine, cliente top, incassi mensili). Ognuno vede solo i propri dati.

## API

| Metodo | URL | Cosa fa |
|---|---|---|
| GET | `/api/types` | Tutti i tipi di cucina |
| GET | `/api/restaurantsTopRated` | I 12 ristoranti con media voti più alta |
| GET | `/api/restaurants/{ids}` | Ristoranti per tipi (`ids` = `1,3,5`), 4 per pagina |
| GET | `/api/restaurantsMenu/{slug}` | Dettagli, tipi, media voti e menu paginato |
| POST | `/api/orders` | Crea un ordine e invia l'email |

## Database

`users` 1─1 `restaurants` · `restaurants` 1─N `plates` / `rates` / `orders` · `restaurants` N─N `types` (pivot `restaurant_type`) · `orders` N─N `plates` (pivot `order_plate`). Schema: `db structure.webp`.

## Avvio in locale

Con Docker (consigliato, anche da Codespace) servono solo Docker e Node 18+:

```bash
cp .env.example .env    # poi DB_HOST=db, DB_DATABASE=deliveboo, DB_USERNAME=deliveboo, DB_PASSWORD=local_password, MAIL_MAILER=log
docker compose build && docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose up -d && docker compose exec app php artisan migrate --seed
npm install && npm run watch   # http://127.0.0.1:8000 (Codespace: indirizzo inoltrato della porta 8000)
```

Senza Docker (PHP 7.4–8.1 e MySQL sul computer):

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate   # poi configura DB_* e MAIL_* in .env
php artisan migrate --seed
php artisan storage:link
npm run dev            # oppure: npm run watch
php artisan serve      # http://127.0.0.1:8000
```

Comandi utili: `npm run prod` (build ottimizzata), `php artisan test`, `php artisan migrate:fresh --seed`.

### Database: migrations e seed

```bash
php artisan migrate                                  # crea/aggiorna le tabelle
php artisan db:seed                                  # tutti i seeder (DatabaseSeeder)
php artisan db:seed --class=PlatesTableSeeder        # un solo seeder
```

Ordine dei seeder: Users → Types → Restaurants → Plates → Orders → Rates. Un seeder lanciato da solo richiede che i precedenti siano già stati eseguiti; se manca un riferimento si ferma con un errore chiaro e non scrive nulla (ogni seeder gira in una transazione).

**Rilanciare il seed è sicuro**: i seeder sono idempotenti. Trovano i record tramite chiavi naturali (email dell'utente, nome di tipo e ristorante, ristorante + nome del piatto, ristorante + email + data dell'ordine) e creano solo quello che manca. Non sovrascrivono i record esistenti e non cancellano nulla. Le pivot usano `syncWithoutDetaching`. Per i voti, che non hanno una chiave, il seeder aggiunge solo le righe mancanti per arrivare al numero previsto per ogni (ristorante, voto). `php artisan migrate:fresh --seed` invece **cancella tutte le tabelle** e ricrea il DB da zero: usalo solo se vuoi perdere i dati.

`php artisan test` gira su SQLite in memoria (vedi `phpunit.xml`) e non tocca il DB di sviluppo. `tests/Feature/DatabaseSeederTest.php` verifica che due seed consecutivi diano gli stessi conteggi e che i dati siano coerenti (FK, piatti degli ordini, prezzi, slug).

## Deploy

`Dockerfile` (Apache + PHP 8.2, `composer install --no-dev`, `npm run prod`) usato da `render.yaml` (servizio *deliveboo-laravel*, SQLite). `build.sh` è uno script equivalente per build manuali. Variabili da impostare su Render: `APP_KEY`, e le `MAIL_*` per le email.

## Struttura cartelle

```
app/            modelli, controller (Admin, Api, Auth), middleware, Mail
config/         impostazioni Laravel
database/       migrations, seeds, factories
public/         file serviti al browser (js/css compilati, immagini)
resources/js    sito Vue (pages, components, store, routes) + jsBack (script admin)
resources/sass  stili SCSS
resources/views pagine Blade (admin, auth, guest, layouts, mails)
routes/         web.php, api.php
```

## Problemi noti

- Le chiamate axios in `resources/js/store.js` e `pages/Payment.vue` usano `http://127.0.0.1:8000` fisso: in produzione vanno sostituite con URL relativi (`/api/...`).
- Il pagamento Braintree genera il *nonce* solo lato client: manca la conferma della transazione lato server.
- `resources/js/routes copy.js` è una copia vecchia non usata; `Admin/HomeController` non è collegato a nessuna rotta; `StatsController@index` è vuoto.
- `public/js` e `public/css` sono file compilati: modificare i sorgenti in `resources/` e ricompilare.
- I test presenti sono solo gli esempi di Laravel.
