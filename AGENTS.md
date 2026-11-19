# AGENTS.md

Guida per agenti AI e nuovi sviluppatori. Panoramica completa: `README.md`; mappa interattiva dei file: `mappa.html`.

## Progetto in breve
DeliveBoo = Laravel 7 (PHP) + sito clienti Vue 2 SPA + pannello ristoratori in Blade. Lingua del codice: inglese (commenti misti IT/EN). Documentazione: italiano.

## Comandi
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed         # tabelle + dati di prova
npm run dev | npm run watch        # compila resources/ -> public/
npm run prod                       # build ottimizzata (usata dal Dockerfile)
php artisan serve                  # http://127.0.0.1:8000
php artisan test                   # solo test di esempio
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
| Nuovo campo/tabella | **nuova** migration in `database/migrations` (non modificare quelle esistenti), aggiorna `$fillable` nel modello in `app/`, seeder se serve |
| Nuovo JS per Blade | `resources/js/jsBack/` **e** aggiungilo in `webpack.mix.js` |
| Stili | partial SCSS in `resources/sass/` importati da `app.scss` (o `<style>` nel componente Vue) |

## Convenzioni
- Modelli in `app/` (namespace `App\`, non `App\Models`): `Restaurant`, `Plate`, `Order`, `Type`, `Rate`, `User`. Relazioni dichiarate con stringhe (`belongsTo('App\Restaurant')`).
- I ristoranti si identificano pubblicamente per `slug`; nell'admin ogni controller deve verificare `restaurant.user_id == auth()->id()` (vedi `PlateController@index`), altrimenti mostra `admin/notYours/*`.
- Vue 2 con Options API, Vuex, `mapState/mapActions`. Indentazione 4 spazi (`.editorconfig`).
- Paginazione lato server con `paginate(4)`; il front usa `jw-pagination`.

## Trappole
- **URL API fissi** `http://127.0.0.1:8000/api/...` in `store.js` e `pages/Payment.vue`: in produzione non funzionano. Se tocchi quei file, passa a URL relativi.
- `public/js/*` e `public/css/app.css` sono **output di build**: non modificarli, modifica `resources/` e ricompila.
- `resources/js/routes copy.js` è codice morto. `Admin/HomeController` è non instradato (la home admin è `RestaurantController@index`).
- La rotta catch-all `Route::get('{any?}')` in `routes/web.php` deve restare **ultima**.
- Braintree: oggi solo nonce lato client, nessuna transazione lato server.
- `api/restaurantsTopRated` e `restMunu` dividono per `count($rates)`: un ristorante senza voti causa divisione per zero.
- Il nome della Mailable è `orderedMessage` ma la classe è `OrderedMessage` e la vista `mails/orderedMessage` (`markdown('mails.orderedmessage')`): attenzione a maiuscole su filesystem case-sensitive (Linux/Docker).

## Non toccare / non committare
`vendor/`, `node_modules/`, `.env`, file in `storage/` (tranne i `.gitignore`), lock file se non cambi dipendenze.

## Prima di chiudere un lavoro
`php artisan route:list` senza errori · `npm run dev` compila · se cambi struttura o file importanti, aggiorna `mappa.html` (array `D` con le spiegazioni) e `README.md`.
