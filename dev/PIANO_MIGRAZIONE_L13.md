# Piano: migrazione DeliveBoo a Laravel 13 (+ Vite / Vue 3)

## 📍 Stato di avanzamento (aggiornato a ogni passo)
Branch: `upgrade/laravel-13`. Regola: a ogni passo completato si spunta qui e si annota cosa resta in "Prossimo passo".

- [x] Fase −1 — verifica compatibilità dipendenze (why-not, dry-run, audit) — 2026-11-22: dry-run `-W` risolto senza conflitti (framework v13.43.0, ui v4.6.3, tinker v3.2.0, collision v8.9.6, phpunit 12.5.45, mockery 1.6.15, guzzle 7.16.5, faker 1.24.1, symfony/console 8.1); nessun pacchetto abandoned; `composer audit` sul lock L13: 0 advisory (lock attuale L7: 58 advisory su 14 pacchetti). Bloccanti da why-not: solo i pacchetti già previsti in rimozione/aggiornamento (ignition, proxy, cors, tinker 2, ui 2)
- [x] Fase 0.a — `chore(deps)`: vue / vue-template-compiler 2.7.16 — commit 6977d61; `npm run dev` ok, smoke HTTP ok (home, /js/vue.js, /api/types, /api/restaurantsTopRated). Rendering nel browser da verificare a mano (vedi checklist finale)
- [x] Fase 0.b — test PHP di caratterizzazione (PublicApi, Ownership + SEC-1/2, Crud, Auth, Routes) + fix nome vista mail — commit 663cc37; suite: 51 test, 629 asserzioni, 5 skip (SEC-1..4, BUG-1)
- [x] Fase 0.c — Vitest: store.spec.js + payment.spec.js (Vue 2) — commit b3c1f23; 16 test verdi (`npm run test:js`)
- [x] Fase 1 — Laravel 13, struttura slim, rotte a tupla, seeder namespaced — commit 54ed13d; Laravel 13.43.0 su PHP 8.4: 51 test, 629 asserzioni (come la baseline), 5 skip; `composer audit` pulito; smoke su SQLite ok (SPA, API, login reale con CSRF/sessione, paginazione Bootstrap, ordine + mail nel log)
- [x] Fase 2 — sintassi moderna (API, Admin, modelli, mailable, test 404) — commit d37c3ee (API), d502aae (Admin + NotFoundTest), commit modelli/mail; 54 test, 647 asserzioni, 5 skip. Oggetto mail verificato: "Ordered Message" come prima
- [x] Fase 3 — Vite + Vue 3 (+ port degli spec) — commit a25e129; vite 8.3, Vue 3.5, router 4.6, vuex 4.1; Vitest 25 test (store 8, payment 8 con asserzioni invariate, nuovo app.smoke 9); PHP 54 test verdi anche senza build; smoke HTTP: asset Vite 200 su SPA, admin e stats
- [x] Fase 4 — Docker, deploy, documentazione, mappa — commit c68aa49 (Docker + migration prezzi), 1916286 (docs + mappa, solo blocco DATI, JS valido e tutti i `go`/`rel` risolti), braintree-web 3.148 (audit)
- [ ] Verifica finale (checklist in fondo) — **automatica fatta**, restano le parti manuali con l'utente:
  - [x] `php artisan test`: 54 test, 647 asserzioni, 5 skip (SEC-1..4, BUG-1) · `npm run test:js`: 25 test · `php artisan route:list` ok · `npm run build` ok
  - [x] `composer audit`: 0 advisory · `npm audit --omit=dev`: 0 (2 moderate solo in dev: `@vue/test-utils` → `js-beautify`, fix solo con `--force`)
  - [x] `migrate:fresh --seed` su MySQL 8 (DB temporaneo) e SQLite · immagine di produzione avviata e provata (SPA, API, asset, `.env` non esposto)
  - [ ] **Manuale** — smoke nel browser (punto 5 della checklist): clienti + admin, incluso dropdown logout, conferma eliminazione, grafico stats
  - [x] Pagamento end-to-end (non ha mai funzionato: la chiave sandbox hardcoded nel front risponde 403). Ora addebito lato server dietro `App\Payments\PaymentGateway`, driver `fake` (default, offline) e `braintree` (`braintree/braintree_php`); importo ricalcolato dal db, `orders.status` pending/paid/failed + `transaction_id`, admin/stats solo `paid`. Provato via HTTP sul container: ordine pagato e rifiutato.
  - [ ] **Manuale** — pagamento nel browser con driver `fake` (4111… ok, 4000 0000 0000 0002 rifiutata) · facoltativo: account sandbox Braintree, `PAYMENT_DRIVER=braintree`, stessa prova
  - [ ] **Manuale** — DB di sviluppo `deliveboo`: `docker compose build && docker compose up -d && docker compose exec app php artisan migrate` (migration dei prezzi e delle colonne di pagamento: già applicate il 2026-11-22; nessun reseed) — comando ora anche in `dev/mappa.html` (Link di avvio → Codespace, Note Docker, Comandi Laravel → Migrations)
  - [ ] **Manuale** — prima del merge: variabili su Render (`APP_KEY`; facoltative `MAIL_*`, `PAYMENT_DRIVER`/`BRAINTREE_*`), poi deploy
  - [ ] **Da decidere** — correzioni separate `fix(security)` SEC-1..SEC-4 e `fix` BUG-1

**Prossimo passo:** verifiche manuali con l'utente (browser, Braintree sandbox, migrate del DB di sviluppo), poi decidere sulle correzioni SEC-1..4 / BUG-1 e sul merge di `upgrade/laravel-13` in `master`.

