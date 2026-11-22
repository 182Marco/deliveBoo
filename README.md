# Foodsy

App di food delivery (repository `deliveBoo`): i clienti scelgono un tipo di cucina, trovano i ristoranti, riempiono il carrello e pagano; i ristoratori gestiscono ristoranti, piatti, ordini e statistiche da un pannello riservato.

> 🗺️ **Apri [`dev/mappa.html`](dev/mappa.html) nel browser** (doppio clic, nessun server) per la mappa interattiva: grafico concettuale, albero di tutti i file con una spiegazione semplice per ognuno, link di avvio con server e Docker, relazioni tra le tabelle del database, comandi Laravel, pagamenti spiegati semplici, carte di prova per simulare pagamenti riusciti e rifiutati, stile e brand (palette, font, regole d'uso). È uno strumento solo di sviluppo: segue la skill `skills/project-map-3-tabs` e non finisce nella build.

## Stack

| Parte | Tecnologie |
|---|---|
| Backend | PHP 8.3+ (8.4 in Docker), Laravel 13, laravel/ui 4 (auth) |
| Sito clienti | Vue 3, Vue Router 4, Vuex 4 (+ vuex-persistedstate), axios, sistema grafico «Mercato» in CSS puro (nessun framework CSS) |
| Pannello ristoratore | Blade, stesso sistema grafico, Chart.js |
| Pagamenti | Braintree (`braintree-web` hosted fields + `braintree/braintree_php` lato server) dietro un'interfaccia `PaymentGateway`, con driver offline `fake` per demo e test |
| Build | Vite 8 (laravel-vite-plugin) |
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
| GET | `/api/payments/config` | Driver di pagamento, `testMode` e client token Braintree per il checkout |
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

`npm run test:js` (Vitest, senza rete) prova lo store, il flusso di pagamento con mock (`tests/js/payment.spec.js`) e che ogni rotta della SPA si apra senza warning Vue.

## Pagamenti

Il pagamento è confermato **lato server**: il browser produce solo un *nonce* monouso (il numero di carta non passa mai dal nostro server), `POST /api/orders` ricalcola l'importo dai prezzi nel database (il prezzo inviato dal client è ignorato), salva l'ordine `pending`, addebita tramite `App\Payments\PaymentGateway` e lo segna `paid` con il `transaction_id` oppure `failed` con il motivo. La mail parte solo a pagamento riuscito; l'admin e le statistiche contano solo gli ordini `paid`. Se il provider non risponde l'ordine resta `pending` (da riconciliare). Rotte limitate con `throttle`.

Il driver si sceglie con `PAYMENT_DRIVER` nel `.env`:

| Driver | Cosa fa | Serve |
|---|---|---|
| `fake` (default) | Demo offline: form carta locale che genera gli stessi nonce di test del sandbox Braintree; `FakeGateway` li interpreta | niente |
| `braintree` | Hosted fields Braintree con client token generato dal server e `Transaction::sale` (`submitForSettlement`) | account sandbox gratuito su sandbox.braintreegateway.com → `BRAINTREE_MERCHANT_ID`, `BRAINTREE_PUBLIC_KEY`, `BRAINTREE_PRIVATE_KEY` |

### Simulare un pagamento

In un ambiente di prova (driver `fake`, oppure `braintree` con `BRAINTREE_ENVIRONMENT=sandbox`) il checkout mostra sopra il campo *First name* una barra discreta **Test mode** che apre una finestra con la carta che paga e quella che viene rifiutata, da copiare; in produzione non compare (`testMode` di `/api/payments/config`).

| Driver | Pagamento riuscito | Pagamento rifiutato |
|---|---|---|
| `fake` | **4111 1111 1111 1111** | **4000 0000 0000 0002** |
| `braintree` sandbox | **4111 1111 1111 1111** | la stessa carta con un **totale tra €2,000.00 e €2,999.99**: in sandbox Braintree rifiuta in base all'importo, non al numero di carta. Con i dati di prova: 12 × Dom Pérignon da Peri's = €2,040.00 |

Dopo un pagamento riuscito la pagina di successo mostra la **ricevuta**: numero d'ordine, chi ha incassato (*Braintree sandbox* o *Demo payment*) e il **Transaction ID** assegnato dal provider; lo stesso dato compare nel dettaglio ordine dell'area partner. È la prova che la transazione è stata accettata, senza dare accesso al pannello Braintree.

Scadenza: qualsiasi data futura; CVV: qualsiasi. Istruzioni passo passo nella tab “Carte di prova” di `dev/mappa.html`. I test automatici non chiamano mai Braintree.

`braintree-web` (~400 kB) è un chunk separato, scaricato solo quando il driver è `braintree`.

## Stile

Direzione grafica «Mercato», un mercato gastronomico contemporaneo: superfici porcellana e avena, verde rosmarino `#1f2b23` per la profondità, paprika `#a8501c` per le azioni, `#D0682C` (colore ufficiale) solo per il logo e per grandi superfici con testo scuro; oliva, zafferano e pomodoro per positivo, attenzione ed errore. Font locali Bricolage Grotesque (titoli, nomi, prezzi) e Figtree (testo). Tutti i valori sono in `resources/css/tokens.css`; il perché di ogni scelta e le regole d'uso sono nella tab “Stile e brand” di `dev/mappa.html`.

Le foto di piatti, ristoranti e cucine sono **solo link esterni** (Unsplash): il progetto non ospita foto, `public/images/` contiene solo il logo.

## Deploy

`Dockerfile` multi-stage (stage `node:22` con `npm ci && npm run build`, poi Apache + PHP 8.4 con DocumentRoot `public/` e `composer install --no-dev`) usato da `render.yaml` (servizio *deliveboo-laravel*, SQLite). `build.sh` è uno script equivalente per build manuali. Variabili da impostare su Render: `APP_KEY`, le `MAIL_*` per le email e, per pagamenti Braintree veri, `PAYMENT_DRIVER=braintree` con le `BRAINTREE_*`.

## Struttura cartelle

```
app/            modelli, controller (Admin, Api, Auth), Mail, Payments (gateway di pagamento), AppServiceProvider
bootstrap/      app.php: rotte, middleware, eccezioni (al posto di Kernel e provider)
config/         impostazioni Laravel (solo quelle con valori del progetto)
database/       migrations, seeders, factories
public/         file serviti al browser (build/ di Vite, logo)
resources/js    sito Vue (pages/ e components/ a cartelle: .vue + .logic.js + .css, api/, utils/, store, routes) + jsBack (script admin)
resources/css   sistema grafico: tokens, font, base, primitive ui-*, entry site.css e admin.css
resources/views pagine Blade (admin, auth, guest, layouts, mails)
routes/         web.php, api.php, console.php
tests/          Feature (PHPUnit) e js (Vitest)
vite.config.js  entry point della build e configurazione di Vitest
```

## Problemi noti

- `Admin/HomeController` non è collegato a nessuna rotta; `StatsController@index` è vuoto.
- `public/build` è generata da Vite: modificare i sorgenti in `resources/` e ricompilare.
- Mancano alcuni controlli del proprietario nell'admin (SEC-1..SEC-4: creazione piatti, modifica/eliminazione di ristoranti e piatti, statistiche) e la creazione di un ristorante dall'admin dà errore 500 perché non imposta lo slug (BUG-1). Sono fissati dai test come comportamento attuale e vanno corretti in commit separati: dettagli in `dev/PIANO_MIGRAZIONE_L13.md`.
