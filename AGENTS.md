# AGENTS.md

Guida per agenti AI e nuovi sviluppatori. Panoramica completa: `README.md`; mappa interattiva dei file: `dev/mappa.html`.

## Progetto in breve
DeliveBoo = Laravel 13 (PHP ≥ 8.3) + sito clienti Vue 3 SPA (Vite) + pannello ristoratori in Blade. Lingua del codice: inglese (commenti misti IT/EN). Documentazione: italiano.

## Comandi
Avvio con Docker (Codespace o PC nuovo) passo per passo: tab "Link di avvio" di `dev/mappa.html`. L'immagine PHP 8.4 di sviluppo è `docker/dev/Dockerfile`. Storia e stato della migrazione 7 → 13: `dev/PIANO_MIGRAZIONE_L13.md`.
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed         # tabelle + dati di prova
php artisan db:seed                # rilanciabile: i seeder sono idempotenti
php artisan db:seed --class=OrdersTableSeeder   # un solo seeder
npm run build                      # Vite: resources/ -> public/build (usata dal Dockerfile)
npm run watch                      # come build, ricompila a ogni modifica (consigliato in Codespace)
npm run dev                        # server Vite con HMR (porta 5173: in Codespace va inoltrata)
php artisan serve                  # http://127.0.0.1:8000
php artisan test                   # SQLite in memoria: API, admin, auth, rotte, seeder
npm run test:js                    # Vitest: store, flusso Braintree (mock), smoke di ogni rotta SPA
php artisan route:list             # verifica le rotte
```

## Dove mettere cosa
| Voglio… | Tocco |
|---|---|
| Nuovo endpoint JSON | rotta in `routes/api.php` (sintassi a tupla `[Controller::class, 'metodo']`) + metodo in `app/Http/Controllers/Api/*` (risposte `response()->json()`) |
| Nuova pagina cliente | cartella `resources/js/pages/Nome/` (come un componente) + export in `pages/index.js` + rotta in `resources/js/routes.js` (non serve toccare `routes/web.php`: la catch-all la serve già) |
| Nuovo componente Vue | cartella `resources/js/components/Nome/` con `Nome.vue` (solo template), `Nome.logic.js` (oggetto Options API esportato per nome), `Nome.scss` (`<style scoped src>`), `index.js`; `Nome.utils.js` se ci sono funzioni pure. Export in `components/index.js` |
| Nuova chiamata al server | funzione `async` in `resources/js/api/` (mai axios nei componenti) |
| Nuova funzione pura condivisa | `resources/js/utils/` (+ export in `utils/index.js`) |
| Nuovo stato/azione (carrello, fetch) | `resources/js/store.js` (mutations sincrone, actions `async` che usano `resources/js/api`) |
| Nuova pagina admin | rotta in `routes/web.php` dentro il gruppo `admin` (middleware `auth`), metodo in `Controllers/Admin`, vista in `resources/views/admin/...` che estende `layouts.app` |
| Middleware, proxy, redirect dopo il login | `bootstrap/app.php` (`withMiddleware`): non esistono più `app/Http/Kernel.php` né `RouteServiceProvider` |
| Nuovi dati di prova | seeder in `database/seeders/` (namespace `Database\Seeders`, eccezioni come `\RuntimeException`): riferimenti per chiave naturale (email, nome), **mai ID**; `firstOrNew` + `syncWithoutDetaching` per restare idempotenti |
| Nuovo campo/tabella | **nuova** migration in `database/migrations` (non modificare quelle esistenti), aggiorna `#[Fillable([...])]` nel modello in `app/`, seeder se serve |
| Nuovo JS per Blade | `resources/js/jsBack/` **e** aggiungilo agli `input` di `vite.config.js`; nella vista `@vite('resources/js/jsBack/nome.js')` |
| Stili | partial SCSS in `resources/sass/` importati da `app.scss` (o `<style>` nel componente Vue) |

## Convenzioni
- Modelli in `app/` (namespace `App\`, non `App\Models`): `Restaurant`, `Plate`, `Order`, `Type`, `Rate`, `User`. Attributi Eloquent (`#[Fillable]`, `#[Hidden]`, `#[WithoutTimestamps]`), relazioni con `::class` e tipo di ritorno (`belongsTo(Restaurant::class): BelongsTo`).
- I ristoranti si identificano pubblicamente per `slug`; nell'admin ogni controller deve verificare `restaurant.user_id == auth()->id()` (vedi `PlateController@index`), altrimenti mostra `admin/notYours/*`; i record inesistenti danno 404 (`firstOrFail`/`findOrFail`).
- Vue 3 con Options API, Vuex 4, `mapState/mapActions`, Vue Router 4. Indentazione 4 spazi (`.editorconfig`).
- Stile JS (skill `frontend-standards`): arrow function di default (metodi/hook Options API restano a metodo perché usano `this`), export nominati raccolti in fondo al file (l'`export default` del `.vue` è l'eccezione dell'SFC).
- Tra componenti si importa la cartella del figlio (`../Hamburger`), le pagine importano dal barrel `components/index.js`. `App.logic.js` importa Header/Content/Footer dalle loro cartelle e **non** dal barrel: altrimenti il CSS degli altri componenti finirebbe prima di `App.scss` e regole globali come `.cont { margin: 0 auto }` vincerebbero sul reset scoped.
- Paginazione lato server con `paginate(4)`; il front usa `PaginateEl.vue`, l'admin `->links()` con stile Bootstrap 4 (`Paginator::useBootstrapFour()` in `AppServiceProvider`).

## Trappole
- **URL sempre relativi** nel front (`/api/...`, `/login`): mai `http://127.0.0.1:8000/...` scritto fisso, che non funziona da Codespace né in produzione. Nelle email (link assoluti) usa `url('/')`.
- Session, cache e code su `file`/`sync` (default in `config/` e `.env.example`): il progetto non ha le tabelle `sessions`, `cache`, `jobs`. Con i default di Laravel 11+ (`database`) il login darebbe errore.
- **Bootstrap resta 4.6**: viste Blade e componenti usano classi e `data-toggle` di BS4. Non aggiornarlo insieme ad altro.
- Vite richiede Node ≥ 20.19 (il Dockerfile usa uno stage `node:22`). `package.json` ha `"type": "module"`.
- Vue 3: niente contenuto dentro `<router-view>` (anche un commento diventa slot e la pagina non si vede); istanze di librerie esterne nei `data` con `markRaw` (vedi `pages/Payment/Payment.logic.js`). Un `watch` su un array dello store scatta su `push`/`splice` solo con `deep: true` (vedi `Cart.logic.js`).
- Gli `@import` dei partial in ogni `Nome.scss` (anche `reset` e `utilities`) generano CSS scoped che sovrascrive Bootstrap: non toglierli né appiattire i selettori senza una verifica visiva.
- In Codespace l'HMR di `npm run dev` richiede la porta 5173 inoltrata: più semplice `npm run watch`.
- Su MySQL `float()` nelle migration crea un `FLOAT` a singola precisione: per i prezzi vedi `2026_11_22_000000_restore_price_column_types` (`DOUBLE(5,2)` / `DOUBLE(8,2)`). Non passare a `decimal`: Eloquent restituirebbe stringhe e il carrello le concatenerebbe.
- Ogni file PHP deve iniziare esattamente con `<?php`: anche un solo a capo prima (era in `routes/api.php`) viene stampato a ogni richiesta, blocca header e cookie di sessione e il login dà **419 Page Expired**.
- `public/build/` è **output di build** (non versionato): modifica `resources/` e ricompila. I test PHP usano `withoutVite()` e non richiedono la build.
- `Admin/HomeController` è non instradato (la home admin è `RestaurantController@index`).
- La rotta catch-all `Route::get('{any?}')` in `routes/web.php` deve restare **ultima**.
- Braintree: oggi solo nonce lato client, nessuna transazione lato server. I test automatici (`tests/js/payment.spec.js`) usano solo mock (axios e `braintree-web` mockati come moduli); la verifica sandbox (carta 4111 1111 1111 1111) è manuale.
- `POST /api/orders` risponde **200** anche con errori di validazione (`{errors}`): la pagina `Payment` legge quel formato, non trasformarlo in 422.
- Problemi noti, fissati dai test come comportamento attuale e da correggere in commit separati: **SEC-1..SEC-4** (mancano controlli del proprietario in `PlateController@store/update/destroy`, `RestaurantController@update/destroy`, `StatsController@show`) e **BUG-1** (`RestaurantController@store` non imposta lo `slug` → 500). Dettagli in `dev/PIANO_MIGRAZIONE_L13.md`.
- La vista della mail è `mails/orderedMessage` (`O` maiuscola): su filesystem case-sensitive (Linux/Docker) il nome deve combaciare esattamente.

## Non toccare / non committare
`vendor/`, `node_modules/`, `.env`, file in `storage/` (tranne i `.gitignore`), lock file se non cambi dipendenze.

## Prima di chiudere un lavoro
`php artisan test` e `npm run test:js` verdi · `php artisan route:list` senza errori · `npm run build` compila · se cambi struttura o file importanti, aggiorna `dev/mappa.html` e `README.md`.

## Mappa del progetto (`dev/mappa.html`)
- Segue la skill `skills/project-map-3-tabs/SKILL.md` (`.claude/skills` è un symlink a `skills/`): palette e misure fisse, **si modifica solo il blocco `DATI`** (`FILES` con le righe `F(...)`, `NODES`, `EDGES`, `TARGETS`, `NOTES`, `CMDS`…), mai CSS né `RENDER`.
- Ha 5 tab: le 3 della skill (`graph`, `code`, `links`) più due estensioni aggiunte su richiesta: `db` "Relazioni tabelle" (dati `DB_PAGE`, `DB_KINDS`, `REL_TYPES`, `DB_GROUPS`, `DB_TABLES`, `DB_LINKS`, `DB_RELS`, `DB_ALONE`) e `cmd` "Comandi Laravel" (`CMD_PAGE`, `CMDS`). Le estensioni hanno un proprio blocco nel `RENDER` e CSS con i soli colori della palette.
- Nuova tabella, colonna chiave o relazione → aggiorna `DB_TABLES`/`DB_LINKS` (schema) e `DB_RELS` (dettaglio con colonne e pivot).
- Nuovo file → nuova riga `F(...)`; nuovo flusso → nodo/arco. Ogni `go` e `rel` deve essere un percorso presente in `FILES`.
- `dev/` (come `skills/` e `.claude/`) è solo sviluppo: **mai referenziata** da `app/`, `resources/`, `routes/`, `public/` o dalla config, ed esclusa dall'immagine Docker tramite `.dockerignore`.