**Note di esecuzione:** (decisioni prese durante il lavoro, problemi incontrati)
- Laravel 7 **non gira su PHP 8.4** (deprecazioni di Symfony 5 trasformate in eccezioni). Fino alla Fase 1 i test PHP si lanciano nel container PHP 8.0:
  `docker run --rm -v "$PWD":/app -w /app deliveboo-php80 php artisan test` (baseline: 6 test verdi). Dalla Fase 1 in poi: `php artisan test` sull'host (PHP 8.4).
- Verifiche Composer eseguite su una copia in scratchpad, lock del progetto non toccato.
- Comando compatto per un solo file: `docker run --rm -v "$PWD":/app -w /app deliveboo-php80 vendor/bin/phpunit --colors=never tests/Feature/AuthTest.php`.
- **Problemi emersi in Fase 0.b** (tutti fissati come `_CurrentBehaviour` + test corretto in skip; correzione in commit separati, **non** durante la migrazione):
  - **SEC-1** `PlateController@store`: piatto creabile in un ristorante altrui (`restaurant_id` dal form).
  - **SEC-2** `StatsController@show`: ordini e dati cliente di qualsiasi ristorante leggibili.
  - 🆕 **SEC-3** `RestaurantController@update/@destroy`: nessun controllo proprietario (un utente può modificare/cancellare ristoranti altrui).
  - 🆕 **SEC-4** `PlateController@update/@destroy`: idem per i piatti.
  - ✅ **BUG-1** `RestaurantController@store`: non impostava `slug` (NOT NULL) → 500. Corretto con `Restaurant::uniqueSlug` (lavoro dataset, `dev/PIANO_DATASET.md`).
  - Minore, solo annotato: `plates/create/{slug}` di un ristorante altrui mostra il form (lista solo i ristoranti dell'utente, nessun dato esposto).
  - Fase 0.c: installare vite 7 ha portato `sass` da 1.37 a 1.108 (peer opzionale di vite `^1.70`, non riportabile indietro senza `--legacy-peer-deps`). Il CSS compilato da Mix è equivalente (formato colori e ordine delle regole): i file in `public/` **non** sono stati ricommitati; si rigenerano comunque in Fase 3 con Vite.
  - Fase 1: il PHP dell'host **non ha GD** → nei test `UploadedFile::fake()->image()` sostituito da un PNG 1x1 reale (`CrudTest::png()`).
  - Fase 1: i `docker run` come root lasciano file di root in `storage/` → `Permission denied` sull'host. Rimedio: `docker run --rm -v "$PWD":/app -w /app php:8.0-cli chown -R $(id -u):$(id -g) storage bootstrap/cache`.
  - Fase 1: `MAIL_FROM_ADDRESS=null` non è più accettato da Symfony Mailer → `.env.example` ora ha `noreply@deliveboo.test`. **Da verificare** il `.env` locale e le variabili su Render (Fase 4).
  - Tra Fase 1 e Fase 4 `docker/dev/Dockerfile` (PHP 8.0) e il `Dockerfile` di produzione (PHP 8.2) **non possono eseguire Laravel 13**: il branch non va deployato prima della Fase 4.
  - Smoke manuale veloce senza MySQL: `DB_CONNECTION=sqlite DB_DATABASE=<file in scratchpad> php artisan migrate --seed --force && php artisan serve --port=8013`.
  - Fase 3, decisione: **tenuto `vuex-persistedstate`** (v4.0.0, funziona con Vuex 4, stessa chiave `vuex`); l'alternativa del plugin locale resta documentata.
  - Fase 3, regressioni Vue 3 trovate da `app.smoke.spec.js` e corrette: `<router-view>` con un commento dentro (in Vue Router 4 diventa slot → pagina vuota in dev); primo render di `RestMenu`/`singleTypeRest` senza dati (Vue 2 lo saltava, Vue 3 lancia) → `v-if` sulla radice; istanza Braintree con `markRaw`.
  - Fase 3: `package.json` ha `"type": "module"`; `vitest.config.mjs` assorbito in `vite.config.js` (chiave `test`).
  - Fase 3: le viste con `@include('layouts/deleteConfirm')` **dopo** `@endsection` stampano lo script prima del layout: con i moduli Vite (differiti) la conferma di eliminazione aggancia comunque i form. Da verificare a mano che funzioni (prima poteva non agganciarli).
  - Nessun browser headless nella Codespace: il rendering visivo resta nella checklist manuale.
  - Fase 4 (Docker, commit c68aa49): immagine dev `deliveboo-php84`; Dockerfile prod multi-stage (node:22 → php:8.4-apache). Corretti nel rifacimento: **DocumentRoot era la radice del progetto** (ora `public/`) e `.dockerignore` non escludeva `.env`/`vendor`/`node_modules`.
  - Fase 4: su MySQL `float('price', 5, 2)` con L11+ crea `FLOAT` singola precisione (L7: `DOUBLE(5,2)`), `float('price')` crea `DOUBLE` senza scala (L7: `DOUBLE(8,2)`) → nuova migration `2026_11_22_000000_restore_price_column_types` (solo MySQL/MariaDB, no-op su DB esistenti). Verificato su DB MySQL temporaneo `deliveboo_l13test` (poi eliminato): il DB di sviluppo `deliveboo` **non è stato toccato** (va migrato con `php artisan migrate`, che aggiunge solo questa migration).
  - Test PHP nel container con GD: `docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app deliveboo-php84 php vendor/bin/phpunit`.
  - Dopo la Fase 4: `dev/mappa.html` ha una 5ª tab “Relazioni tabelle” (schema 1─N / N─N con pivot, poi ogni relazione con le colonne chiave). README corretto: `users` 1─N `restaurants` (non 1─1).
  - Messaggio `max` dei piatti con doppio spazio ("the  50 characters") per via della regola `'max: 50'`: in Fase 2, con le regole ad array, diventa a spazio singolo → aggiornare l'asserzione in `CrudTest::testStorePlateValidationMessages` (cambio voluto, unico).

> **Legenda revisione 2:** 🆕 = parte aggiunta · ✏️ = parte modificata. Tutto il resto è invariato rispetto alla versione 1.
> Le modifiche riguardano solo tre punti: (1) compatibilità delle dipendenze, (2) test di autorizzazione su `PlateController@store` e `StatsController@show`, (3) verifica Braintree separata.

## Context
Oggi DeliveBoo gira su Laravel 7.29 (fuori supporto dal 2021), PHP ≥7.2, Laravel Mix 5/webpack 4 (serve `--openssl-legacy-provider`) e Vue 2 (EOL). Laravel 13 richiede **PHP ^8.3**: la Codespace ha già PHP 8.4 e Node 24, mentre le immagini Docker sono ancora 8.0 (dev) e 8.2 (prod).
L'obiettivo è arrivare a Laravel 13 e Vue 3 **senza regressioni visibili** e cogliere l'aggiornamento per togliere il codice superfluo: struttura slim di Laravel 11+, query Eloquent al posto dei cicli manuali, `firstOrFail` al posto di `$x[0]`.
Decisioni prese con te: frontend **Vite + Vue 3**; i record inesistenti nell'admin rispondono **404** (oggi danno 500).

## Strategia
- Branch `upgrade/laravel-13`, un commit per ogni passo e test verdi a ogni commit.
- **Prima una rete di sicurezza sul codice attuale (Fase 0)**, poi si migra. I test di caratterizzazione descrivono il comportamento attuale e sono la definizione di "nessuna regressione".
- Salto diretto 7 → 13, senza passare da 8, 9, 10, 11 e 12: l'app è piccola (~10 controller) e i test coprono il salto. La struttura slim si applica sul posto, non in un progetto nuovo, così `git log` conserva la storia dei file.
- Il backend va prima del frontend: la Fase 3 parte solo con la suite PHP verde.
- 🆕 Le correzioni di sicurezza emerse dai test (vedi Fase 0, punto 2) vanno su **commit separati con prefisso `fix(security):`**. Non entrano nei commit di migrazione e non ne bloccano la sequenza.

---

## 🆕 Fase −1 — Verifica di compatibilità delle dipendenze (prima di scrivere `composer.json` e `package.json`)
**Fonti consultate il 2026-11-22**: `composer show -a <pacchetto> <versione>` (metadati di Packagist, campi `requires` e `abandoned`), i requisiti di `laravel/framework v13.43.0` e dello skeleton `laravel/laravel v13.11.0`, `npm view <pacchetto> version peerDependencies engines deprecated`.

**PHP / Composer**: risultati verificati.

| Pacchetto | Oggi | Target | Evidenza |
|---|---|---|---|
| `laravel/framework` | ^7.29 | `^13.0` | v13.43.0: `php ^8.3`, `symfony/* ^7.4 \|\| ^8.0`, `guzzlehttp/guzzle ^7.8.2 \|\| ^8.0` |
| `laravel/ui` | ^2.4 | `^4.6` | v4.6.3: `illuminate/* …\|^13.0`, `php ^8.0`, `symfony/console ^6\|^7\|^8`, non segnato abandoned |
| `laravel/tinker` | ^2.5 | ✏️ **`^3.0`** (non 2.x) | lo skeleton 13 richiede `^3.0`; v3.1.0 dichiara `illuminate/* …\|^13.0` |
| `nunomaduro/collision` | ^4.3 | `^8.6` | skeleton 13: `^8.6`; v8.9.5: `php ^8.2`, `symfony/console ^7.4.14 \|\| ^8.1.1` |
| `phpunit/phpunit` | ^8.5\|^9.3 | ✏️ `^12.5.12` | skeleton 13: `^12.5.12`; il framework accetta `^11.5.50 \|\| ^12.5.8 \|\| ^13.0.3`; PHPUnit 12 richiede `php >=8.3` |
| `mockery/mockery` | ^1.3.1 | `^1.6` | il framework suggerisce `^1.6.10`; 1.6.14: `php >=7.3` |
| `fakerphp/faker` | ^1.15 | `^1.23` | skeleton 13: `^1.23`; v1.24.0. Resta in `require` perché il seeding in produzione usa `--no-dev`; oggi lo usa solo `UserFactory` |
| `guzzlehttp/guzzle` | ^6.3\|^7.0 | `^7.8.2` | è il minimo richiesto dal framework. La 8.x è ammessa ma non porta benefici e nell'app non c'è uso diretto |
| `fideloper/proxy`, `fruitcake/laravel-cors` (+ `asm89/stack-cors` indiretto), `facade/ignition` | presenti | **rimossi** | TrustProxies e HandleCors sono nel core da L9; ignition 2.x supporta solo L7/L8 e lo skeleton 13 non include una pagina errori esterna |

**Passi da eseguire prima della Fase 1**, in sola lettura, senza modificare il lock:
1. `composer why-not laravel/framework ^13.0` sul branch, per elencare cosa blocca il salto.
2. `composer update --dry-run -W` con il `composer.json` candidato, in una copia nella scratchpad: conflitti indiretti, pacchetti segnalati come *abandoned* nell'output, versioni risolte.
3. `composer audit` sul lock risultante.
4. Se un pacchetto non risolve, si documenta l'alternativa **prima** di procedere. Alternative già individuate:
   - se `laravel/ui` non risolvesse: Laravel Fortify con le stesse viste Blade (fallback, più lavoro)
   - se `collision` 8 andasse in conflitto con `symfony/console` 8: `^8.9`, o la versione indicata da `why-not`

**npm**: risultati verificati.

| Pacchetto | Target | Evidenza / nota |
|---|---|---|
| `vite` | `^8` | 8.3.8; `engines.node ^20.19 \|\| >=22.12` (Codespace: Node 24 ✓; serve lo stage `node:22` nel Dockerfile) |
| `laravel-vite-plugin` | `^3` | 3.2.1, peer `vite ^8` |
| `@vitejs/plugin-vue` | `^6` | 6.0.11, peer `vue ^3.2.25`, `vite ^5…^8` |
| `vue-router` | ✏️ **`^4.6`** | esiste la 5.x, ma si resta su 4 (4.6.5) per avere la stessa API prevista nel piano |
| `vuex` | `^4.1` | 4.1.0, peer `vue ^3.2`; in manutenzione, non deprecato |
| `vuex-persistedstate` | `4.1.0` | ⚠️ **deprecato su npm** ("Package no longer supported"); peer `vuex ^3 \|\| ^4` quindi funziona. **Alternativa documentata**: sostituirlo con un plugin locale di ~10 righe in `store.js` (`store.subscribe` + `localStorage` con la stessa chiave `vuex` e lo stesso formato JSON, così i carrelli salvati restano validi). Decisione da prendere all'inizio della Fase 3; lo spec dello store la copre in entrambi i casi |
| `braintree-web` | `^3.148` | non deprecato, senza peer; resta |
| `vitest` | Fase 0: `^3.2` · Fase 3: `^5` | vitest 5 ha peer `vite ^6…^8`. In Fase 0 serve vite ≤7 per il plugin Vue 2 (riga sotto) |
| `@vitejs/plugin-vue2` + `@vue/test-utils@1` | solo Fase 0, dev | plugin-vue2 2.3.4 ha peer `vue ^2.7`, `vite ≤7`; test-utils 1.3.6 ha peer `vue 2.x`. ⚠️ Il lock ha `vue 2.6.14`: va allineato a **2.7.16** insieme a `vue-template-compiler`. È nel range `^2.6.14` già dichiarato e va in un commit dedicato con smoke test |
| `@vue/test-utils` | Fase 3: `^2.6` | peer `vue 3.x` |
| `bootstrap-vue`, `jw-vue-pagination` | rimossi | entrambi registrati ma inutilizzati e solo Vue 2 |

Commit: nessuno, è una verifica. L'esito (tabelle più l'output di `why-not`/`dry-run`) si annota nella descrizione della PR.

