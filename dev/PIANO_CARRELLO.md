# Piano: carrello persistente lato server (utenti e guest)

## Stato di avanzamento
> Fonte di verità sullo stato dei lavori. Prima di ogni step rileggere questa sezione; a fine step aggiornarla subito.
> Regola: uno step è completato solo con i test previsti verdi; i problemi aperti restano qui finché non sono risolti.

| Voce | Valore |
|---|---|
| Piano | approvato il 2026-11-24 |
| Step corrente | 8b — Rimozione del vecchio sistema: **in attesa di conferma dell'utente** (fine del periodo di prova) |
| Ultimo step completato | 8a — Attivazione (`CART_SERVER=true` di default) e documentazione |
| Stato dei test | PHP 345 ✓ · JS 375 ✓ · `npm run build` ✓ · `route:list` ✓ (83 rotte) · prove headless sul server principale col flag acceso ✓ |
| Prossima attività | Dopo il periodo di prova e la conferma dell'utente, Step 8b: togliere modalità locale (`cart` locale, mutations `addPlate`/`removePlate`/`emptyCart`, ramo `local` delle azioni, `cartLines(state.cart)`), `POST /api/cart/import` + `CartService::import` + `importLocalCart`, flag `CART_SERVER` e il 404 di `EnsureCartToken`, test della modalità locale (`store.spec.js`, helper di `cart.spec.js`, fixture 404 dello smoke); aggiornare AGENTS/README/mappa. Rollback fino ad allora: `CART_SERVER=false` |
| Problemi aperti | nessuno bloccante. **Da sapere**: la potatura parte solo se lo scheduler gira (`php artisan schedule:run` ogni minuto via cron, oppure `schedule:work`); né `docker-compose.yml` né i Dockerfile lo avviano oggi (documentato nello Step 8a). **Preesistente, fuori piano**: le altre rotte API con `throttle:N,M` senza nome (`/api/me`, ricerca, recensioni, `/api/payments/config`, `POST /api/orders`) condividono ancora un unico contatore per client; il carrello ora ne ha uno suo |

> Nota operativa: il PHP del Codespace non ha `pdo_mysql`; i comandi sul DB di sviluppo vanno lanciati nel container: `docker exec deliveboo-app-1 php artisan …`. `php artisan test` (SQLite in memoria) gira anche dall'host.
> Prova manuale col flag acceso senza toccare il `.env`: `docker exec -d -w /app/public -e CART_SERVER=true deliveboo-app-1 php -S 0.0.0.0:8001 /app/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`, poi aprire `http://<IP del container>:8001` (`docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' deliveboo-app-1`). `artisan serve` non va bene: non passa `CART_SERVER` ai worker. A fine prova fermare il server e cancellare i carrelli di prova.

### Problemi risolti
| Data | Problema | Correzione | Verifica |
|---|---|---|---|
| 2026-11-24 | (Step 8a) Le rotte `/api/cart*` con `throttle:120,1` senza nome condividevano il contatore di tutte le rotte API con throttle anonimo: pochi click sul carrello → 429 su `/api/payments/config` e `POST /api/orders` | contatore proprio `throttle:120,1,cart` in `routes/api.php` | `CartTest::testCartRequestsDoNotUseUpTheCheckoutLimit` (fallisce senza correzione, verificato); prova headless senza 429 |
| 2026-11-24 | (Step 8a, logica dello Step 6) Modifiche mostrate ma perse se la pagina si scarica con la coda piena (reload, chiusura, link a una pagina Blade come «Accedi») | su `pagehide` le modifiche ancora in `cartOverrides` partono con `fetch` `keepalive` (`sendCartItemOnUnload` in `api/cart.js`, quantità assolute → idempotenti). Limite noto: in caso di «svuota» + aggiunta di un altro ristorante e uscita immediata l'ordine delle richieste keepalive non è garantito (l'aggiunta può ricevere 409) | test `cartServer.spec.js` «leaving the page»; prova headless con reload immediato ripetuta 3 volte: 3 porzioni su 3 |

