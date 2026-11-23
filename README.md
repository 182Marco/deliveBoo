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
- **Lingue**: inglese (default), italiano, tedesco, francese, spagnolo. Tutte le stringhe statiche di sito, area partner, email, API e validazione stanno in un unico oggetto, `resources/js/utils/strings.json`: Vue lo legge con `t()` (`resources/js/utils/i18n.js`, solo il ramo `site` finisce nel bundle), Laravel con `__()` tramite `app/I18n/StringsLoader.php`. Il menu lingua dell'header (bandiera SVG + sigla) salva la scelta nel cookie `foodsy_locale`, letto dal middleware `SetLocale`: pagine, risposte API e mail seguono la stessa lingua.

## Funzionalità

**Cliente**: sito in 5 lingue (EN, IT, DE, FR, ES) · scelta di uno o più tipi di cucina · ristoranti migliori (media voti) · ristoranti per tipo (paginati) · menu del ristorante · carrello (un solo ristorante per ordine) · pagamento · email di conferma.

**Account e ruoli**: una sola tabella `users` con ruolo `customer` (default), `restaurateur` o `admin`; un solo login (si arriva su sito, `/admin` o `/platform` secondo il ruolo); registrazione a schede «Ordino cibo» / «Ho un ristorante» (le CTA partner aprono `/register?role=restaurateur`); l'admin non si registra mai da un form pubblico. Ogni area è protetta lato server (middleware `role:` e `active`, Policy); un account bannato perde la sessione alla richiesta successiva. Piano completo (login clienti, Google, backoffice `/platform`): `dev/PIANO_AUTH_CLIENTI.md`.

**Account demo** (progetto portfolio, box nella pagina di login, `config/demo.php`): admin `admin@foodsy.test` / `foodsy-admin`, ristoratore Federico Romiti `federico.romiti@foodsy.test` / `12345678`, cliente Mario Rossi `mario.rossi@foodsy.test` / `foodsy-demo`. Sono valori di sviluppo (`DEMO_*` nel `.env`); finché la demo è attiva questi account non si possono modificare, bannare né reimpostare.

**Ristoratore**: area partner nelle stesse 5 lingue · registrazione/login · avatar illustrato nel menu account, personalizzabile con un editor · CRUD ristoranti (con immagine, tipi, descrizione e fascia di prezzo) · CRUD piatti con foto caricata **oppure link https a un'immagine ospitata altrove** (il sito la mostra da lì, senza copiarla), ingredienti strutturati, disponibilità (disponibile / esaurito / non in menu), stagionalità, porzioni, calorie e informazioni alimentari **facoltative** (diete e allergeni, con "Not declared" che non scrive nulla) · lista e dettaglio ordini (quantità × prezzo pagato) · statistiche con grafici (spesa per ordine, cliente top, incassi mensili). Ognuno vede solo i propri dati.

## API