---

## Fase 0 — Rete di sicurezza su Laravel 7 (nessun cambio di comportamento)
I test si scrivono compatibili con PHPUnit 9 **e** 12: metodi `testXxx`, niente annotazioni `@test` o `@dataProvider`, SQLite in memoria come già fa `phpunit.xml`, dati da `$this->seed()` (i seeder sono già idempotenti).

1. `tests/Feature/Api/PublicApiTest.php`
   - `GET /api/types` → `{types:[{id,name}]}`
   - `GET /api/restaurantsTopRated` → al massimo 12 elementi, ordinati per `average` decrescente, `average` numerico e uguale alla media calcolata in SQL
   - `GET /api/restaurants/1,2?page=1` → `data`, `current_page`, `last_page`, `per_page=4`. Usare `assertJsonStructure`, non il confronto esatto: da L8 il paginator aggiunge `links`.
   - `GET /api/restaurantsMenu/{slug}` → `averageRate`, `location`, `types` (array di nomi), `menu` paginato a 4
   - `POST /api/orders` valido → 200 che restituisce il payload, ordine e pivot salvati, `Mail::fake()->assertSent(OrderedMessage)`
   - `POST /api/orders` non valido → **200** con `{errors:{customer_name:[…]}}`: è il formato che `Payment.vue` legge, va preservato.