### Registro degli step
| Step | Stato | Data | Cosa è stato fatto | File | Test ed esito |
|---|---|---|---|---|---|
| 0 | ✅ completato | 2026-11-24 | piano copiato qui con stato di avanzamento; test di caratterizzazione del carrello visto dal cliente (aggiunta/rimozione da `Plate`, piatto non ordinabile, warning altro ristorante, svuota/torna, righe e totale di `OrderSummary`, pagina `Cart`, badge `Header`, payload `buildOrder`). Gli helper `seed`/`portions` in cima al file sono l'unico punto che conosce la forma del carrello: si aggiornano negli step successivi, le aspettative no | `dev/PIANO_CARRELLO.md`, `tests/js/cart.spec.js` | `npm run test:js` 346/346 ✓ (10 nuovi), `php artisan test` 303/303 ✓ (baseline) |
| 1 | ✅ completato | 2026-11-24 | getter `cartLines` (`{ plate, quantity }`), `cartCount`, `cartAmount`, `cartRestaurant` (`{ id, slug }`/null), `portionsOf(plateId)`; nessun componente legge più `state.cart`. Rimossi `state.total`, `changeTotal`, `cartLenght`, `cartTotal`. `canAddPlate(restaurant, plate)` ora riceve il ristorante del carrello; `buildOrder(lines, …)` genera lo stesso payload (un id per porzione). Risolta la nota dello Step 0: `Warning.cartSlug` non va più in errore a carrello vuoto. `Plate.removeOne` passa il piatto del menu (la mutation confronta per id) | `resources/js/store.js`, `resources/js/utils/cart.js`, `resources/js/utils/index.js`, `components/{Header,OrderSummary,Plate,Warning}`, `pages/{Cart,RestMenu,Payment}` (`.logic.js`/`.vue`, `Payment.utils.js`), test `tests/js/{store,cart,payment,app.smoke}.spec.js` | `npm run test:js` 346/346 ✓; `php artisan test` 303/303 ✓; `npm run build` ✓; verifica manuale con Chromium headless su `php artisan serve`: aggiunta/stepper dal menu, reload (carrello conservato), warning altro ristorante + «torna», pagina carrello (righe, totale, −), importo del bottone di pagamento, svuota carrello; nessun errore/warning in console |
| 2 | ✅ completato | 2026-11-24 | azioni `setQuantity({ plate, quantity })` (quantità assoluta, 0 = rimozione, la riga mantiene la posizione), `addOne(plate)` (controlla il ristorante: se diverso apre il warning e restituisce `false`), `removeOne(plate)`, `clearCart`; implementate con le mutations locali `addPlate`/`removePlate`/`emptyCart`, che ora usa solo lo store. `Plate`, `OrderSummary`, `Cart`, `Warning`, `Payment` usano `mapActions`; la regola «un ristorante per ordine» è passata dal componente `Plate` all'azione `addOne` (punto unico, pronto per il 409 del server) | `resources/js/store.js`, `components/{Plate,OrderSummary,Warning}`, `pages/{Cart,Payment}`, `tests/js/store.spec.js` | `npm run test:js` 349/349 ✓ (3 nuovi sulle azioni); `php artisan test` 303/303 ✓; `npm run build` ✓; verifica headless ripetuta ✓, nessun errore in console |
| 3 | ✅ completato | 2026-11-24 | migration `carts` (`user_id` unique cascade, `guest_token` char(64) unique, `restaurant_id` nullOnDelete, `version`, `notice` json, timestamps + indice su `updated_at`) e `cart_items` (unique `cart_id`+`plate_id`, cascade); modelli `App\Cart` (nessun campo mass-assignable, cast `notice` array, `items()` in ordine di inserimento, `isGuest()`), `App\CartItem` (`plate()` con `withTrashed`), `User::cart()` | `database/migrations/2026_11_28_000000_create_carts_tables.php`, `app/Cart.php`, `app/CartItem.php`, `app/User.php`, `tests/Feature/CartModelTest.php` | `php artisan test` 310/310 ✓ (7 nuovi: righe in ordine, mass assignment bloccato, un carrello per account, una riga per piatto, carrello guest, piatto soft-deleted, cascade alla cancellazione dell'account); `npm run test:js` 349 ✓ (front non toccato); `migrate` → `migrate:rollback --step=1` → `migrate` ✓ nel container senza toccare gli altri dati; `route:list` ✓ |
| 4 | ✅ completato | 2026-11-24 | `config/cart.php` (`CART_SERVER` default false, cookie `foodsy_cart` 400 giorni, limiti 20 per riga / 100 totali); `CartService` (`find`, `findOrCreate` con recupero su violazione dell'indice unico, `setQuantity` assoluta, `clear`, `import`, `pullNotices`, `snapshot`; ogni scrittura in transazione con `lockForUpdate` + ricalcolo di ristorante e `version`); `CartException` (codici `other_restaurant` 409, `unavailable`/`not_accepting`/`too_many` 422); middleware `cart.token` (`EnsureCartToken`: 404 a flag spento, token casuale di 40 caratteri, cookie HttpOnly/Lax/Secure se https, rinnovato a ogni risposta; nel DB solo SHA-256); `Api\CartController` (`GET /api/cart`, `PUT`/`DELETE /api/cart/items/{plate}`, `DELETE /api/cart`, `POST /api/cart/import`), tutte con `active` + `throttle:120,1`; ogni risposta `{ cart, notices }`, gli errori aggiungono `code` e `message`. Testi `api.cart.{otherRestaurant,unavailable,tooMany}` in 5 lingue. Scelte di dettaglio: il campo `version` nella request non è previsto (last-write-wins per riga, come da Fase 5); il carrello appartiene all'account per **tutti** i ruoli loggati; un path non numerico dà 405 (catch-all GET della SPA), non 404 | `config/cart.php`, `app/Carts/CartService.php`, `app/Carts/CartException.php`, `app/Http/Middleware/EnsureCartToken.php`, `app/Http/Controllers/Api/CartController.php`, `routes/api.php`, `bootstrap/app.php` (alias `cart.token`), `resources/js/utils/strings.json`, `tests/Feature/Api/CartTest.php` | `php artisan test` 327/327 ✓ (17 nuovi in `CartTest`: flag spento, prima visita senza righe nel DB, cookie e hash, quantità assoluta idempotente, rimozioni idempotenti, 409 e `replace`, piatto non ordinabile solo in diminuzione, piatto eliminato, 404/422 di validazione, limite totale, prezzi sempre dal menu, carrello dell'account separato dal guest, ban 403, ristoratore bannato, import solo su carrello vuoto, creazione concorrente, throttle); `StringKeysTest`/`LocaleTest` ✓; `npm run test:js` 349 ✓; `route:list` ✓ |
| 5 | ✅ completato | 2026-11-24 | `CartService::merge(User, token)` con le regole della Fase 3 (guest vuoto → resta il carrello dell'account; account vuoto → il carrello guest passa all'account; stesso ristorante → unione con quantità massima entro i limiti, notice `merged`; altro ristorante → vince il guest, notice `replaced` col nome del ristorante sostituito; piatti non più ordinabili scartati, notice `removed`; account demo → sempre il carrello guest, anche vuoto, senza notice), in transazione con `lockForUpdate` su entrambi i carrelli, idempotente. Listener `App\Listeners\MergeGuestCart` su `Illuminate\Auth\Events\Login` (password, Google, registrazione, remember-me), inattivo a flag spento. `CartService::clearFor()` chiamato da `OrderController` solo dopo `PAID`, in try/catch con log. **Scostamento dal piano**: il listener non è registrato in `AppServiceProvider` ma dall'event discovery di Laravel (`app/Listeners`): registrarlo anche a mano lo farebbe girare due volte | `app/Carts/CartService.php`, `app/Listeners/MergeGuestCart.php`, `app/Http/Controllers/Api/OrderController.php`, `tests/Feature/CartMergeTest.php`, `tests/Feature/Api/CustomerOrdersTest.php` | `php artisan test` 342/342 ✓ (12 nuovi in `CartMergeTest`: i 6 casi di merge, limiti, idempotenza, login Google, registrazione, flag spento, notice mostrata una volta; 3 nuovi in `CustomerOrdersTest`: pagamento riuscito svuota carrello guest e account, rifiutato lo lascia intatto); `AuthTest`/`GoogleAuthTest` invariati ✓; `npm run test:js` 349 ✓; `route:list` ✓ |
| 6 | ✅ completato | 2026-11-24 | `api/cart.js` (`fetchCart`, `putCartItem`, `deleteCartItem`, `clearServerCart`, `importCart`); `utils/cartSync.js` (`createCartQueue`: una richiesta per volta, coalescenza per piatto, `reset` per lo svuotamento; `withOverrides`, `snapshotLines`, `isNewerSnapshot`); store con modalità `pending`/`server`/`local` (`GET /api/cart` 200 = server, 404 = locale), `serverCart` (ultimo carrello confermato) + `cartOverrides` (modifiche in viaggio, mostrate subito), azioni create per store (`createCartActions`: `loadCart`, `importLocalCart`, `setQuantity`, `addOne`, `removeOne`, `clearCart`, `watchCart`, `cartIdle`); le scritture aspettano il primo caricamento; errori: 409 → riallinea e apre `Warning`, 422 con codice → notice col messaggio del server, rete → notice offline, 419 → GET e un secondo tentativo, 403 → `loadUser`; sessione scaduta (risposta `owner: guest` con utente in store) → `user = null` + notice; `BroadcastChannel("foodsy-cart")`, ricarica su `visibilitychange` (se la coda è ferma) e su `online`; persistenza in localStorage senza `serverCart`, `cartMode`, `cartOverrides`, `cartNotices`, `cartOffline`, `warn` (il carrello locale resta solo per la modalità locale e viene svuotato dopo l'import riuscito). Nuovo componente `CartNotice` (in `App`, `aria-live`, 8 s o chiusura). Pagina `Cart`: «caricamento» finché il server non risponde, badge «non disponibile» e checkout bloccato per righe non ordinabili. Testi `site.cartNotice.*`, `site.cart.loading`, `site.cart.unavailableLines` in 5 lingue. **Scostamento dal piano**: in `Warning` «nuovo ordine» resta «svuota la borsa» (`clearCart` → `DELETE /api/cart`) come oggi, invece di `replace` + aggiunta: i test di caratterizzazione dello Step 0 fissano questo comportamento; `replace` resta disponibile nell'API | `resources/js/api/{cart,index}.js`, `resources/js/utils/{cartSync,index}.js`, `resources/js/store.js`, `resources/js/App.{vue,logic.js}`, `resources/js/components/CartNotice/*`, `resources/js/components/index.js`, `resources/js/pages/Cart/Cart.{vue,logic.js,css}`, `resources/js/utils/strings.json`, `tests/js/{cartServer,cart,app.smoke}.spec.js` | `npm run test:js` 374/374 ✓ (23 nuovi in `cartServer.spec.js`, 2 in `cart.spec.js`); `php artisan test` 342 ✓; `npm run build` ✓; prova headless col flag acceso: raffica di click (ottimistico 4, poi 5 dopo le risposte), reload (carrello dal server), localStorage senza carrello del server, cookie HttpOnly cifrato da 400 giorni, seconda tab aggiornata, warning + nuovo ordine, offline con notice, login con carrello guest (account vuoto: lo prende), logout (carrello guest vuoto), login con carrello di altro ristorante (notice «replaced» mostrata una sola volta), import del vecchio localStorage con prezzi dal DB; nessun errore in console. Col flag spento: prova dello Step 1 ripetuta ✓. Carrelli di prova cancellati dal DB di sviluppo |
| 7 | ✅ completato | 2026-11-24 | `App\Cart` con `MassPrunable` e `prunable()` (carrelli guest fermi da più di `Cart::GUEST_DAYS` = 30 giorni; qualsiasi carrello vuoto fermo da più di un giorno; le righe vanno via in cascata; un carrello di account con righe resta); `Schedule::command('model:prune', ['--model' => [Cart::class]])->daily()` in `routes/console.php` | `app/Cart.php`, `routes/console.php`, `tests/Feature/CartModelTest.php` | `php artisan test` 344/344 ✓ (2 nuovi: potatura con `travel()` su 5 casi, schedulazione giornaliera); `schedule:list` ✓; `model:prune --pretend` nel container ✓ (nessun record) |
| 8a | ✅ completato | 2026-11-24 | `CART_SERVER` true di default (`config/cart.php`) e in `.env.example`; durante le prove col flag acceso trovati e corretti due problemi (vedi «Problemi risolti»: contatore di throttle condiviso col checkout; modifiche perse allo scaricamento della pagina → `fetch` keepalive su `pagehide`). Documentazione: `AGENTS.md` (riga «Carrello» in «Dove mettere cosa», voce «Carrello persistente» nelle Trappole, compresa la regola sui throttle senza nome), `README.md` (come funziona, funzionalità, tabella API, database, scheduler nel deploy), `dev/mappa.html` solo blocco DATI (21 righe `F(...)` nuove e 5 aggiornate; tabelle `carts`/`cart_items` in `DB_TABLES`, 4 collegamenti in `DB_LINKS`, 2 relazioni in `DB_RELS`, gruppi e `viewBox` allargati, `opening_hours` e `platform_audit_logs` spostati per non incrociare le linee) | `config/cart.php`, `.env.example`, `routes/api.php`, `resources/js/api/{cart,index}.js`, `resources/js/store.js`, `tests/Feature/Api/CartTest.php`, `tests/js/cartServer.spec.js`, `AGENTS.md`, `README.md`, `dev/mappa.html` | `php artisan test` 345/345 ✓; `npm run test:js` 375/375 ✓; `npm run build` ✓; `route:list` ✓; prove headless sul server principale (ora col flag acceso): flusso base ×2 senza errori né 429, 17 scenari dello Step 6 ✓; mappa: dati validati (ogni `rel`/`go` esiste in `FILES`), nessun errore JS, schema controllato a vista. Carrelli di prova cancellati dal DB di sviluppo |
| 8b | ⏸ in attesa | | rimozione di modalità locale, import, flag e codice morto **dopo il periodo di prova**, su conferma dell'utente | | |

## Context
Oggi il carrello vive **solo nel browser**: è l'array `state.cart` dello store Vuex, salvato in `localStorage` (chiave `vuex`) da `vuex-persistedstate` (il "portfolio" della richiesta = questa persistenza locale). Il server non ne sa nulla fino a `POST /api/orders`. Obiettivo: il database diventa la fonte autorevole del carrello per utenti autenticati (carrello legato a `user_id`) e per guest (carrello legato a un identificativo anonimo in cookie a lunga scadenza), con merge esplicito al login. Nessuna modifica al codice prima dell'approvazione; il piano verrà copiato in `dev/PIANO_CARRELLO.md` (convenzione dei piani in `dev/`) come primo step operativo.

---

## Fase 1 — Analisi dell'esistente

### Dove sta il carrello
| Elemento | File | Cosa fa |
|---|---|---|
| Stato | `resources/js/store.js` | `cart: []` = **una voce per porzione** (oggetto piatto intero copiato dal menu: id, name, price, img, restaurant_id, restaurant_slug, orderable…); `total` (cache del totale); `warn` (modale "due ristoranti") |
| Mutations | `store.js` | `addPlate` (push), `removePlate` (toglie l'**ultima** porzione con quell'id), `emptyCart`, `changeTotal`, `changeWarn` |
| Getter | `store.js` | `cartLenght` (= numero di porzioni) |
| Persistenza | `store.js` → `createPersistedState({ reducer })` | tutto lo stato tranne `user` in `localStorage["vuex"]`; nessun sessionStorage né cookie per il carrello |
| Funzioni pure | `resources/js/utils/cart.js` | `countPortions`, `uniquePlates`, `cartTotal`, `canAddPlate` (regola "un solo ristorante per ordine") |
| API | nessuna per il carrello; `resources/js/api/orders.js` → `postOrder` solo al checkout | |
| Nessuna sincronizzazione | — | né tra tab (ogni tab riscrive `localStorage` con il proprio stato), né con il server, né con l'account |

### Componenti UI che leggono/scrivono il carrello
- **Scrivono**: `components/Plate` (`addToCart` con `canAddPlate` → `addPlate` o `changeWarn`; `removeOne`), `components/OrderSummary` (stepper `addPlate`/`removePlate`), `pages/Cart` (stepper + `emptyCart`), `components/Warning` (`emptyCart` / torna al ristorante del carrello), `pages/Payment` (`emptyCart` dopo pagamento riuscito).
- **Leggono**: `components/Header` (badge `cartLenght` + importo), `pages/RestMenu` (barra mobile con conteggio + importo), `pages/Payment` (importo, `buildOrder`), `Warning` (`cart[0].restaurant_slug`).
- Route: `/your-cart` (`routes.js`, nome `cart`), link da `MenuMobile`, `Footer`.

### Flusso end-to-end attuale
```
Plate.addToCart ── canAddPlate? ──no──▶ changeWarn ▶ Warning (svuota / torna al ristorante)
       │ sì
       ▼
commit addPlate(plate)  → state.cart.push(copia del piatto del menu, prezzo incluso)
       ▼
vuex-persistedstate     → localStorage["vuex"] (sincrono, a ogni mutation)
       ▼
reload / nuova visita   → createAppStore() ripristina lo stato da localStorage (prezzi congelati)
       ▼
Cart / OrderSummary     → uniquePlates + countPortions; Cart.logic ricalcola `total` (watch deep)
       ▼
Payment.postForm        → buildOrder: { restaurant_id: cart[0].restaurant_id, plates: [id, id, …], customer…, nonce }
       ▼
POST /api/orders        → Api\OrderController@store: ricalcola l'importo dai prezzi del db,
                          controlla orderable/ristorante, crea l'ordine, addebita
       ▼
risposta OK             → commit emptyCart → /success
```
Login/logout: login, registrazione e Google sono **pagine Blade** (`Auth\LoginController`, `RegisterController`, `GoogleController`) con redirect a pagina intera; l'SPA ricarica e chiama `loadUser` (`GET /api/me`). Logout SPA = `POST /logout` + `window.location.assign("/")`. Il carrello in localStorage **sopravvive a login e logout**: chi usa un PC condiviso vede il carrello del precedente utente.

### Dipendenze e coupling
- **Forma "una voce per porzione"** usata da 6 componenti, da `utils/cart.js`, da `buildOrder` (`plates: [id ripetuti]`) e dal backend (`array_count_values`).
- `removePlate` usa l'id ma `Plate.removeOne` cerca ancora "l'oggetto nel carrello" (commento storico).
- `Warning` dipende da `cart[0].restaurant_slug` (campo aggiunto dall'API menu).
- Prezzi e `orderable` **congelati** al momento dell'aggiunta: il carrello mostra prezzi vecchi; il server corregge solo al pagamento (`api.order.notOnMenu` / `notAvailable`).
- Stato `total` duplicato (calcolato in `Cart`, mai letto altrove in modo affidabile; gli altri usano `cartTotal`).
- Test: `tests/js/store.spec.js` (incl. "persists the state in localStorage"), `tests/js/payment.spec.js`, `tests/js/app.smoke.spec.js`; backend `tests/Feature/Api/PublicApiTest.php`, `CustomerOrdersTest.php`.

### Flussi speciali
- Regola "un ristorante per ordine" (client + server).
- Ruoli: `orders.user_id` solo per `customer`; ristoratori e admin ordinano come guest (`OrderController`).
- Account demo **condivisi** (`config/demo.php`, Mario Rossi): con un carrello per utente, visitatori diversi vedrebbero lo stesso carrello.
- Cliente loggato che registra un account partner (`RegisterController@register` chiude la sessione cliente e apre la nuova).
- Ban: middleware `active` (403 + logout) già su `/api/me` e `/api/orders`.
- Sessione Laravel su `file`, `lifetime` 120 min, nessuna tabella `sessions`/`cache`.

### Riutilizzabile
- `statefulApi()` Sanctum: le chiamate `/api` del sito hanno già sessione, cookie cifrati (`EncryptCookies`) e CSRF automatico (axios `X-XSRF-TOKEN`).
- `Plate::isOrderable()` / `scopeOrderable`, `Plate` con `SoftDeletes` e cast `price => float`.
- Pattern di concorrenza `RestaurantRating::write` (transazione + `lockForUpdate` + ricalcolo da zero).
- Payload del menu in `Api\RestaurantController@restMunu` (campi piatto per la UI).
- `EnsureActive`, throttle, `__()` + `strings.json` per i messaggi, `MeController::payload` come modello di risposta minimale.
- `utils/cart.js` e componenti UI (stepper `QuantityStepper`) restano: cambia solo la sorgente dei dati.

---

## Fase 2 — Gap analysis

| Componente attuale | Ruolo attuale | Ruolo futuro | Modifiche necessarie | Rischi |
|---|---|---|---|---|
| `store.js` `state.cart` (array porzioni) | fonte di verità | **copia** dello snapshot server `{ items:[{plate_id, quantity, plate}], restaurant, count, total, version, owner }` | nuova forma + getter `cartLines`, `cartCount`, `cartAmount`; mutation `setCart(snapshot)` | regressione su tutti i lettori del carrello |
| `addPlate`/`removePlate`/`emptyCart` (mutations) | scrittura diretta | azioni `async` (`setQuantity`, `addOne`, `removeOne`, `clearCart`) con update ottimistico + chiamata API serializzata | sostituite nei componenti con `mapActions` | doppio click, ordine delle risposte |
| `vuex-persistedstate` | persistenza del carrello | non persiste più `cart`/`total`/`warn` (reducer); resta per tipi selezionati ecc. | modificare `persisted`; import una tantum del carrello legacy | perdita dei carrelli esistenti se l'import fallisce |
| `utils/cart.js` | calcoli su porzioni | calcoli su righe (`quantity`) + `canAddPlate` sulla riga/ristorante | riscrittura funzioni pure + test | nomi usati in più posti |
| `Plate`, `OrderSummary`, `Cart`, `Warning`, `Header`, `RestMenu` | leggono/scrivono array | leggono getter, chiamano azioni | piccole modifiche per componente | UI incoerente se fatte a metà |
| `Payment` + `buildOrder` | invia `plates` ripetuti | invia lo stesso payload dalle righe (compatibile); il server svuota il carrello a ordine pagato | `buildOrder` da `cartLines`; `OrderController` chiama `CartService::clearFor()` | carrello svuotato due volte (innocuo) |
| `Api\OrderController` | unico punto server | invariato nel contratto (`200 + {errors}`) + svuota il carrello dopo `PAID` | 1 chiamata in più | non deve mai far fallire un pagamento riuscito (try/catch) |
| Login Blade (password, Google, registrazione) | nessun effetto sul carrello | evento `Login` → **merge** guest → utente | listener `MergeGuestCart` | merge doppio (remember-me) → deve essere idempotente |
| Logout | nessun effetto | sessione invalidata, cookie guest resta → l'SPA mostra il carrello guest (vuoto dopo il merge) | nessuna modifica backend; ricarica già presente | — |
| — (assente) | — | tabelle `carts`, `cart_items`; `App\Cart`, `App\CartItem`; `App\Carts\CartService`; `Api\CartController`; middleware cookie guest; `api/cart.js` | nuovi file | race condition, carrelli orfani |
| Test JS/PHP | caratterizzano localStorage | caratterizzano API + sync | nuovi `CartTest`, `CartMergeTest`, `cart.spec.js`; aggiornare `store.spec.js` | — |

---

## Fase 3 — Strategia di merge (scelta motivata)

Strategie valutate: (a) merge dei prodotti, (b) prevale l'utente, (c) prevale il guest, (d) chiedere all'utente.
Vincolo decisivo: **un carrello = un solo ristorante**, quindi un merge puro tra ristoranti diversi è impossibile.

**Proposta: "il guest recente vince, stesso ristorante si fonde"**
1. Carrello guest vuoto/assente → si usa il carrello utente (nessun cambio).
2. Carrello utente vuoto/assente → il carrello guest passa all'utente (`user_id` impostato, `guest_token` azzerato: nessuna copia).
3. Stesso ristorante → unione delle righe; per un piatto presente in entrambi **quantità = max(guest, utente)** (non la somma: tipicamente è lo stesso ordine ricostruito su un altro dispositivo; la somma raddoppierebbe), limiti massimi applicati.
4. Ristoranti diversi → **vince il guest** (è l'intenzione più recente: l'utente ha appena composto quel carrello ed è andato al login per pagarlo); il carrello utente precedente viene sostituito e la risposta successiva di `GET /api/cart` porta una notice `replaced` ("Il carrello di <ristorante> è stato sostituito") mostrata una volta.
5. Piatti non più ordinabili o eliminati → rimossi al merge con notice `removed`.
6. **Account demo** (`User::isDemoAccount()`): sempre caso 4 (vince il guest, anche vuoto) — sono condivisi tra visitatori e non devono mostrare il carrello di altri.

Perché non (b): chi aggiunge piatti da guest e poi accede per pagare perderebbe proprio quello che stava comprando. Perché non (d): il login è una pagina Blade con redirect per ruolo, un dialogo intermedio complica il flusso per un caso raro; la notice rende comunque esplicito cosa è successo.
Il merge è **server-side nel listener dell'evento `Illuminate\Auth\Events\Login`**: copre login con password, Google, registrazione e remember-me senza toccare i controller; è idempotente (dopo il merge il carrello guest non esiste più).

---

## Fase 4 — Architettura finale

### Modello dati (nuova migration `2026_11_28_000000_create_carts_tables`)
**`carts`**: `id`, `user_id` (FK users, **unique**, nullable, `cascade` on delete), `guest_token` (char(64), **unique**, nullable: SHA-256 del token del cookie, mai il token in chiaro), `restaurant_id` (FK nullable, `set null`), `version` (unsigned int, +1 a ogni scrittura), `notice` (json nullable: notice da mostrare una volta), timestamps (`updated_at` = ultima attività). Check applicativo: esattamente uno tra `user_id` e `guest_token`.
**`cart_items`**: `id`, `cart_id` (FK cascade), `plate_id` (FK plates), `quantity` (unsigned smallint), timestamps, **unique(`cart_id`, `plate_id`)**.
Nessun prezzo nel carrello: sempre letto da `plates` (il prezzo pagato resta `order_plate.unit_price`).
`GuestIdentity` come tabella separata **non serve** ora (il token vive sulla riga `carts`); diventa utile solo se in futuro l'identità anonima servirà ad altro (preferiti, analytics) — variante annotata nel piano.

Modelli: `App\Cart` (`#[Fillable]`, `user()`, `items()`, `restaurant()`, trait `MassPrunable`), `App\CartItem` (`plate()` con `withTrashed` per poter mostrare/rimuovere piatti eliminati). Servizio `App\Carts\CartService` (unico punto di scrittura, come `RestaurantRating`): `resolve(Request)`, `setQuantity`, `clear`, `merge(guestCart, user)`, `snapshot(cart)`.

### Identità guest
- Middleware `App\Http\Middleware\EnsureCartToken` sulle sole rotte `/api/cart*`: se manca il cookie `foodsy_cart` genera `Str::random(40)` e lo accoda (400 giorni = massimo accettato dai browser, `HttpOnly`, `SameSite=Lax`, `Secure` fuori da local, cifrato da `EncryptCookies`). La riga `carts` si crea **pigramente** alla prima scrittura (i bot e le visite senza acquisti non creano righe).
- A ogni scrittura il cookie viene rinnovato (scadenza scorrevole).

### API (tutte sotto `active` + `throttle:120,1`; risposte con status HTTP corretti: nuova API, nessun vincolo legacy)
| Nome | Metodo e path | Request | Note |
|---|---|---|---|
| GetCart | `GET /api/cart` | — | carrello dell'utente se loggato, altrimenti del token guest; crea nulla; consuma la `notice` |
| AddItem / UpdateItem | `PUT /api/cart/items/{plate}` | `{ quantity: int 0..20, replace?: bool, version?: int }` | **quantità assoluta** (idempotente). `quantity: 0` = rimozione. `replace: true` svuota un carrello di altro ristorante e aggiunge |
| RemoveItem | `DELETE /api/cart/items/{plate}` | — | idempotente (204-like: risponde col carrello anche se la riga non c'era) |
| ClearCart | `DELETE /api/cart` | — | idempotente |
| ImportLegacy | `POST /api/cart/import` | `{ items: [{ plate_id, quantity }] }` | solo transizione: importa il carrello di localStorage **solo se il carrello server è vuoto**; rimosso a migrazione conclusa |
| MergeCart | nessun endpoint pubblico | — | `CartService::merge` chiamato dal listener `Login` (e utilizzabile nei test) |

**Response (sempre lo snapshot completo):**
```json
{ "cart": { "owner": "guest|user", "version": 7,
  "restaurant": { "id": 3, "slug": "…", "name": "…" },
  "items": [{ "plate_id": 12, "quantity": 2, "line_total": 25.0,
              "plate": { "id": 12, "name": "…", "price": 12.5, "img": "…", "orderable": true, "restaurant_id": 3, "restaurant_slug": "…" } }],
  "count": 2, "total": 25.0 },
  "notices": [{ "code": "replaced|removed|merged|unavailable", "params": {…} }] }
```
**Validazioni**: piatto esistente e non eliminato (404), `isOrderable()` per aumentare la quantità (422 `cart.unavailable`; ridurre/rimuovere è sempre permesso), quantità 0..20 per riga e ≤ 100 porzioni totali (stesso limite di `POST /api/orders`), stesso ristorante del carrello salvo `replace` (409 `{ code: "other_restaurant", cart }` → la UI apre `Warning`), ristorante di un ristoratore bannato non accetta aggiunte (422, come `api.order.notAccepting`).
**Errori**: 403 ban (`active`, logout), 404, 409, 422 (`{ message, errors }` standard Laravel), 429 throttle, 419 CSRF scaduto (la UI ricarica il token con un GET e ritenta una volta). Ogni errore include lo snapshot corrente quando disponibile, così il client si riallinea.
Testi: nuove chiavi `api.cart.*` e `site.cart.notices.*` in `strings.json`, 5 lingue.

### Checkout
`POST /api/orders` resta identico (payload `plates` ripetuti generato dalle righe, server sempre autorevole sui prezzi). Dopo `PAID`, `OrderController` chiama `CartService::clearFor($request)` in try/catch (un errore non annulla mai un pagamento). Il client svuota comunque lo stato locale. Evoluzione futura possibile (non in questo piano): ordine da `cart_id` invece che da `plates`.

### Frontend
- `resources/js/api/cart.js`: `fetchCart`, `putCartItem`, `deleteCartItem`, `clearCart`, `importCart`.
- Store: `cart` = snapshot server (fuori dal reducer di persistenza), `cartPending`, `cartNotice`; getter `cartLines`, `cartCount`, `cartAmount`, `cartRestaurant`, `portionsOf(plateId)`; mutation `setCart`, `applyOptimistic`; azioni `loadCart`, `setQuantity(plate, qty)`, `addOne`, `removeOne`, `clearCart`.
- **Coda di sincronizzazione** (`utils/cartSync.js`, pura e testabile): una richiesta in volo per volta; le modifiche successive allo stesso piatto si **coalescono** nell'ultima quantità; ogni risposta sostituisce lo snapshot solo se `version` ≥ corrente; in errore si torna all'ultimo snapshot confermato + notice.
- **Inizializzazione**: `App.logic` `created` → `loadUser` e `loadCart` in parallelo; finché il primo snapshot non arriva i componenti mostrano lo stato "caricamento" (badge senza numero), mai un carrello vuoto finto.
- **Login**: avviene su pagina Blade → il merge è già fatto dal server → al ritorno nell'SPA `loadCart` riceve il carrello utente + eventuale notice. Nessuna logica client.
- **Guest**: il cookie è HttpOnly e gestito dal server; il client non lo legge mai.
- **Refresh**: stato ricostruito da `GET /api/cart` (non più da localStorage).
- **Logout**: già fa `window.location.assign("/")` → `GET /api/cart` risponde col carrello guest (vuoto dopo il merge): sul PC condiviso il carrello dell'utente non resta visibile.
- **Sessione scaduta**: le richieste diventano guest; se `store.user` era valorizzato e lo snapshot risponde `owner: "guest"`, lo store imposta `user = null` e mostra la notice "sessione scaduta, accedi per ritrovare il carrello" (le modifiche fatte intanto vanno nel carrello guest e verranno fuse al login).
- **Multi-tab**: `BroadcastChannel("foodsy-cart")` invia il nuovo snapshot alle altre tab; in più `loadCart` su `visibilitychange` → visibile (fallback per browser senza BroadcastChannel).
- **Offline**: evento `offline` → stepper disabilitati e notice; `online` → `loadCart`. Nessuna coda offline persistente (scelta voluta: il server è la fonte autorevole; una coda offline reintrodurrebbe conflitti).

---

## Fase 5 — Criticità e contromisure
| Criticità | Contromisura |
|---|---|
| Race condition / richieste concorrenti sullo stesso carrello | `CartService` in transazione con `lockForUpdate` sulla riga `carts`; `version` incrementata; unique `(cart_id, plate_id)` + `upsert` |
| Creazione concorrente del carrello (due tab, prima aggiunta) | unique su `user_id` e `guest_token` + `createOrFirst` |
| Doppio click su "aggiungi" | quantità assoluta calcolata dal client a partire dallo stato ottimistico + coda serializzata: due click = +2 voluti, mai richieste duplicate che divergono; il bottone non si blocca |
| Aggiornamenti di quantità simultanei (stessa tab) | coalescenza nella coda: parte solo l'ultima quantità |
| Multi-tab / multi-dispositivo | last-write-wins per riga (quantità assoluta), snapshot con `version`, BroadcastChannel + refetch su focus |
| Risposte fuori ordine | snapshot applicato solo se `version` ≥ corrente |
| Carrelli orfani | `Cart::prunable()`: guest inattivi > 30 giorni, qualsiasi carrello vuoto > 1 giorno; `model:prune` schedulato giornaliero in `routes/console.php` + comando manuale |
| Scadenza/cancellazione cookie | 400 giorni scorrevoli; se perso, nuovo token e carrello vuoto (il vecchio diventa orfano e viene potato); utenti loggati non dipendono dal cookie |
| Furto del cookie guest | token casuale 40 caratteri, cifrato, HttpOnly; nel DB solo l'hash; contiene solo un carrello |
| Login dopo carrello guest | merge nel listener `Login` (Fase 3), idempotente, notice esplicita |
| Account demo condivisi | il guest vince sempre al login demo; nessun carrello demo persistente tra visitatori |
| Registrazione partner da cliente loggato | il carrello resta del cliente; il nuovo account riceve il carrello guest (vuoto) |
| Prezzi/disponibilità cambiati | lo snapshot porta sempre prezzi attuali e `orderable`; righe non ordinabili mostrate disattivate e bloccano il checkout lato UI (il server resta la verifica finale) |
| Piatto eliminato (soft delete) | riga mostrata come "non più disponibile", rimovibile; esclusa dal checkout |
| Offline/online | Fase 4, nessuna coda offline |
| Ban durante l'uso | `active` → 403 + logout; l'SPA ricarica |
| Volume DB | una riga per carrello attivo + righe piatti; potatura giornaliera |

---

## Piano di migrazione (step incrementali, convivenza vecchio/nuovo)
Flag runtime `config/cart.php` → `'server' => env('CART_SERVER', false)`: con il flag spento le rotte `/api/cart*` rispondono 404 e il front resta sul carrello locale; acceso, il front usa il server. Rollback di ogni step lato front = spegnere il flag (nessuna rebuild).

**Step 0 — Documento e test di caratterizzazione**
Obiettivo: `dev/PIANO_CARRELLO.md` (questo piano + sezione Stato); test JS che fissano il comportamento attuale di aggiunta/rimozione/warning/checkout. File: `dev/PIANO_CARRELLO.md`, `tests/js/cart.spec.js`. Impatto nullo. Rischio nullo. Test: `npm run test:js`. Rollback: eliminare i file.

**Step 1 — Getter di lettura (refactor senza cambi di comportamento)**
Obiettivo: i componenti smettono di leggere l'array porzioni. Aggiungere getter `cartLines`, `cartCount`, `cartAmount`, `cartRestaurant`, `portionsOf` calcolati **dall'array attuale**; `Header`, `RestMenu`, `Cart`, `OrderSummary`, `Plate`, `Warning`, `Payment` usano i getter; rimosso `state.total`/`changeTotal`. File: `store.js`, `utils/cart.js`, i 7 `*.logic.js`/`.vue`. Impatto: medio, solo front. Rischio: basso. Test: `store.spec.js`, smoke, verifica manuale menu → carrello → checkout. Rollback: `git revert`.

**Step 2 — Azioni di scrittura (ancora locali)**
Obiettivo: i componenti chiamano azioni `addOne/removeOne/setQuantity/clearCart` invece delle mutations; implementazione locale (stesso array). Il punto di scambio verso il server diventa uno solo. File: `store.js`, componenti scriventi. Rischio basso. Test: JS. Rollback: revert.

**Step 3 — Schema e modelli**
Obiettivo: migration `carts`/`cart_items`, `App\Cart`, `App\CartItem`, relazione `User::cart()`. Nessuna rotta. Impatto: DB (additivo, nessuna tabella esistente toccata). Rischio basso. Test: `php artisan migrate` su DB di sviluppo (senza `fresh`), test PHP verdi. Rollback: `migrate:rollback --step=1`.

**Step 4 — `CartService` + API dietro flag**
Obiettivo: servizio, `EnsureCartToken`, `Api\CartController`, rotte in `routes/api.php`, chiavi `strings.json` (5 lingue), `config/cart.php`. File: `app/Carts/CartService.php`, `app/Http/Controllers/Api/CartController.php`, `app/Http/Middleware/EnsureCartToken.php`, `routes/api.php`, `bootstrap/app.php` (alias), `config/cart.php`, `strings.json`. Impatto: solo nuove rotte, spente di default. Rischio basso. Test: `tests/Feature/Api/CartTest.php` (guest con cookie, utente, quantità assoluta idempotente, 409 altro ristorante, `replace`, limiti, piatto eliminato/non ordinabile, ban, concorrenza simulata con due richieste), `RoutesTest`, `StringKeysTest`, `LocaleTest`. Rollback: flag spento / revert.

**Step 5 — Merge al login + svuotamento dopo l'ordine**
Obiettivo: listener `App\Listeners\MergeGuestCart` su `Login` (attivo solo col flag), regole Fase 3, notice; `OrderController` svuota il carrello dopo `PAID`. File: `app/Listeners/MergeGuestCart.php`, `app/Providers/AppServiceProvider.php` (registrazione evento), `CartService`, `Api/OrderController.php`. Impatto: login e checkout (con flag). Rischio medio (flusso di login). Test: `tests/Feature/CartMergeTest.php` (i 6 casi di merge, password/Google fake/registrazione, idempotenza remember-me, account demo), `CustomerOrdersTest` (carrello svuotato solo se `PAID`, mai su rifiuto), `AuthTest` invariato. Rollback: flag spento.

**Step 6 — Front sul server (convivenza)**
Obiettivo: `api/cart.js`, coda `utils/cartSync.js`, azioni dello Step 2 rimappate sul server quando `GET /api/cart` risponde (404 = modalità locale); import una tantum del carrello di localStorage (`POST /api/cart/import`) poi rimozione di `cart` dal localStorage; `cart`/`warn` fuori dal reducer di persistenza; notice in UI; BroadcastChannel, `visibilitychange`, `online/offline`. File: `api/cart.js`, `api/index.js`, `utils/cartSync.js`, `utils/index.js`, `store.js`, `App.logic.js`, `Warning.logic.js` (409 → modale, "svuota" = `replace`), `Payment.logic.js`, eventuale componente notice. Impatto: alto (tutta l'esperienza carrello). Rischio medio-alto. Test: Vitest con axios mockato (coalescenza, ordine delle risposte, rollback su errore, 409, import legacy, sessione scaduta), smoke, prova manuale in due tab e con DevTools offline, login dopo carrello guest nei 4 scenari. Rollback: `CART_SERVER=false` → il front torna al carrello locale (il localStorage viene ripulito solo dopo import riuscito, quindi nulla va perso).

**Step 7 — Pulizia carrelli orfani**
Obiettivo: `Cart::prunable()` + `Schedule::command('model:prune', ['--model' => Cart::class])->daily()` in `routes/console.php`. Test: test PHP con `travel()`. Rollback: rimuovere lo schedule.

**Step 8 — Attivazione e rimozione del vecchio sistema**
Obiettivo: `CART_SERVER=true` di default (`.env.example`), dopo un periodo di prova rimozione di modalità locale, endpoint `import`, flag e codice morto (`addPlate`/`removePlate`, test localStorage). Aggiornare `AGENTS.md` (righe "Dove mettere cosa" e Trappole), `README.md`, `dev/mappa.html` (solo blocco DATI: `FILES`, nodi/archi del flusso carrello, `DB_TABLES`/`DB_LINKS`/`DB_RELS`). Rischio basso. Rollback: revert del commit di pulizia.

---

## File critici
- Front: `resources/js/store.js`, `resources/js/utils/cart.js`, `resources/js/components/{Plate,OrderSummary,Warning,Header}/*.logic.js`, `resources/js/pages/{Cart,RestMenu,Payment}/*.logic.js`, `resources/js/pages/Payment/Payment.utils.js` (`buildOrder`), `resources/js/App.logic.js`, nuovi `resources/js/api/cart.js`, `resources/js/utils/cartSync.js`.
- Back: `app/Http/Controllers/Api/OrderController.php`, `routes/api.php`, `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`, `routes/console.php`, `resources/js/utils/strings.json`, nuovi `app/Cart.php`, `app/CartItem.php`, `app/Carts/CartService.php`, `app/Http/Controllers/Api/CartController.php`, `app/Http/Middleware/EnsureCartToken.php`, `app/Listeners/MergeGuestCart.php`, `config/cart.php`, migration.
- Riuso: `Plate::isOrderable()`, pattern `RestaurantRating::write`, `EnsureActive`, `MeController::payload` come esempio di payload minimale, `User::isDemoAccount()`.

## Verifica finale
`php artisan test` e `npm run test:js` verdi · `php artisan route:list` · `npm run build` · `php artisan migrate` (senza `fresh`: DB di sviluppo da ~185k ordini da preservare) · prova manuale con `npm run watch` + `php artisan serve`: guest aggiunge → reload → carrello presente; login con carrello guest (stesso ristorante, ristorante diverso, utente vuoto, account demo); due tab aperte; checkout riuscito (carrello vuoto anche dopo reload) e rifiutato (carrello intatto); logout su PC condiviso (carrello utente non visibile); DevTools offline/online.