| Metodo | URL | Cosa fa |
|---|---|---|
| GET | `/api/types` | Tutti i tipi di cucina |
| GET | `/api/restaurantsTopRated` | I 12 ristoranti con media voti più alta |
| GET | `/api/restaurantsSearch?q=` | Ricerca per nome (lente nell'header): fino a 8 ristoranti, prima quelli il cui nome inizia con il testo; meno di 2 caratteri = lista vuota |
| GET | `/api/restaurants/{ids}` | Ristoranti per tipi (`ids` = `1,3,5`), 4 per pagina |
| GET | `/api/restaurantsMenu/{slug}` | Dettagli, tipi, media voti e menu paginato per portata (ogni piatto ha anche `orderable`, `availability`, `course`, `ingredient_list`) |
| GET | `/api/payments/config` | Driver di pagamento, `testMode` e client token Braintree per il checkout |
| POST | `/api/orders` | Crea un ordine (una riga per piatto con quantità e prezzo pagato; rifiuta i piatti esauriti o fuori stagione) e invia l'email |

## Database

`users` 1─N `restaurants` · `restaurants` 1─N `plates` / `rates` / `orders` / `opening_hours` · `restaurants` N─N `types` (pivot `restaurant_type`) · `orders` N─N `plates` (pivot `order_plate` con `quantity` e `unit_price`) · `plates` N─N `ingredients` (pivot `plate_ingredient`) · `ingredients` N─N `allergens` (pivot `allergen_ingredient`, dato di riferimento) · `plates` N─N `allergens` (pivot `allergen_plate`: **dichiarazione del ristorante**, `contains` / `may_contain`) · `plates` N─N `dietary_tags` (pivot `dietary_tag_plate`, `status` yes / no / unknown e `source`). Schema: `db structure.webp` (storico); versione aggiornata e interattiva nella tab “Relazioni tabelle” di `dev/mappa.html`.

**Informazioni alimentari (pensate per un futuro assistente che interroga il DB)**:

- nessuna riga = **non dichiarato**; `unknown` = dichiarato come non verificato; `yes` / `no` = dichiarato. Un'informazione mancante non diventa mai un "no" né un "sì";
- gli allergeni **derivati** dagli ingredienti (registro) e quelli **dichiarati** dal ristorante restano separati. `Plate::freeFrom([...])` restituisce un piatto solo se il ristorante ha dichiarato gli allergeni come `complete`, non dichiara né `contains` né `may_contain` (cross-contact), gli ingredienti non li contengono e sono tutti `verified`;
- `nickel_level` degli ingredienti e il tag `low_nickel` sono **indicativi** (dati del dataset), mai una garanzia medica o una certificazione;
- altri scope: `Plate::orderable($mese)`, `withDiet([...])`, `withIngredients([...])`, `withoutIngredients([...])`, `$plate->derivedAllergens()`, `Restaurant::withRating()`.

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
php artisan db:seed --class=MenusSeeder              # un solo seeder
SEED_PROFILE=test php artisan db:seed                # catalogo ridotto (quello dei test)
```

Il dataset ha tre livelli, in quest'ordine (`DatabaseSeeder`):

1. **dati di riferimento**: `AllergensSeeder` (i 14 allergeni del Reg. UE 1169/2011), `DietaryTagsSeeder`, `IngredientsSeeder` (registro di circa 700 ingredienti con origine, lattosio, nichel indicativo e allergeni);
2. **dati storici scritti a mano**: Users, Types, Restaurants, `DemoAccountsSeeder` (admin e cliente demo di `config/demo.php`; il ristoratore demo è Federico Romiti di `UsersTableSeeder`), `LegacyRestaurantDetailsSeeder` (segmento, descrizione, posizione e orari dei ristoranti storici: riempie solo i campi vuoti), Plates, `LegacyPlateDetailsSeeder` (ingredienti strutturati dei piatti storici), Orders, Rates;
3. **catalogo generato**: `OwnersSeeder`, `CatalogRestaurantsSeeder` (con orari), `MenusSeeder`, `OrderHistorySeeder`, `RatingsSeeder`.

Il catalogo generato nasce da file di dati scritti a mano in `database/seeders/data/`: per ogni cucina concept e ricette con varianti per fascia (`base` / `mid` / `high`), bevande per livello e contesto, città, nomi, foto. La generazione è **deterministica** (`database/seeders/Support/`: stesso input, stesso catalogo, senza dipendere dagli ID) e dimensionata da `config/seeding.php`:

- dimensione dei menu per segmento: pochi piatti per street food e locali economici, 30-75 per trattorie e ristoranti tradizionali, 50-110 per i premium; **stellati e fine dining 5-10 piatti** (menu degustazione e poca carta) più una carta dei vini di 25-45 voci; **sushi 80-150 voci** (nigiri e sashimi per pesce, gunkan, maki, temaki, uramaki, fritti, antipasti, box);
- `SEED_PROFILE=full` (default): 48 nuovi ristoratori (password `foodsy-demo`, email `@example.*`), circa 245 ristoranti (30 per l'account demo Romiti), circa 6.000 piatti (il numero esatto varia con le regole dei menu), circa 11.000 ordini 2025-2026, circa 19.000 voti. Su MySQL `migrate:fresh --seed` impiega circa 20 secondi;
- `SEED_PROFILE=test` (forzato da `phpunit.xml`): 2 ristoratori, menu di 6-12 piatti, stessa logica.

Un seeder lanciato da solo richiede che i precedenti siano già stati eseguiti; se manca un riferimento si ferma con un errore chiaro e non scrive nulla.

**Rilanciare il seed è sicuro**: i seeder sono idempotenti. Trovano i record tramite chiavi naturali (email dell'utente, nome di tipo, ristorante e ingrediente, ristorante + nome del piatto, ristorante + email + data dell'ordine) e creano solo quello che manca; i seeder generati saltano i ristoranti che hanno già piatti, ordini o voti. Non sovrascrivono i record esistenti e non cancellano nulla. `php artisan migrate:fresh --seed` invece **cancella tutte le tabelle** e ricrea il DB da zero: usalo solo se vuoi perdere i dati.

Foto: solo URL di `images.unsplash.com` (Unsplash License), ognuna verificata e assegnata a una categoria; fonte, licenza e limiti in testa a `database/seeders/data/images.php`.

`php artisan test` gira su SQLite in memoria (vedi `phpunit.xml`) e non tocca il DB di sviluppo:

- il DB in memoria viene seminato **una volta per processo** (`$seed = true` in `tests/TestCase.php`) e ogni test gira in una transazione;
- `DatabaseSeederTest`: due seed consecutivi danno gli stessi conteggi, dati coerenti (FK, righe degli ordini e totali, slug, ogni piatto con ingredienti, ogni proprietario con ristoranti), dichiarazioni mai più ottimiste dei dati;
- `DietaryQueriesTest`: le ricerche alimentari e commerciali su fixture fatte a mano (un'informazione mancante non è mai compatibile);
- `CatalogDataTest`: file di dati, piano del catalogo e coerenza dei menu con il posizionamento;
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

**Avatar**: chi non entra con Google ha un avatar cartoon a mezzo busto generato dal pacchetto locale `packages/svg-avatar` (SVG originali, nessuna dipendenza). Il seed (`users.avatar`, oppure `user-{id}`) dà sempre lo stesso avatar; «Personalizza avatar» nel menu account partner apre un editor (viso, pelle, capelli, occhi, sopracciglia, naso, bocca, vestito, accessorio, sfondo, suggerimenti per look, «Sorprendimi») che salva `{ seed, overrides }`, validati lato server sui dati del pacchetto. Il pacchetto è indipendente dall'app (non conosce Laravel né utenti), si importa solo come `svg-avatar`, `svg-avatar/vue` o `svg-avatar/editor` (alias in `vite.config.js`) e ha README e test propri. Stato e decisioni: `AVATAR_IMPLEMENTATION_PLAN.md`.

## Deploy

`Dockerfile` multi-stage (stage `node:22` con `npm ci && npm run build`, che copia anche `packages/`, poi Apache + PHP 8.4 con DocumentRoot `public/` e `composer install --no-dev`) usato da `render.yaml` (servizio *deliveboo-laravel*, SQLite). `build.sh` è uno script equivalente per build manuali. Variabili da impostare su Render: `APP_KEY`, le `MAIL_*` per le email e, per pagamenti Braintree veri, `PAYMENT_DRIVER=braintree` con le `BRAINTREE_*`.

## Struttura cartelle

```
app/            modelli, controller (Admin, Api, Auth), Dietary (regole delle diete derivate), Mail, Payments (gateway di pagamento), AppServiceProvider
bootstrap/      app.php: rotte, middleware, eccezioni (al posto di Kernel e provider)
config/         impostazioni Laravel (solo quelle con valori del progetto)
database/       migrations, seeders (data/ = dati del catalogo, Support/ = generatore), factories
public/         file serviti al browser (build/ di Vite, logo)
resources/js    sito Vue (pages/ e components/ a cartelle: .vue + .logic.js + .css, api/, utils/, store, routes) + jsBack (script admin)
resources/css   sistema grafico: tokens, font, base, primitive ui-*, entry site.css e admin.css
resources/views pagine Blade (admin, auth, guest, layouts, mails)
routes/         web.php, api.php, console.php
packages/       pacchetti locali indipendenti dall'app (svg-avatar: avatar SVG deterministici, componente Vue, editor JS)
tests/          Feature (PHPUnit) e js (Vitest)
vite.config.js  entry point della build e configurazione di Vitest
```

## Problemi noti

- `Admin/HomeController` non è collegato a nessuna rotta; `StatsController@index` è vuoto.
- `public/build` è generata da Vite: modificare i sorgenti in `resources/` e ricompilare.
- I ristoranti storici del seed hanno ancora nomi e piatti di marchi reali (scelta voluta); tutto il catalogo generato è sintetico.
- I controlli del proprietario nell'area partner (SEC-1..SEC-4: creazione piatti, modifica/eliminazione di ristoranti e piatti, statistiche) sono corretti con le Policy `RestaurantPolicy` e `PlatePolicy`; BUG-1 (slug alla creazione di un ristorante) è corretto. Dettagli in `dev/PIANO_MIGRAZIONE_L13.md`.