2. ✏️ `tests/Feature/Admin/OwnershipTest.php`: per ogni pagina admin (restaurants show/edit, plates index/show/edit/create, orders index/show, stats show)
   - il proprietario riceve 200 e la vista giusta
   - un altro utente riceve `assertViewIs('admin.notYours.*')`
   - un ospite viene rediretto a `/login`

   🆕 **Copertura esplicita di `PlateController@store` e `StatsController@show`**, con tre attori per ciascuno:

   | Azione | Proprietario | Altro utente | Guest |
   |---|---|---|---|
   | `POST /admin/plate/store` con `restaurant_id` di un proprio ristorante | piatto creato, redirect a `admin.plates.show` | `restaurant_id` di un ristorante **altrui** → oggi il piatto viene creato nel ristorante altrui | redirect a `/login`, nessun piatto creato |
   | `GET /admin/stats/{restaurant_id}` | 200, `admin.stats.show` con i suoi ordini | oggi 200 con gli **ordini e i dati cliente altrui** | redirect a `/login` |

   - Il test di caratterizzazione (`testOtherUserCan…_CurrentBehaviour`) fissa il comportamento attuale, perché la baseline deve restare verde e la migrazione non deve cambiarlo per sbaglio.
   - Il test del comportamento corretto (`testOtherUserCannot…`) si scrive subito ma con `markTestSkipped('SEC-1: …')` / `('SEC-2: …')`.
   - Vulnerabilità già presenti, documentate qui e nella PR come **SEC-1** (store piatto in un ristorante altrui, IDOR sul `restaurant_id` del form) e **SEC-2** (stats: lettura di ordini e dati personali dei clienti di altri ristoranti).
   - Si correggono in commit `fix(security):` separati: controllo del proprietario e vista `admin.notYours.*` coerente con gli altri metodi. Nello stesso commit il test di caratterizzazione si rimuove e lo skip si toglie.
   - La correzione **non** fa parte delle Fasi 1–2 e si può applicare prima o dopo la migrazione.
