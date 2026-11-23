# AGENTS.md

Guida per agenti AI e nuovi sviluppatori. Panoramica completa: `README.md`; mappa interattiva dei file: `dev/mappa.html`.

## Progetto in breve
**Foodsy** (repository `deliveBoo`) = Laravel 13 (PHP ≥ 8.3) + sito clienti Vue 3 SPA (Vite) + pannello ristoratori ("partner hub") in Blade. **Il prodotto è in inglese (lingua di default) con traduzioni in italiano, tedesco, francese e spagnolo** scelte dal menu lingua dell'header: tutte le stringhe statiche (sito, area partner, email, risposte API, validazione) stanno in un unico oggetto, `resources/js/utils/strings.json`. Dati di prova (nomi di piatti, cucine, allergeni nel db), commenti del codice e test restano in inglese. Il nome del prodotto è ovunque *Foodsy* (mai Deliveroo/DeliveBoo; restano solo gli identificativi tecnici: cartella, database, container). Documentazione di sviluppo: italiano.

## Comandi
Avvio con Docker (Codespace o PC nuovo) passo per passo: tab "Link di avvio" di `dev/mappa.html`. L'immagine PHP 8.4 di sviluppo è `docker/dev/Dockerfile`. Storia e stato della migrazione 7 → 13: `dev/PIANO_MIGRAZIONE_L13.md`.
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed         # tabelle + dati di prova
php artisan db:seed                # rilanciabile: i seeder sono idempotenti
php artisan db:seed --class=OrdersTableSeeder   # un solo seeder
SEED_PROFILE=test php artisan db:seed           # catalogo generato ridotto (default: full, config/seeding.php)
npm run build                      # Vite: resources/ -> public/build (usata dal Dockerfile)
npm run watch                      # come build, ricompila a ogni modifica (consigliato in Codespace)
npm run dev                        # server Vite con HMR (porta 5173: in Codespace va inoltrata)
php artisan serve                  # http://127.0.0.1:8000
php artisan test                   # SQLite in memoria seminato una volta (profilo test): API, admin, auth, rotte, seeder, query alimentari
npm run test:js                    # Vitest: store, flusso di pagamento (mock), smoke di ogni rotta SPA
php artisan route:list             # verifica le rotte
```

## Dove mettere cosa
| Voglio… | Tocco |
|---|---|
| Nuovo endpoint JSON | rotta in `routes/api.php` (sintassi a tupla `[Controller::class, 'metodo']`) + metodo in `app/Http/Controllers/Api/*` (risposte `response()->json()`) |
| Nuova pagina cliente | cartella `resources/js/pages/Nome/` (come un componente) + export in `pages/index.js` + rotta in `resources/js/routes.js` (non serve toccare `routes/web.php`: la catch-all la serve già) |
| Nuovo componente Vue | cartella `resources/js/components/Nome/` con `Nome.vue` (solo template), `Nome.logic.js` (oggetto Options API esportato per nome), `Nome.css` (CSS puro, `<style scoped src>`, solo se il componente ha stili propri), `index.js`; `Nome.utils.js` se ci sono funzioni pure. Export in `components/index.js` |
| Nuovo testo visibile (Vue, Blade, mail, API, validazione) | chiave in `resources/js/utils/strings.json` con **tutte e 5** le lingue e gli stessi segnaposto `:nome` (`LocaleTest` lo verifica); in Vue `t("gruppo.chiave", { nome })` (sotto `site`, `t` importato da `utils` ed esposto nei `methods`), in Blade/PHP `__('partner.gruppo.chiave')` o `__('site....')`. Plurali `uno\|tanti` con `{ count }` / `trans_choice`. Mai testo scritto nel template |
| Nuova chiamata al server | funzione `async` in `resources/js/api/` (mai axios nei componenti) |
| Nuova funzione pura condivisa | `resources/js/utils/` (+ export in `utils/index.js`) |
| Nuovo stato/azione (carrello, fetch) | `resources/js/store.js` (mutations sincrone, actions `async` che usano `resources/js/api`) |
| Nuova pagina admin | rotta in `routes/web.php` dentro il gruppo `admin` (middleware `auth`), metodo in `Controllers/Admin`, vista in `resources/views/admin/...` che estende `layouts.app` |
| Middleware, proxy, redirect dopo il login | `bootstrap/app.php` (`withMiddleware`): non esistono più `app/Http/Kernel.php` né `RouteServiceProvider` |
| Nuovi dati di prova | catalogo generato: **dati** in `database/seeders/data/` (`cuisines/<chiave>.php` con concept e ricette a varianti `base`/`mid`/`high`, `drinks.php`, `ingredients.php`, `images.php` con soli ID Unsplash verificati), **logica** in `database/seeders/Support/` (deterministica con `Rng`, mai ID né stato del DB); un ingrediente nuovo va nel registro `ingredients.php` (`CatalogDataTest` lo verifica). Seeder scritti a mano in `database/seeders/` (namespace `Database\Seeders`, eccezioni come `\RuntimeException`): riferimenti per chiave naturale (email, nome), **mai ID**; idempotenti (`firstOrNew` + `syncWithoutDetaching`, oppure inserimenti in blocco solo di ciò che manca) |
| Ricerca su piatti/diete | scope di `app/Plate.php` (`orderable`, `withDiet`, `freeFrom`, `withIngredients`, `withoutIngredients`, `derivedAllergens`); regole delle diete derivate in `app/Dietary/Derivation.php` (le usano admin e seeder) |
| Nuovo campo/tabella | **nuova** migration in `database/migrations` (non modificare quelle esistenti), aggiorna `#[Fillable([...])]` nel modello in `app/`, seeder se serve |
| Nuovo JS per Blade | `resources/js/jsBack/` **e** aggiungilo agli `input` di `vite.config.js`; nella vista `@vite('resources/js/jsBack/nome.js')` |
| Stili | token in `resources/css/tokens.css`, primitive condivise `ui-*` in `resources/css/ui.css` (titoli, eyebrow, bottoni, campi, chip, badge, card, notice, tabelle, modale, paginazione, rail, logo, foto); layout dell'area partner `adm-*`/`auth-*` in `resources/css/admin.css`; il resto nel `Nome.css` scoped del componente. Regole nella sezione **Design system** |
| Icona | path SVG in `resources/icons/icons.json` (un solo set): `<Icon name="..." />` in Vue, `<x-icon name="..." />` in Blade |
| Avatar utente | pacchetto indipendente `packages/svg-avatar` (README proprio). Nell'app solo `import { ... } from "svg-avatar"`, `import { Avatar } from "svg-avatar/vue"` o `import { mountAvatarEditor } from "svg-avatar/editor"`. In Blade `<x-avatar :seed="$user->avatarSeed()" :overrides="$user->avatarOverrides()" />` (disegnato da `jsBack/avatar.js`); pagina «Personalizza avatar» = `admin.avatar.edit`/`update` (`AvatarController`, `UpdateAvatarRequest` + `App\Rules\AvatarOverrides`, vista `admin/avatar/edit`, `jsBack/avatarEditor.js`). Nuove parti in `assets/parts.json` (solo slot colore; una categoria nuova va in `unseededCategories`, altrimenti cambia gli avatar esistenti), colori in `palettes/default.json`, compatibilità in `rules/rules.json`, poi rigenera `tests/fixtures/options.json` (`compat.json` non si rigenera mai); etichette delle varianti in `partner.avatar.editor` di `strings.json`; anteprima in `dev/avatar-grid.html`. Piano e stato: `AVATAR_IMPLEMENTATION_PLAN.md` |
| Nuova lingua | voce in `locales` di `strings.json` + traduzione di ogni foglia + bandiera in `resources/icons/flags.json` (path su tela 30×20) |
| Campo di form Blade | `<x-field name="..." label="..." :value="old(...)" />` (label, input o `as="textarea"`, errore di validazione) |

## Design system
- Direzione «Mercato» (mercato gastronomico contemporaneo): fondo porcellana `--paper`, card `--surface`, pozzetti avena `--oat`, profondità verde rosmarino `--forest` (footer, banda Guest favourites, carrello, chip attive), paprika `--brand-strong` per le azioni (un solo bottone primario per schermata); `--brand` (#D0682C, colore ufficiale) solo per logo e grandi superfici con testo scuro: con testo bianco non passa AA. Stati: `--olive` ok, `--saffron` attenzione/voti, `--tomato` errore. Font locali: Bricolage Grotesque (titoli, nomi, prezzi) e Figtree (testo), in `resources/fonts/`. Spiegazione completa e regole d'uso: tab “Stile e brand” di `dev/mappa.html`.
- Motivo grafico: il vassoio (la barra sotto la cloche del logo) come barretta arancio davanti a ogni `ui-eyebrow`.
- Foto: **solo link esterni** (Unsplash) nei seeder e nei componenti, mai file in `public/images/` (che contiene solo il logo). Proporzioni: piatti 4:3, card ristorante 3:2, cucine 4:5.
- **Logo**: un solo file, `public/images/foodsy-mark.svg` (geometria originale, colore #D0682C), usato da `Logo` (Vue), `<x-logo>` (Blade), favicon (`partials/favicon`); `favicon.ico`, `apple-touch-icon.png` e `images/foodsy-mark.png` (email) sono generati da quello.
- CSS: **una classe per selettore** (anche `:hover` va sull'elemento stesso, mai `.padre:hover .figlio`), modificatori come classi aggiuntive (`ui-btn ui-btn--primary`), niente nesting né selettori discendenti/figli, niente `!important` (le due eccezioni motivate sono in `base.css`). Animazioni solo su `transform`/`opacity`. Breakpoint fissi mobile-first: 600, 900, 1200 px. Ogni colore, spazio, raggio e ombra viene da `tokens.css`.
- Entry CSS: `resources/css/site.css` (importato da `vue.js`) e `resources/css/admin.css` (in `vite.config.js`, usato da `layouts/app` e `layouts/auth`).
- Numeri: prezzi con `formatPrice` e la classe `ui-price` (cifre tabulari), formato `€12.00`.

## Convenzioni
- Modelli in `app/` (namespace `App\`, non `App\Models`): `Restaurant`, `Plate`, `Order`, `Type`, `Rate`, `User`. Attributi Eloquent (`#[Fillable]`, `#[Hidden]`, `#[WithoutTimestamps]`), relazioni con `::class` e tipo di ritorno (`belongsTo(Restaurant::class): BelongsTo`).
- I ristoranti si identificano pubblicamente per `slug`; nell'admin ogni controller deve verificare `restaurant.user_id == auth()->id()` (vedi `PlateController@index`), altrimenti mostra `admin/notYours/*`; i record inesistenti danno 404 (`firstOrFail`/`findOrFail`).
- Vue 3 con Options API, Vuex 4, `mapState/mapActions`, Vue Router 4. Indentazione 4 spazi (`.editorconfig`).
- Stile JS (skill `frontend-standards`): arrow function di default (metodi/hook Options API restano a metodo perché usano `this`), export nominati raccolti in fondo al file (l'`export default` del `.vue` è l'eccezione dell'SFC).
- Tra componenti si importa la cartella del figlio (`../Hamburger`), le pagine importano dal barrel `components/index.js`. `App.logic.js` importa Header/Footer dalle loro cartelle.
- Paginazione lato server con `paginate(4)`; il front usa `PaginateEl.vue`, l'admin `->links()` con la vista `resources/views/pagination/foodsy.blade.php` (`Paginator::defaultView` in `AppServiceProvider`).

## Trappole
- Lingua: cookie `foodsy_locale` (non cifrato, `encryptCookies(except:)` in `bootstrap/app.php`) letto da `App\Http\Middleware\SetLocale` su web e api; senza cookie **inglese**, la lingua del browser è ignorata. Il sito Vue lo scrive da `setLocale` (`utils/i18n.js`, la lingua è un `ref` di modulo, non nello store), l'area partner con la rotta `locale` (`/locale/{codice}`, prima della catch-all). Laravel legge le traduzioni da `strings.json` via `App\I18n\StringsLoader` (non esiste più `resources/lang`). Il bundle Vue importa solo i rami `site` e `locales` (`import { site } from "./strings.json"`): non importare l'oggetto intero. Nei test JSON i cookie partono solo con `withCredentials()`.
- **URL sempre relativi** nel front (`/api/...`, `/login`): mai `http://127.0.0.1:8000/...` scritto fisso, che non funziona da Codespace né in produzione. Nelle email (link assoluti) usa `url('/')`.
- Session, cache e code su `file`/`sync` (default in `config/` e `.env.example`): il progetto non ha le tabelle `sessions`, `cache`, `jobs`. Con i default di Laravel 11+ (`database`) il login darebbe errore.
- **Niente Bootstrap, jQuery, Font Awesome né altri framework CSS**: sono stati rimossi da sito e area partner. Il menu account dell'admin è un `<details>` nativo (`resources/js/app.js` lo chiude con Esc o clic fuori).
- Vite richiede Node ≥ 20.19 (il Dockerfile usa uno stage `node:22`). `package.json` ha `"type": "module"`.
- Vue 3: niente contenuto dentro `<router-view>` (anche un commento diventa slot e la pagina non si vede); istanze di librerie esterne nei `data` con `markRaw` (vedi `pages/Payment/Payment.logic.js`). Un `watch` su un array dello store scatta su `push`/`splice` solo con `deep: true` (vedi `Cart.logic.js`).
- Un elemento con l'attributo `hidden` resta sempre nascosto (`base.css`): i dialoghi Blade (`#deleteConfirm`, `#loggedMsgEl`) si mostrano togliendo `hidden`, non con classi.
- Le immagini sono URL esterni (Unsplash) e un host può non rispondere: `FoodImage` (Vue) e `<x-photo>` (Blade) mostrano il marchio come segnaposto, anche quando `img` è `null`. I seeder saltano i record esistenti: cambiare una foto nel seeder non aggiorna un DB già popolato.
- Un elemento `position: absolute` (es. `ui-sr-only`) dentro un contenitore che scorre in orizzontale deve avere un antenato posizionato dentro quel contenitore (`.ui-rail` e `.ui-table-wrap` sono `position: relative`), altrimenti allarga tutta la pagina.
- In Codespace l'HMR di `npm run dev` richiede la porta 5173 inoltrata: più semplice `npm run watch`.
- Prezzi: `DECIMAL(8,2)` sui piatti e `DECIMAL(10,2)` sugli ordini (`2026_11_23_100000_use_decimal_for_prices`), con **cast `'price' => 'float'`** in `Plate` e `Order`: senza il cast Eloquent restituirebbe stringhe e il carrello le concatenerebbe. Le query con `DB::table()` non applicano i cast. `order_plate.unit_price` è il prezzo pagato: lo storico non segue le modifiche del menu.
- Informazioni alimentari a quattro stati: nessuna riga = non dichiarato, `unknown`, `yes`, `no`. Mai un default negativo e mai "sicuro" per assenza di dati (`Plate::freeFrom` richiede dichiarazione `complete`, nessun `contains`/`may_contain`, nessun allergene derivato e ingredienti tutti `verified`). Il nichel è **indicativo**: niente garanzie né certificazioni.
- `plates.visible` (letto dal sito) segue `availability` grazie a un hook `saving` del modello. Dentro la classe `Plate` usare `getAttribute`/`setAttribute('visible')`: `$this->visible` sarebbe la proprietà protetta `Model::$visible`.
- I piatti si eliminano soft (`SoftDeletes`): restano negli ordini (`Order::plates()` è `withTrashed`). Nei seeder usare `withTrashed()` per non ricreare un piatto eliminato. Il nome del piatto è unico **per ristorante**, lo slug globalmente (`Plate::uniqueSlug`, `Restaurant::uniqueSlug`).
- Il pivot degli ingredienti si chiama `plate_ingredient` (non quello alfabetico di Laravel): la relazione è `ingredientList()` (`ingredients` è la colonna di testo mostrata dal sito, rigenerata da `syncIngredients`).
- `Eloquent\Collection::only()` filtra per chiave primaria, non per chiave dell'array: per le chiavi usare `->toBase()->only()`.
- Test: il DB in memoria viene seminato una sola volta (`$seed = true` in `tests/TestCase.php`) e ogni test è una transazione: non chiamare `$this->seed()` nel `setUp`. `DatabaseSeederTest` svuota le tabelle da sé.
- Ogni file PHP deve iniziare esattamente con `<?php`: anche un solo a capo prima (era in `routes/api.php`) viene stampato a ogni richiesta, blocca header e cookie di sessione e il login dà **419 Page Expired**.
- `public/build/` è **output di build** (non versionato): modifica `resources/` e ricompila. I test PHP usano `withoutVite()` e non richiedono la build.
- `Admin/HomeController` è non instradato (la home admin è `RestaurantController@index`).
- La rotta catch-all `Route::get('{any?}')` in `routes/web.php` deve restare **ultima**.
- Pagamenti: `POST /api/orders` addebita lato server tramite `App\Payments\PaymentGateway` (binding in `AppServiceProvider` da `PAYMENT_DRIVER`: `fake` di default, `braintree` con le `BRAINTREE_*`). L'importo si calcola **sempre** dai prezzi nel db, mai dal client; `status`/`transaction_id` non sono fillable e li scrive solo il flusso di pagamento. Admin e statistiche usano `Order::paid()`. I test non chiamano mai Braintree (`phpunit.xml` forza `PAYMENT_DRIVER=fake` anche se il `.env` dice `braintree`; `braintree-web` mockato); con un account sandbox vero la verifica è manuale. Carte di prova: tab “Carte di prova” della mappa; in sandbox Braintree il rifiuto dipende dall'**importo** (totale €2,000.00–2,999.99), non dalla carta. `GET /api/payments/config` espone `testMode` (driver fake o Braintree non `production`): solo allora il checkout mostra la barra “Test mode” sopra *First name*, che apre la finestra con le carte (`testPayments` in `pages/Payment/Payment.utils.js`). Dopo il pagamento `Payment` apre la pagina di successo con `?order=&tx=&via=` (`paymentReceipt`): la ricevuta mostra il Transaction ID; l'area partner lo mostra con `Order::paymentProvider()`. Non pubblicare credenziali del pannello Braintree: la prova è il Transaction ID. `braintree-web` si carica con `import()` in `api/payments.js`: non importarlo staticamente altrove (le opzioni dei campi sono in `api/hostedFields.js`).
- `POST /api/orders` risponde **200** anche con errori di validazione o pagamento rifiutato (`{errors}`, `errors.payment`): la pagina `Payment` legge quel formato, non trasformarlo in 422/402.
- Area di servizio fittizia: **cantone di Zurigo e dintorni** (`database/seeders/data/cities.php`, cantone nella colonna `province`, CAP svizzeri a 4 cifre, telefoni 044/052…). Ristoranti storici e generati sono tutti lì; i seeder saltano i record esistenti, quindi per applicare un cambio di città a un DB già popolato serve `migrate:fresh --seed`.
- Dataset ed evoluzione del DB per il futuro assistente AI: stato, scelte e problemi aperti in `dev/PIANO_DATASET.md`.
- Problemi noti, fissati dai test come comportamento attuale e da correggere in commit separati: **SEC-1..SEC-4** (mancano controlli del proprietario in `PlateController@store/update/destroy`, `RestaurantController@update/destroy`, `StatsController@show`) (BUG-1, slug mancante in `RestaurantController@store`, è corretto: `Restaurant::uniqueSlug`). Dettagli in `dev/PIANO_MIGRAZIONE_L13.md`.
- La vista della mail è `mails/orderedMessage` (`O` maiuscola): su filesystem case-sensitive (Linux/Docker) il nome deve combaciare esattamente.

## Non toccare / non committare
`vendor/`, `node_modules/`, `.env`, file in `storage/` (tranne i `.gitignore`), lock file se non cambi dipendenze.

## Prima di chiudere un lavoro
`php artisan test` e `npm run test:js` verdi · `php artisan route:list` senza errori · `npm run build` compila · se cambi struttura o file importanti, aggiorna `dev/mappa.html` e `README.md`.

## Mappa del progetto (`dev/mappa.html`)
- Segue la skill `skills/project-map-3-tabs/SKILL.md` (`.claude/skills` è un symlink a `skills/`): palette e misure fisse, **si modifica solo il blocco `DATI`** (`FILES` con le righe `F(...)`, `NODES`, `EDGES`, `TARGETS`, `NOTES`, `CMDS`…), mai CSS né `RENDER`.
- Ha 8 tab: le 3 della skill (`graph`, `code`, `links`) più cinque estensioni aggiunte su richiesta: `db` "Relazioni tabelle" (dati `DB_PAGE`, `DB_KINDS`, `REL_TYPES`, `DB_GROUPS`, `DB_TABLES`, `DB_LINKS`, `DB_RELS`, `DB_ALONE`), `cmd` "Comandi Laravel" (`CMD_PAGE`, `CMDS`), `pay` "Pagamenti" (`PAY_PAGE`, `PAY_SECTIONS`: il flusso spiegato in linguaggio semplice), `test` "Carte di prova" (`TEST_PAGE`: unico posto con le carte e le istruzioni per pagamenti riusciti e rifiutati, per driver) e `style` "Stile e brand" (`STYLE_PAGE`: concetto, palette con ruolo di ogni token, font, logo, foto, regole e checklist). Le estensioni hanno un proprio blocco nel `RENDER` e CSS con i soli colori della palette (i campioni della palette Foodsy arrivano dai dati).
- Cambi un token, un font o una regola di stile → aggiorna `STYLE_PAGE`; cambi carte di prova o regole di rifiuto → aggiorna `TEST_PAGE` (e `testPayments` in `pages/Payment/Payment.utils.js`).
- Nuova tabella, colonna chiave o relazione → aggiorna `DB_TABLES`/`DB_LINKS` (schema) e `DB_RELS` (dettaglio con colonne e pivot).
- Nuovo file → nuova riga `F(...)`; nuovo flusso → nodo/arco. Ogni `go` e `rel` deve essere un percorso presente in `FILES`.
- `packages/` contiene pacchetti **indipendenti dall'app**: non importano mai `resources/`, `app/` o altri file del progetto, non nominano Foodsy e si usano solo dagli entry pubblici (alias in `vite.config.js`). Lo verifica `packages/svg-avatar/tests/boundaries.spec.js`. Il Dockerfile li copia nello stage `assets`. Cambiare ordine, pesi o forme delle varianti esistenti in `parts.json` cambia gli avatar già assegnati e fa fallire `tests/compat.spec.js`: è voluto, non rigenerare `compat.json`. L'editor non deve mai cercare elementi nella pagina ospite (`document.querySelector`…): riceve l'elemento in cui montarsi.
- `dev/` (come `skills/` e `.claude/`) è solo sviluppo: **mai referenziata** da `app/`, `resources/`, `routes/`, `public/` o dalla config, ed esclusa dall'immagine Docker tramite `.dockerignore`.
