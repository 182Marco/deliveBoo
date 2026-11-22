# AGENTS.md

Guida per agenti AI e nuovi sviluppatori. Panoramica completa: `README.md`; mappa interattiva dei file: `dev/mappa.html`.

## Progetto in breve
DeliveBoo = Laravel 7 (PHP) + sito clienti Vue 2 SPA + pannello ristoratori in Blade. Lingua del codice: inglese (commenti misti IT/EN). Documentazione: italiano.

## Comandi
Avvio con Docker (Codespace o PC nuovo) passo per passo: tab "Link di avvio" di `dev/mappa.html`. L'immagine PHP 8.0 di sviluppo è `docker/dev/Dockerfile`.
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed         # tabelle + dati di prova
php artisan db:seed                # rilanciabile: i seeder sono idempotenti
php artisan db:seed --class=OrdersTableSeeder   # un solo seeder
npm run dev | npm run watch        # compila resources/ -> public/
npm run prod                       # build ottimizzata (usata dal Dockerfile)
php artisan serve                  # http://127.0.0.1:8000
php artisan test                   # SQLite in memoria; include DatabaseSeederTest
php artisan route:list             # verifica le rotte
```

## Dove mettere cosa
| Voglio… | Tocco |
|---|---|
| Nuovo endpoint JSON | rotta in `routes/api.php` + metodo in `app/Http/Controllers/Api/*` (namespace `Api`, risposte `response()->json()`) |
| Nuova pagina cliente | `resources/js/pages/Nome.vue` + rotta in `resources/js/routes.js` (non serve toccare `routes/web.php`: la catch-all la serve già) |
| Nuovo componente Vue | `resources/js/components/` |
| Nuovo stato/azione (carrello, fetch) | `resources/js/store.js` (mutations sincrone, actions con axios) |
| Nuova pagina admin | rotta in `routes/web.php` dentro il gruppo `admin` (middleware `auth`), metodo in `Controllers/Admin`, vista in `resources/views/admin/...` che estende `layouts.app` |
| Nuovi dati di prova | seeder in `database/seeds/`: riferimenti per chiave naturale (email, nome), **mai ID**; `firstOrNew` + `syncWithoutDetaching` per restare idempotenti |
| Nuovo campo/tabella | **nuova** migration in `database/migrations` (non modificare quelle esistenti), aggiorna `$fillable` nel modello in `app/`, seeder se serve |
| Nuovo JS per Blade | `resources/js/jsBack/` **e** aggiungilo in `webpack.mix.js` |
| Stili | partial SCSS in `resources/sass/` importati da `app.scss` (o `<style>` nel componente Vue) |

## Convenzioni
- Modelli in `app/` (namespace `App\`, non `App\Models`): `Restaurant`, `Plate`, `Order`, `Type`, `Rate`, `User`. Relazioni dichiarate con stringhe (`belongsTo('App\Restaurant')`).
- I ristoranti si identificano pubblicamente per `slug`; nell'admin ogni controller deve verificare `restaurant.user_id == auth()->id()` (vedi `PlateController@index`), altrimenti mostra `admin/notYours/*`.
- Vue 2 con Options API, Vuex, `mapState/mapActions`. Indentazione 4 spazi (`.editorconfig`).
- Paginazione lato server con `paginate(4)`; il front usa `jw-pagination`.

## Trappole
- **URL sempre relativi** nel front (`/api/...`, `/login`): mai `http://127.0.0.1:8000/...` scritto fisso, che non funziona da Codespace né in produzione. Nelle email (link assoluti) usa `url('/')`.
- Laravel Mix 5 usa webpack 4, che con Node ≥ 17 dà `ERR_OSSL_EVP_UNSUPPORTED`: per questo gli script in `package.json` passano `NODE_OPTIONS=--openssl-legacy-provider`. Non toglierlo, a meno di aggiornare a Mix 6 o versioni successive.
- Ogni file PHP deve iniziare esattamente con `<?php`: anche un solo a capo prima (era in `routes/api.php`) viene stampato a ogni richiesta, blocca header e cookie di sessione e il login dà **419 Page Expired**.
- `public/js/*` e `public/css/app.css` sono **output di build**: non modificarli, modifica `resources/` e ricompila.
- `resources/js/routes copy.js` è codice morto. `Admin/HomeController` è non instradato (la home admin è `RestaurantController@index`).
- La rotta catch-all `Route::get('{any?}')` in `routes/web.php` deve restare **ultima**.
- Braintree: oggi solo nonce lato client, nessuna transazione lato server.
- `api/restaurantsTopRated` e `restMunu` dividono per `count($rates)`: un ristorante senza voti causa divisione per zero.
- Il nome della Mailable è `orderedMessage` ma la classe è `OrderedMessage` e la vista `mails/orderedMessage` (`markdown('mails.orderedmessage')`): attenzione a maiuscole su filesystem case-sensitive (Linux/Docker).

## Non toccare / non committare
`vendor/`, `node_modules/`, `.env`, file in `storage/` (tranne i `.gitignore`), lock file se non cambi dipendenze.

## Prima di chiudere un lavoro
`php artisan route:list` senza errori · `npm run dev` compila · se cambi struttura o file importanti, aggiorna `dev/mappa.html` e `README.md`.

## Mappa del progetto (`dev/mappa.html`)
- Segue la skill `skills/project-map-3-tabs/SKILL.md` (`.claude/skills` è un symlink a `skills/`): palette e misure fisse, **si modifica solo il blocco `DATI`** (`FILES` con le righe `F(...)`, `NODES`, `EDGES`, `TARGETS`, `NOTES`, `CMDS`…), mai CSS né `RENDER`.
- Ha 4 tab: le 3 della skill (`graph`, `code`, `links`) più `cmd` "Comandi Laravel", aggiunta su richiesta.
- Nuovo file → nuova riga `F(...)`; nuovo flusso → nodo/arco. Ogni `go` e `rel` deve essere un percorso presente in `FILES`.
- `dev/` (come `skills/` e `.claude/`) è solo sviluppo: **mai referenziata** da `app/`, `resources/`, `routes/`, `public/` o dalla config, ed esclusa dall'immagine Docker tramite `.dockerignore`.