3. `tests/Feature/Admin/CrudTest.php`: store, update e destroy di ristoranti e piatti con `Storage::fake()` (immagine e sync dei tipi), redirect verso le rotte nominate, messaggi di validazione personalizzati.
4. `tests/Feature/AuthTest.php`: login → `/admin`, register con `vat_number` di 11 cifre, utente loggato su `/login` → `/admin`, logout.
5. `tests/Feature/RoutesTest.php`: per ogni rotta nominata (`admin.plates.index`, `admin.plate.delete`, `admin.stats.show`…) controlla `route(name, param) === url(uri)` e il metodo HTTP. La catch-all `/qualsiasi/percorso` deve rendere `guest.home`. Sostituisce il confronto di `route:list`, il cui formato cambia tra le versioni.
6. ✏️ Frontend: Vitest in modalità `jsdom` (✏️ versioni della Fase −1: `vitest ^3.2`, `@vitejs/plugin-vue2`, `@vue/test-utils@1`) con `tests/js/store.spec.js` su `resources/js/store.js`.
   - mutations: `addType`/`pullType`, `addPlate`/`removePlate`, `changeTotal`, `emptyCart`
   - actions con axios mockato: l'URL `/api/restaurants/1,2?page=2` e i commit di paginazione
   - Vitest gira accanto a Mix: dev-dependency in più, nessun cambio alla build.
   - 🆕 Prima va un commit dedicato `chore(deps): allinea vue e vue-template-compiler a 2.7.16` (nel range già dichiarato), verificato con `npm run dev` e smoke test, perché il plugin Vue 2 di Vite lo richiede.
7. 🆕 `tests/js/payment.spec.js`: test del flusso Braintree **senza rete**. È la parte automatica della verifica Braintree separata (vedi la sezione dedicata in fondo).
   - `vi.mock('braintree-web')`: `client.create` e `hostedFields.create` restituiscono oggetti finti; `tokenize()` è controllato dal test.
   - `vi.mock('axios')`; store Vuex e router reali o stub minimi.
   - Nessuna chiamata a Braintree o ad altri servizi esterni: le hosted fields sono iframe remoti e non si caricano nei test.
   - Casi:
     - **Inizializzazione**: al `mounted` viene chiamato `client.create({authorization})`, poi `hostedFields.create` con i selettori `#creditCardNumber`, `#cvv` e `#expireDate`; l'istanza viene salvata in `hostedFieldInstance`.
     - **Init fallita**: `client.create` rifiuta → `console.error`; `payWithCreditCard()` non tokenizza e non fa POST.
     - **Campi cliente mancanti**: niente `tokenize` e niente POST.
     - **Tokenizzazione ok**: `tokenize` restituisce `{nonce}` → `this.nonce` valorizzato e POST `/api/orders` con payload esatto (`restaurant_id` dal primo elemento del carrello, `price = total`, i 5 campi cliente, `plates` = id del carrello).
     - **Tokenizzazione fallita**: `tokenize` rifiuta con `{message}` → `this.error === message`, nessun POST, carrello intatto.
     - **Risposta con `errors`**: `this.errors` popolato, `sending` torna `false`, carrello **non** svuotato, nessun redirect.
     - **Completamento ordine**: risposta senza `errors` → campi azzerati, commit `emptyCart`, `$router.push({name:'success'})`.
     - **Errore di rete del POST**: axios rifiuta → nessun redirect e carrello intatto (comportamento attuale, `sending` resta `true`). Solo caratterizzazione, nessuna correzione.
   - Lato server, il completamento dell'ordine è già coperto dal punto 1 (`POST /api/orders` valido e non valido, mail inviata).
8. Fix preliminare, l'unico della fase: `OrderedMessage` usa `markdown('mails.orderedmessage')`, ma il file è `orderedMessage.blade.php`. Su Linux/Docker la mail fallisce già oggi. Basta `mails.orderedMessage`, così la baseline parte verde.

✏️ Commit:
- `chore(deps): allinea vue 2.7.16` (🆕)
- `test: caratterizzazione API, admin, auth, rotte, store e pagamento prima della migrazione`
- 🆕 eventuali `fix(security): SEC-1 …` e `fix(security): SEC-2 …`, separati e in qualsiasi momento

---

## Fase 1 — Backend su Laravel 13 (stesso comportamento)
✏️ **composer.json** (versioni dalla tabella della Fase −1, da applicare solo dopo un `dry-run` senza conflitti)
- `php ^8.3`, `laravel/framework ^13.0`, `laravel/ui ^4.6`, ✏️ `laravel/tinker ^3.0`, ✏️ `guzzlehttp/guzzle ^7.8.2`, `fakerphp/faker ^1.23`.
- Rimuovere `fideloper/proxy`, `fruitcake/laravel-cors` (sono nel core), `facade/ignition`.
- Dev: `nunomaduro/collision ^8.6`, ✏️ `phpunit/phpunit ^12.5.12`, `mockery ^1.6`.
- Autoload PSR-4 `Database\Seeders\` e `Database\Factories\` al posto della classmap. Script presi dallo skeleton 13.x.
- 🆕 Dopo `composer update -W`: `composer audit` pulito e nessun avviso *abandoned* nell'output. Se ne compaiono, si annotano nella PR con l'alternativa.

**Struttura slim** (file presi dallo skeleton `laravel/laravel` 13.x)
- Nuovi o sostituiti: `bootstrap/app.php`, `bootstrap/providers.php`, `public/index.php`, `artisan`, `phpunit.xml` (`<source>` al posto di `<filter>`; restano gli env SQLite già presenti), `tests/TestCase.php` (senza `CreatesApplication`).
- Da eliminare: `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, tutti i middleware in `app/Http/Middleware/` e i provider tranne `AppServiceProvider`, più `server.php`. Prima di cancellare un middleware controllare che sia quello di default, cioè senza `$except` o `$proxies` personalizzati.
- `bootstrap/app.php`:
  ```php
  ->withRouting(web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php')
  ->withMiddleware(function (Middleware $m) {
      $m->redirectUsersTo('/admin');   // ex RedirectIfAuthenticated + RouteServiceProvider::HOME
      $m->trustProxies(at: '*');       // Render sta dietro proxy
  })
  ```
