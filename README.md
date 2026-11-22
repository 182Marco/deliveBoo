# DeliveBoo

Clone di Deliveroo: i clienti scelgono un tipo di cucina, trovano i ristoranti, riempiono il carrello e pagano; i ristoratori gestiscono ristoranti, piatti, ordini e statistiche da un pannello riservato.

> 🗺️ **Apri [`dev/mappa.html`](dev/mappa.html) nel browser** (doppio clic, nessun server) per la mappa interattiva: grafico concettuale, albero di tutti i file con una spiegazione semplice per ognuno, link di avvio con server e Docker, relazioni tra le tabelle del database, comandi Laravel. È uno strumento solo di sviluppo: segue la skill `skills/project-map-3-tabs` e non finisce nella build.

## Stack

| Parte | Tecnologie |
|---|---|
| Backend | PHP 8.3+ (8.4 in Docker), Laravel 13, laravel/ui 4 (auth) |
| Sito clienti | Vue 3, Vue Router 4, Vuex 4 (+ vuex-persistedstate), Bootstrap 4, axios |
| Pannello ristoratore | Blade, Bootstrap 4, Chart.js |
| Pagamenti | Braintree (`braintree-web`, hosted fields) |
| Build | Vite 8 (laravel-vite-plugin), Sass |
| Test | PHPUnit 12 (SQLite in memoria), Vitest + @vue/test-utils |
| Database | MySQL in locale, SQLite su Render |
| Deploy | Docker (node:22 per la build + php:8.4-apache) su Render |

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

`users` 1─N `restaurants` · `restaurants` 1─N `plates` / `rates` / `orders` · `restaurants` N─N `types` (pivot `restaurant_type`) · `orders` N─N `plates` (pivot `order_plate`). Schema: `db structure.webp`; versione interattiva con colonne e pivot nella tab “Relazioni tabelle” di `dev/mappa.html`.

## Avvio in locale

Con Docker (consigliato, anche da Codespace) servono solo Docker e Node 20.19+:

```bash
cp .env.example .env    # poi DB_HOST=db, DB_DATABASE=deliveboo, DB_USERNAME=deliveboo, DB_PASSWORD=local_password, MAIL_MAILER=log
docker compose build && docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose up -d && docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
npm install && npm run watch   # http://127.0.0.1:8000 (Codespace: indirizzo inoltrato della porta 8000)
```

Senza Docker (PHP 8.3+ con pdo_mysql/pdo_sqlite e gd, MySQL o SQLite sul computer):

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate   # poi configura DB_* e MAIL_* in .env
php artisan migrate --seed
php artisan storage:link
npm run build          # oppure: npm run watch (ricompila a ogni modifica) / npm run dev (HMR)
php artisan serve      # http://127.0.0.1:8000
```

Comandi utili: `npm run build` (build ottimizzata), `php artisan test`, `npm run test:js`, `php artisan migrate:fresh --seed`.

Migrazione da Laravel 7 a 13 e da Mix/Vue 2 a Vite/Vue 3: piano, decisioni e stato in [`dev/PIANO_MIGRAZIONE_L13.md`](dev/PIANO_MIGRAZIONE_L13.md). Su un DB già esistente basta `php artisan migrate` (aggiunge solo la migration che riallinea i tipi dei prezzi su MySQL).

### Database: migrations e seed

```bash
php artisan migrate                                  # crea/aggiorna le tabelle
php artisan db:seed                                  # tutti i seeder (DatabaseSeeder)
php artisan db:seed --class=PlatesTableSeeder        # un solo seeder
```

Ordine dei seeder: Users → Types → Restaurants → Plates → Orders → Rates. Un seeder lanciato da solo richiede che i precedenti siano già stati eseguiti; se manca un riferimento si ferma con un errore chiaro e non scrive nulla (ogni seeder gira in una transazione).

**Rilanciare il seed è sicuro**: i seeder sono idempotenti. Trovano i record tramite chiavi naturali (email dell'utente, nome di tipo e ristorante, ristorante + nome del piatto, ristorante + email + data dell'ordine) e creano solo quello che manca. Non sovrascrivono i record esistenti e non cancellano nulla. Le pivot usano `syncWithoutDetaching`. Per i voti, che non hanno una chiave, il seeder aggiunge solo le righe mancanti per arrivare al numero previsto per ogni (ristorante, voto). `php artisan migrate:fresh --seed` invece **cancella tutte le tabelle** e ricrea il DB da zero: usalo solo se vuoi perdere i dati.

`php artisan test` gira su SQLite in memoria (vedi `phpunit.xml`) e non tocca il DB di sviluppo:

- `DatabaseSeederTest`: due seed consecutivi danno gli stessi conteggi, dati coerenti (FK, piatti degli ordini, prezzi, slug);
- `Api/PublicApiTest`: forme JSON lette dalla SPA, ordini, email;
- `Admin/OwnershipTest`, `Admin/CrudTest`, `Admin/NotFoundTest`: proprietario / altro utente / ospite, CRUD con upload, 404;
- `AuthTest`, `RoutesTest`: login/registrazione/redirect, URI e nomi di ogni rotta.

`npm run test:js` (Vitest, senza rete) prova lo store, il flusso di pagamento Braintree con mock (`tests/js/payment.spec.js`) e che ogni rotta della SPA si apra senza warning Vue. Il pagamento reale si verifica a mano in sandbox con la carta di test 4111 1111 1111 1111.

## Deploy

`Dockerfile` multi-stage (stage `node:22` con `npm ci && npm run build`, poi Apache + PHP 8.4 con DocumentRoot `public/` e `composer install --no-dev`) usato da `render.yaml` (servizio *deliveboo-laravel*, SQLite). `build.sh` è uno script equivalente per build manuali. Variabili da impostare su Render: `APP_KEY`, e le `MAIL_*` per le email.

## Struttura cartelle

```
app/            modelli, controller (Admin, Api, Auth), Mail, AppServiceProvider
bootstrap/      app.php: rotte, middleware, eccezioni (al posto di Kernel e provider)
config/         impostazioni Laravel (solo quelle con valori del progetto)
database/       migrations, seeders, factories
public/         file serviti al browser (build/ di Vite, immagini)
resources/js    sito Vue (pages/ e components/ a cartelle: .vue + .logic.js + .scss, api/, utils/, store, routes) + jsBack (script admin)
resources/sass  stili SCSS
resources/views pagine Blade (admin, auth, guest, layouts, mails)
routes/         web.php, api.php, console.php
tests/          Feature (PHPUnit) e js (Vitest)
vite.config.js  entry point della build e configurazione di Vitest
```

## Problemi noti

- Il pagamento Braintree genera il *nonce* solo lato client: manca la conferma della transazione lato server.
- `Admin/HomeController` non è collegato a nessuna rotta; `StatsController@index` è vuoto.
- `public/build` è generata da Vite: modificare i sorgenti in `resources/` e ricompilare.
- Mancano alcuni controlli del proprietario nell'admin (SEC-1..SEC-4: creazione piatti, modifica/eliminazione di ristoranti e piatti, statistiche) e la creazione di un ristorante dall'admin dà errore 500 perché non imposta lo slug (BUG-1). Sono fissati dai test come comportamento attuale e vanno corretti in commit separati: dettagli in `dev/PIANO_MIGRAZIONE_L13.md`.