- `AppServiceProvider::boot()`: `Paginator::useBootstrapFour();`. **Critico**: da L8 il default è Tailwind e i `->links()` delle 3 viste admin perderebbero lo stile.

**Rotte**: senza namespace di default la sintassi a stringa non funziona più, quindi si passa alla forma a tupla. URI, nomi e metodi restano **identici**, compresi `plate.delete` e il gruppo `admin.`, con la catch-all sempre ultima.
```php
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [RestaurantController::class, 'index'])->name('home');
    Route::resource('restaurants', RestaurantController::class);
    Route::controller(PlateController::class)->group(function () {
        Route::get('plates/{slug}', 'index')->name('plates.index');
        // ... stesse righe di oggi, una per URI
    });
    Route::resource('stats', StatsController::class)->only(['index', 'show']);
});
```
- `Route::resource('stats')` oggi registra 7 rotte, ma solo `show` è usata e `index` è vuota. Con `->only(['index','show'])` restano le due che esistono come metodo. Il RoutesTest lo verifica.
- `routes/api.php`: `Route::controller(...)` oppure tuple, stessi path.

**Auth (laravel/ui)**
- `App\Http\Controllers\Controller` resta così com'è (estende `Illuminate\Routing\Controller`), perché i controller Auth chiamano ancora `$this->middleware('guest')`.
- `RouteServiceProvider::HOME` sparisce: nei 4 controller Auth `protected $redirectTo = '/admin';`.

**Config ed env** (sono le trappole principali del salto a L11+)
- Tenere solo i file di `config/` con personalizzazioni reali, come `cors.php` (`api/*`); per il resto valgono i default del framework e le variabili env.
- `.env.example` e `render.yaml`:
  - `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`. I default di L11+ sono `database` e senza le tabelle `sessions`/`cache` il login darebbe 500.
  - Rinomina `CACHE_DRIVER`→`CACHE_STORE` e `BROADCAST_DRIVER`→`BROADCAST_CONNECTION`.
- Password reset: `AUTH_PASSWORD_RESET_TOKEN_TABLE=password_resets`. La tabella esiste già e **non** si aggiunge una migration.
- Migration: **non si toccano** (regola di AGENTS.md).
  - `float('price', 5, 2)` su L11+ ignora i parametri in più e su un `migrate:fresh` MySQL crea un `double` senza scala.
  - Non si passa comunque a `decimal`: Eloquent restituirebbe stringhe e `Cart.vue`/`store.js` sommerebbero `"12.50"+"8.00"` concatenandole. I test sui prezzi con `assertEqualsWithDelta` restano a guardia.

**Seeder e factory**
- `database/seeds/` → `database/seeders/` con `namespace Database\Seeders;`.
- **Attenzione**: `throw new RuntimeException(...)` diventa `\RuntimeException`, altrimenti si cerca `Database\Seeders\RuntimeException` e `testSeederFailsClearlyWhenDependenciesAreMissing` fallisce.
- `db:seed --class=OrdersTableSeeder` e `$this->seed('PlatesTableSeeder')` continuano a funzionare: L13 aggiunge da solo il prefisso `Database\Seeders\`.
- `UserFactory` passa al formato a classe (`Database\Factories\UserFactory`) e `User` usa `HasFactory`.

Commit: `build: Laravel 13 con struttura slim, rotte a tupla, seeder namespaced`. ✏️ Gate: `php artisan test` deve dare verdi tutti i test della Fase 0, compresi quelli di caratterizzazione SEC-1/SEC-2 se le correzioni non sono ancora state applicate.

---

## Fase 2 — Sintassi moderna dove riduce il codice
Ogni voce è un commit piccolo, sempre con test verdi.

**API pubblica (`app/Http/Controllers/Api/`)**: ~120 righe diventano ~30.
```php
public function restTopRated() {
    return Restaurant::withAvg('rates as average', 'rate')
        ->orderByDesc('average')->take(12)->get()
        ->each(fn ($r) => $r->average = (float) $r->average);   // stesso tipo JSON di oggi
}
public function restByTypes(string $ids) {
    return Restaurant::whereHas('types', fn ($q) => $q->whereIn('type_id', explode(',', $ids)))->paginate(4);
}
public function restMunu(string $slug) {
    $rest = Restaurant::where('slug', $slug)->firstOrFail();
    return ['averageRate' => (float) $rest->rates()->avg('rate'), 'location' => $rest,
            'types' => $rest->types()->pluck('name'), 'menu' => $rest->plates()->paginate(4)];
}
```
- Elimina anche la divisione per zero di un ristorante senza voti, che diventa `average: 0`. Aggiungere un test per questo caso e togliere la trappola da AGENTS.md.
- `TypeController@index` diventa `['types' => Type::all()]`.
- `OrderController@store` mantiene il `Validator` manuale, per non cambiare la risposta 200 `{errors}`. Poi `Order::create($data)->plates()->sync(...)` e la mail con `new OrderedMessage($data)`.

**Admin (`app/Http/Controllers/Admin/`)**
- Si usa `firstOrFail()`, `findOrFail()` o il route model binding (`{restaurant:slug}`, `{plate:slug}`, `{order}`) al posto di `->get()[0]` e dei `if(!$x) abort(404)` irraggiungibili. Gli URI non cambiano e `route(..., $slug)` è posizionale, quindi le viste restano uguali.
- Il controllo del proprietario diventa una riga, con la vista `admin.notYours.*` invariata:
  `if ($restaurant->user_id != auth()->id()) return view('admin.notYours.plate');`
  Resta il confronto non stretto di oggi, perché su MySQL `user_id` può arrivare come stringa.
- Upload: `$request->file('img')?->store('plates-imgs')` al posto di `array_key_exists` + `Storage::put`. Il disco di default è lo stesso.
- Regole di validazione scritte come array (`['required','max:50']`) al posto di `'required | max: 50'` con gli spazi. Il CrudTest garantisce messaggi identici.
- **Non** passare a `$request->validated()` per il fill: `restaurant_id` dei piatti arriva dal form senza regola e sparirebbe. Si resta su `$request->all()` con `$fillable`.
- `OrderController@show` diventa `Order::with('plates','restaurant')->findOrFail($id)`: una query al posto di tre.

**Modelli (`app/*.php`)**
- Relazioni con `::class` e tipo di ritorno (`BelongsTo`, `HasMany`…) e `casts()` come metodo.
- Namespace `App\` invariato, per non toccare tutti gli import.
- Gli attributi PHP di Eloquent si usano solo se esistono in `vendor/laravel/framework/src/Illuminate/Database/Eloquent/Attributes/`; va verificato dopo `composer install`.

**Mailable**: costruttore con promozione (`public function __construct(public array $contact)`) e `content(): Content` con `markdown: 'mails.orderedMessage'`.

**Test 404 nuovi** (comportamento concordato): `/admin/plates/inesistente`, `/admin/orders/show/999999` e `/api/restaurantsMenu/inesistente` devono dare 404.

✏️ **Bug esistenti emersi**: commit separati, da confermare uno per uno durante il lavoro.
- `admin/restaurants/show.blade.php:12`: passa `$restaurant->id` a una rotta che si aspetta lo slug (oggi 500, dopo la migrazione 404). Va corretto in `$restaurant->slug`.
- `RegisterController@create` salva `last_name = $data['name']`.
- ✏️ `PlateController@store` e `StatsController@show` non controllano il proprietario del ristorante. Ora sono tracciati come **SEC-1 / SEC-2** nella Fase 0, punto 2: test già scritti e correzione `fix(security):` separata dalla migrazione.

---

## Fase 3 — Frontend: Vite + Vue 3
✏️ **Dipendenze** (versioni dalla tabella npm della Fase −1)
- Via: `laravel-mix`, `cross-env`, `resolve-url-loader`, `sass-loader`, `vue-template-compiler`, `bootstrap-vue` (registrato ma nessun `<b-*>` usato), `jw-vue-pagination` (registrato ma non usato: c'è `PaginateEl.vue`), `lodash` se `window._` risulta inutilizzato. 🆕 Tolti anche gli strumenti di test solo Vue 2: `@vitejs/plugin-vue2`, `@vue/test-utils@1`.
- Dentro: `vite ^8`, `laravel-vite-plugin ^3`, `@vitejs/plugin-vue ^6`, `vue@3`, ✏️ `vue-router ^4.6`, `vuex ^4.1`.
- 🆕 Dev: `vitest ^5`, `@vue/test-utils ^2.6`, `jsdom`.
- Resta `vuex-persistedstate@4`, compatibile con Vuex 4 e con la stessa chiave localStorage `vuex`, quindi i carrelli già salvati sopravvivono. 🆕 Poiché è deprecato su npm, all'inizio della fase si sceglie se tenerlo o usare l'alternativa locale documentata nella Fase −1; lo spec dello store copre la persistenza in entrambi i casi.
- `axios ^1`, `chart.js`, `braintree-web`, `sass`.
- **Bootstrap resta 4.6** (con `popper.js`): le viste Blade usano classi e `data-toggle` di BS4, e il passaggio a BS5 porterebbe regressioni visive.

**Build**
- `vite.config.js` sostituisce `webpack.mix.js`, con gli stessi 6 input: `resources/sass/app.scss`, `resources/js/app.js`, `resources/js/vue.js` e i 3 file in `resources/js/jsBack/`.
- Script `dev`/`build` = `vite`/`vite build`; `NODE_OPTIONS=--openssl-legacy-provider` non serve più.
- Blade: `asset('js/...')` e `asset('css/app.css')` diventano `@vite('resources/...')` in `guest/home`, `layouts/app`, `layouts/logged`, `layouts/deleteConfirm`, `auth/login` e `admin/stats/show`.
- `stats/show`: lo `<script>` inline con `const relatedOrders` resta **prima** di `@vite`. I moduli sono differiti e vedono le `const` globali degli script classici.
- `require()` diventa `import`, `@import "~bootstrap/..."` diventa `@import "bootstrap/..."`. I warning Sass di BS4 si silenziano con `css.preprocessorOptions.scss.quietDeps/silenceDeprecations`.
- Da eliminare: `public/js/*`, `public/css/app.css`, `public/mix-manifest.json`. Aggiungere `public/build/` e `public/hot` a `.gitignore`.

**Codice Vue** (Options API e `mapState/mapActions` restano, cambia solo il bootstrap)
- `resources/js/vue.js`: `createApp(App).use(store()).use(router).mount('#root')`.
- `routes.js`: `createRouter({ history: createWebHistory(), linkExactActiveClass: 'active', routes })`, con `path: '*'` → `'/:pathMatch(.*)*'`.
- `store.js`: `createStore({...})` senza `Vue.use`, e lo spec di Vitest della Fase 0 deve passare con le sole modifiche agli import.
- 🆕 Anche `tests/js/payment.spec.js` si porta su `@vue/test-utils` 2 cambiando solo mount, mock e import. **Le asserzioni restano identiche** e devono passare tutte: è la verifica di non-regressione del flusso Braintree.
- `@click.native` → `@click` in `singleTypeRest.vue`, `TopRated.vue`, `Plate.vue`, `Warning.vue` e `Header.vue`.
- Da controllare a mano durante il port: `v-model` sui componenti, `$listeners`/`$attrs`, `beforeDestroy`, `.sync`. Il grep non ne ha trovati ma va confermato.
- Cancellare il codice morto `resources/js/routes copy.js`.

**Codespace**: `npm run dev` (HMR) richiede la porta 5173 inoltrata e `server.hmr`/`origin` configurati. In alternativa `npm run build -- --watch`. Va documentato in AGENTS.md.

---

## Fase 4 — Docker, deploy e documentazione
- `docker/dev/Dockerfile`: `php:8.4-cli` (bookworm), senza il trucco `archive.debian.org`.
- `Dockerfile` di produzione:
  - stage `node:22` per `npm ci && npm run build`, poi `COPY public/build`. **Vite 7 richiede Node ≥20.19** (✏️ verificato anche per Vite 8: `^20.19 || >=22.12`) e il `nodejs` di Debian è troppo vecchio.
  - stage finale `php:8.4-apache` con `gd` configurato.
- `build.sh` e `render.yaml`: aggiornare i nomi delle variabili env (Fase 1).
- ✏️ `AGENTS.md`, `README.md`, `SETUP_LOCALE.md`:
  - comandi (`npm run dev/build`, `npx vitest`)
  - tabella "Dove mettere cosa": `bootstrap/app.php` per i middleware, `database/seeders`, `vite.config.js` al posto di `webpack.mix.js`
  - trappole: via quelle su Mix/OpenSSL, divisione per zero e maiuscole della mail; dentro session/cache su `file`, Bootstrap 4 da non aggiornare insieme ad altro, HMR in Codespace
  - 🆕 sezione "Braintree": i test automatici usano solo mock e la verifica sandbox è manuale
  - 🆕 stato di SEC-1/SEC-2 (aperti o corretti)
- `dev/mappa.html`: modificare **solo il blocco `DATI`**. Servono righe `F(...)` per i file nuovi/rimossi (Kernel, Handler, middleware, `bootstrap/app.php`, `vite.config.js`, `database/seeders/*`, `tests/*`), i comandi nella tab `cmd` e `go`/`rel` coerenti con `FILES`.

---

## 🆕 Verifica Braintree (separata dagli smoke test)
**A. Automatica, a ogni commit e senza rete**: `npx vitest run tests/js/payment.spec.js`.
- Copre inizializzazione, init fallita, validazione dei campi, tokenizzazione ok/fallita, risposta con `errors`, completamento dell'ordine (svuotamento carrello e redirect a `success`) ed errore di rete. Dettaglio dei casi nella Fase 0, punto 7.
- Il lato server è coperto da `PublicApiTest` (`POST /api/orders`).
- Nessuna dipendenza da Braintree o da altri servizi esterni; nessun pagamento, né reale né sandbox.

**B. Manuale in sandbox**, prima della migrazione (baseline) e dopo la Fase 3, con la stessa chiave di sandbox già presente in `Payment.vue` e **mai credenziali di produzione**:
1. Aprire `/payment` con un carrello pieno: le tre hosted fields (numero, CVV, scadenza) si caricano e in console non ci sono errori.
2. Carta di test **4111 1111 1111 1111**, scadenza futura, CVV `123` e campi cliente compilati → tokenizzazione riuscita (nonce nella console), POST `/api/orders` 200, ordine visibile in `/admin/orders/{slug}`, mail nel log, carrello svuotato, pagina di successo.
3. Caso di errore: campi carta vuoti o numero incompleto → messaggio d'errore di Braintree mostrato, nessun POST, carrello intatto.
4. Caso di errore server: campi cliente mancanti → errori sotto i campi, nessun redirect.
5. Confermare che non esiste nessuna transazione lato server: oggi c'è solo il nonce lato client (vedi AGENTS.md), quindi nessun addebito, neanche in sandbox.

---

## Verifica (end-to-end)
1. ✏️ A ogni commit: `php artisan test` (Fase 0 + test nuovi, compresi quelli di `OwnershipTest` su `PlateController@store` e `StatsController@show`), `npx vitest run` (store **e** pagamento) e `php artisan route:list` senza errori.
2. 🆕 Prima della Fase 1: tabelle di compatibilità della Fase −1 confermate da `composer why-not`, `composer update --dry-run -W` e `npm view`. Dopo la Fase 1: `composer audit` pulito e nessun pacchetto *abandoned* non documentato.
3. `npm run build` compila e `php artisan migrate:fresh --seed` funziona sia su SQLite sia su MySQL 8 (`docker compose up`).
4. Build dell'immagine di produzione: `docker build .`, poi avvio e `curl /api/types`.
5. ✏️ Smoke test manuale nel browser, prima e dopo, con lo stesso localStorage:
   - **Clienti**: home con top rated e stelle, filtro per tipi con paginazione, menu con paginazione, carrello (incluso il warning "un solo ristorante") e redirect da `/your-cart` a `/payment`. Il pagamento si verifica a parte, nella sezione **Verifica Braintree**.
   - **Admin**: register, login → `/admin`, CRUD ristoranti con immagine e tipi, CRUD piatti, conferma di eliminazione, ordini, grafico stats, dropdown di logout (jQuery/BS4), pagine notYours con un secondo utente, paginazione con stile Bootstrap.
6. 🆕 Verifica Braintree, sezioni A (automatica) e B (manuale sandbox con 4111 1111 1111 1111).
7. 🆕 Sicurezza: se `fix(security): SEC-1/SEC-2` sono stati applicati, i test `testOtherUserCannot…` sono attivi e verdi. Altrimenti restano skippati con il loro motivo e le vulnerabilità sono elencate come aperte nella PR.
