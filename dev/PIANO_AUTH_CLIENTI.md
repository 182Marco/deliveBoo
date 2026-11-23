# Login clienti, Google OAuth e backoffice admin `/platform` — piano v2

Stato: **v2 approvata il 2026-11-23 · **piano completato** (F0–F8 chiuse il 2026-11-25); restano solo le prove manuali elencate in fondo · branch `feat/auth-platform`**.
Fonte di verità del lavoro: dopo ogni step aggiornare il **Registro di avanzamento** in fondo (stato, modifiche, file, test, problemi aperti, prossimo step). Per riprendere a contesto azzerato: leggere questo file, poi `git log` e `git status`.
Avatar: progetto indipendente (`AVATAR_IMPLEMENTATION_PLAN.md`); qui si consuma solo il pacchetto pubblico (`svg-avatar`, `svg-avatar/vue`, `<x-avatar>`), senza duplicare la generazione SVG né portarla nel backoffice.

## Decisioni della v1 confermate
- Laravel Sanctum, autenticazione SPA via cookie (SPA e API sullo stesso dominio).
- Laravel Socialite per Google (gratuito, nessun servizio esterno di identità).
- Un'unica tabella `users` con colonna `role` (`customer`, `restaurateur`, `admin`); niente tabelle o guard separati.
- Checkout ospite sempre disponibile; ordini collegabili all'account (`orders.user_id` nullable).
- Una sola pagina di login (redirect in base al ruolo) e una registrazione a schede «Ordino cibo» / «Ho un ristorante», con `?role=restaurateur` dalle CTA partner.
- Avatar: foto Google se presente, altrimenti avatar SVG del pacchetto.
- Opzioni scartate in v1 (token API, Auth0/Firebase/Clerk, guard separati): restano scartate per gli stessi motivi.

## Fatti verificati (repository e DB di sviluppo, analisi in sola lettura del 2026-11-23)
- **`users`** (60 righe, tutte di ristoratori):
  - colonne: `name`, `last_name`, `company_name`, `phone`, `avatar` json, `email` UNIQUE;
  - `vat_number` è **NOT NULL UNIQUE** e `password` **NOT NULL**: va cambiato per i clienti e per Google;
  - mancano `role`, ban e `google_id`.
- **Account esistenti**:
  - **Federico Romiti**: `federico_romiti@gmail.com` / `foodsy-owner` (`UsersTableSeeder`, ristoratore storico);
  - ristoratori generati: password `foodsy-demo` (`config/seeding.php`);
  - nessun admin e nessun utente cliente;
  - nessun "Mario Rossi": negli ordini da ospite ci sono solo altri Rossi sintetici.
- **`orders`** (11.534 righe, dal 2021-01 al 2026-11-21; la storia generata parte dal 2025-01). Stati:
  - `status`: `paid` / `failed` (334);
  - `fulfillment_status`: `delivered` 10.958, `cancelled` 207, `received`/`preparing`/`out_for_delivery` 35;
  - poi `price` DECIMAL (somma delle righe), `delivered_at` e `customer_*`.
  - **Nessun `user_id`, nessuna coordinata**. `customer_address` contiene solo "via + civico", senza città né CAP.
- **`order_plate`** (34.021 righe): `quantity` e `unit_price` pagato. Servono per i piatti più venduti.
- **`restaurants`** (261, tutti con proprietario):
  - `latitude`/`longitude` presenti su **tutti**;
  - `city` (20 comuni), `postal_code` (35), `province`;
  - `segment`, `price_range`, `delivery_fee`, `min_order`, `prep_minutes`; tipi tramite `restaurant_type`.
- **`rates`** (19.322): `restaurant_id`, `rate` e data, senza utente.
- **Codice già presente**:
  - `chart.js` 3 in `package.json`, con `jsBack/chart*.js` usati dalle statistiche partner;
  - `Order::paid()`, il layout `adm-*`, `<x-field>`, la paginazione `foodsy` e i componenti `<x-avatar>` e `<x-icon>`;
  - il gruppo `admin` ha solo il middleware `auth`; `redirectUsersTo('/admin')` sta in `bootstrap/app.php`;
  - SEC-1..SEC-4 sono ancora aperti.

## Cosa cambia rispetto alla v1 e perché
1. Il backoffice `/platform` passa da "predisposizione" a **perimetro attivo**, con 4 tab reali.
2. Nuovi requisiti: **ban** (`banned_at`, `ban_reason`, `banned_by`), **audit log** e **protezioni sugli admin** (niente auto-ban, niente azioni su altri admin, mai zero admin attivi).
3. **Google più rigido**. In v1 un'email esistente collegava l'account in automatico; ora:
   - si collega solo per `google_id`;
   - se l'email esiste già, si collega soltanto dopo il login con password, da "Account";
   - gli admin non possono usare Google;
   - il ruolo di un account esistente non cambia mai.
4. **Account demo** gestiti da config/env, con un box nella pagina di login in 5 lingue.
5. **Mario Rossi**: cliente demo con storico ampio e coerente, base dati del futuro bot.
6. SEC-1..SEC-4 diventano il **primo step**: sono prerequisiti di sicurezza.
7. La 2FA per gli admin (Fortify) resta rinviata e viene segnata tra i problemi aperti.
8. Ordine delle fasi rivisto e stima aggiornata.
9. Decisioni dell'utente D1–D6 (vedi «Decisioni prese»): Leaflet + OSM lazy, email demo di Federico, arricchimento dei clienti, ban che blocca gli ordini, commissione simulata 25%, nessun reset della demo.

## Architettura del backoffice (un'app, un sistema di autorizzazione)
- **Rotte**: un solo gruppo in `routes/web.php`:
  - `Route::prefix('platform')->name('platform.')->middleware(['auth', 'active', 'role:admin'])`;
  - pagine: `/platform` (→ mappe), `/platform/maps`, `/platform/charts`, `/platform/tables/{section}`, `/platform/users…`;
  - endpoint JSON per mappa e grafici sotto `/platform/data/*`, **nello stesso gruppo**. Valgono sessione e CSRF già esistenti, Sanctum non serve.
- **Autorizzazione**:
  - middleware `EnsureRole` (`role:`) e `EnsureActive` (`active`), registrati in `bootstrap/app.php`;
  - `Gate::define('platform')` e `App\Policies\UserPolicy` (`update`, `ban`, `unban`), chiamati con `$this->authorize()` in **ogni** azione di scrittura, oltre ai middleware.
- **Controller**: `App\Http\Controllers\Platform\{MapController, ChartController, TableController, UserController}`. Sono sottili: chiamano servizi condivisi.
- **Servizi** in `app/Analytics/`:
  - `Period`: filtri validati `from`/`to`/`compare`/`city`, con default "ultimi 12 mesi";
  - `OrderMetrics`: definizioni uniche delle metriche, come scope e funzioni;
  - `SqlBuckets`: espressioni mese/settimana/ora per MySQL e per SQLite dei test;
  - `GeoStats`.
  - Le aggregazioni girano solo lato server e il browser riceve serie già pronte.
  - Cache su file di 10 minuti per ogni combinazione di filtri, svuotata quando cambiano gli utenti.
- **Viste**:
  - `layouts/platform.blade.php`: barra in `--forest` con il badge "Platform", 4 tab fisse (Mappe · Grafici · Tabelle · Utenti) e una barra filtri condivisa (`<x-platform.filters>`);
  - stili `plat-*` in un nuovo `resources/css/platform.css`, con i token di `tokens.css` e le regole CSS del progetto;
  - JS in `resources/js/jsBack/platform/` (`maps.js`, `charts.js`, `users.js`), da aggiungere agli `input` di `vite.config.js`. Ogni tab carica **solo il proprio** entry: Leaflet non entra mai nel layout né nelle altre tab (vedi «Tab Mappe: implementazione»).
- **Tabelle**: renderizzate dal server, con ordinamento, ricerca, filtri e paginazione via query string. Le colonne ordinabili stanno in una whitelist e si usa `->links()`. Funzionano anche senza JS e non caricano mai tutti i record.
- **Audit**:
  - tabella `platform_audit_logs`: `actor_id`, `action`, `target_type`, `target_id`, `meta` json, `ip`, `created_at`;
  - classe `App\Audit\AuditLog::record()`;
  - azioni registrate: `user.created`, `user.updated` (diff dei campi), `user.banned`, `user.unbanned`, `admin.login`;
  - consultabile nella tab Utenti (ultimi eventi e scheda del singolo utente).

## Metriche: definizioni e disponibilità
| Metrica | Definizione |
|---|---|
| **Ordini completati** | `status=paid` e `fulfillment=delivered` |
| **Fatturato lordo (GMV)** | somma di `price` degli ordini pagati e non annullati: consegnati più quelli in corso, mostrati separatamente |
| **Valore annullato** | somma degli ordini `paid` con `fulfillment=cancelled` |
| **Pagamenti falliti** | `status=failed`: conteggio e importo tentato, **mai sommato al fatturato** |
| **AOV** (valore medio ordine) | GMV dei completati diviso il numero dei completati |
| **Tasso di annullamento e di fallimento** | rapporti sui rispettivi totali |
| **Tempo di consegna** | `delivered_at - created_at`: mediana e p90 |
| **Ricavi piattaforma (stimati, D5)** | `commission_rate` × GMV degli ordini **completati** (`paid` + `delivered`). Gli ordini in corso maturano commissione solo alla consegna; annullati e falliti = 0. Sempre etichettati «stimati · commissione simulata 25%» |
| **Netto ristorante (stimato)** | GMV completato − ricavi piattaforma, per ristorante |

**Calcolabili** (✅):
- serie per giorno, settimana o mese;
- mappa di calore giorno della settimana × ora;
- confronto con il periodo precedente della stessa durata, o con lo stesso periodo dell'anno prima;
- classifica ristoranti per GMV, ordini, AOV, voto medio (`rates`) e tasso di annullamento;
- performance per città, CAP, cucina (`types`), `segment` e `price_range`;
- piatti più venduti (`order_plate`);
- clienti, identificati da `customer_email`: unici, nuovi e di ritorno per mese, tasso di riacquisto, distribuzione per numero di ordini, top clienti per spesa;
- utenti registrati per ruolo nel tempo (`users.created_at`);
- account bannati.

**Non calcolabili, dichiarati** (❌):
- **ricavi effettivi della piattaforma**: non esiste una commissione registrata sull'ordine e `delivery_fee` non viene addebitata. Si mostrano solo i **ricavi stimati** con la commissione simulata (D5), mai presentati come dato contabile;
- **posizione dei clienti**: l'indirizzo non ha città;
- distanza di consegna, rimborsi effettivi (annullato ≠ rimborsato), traffico e conversione, voti per cliente.
- Le "zone" sono sempre **zone del ristorante**, e la UI lo dice esplicitamente.

**Ordini storici del 2021**: compaiono solo se il periodo li include (c'è un buco 2021→2025). Per questo il default è "ultimi 12 mesi".

**Commissione simulata (D5)**: `config/platform.php` → `commission_rate` da `PLATFORM_COMMISSION_RATE`, default **0.25**. È la fascia media di un marketplace di food delivery che gestisce anche la consegna (indicativamente 15–30% del subtotale). Si applica al momento della query e **non si salva sull'ordine**: è una simulazione dichiarata, quindi cambiare il tasso ricalcola tutta la storia. Ordine di grandezza sui dati attuali: GMV completato totale €396,608.20 → ricavi stimati €99,152.05 (10.958 ordini); ultimi 12 mesi €203,739.00 → €50,934.75 (5.711 ordini). In futuro, per ricavi reali, si potrà registrare `commission_rate` sull'ordine al checkout (decisione di prodotto separata).

**Indici**: si aggiungono solo indici di performance (`orders(status, created_at)`), senza nuove colonne per i grafici.

## Tab Mappe: implementazione (D1)
Vincoli: niente servizi a pagamento, niente tile self-hosted o salvate dal backend, niente Docker in più, un'unica dipendenza nuova (`leaflet` 1.9.x, npm).
- **Caricamento lazy**: `jsBack/platform/maps.js` è un entry piccolo, incluso solo dalla vista della tab Mappe. Contiene solo l'avvio: un `IntersectionObserver` sul contenitore e, quando è visibile, `await import("./map/platformMap.js")`. È questo modulo a importare `leaflet` e `leaflet/dist/leaflet.css`, quindi Vite li mette in un chunk separato. Layout, Grafici, Tabelle, Utenti e il sito Vue non lo caricano mai.
- **Tile OSM**: `https://tile.openstreetmap.org/{z}/{x}/{y}.png`, caricate dal browser direttamente da OSM. Attribuzione sempre visibile («© OpenStreetMap contributors» con link a `openstreetmap.org/copyright`). Si rispetta la tile usage policy: `maxZoom` 19, nessun prefetch, `keepBuffer` di default, vista iniziale sul cantone di Zurigo con zoom limitato all'area dei dati (`maxBounds`).
- **Dati aggregati**: `GET /platform/data/map` (gruppo `/platform`, cache di 10 minuti per filtro) restituisce solo i campi da visualizzare: per i ristoranti `id`, nome, slug, lat/lng, città, ordini, GMV, AOV, ricavi stimati, voto; per le zone gli aggregati; per i record senza coordinate conteggio e primi 20 nomi. Mai ordini singoli né dati dei clienti. Le aggregazioni sono SQL (`GeoStats`).
- **Rendering**: `preferCanvas: true` e `circleMarker` (niente immagini dei marker, niente problemi di path delle icone). Con circa 261 ristoranti e ≤35 zone il **clustering non serve**: niente `markercluster`. Soglia documentata per ripensarci: oltre circa 2.000 punti.
- **Isolamento**: tutta la logica cartografica sta in `jsBack/platform/map/` (`platformMap.js` per creazione, livelli, popup e legenda; `mapData.js` con le funzioni pure di scala raggio e colore, testabili). Il testo dei popup si costruisce con `textContent`, mai con HTML del server. Stili `plat-map*` in `platform.css`, con i soli token.
- **Responsive e inizializzazione**: la mappa nasce solo quando il contenitore ha dimensioni. Un `ResizeObserver` chiama `invalidateSize()` (rotazione, sidebar, breakpoint 600/900/1200). Sotto 900 px l'elenco delle zone va sotto la mappa.
- **Misure e verifiche (obbligatorie per chiudere 4.3)**:
  - `npm run build`: dimensione raw e gzip del chunk Leaflet (JS e CSS) e di `maps.js`, confrontate con la build precedente, da riportare nel registro e in `dev/mappa.html`;
  - `public/build/manifest.json`: `leaflet` compare solo tra i `dynamicImports` di `maps.js`, mai negli `imports` di `vue.js`, `app.js` o degli altri entry della piattaforma (controllo con uno script in Vitest che legge il manifest dopo la build);
  - Vitest con `leaflet` mockato: l'import dinamico parte solo quando l'observer segnala la visibilità; i dati senza coordinate finiscono nell'elenco «Senza posizione»; scale e colori;
  - PHPUnit: l'endpoint mappa risponde 403 o redirect a non-admin, contiene solo aggregati (nessuna email cliente), gestisce un ristorante con `latitude` null (escluso dai punti, presente in `missing`), rispetta i filtri;
  - manuale (DevTools → Network): nessuna richiesta di Leaflet o di tile aprendo Grafici, Tabelle o Utenti; sulla tab Mappe tile solo per la vista corrente.

## Le quattro tab
1. **Mappe**: Leaflet + tile OpenStreetMap (D1). Livelli attivabili:
   - ristoranti: un cerchio per ristorante, raggio proporzionale a ordini o GMV, colore per cucina o segmento;
   - **zone** (città o CAP del ristorante): cerchio centrato sulla **media delle coordinate reali** dei suoi ristoranti; una zona senza ristoranti geolocalizzati compare solo nell'elenco;
   - popup con ordini, GMV, AOV, ricavi stimati e voto.
   - Accanto alla mappa: classifica delle zone e riquadro «Senza posizione (n)» con i record privi di coordinate (oggi 0), che non bloccano la mappa: nessuna posizione inventata.
   - Usa la barra filtri condivisa (periodo, città, cucina, metrica): al cambio dei filtri si ricaricano i dati e si aggiornano i livelli, senza ricreare la mappa.
2. **Grafici**: Chart.js già presente. Contenuti:
   - una riga KPI con il confronto rispetto al periodo precedente: GMV, ricavi stimati (D5), completati, AOV, annullati, falliti, clienti unici;
   - serie GMV e ordini;
   - AOV nel tempo;
   - mappa di calore giorno × ora;
   - top 10 ristoranti;
   - quota per cucina e per città;
   - clienti nuovi e di ritorno;
   - utenti registrati per ruolo.
   - Ogni grafico ha una nota che definisce la metrica.
3. **Tabelle**: sotto-schede Ristoranti · Ordini · Clienti · Zone · Periodi.
   - Ogni scheda ha ricerca, filtri (periodo, città, stato, cucina), ordinamento per colonna e paginazione.
   - Pagine di dettaglio: ordine (righe e pagamento), ristorante (KPI) e cliente (per email, con collegamento all'account se registrato).
4. **Utenti**:
   - elenco con ricerca per nome o email, filtro per ruolo e stato, badge del ruolo e "Bannato da… il…";
   - scheda dettaglio con ristoranti, ordini e audit;
   - **creazione** di un account cliente o ristoratore. Il ruolo è validato `in:customer,restaurateur`, non c'è mai la voce admin e vale `Password::defaults()`. In alternativa si può mandare il link di reset;
   - **modifica** di nome, cognome, ragione sociale, telefono, email (unica) e P. IVA (solo ristoratori). Ruolo, id e password non si modificano, e gli admin non sono modificabili;
   - **ban** con motivazione obbligatoria e **riattivazione**, entrambi con conferma.

## Strategia di sicurezza
- **SEC-1..SEC-4**:
  - si chiudono per primi, con `RestaurantPolicy`/`PlatePolicy`, risposta `notYours` o 403;
  - i test di caratterizzazione passano da "skip" a verdi.
- **Ruoli**:
  - nuova colonna `users.role` (default `customer`); gli utenti esistenti diventano `restaurateur` tramite migration di dati;
  - `role` **non è fillable** e la registrazione accetta solo `customer|restaurateur`;
  - `admin` si assegna solo da seeder o da `php artisan foodsy:make-admin`, che chiede conferma.
- **Separazione delle aree**:
  - `admin/*` (partner) richiede `role:restaurateur`;
  - `/platform/*` richiede `role:admin`;
  - le API cliente (`/api/me`, `/api/me/orders`) usano `auth:sanctum` più `active`;
  - redirect dopo il login in base al ruolo (cliente → `/`, ristoratore → `/admin`, admin → `/platform`), anche in `redirectUsersTo`.
- **Ban**, verificato lato backend:
  - `LoginController` rifiuta le credenziali corrette con un messaggio dedicato;
  - il middleware `active` disconnette le sessioni già aperte e invalida la sessione; sulle API risponde 403 JSON;
  - i ristoranti di un ristoratore bannato **non accettano ordini** (D4): controllo in `POST /api/orders` (risposta nel formato `{errors}` già letto da `Payment`) e flag nelle API pubbliche per disattivare il pulsante d'ordine; restano visibili;
  - Google rifiuta gli account bannati;
  - il reset password non sblocca il ban.
- **Protezioni sugli admin**:
  - la policy vieta di bannare o modificare sé stessi e qualsiasi altro admin;
  - controllo "almeno un admin attivo" in un'unica funzione (`User::activeAdminCount()`), usata dalla policy e dal comando;
  - rate limit sulle azioni `/platform` di scrittura e sul login.
- **Google**:
  - stato OAuth di Socialite su sessione, senza `stateless()`, quindi verifica di `state` contro il CSRF;
  - richiesto `email_verified === true`;
  - chiave di ricerca: `google_id` (il `sub` di Google, stabile);
  - se l'email esiste già ma l'account non è collegato, **nessun collegamento automatico**: messaggio "accedi con password e collega Google da Account", e il collegamento avviene solo da sessione autenticata con conferma;
  - il ruolo scelto (`?role=`) vale solo per i nuovi account ed è salvato in sessione con whitelist;
  - un ristoratore nuovo via Google completa la P. IVA prima di entrare nel partner hub (middleware `profile.complete`);
  - gli admin sono esclusi da Google.
- **Dati personali**: ogni endpoint cliente filtra per `auth()->id()` e gli ordini si leggono solo tramite `user_id`.

## Account demo
- `config/demo.php`:
  - `DEMO_ACCOUNTS` (true in `.env.example` e nel deploy del portfolio);
  - email e password lette da env, con default solo per lo sviluppo;
  - **nessuna credenziale nel codice di produzione** oltre ai default di sviluppo.
- **`DemoAccountsSeeder`** (idempotente, per email):
  - **Admin** `admin@foodsy.test`;
  - **Mario Rossi** `customer@foodsy.test` (cliente; D2b);
  - **Federico Romiti** `restaurant_owner@foodsy.test` (D2 + D2b, email migrata ovunque), password `foodsy-owner` (dal 2026-11-25, in linea con gli altri account demo), `role=restaurateur`.
- **Protezione degli account demo** (D6): non si possono bannare né modificare dal pannello e le loro password non si cambiano da UI quando `DEMO_ACCOUNTS` è attivo. Il resto dei dati resta modificabile dai visitatori, senza reset periodico.
- **Login**:
  - box elegante `<x-demo-accounts>` (eyebrow "Progetto portfolio", 3 righe ruolo/email/password, bottone "Usa" che compila il form);
  - stringhe in `strings.json` in 5 lingue (`partner.auth.demo.*`);
  - compare solo se `config('demo.enabled')`.

## Mario Rossi: dataset ricco (nuovo `CustomerProfilesSeeder`, deterministico con `Rng`)
- **Periodo e volumi**: storico dal 2025-01 al 2026-11, circa **250 ordini** su 30–40 ristoranti di Zurigo e dintorni.
- **Profilo coerente, da documentare in `dev/PIANO_DATASET.md`**:
  - casa a Zürich Kreis 4, ufficio a Oerlikon;
  - pranzi nei giorni feriali vicino all'ufficio, cene nel weekend;
  - cucine preferite: giapponese e pizza, con altre che cambiano con le stagioni;
  - abitudini alimentari ricorrenti, per esempio piatti senza lattosio, ricavabili dalle `dietary_tags` dei piatti ordinati;
  - ristoranti abituali e piatti riordinati;
  - qualche annullato e qualche pagamento fallito.
- **Ordini**: collegati con `user_id` ed email, con righe `order_plate` ai prezzi reali dei piatti.
- **Account cliente sugli ordini esistenti**: si registra una parte dei clienti sintetici che compaiono negli ordini da ospite, per esempio i 300 più frequenti, con `created_at` uguale al primo ordine. Questo collega i loro ordini e dà dati reali a "andamento registrati", "Clienti" e "Utenti".
- Poi `migrate:fresh --seed` sul DB di sviluppo, **solo dopo la tua conferma**.

## Fasi e step (ogni step = commit, test verdi, aggiornamento del documento)
**F0 — Documento**
- 0.1 Riscrittura di `dev/PIANO_AUTH_CLIENTI.md` (v2 e changelog).

**F1 — Sicurezza e ruoli**
- 1.1 Fix SEC-1..4 con le Policy (test da skip a verde).
- 1.2 Migration su `users`: `role` + indice, `vat_number` e `password` nullable, `google_id` unique, `avatar_url`, `banned_at`/`ban_reason`/`banned_by`. Migration di dati: utenti esistenti → `restaurateur`.
- 1.3 Middleware `role` e `active`, `UserPolicy`, Gate `platform`; `role:restaurateur` sul gruppo `admin`.
- 1.4 Redirect per ruolo (login, `redirectUsersTo`); login rifiutato ai bannati.

**F2 — Autenticazione e account demo**
- 2.1 Registrazione a schede (default cliente, `?role=restaurateur`, P. IVA solo per i ristoratori) e aggiornamento delle CTA della home (Home, MenuMobile e Footer verso `?role=restaurateur`; nell'header «Accedi» generico).
- 2.2a Migrazione email di Federico Romiti ovunque (D2), compresa `dev/mappa.html`.
- 2.2 `config/demo.php`, `DemoAccountsSeeder`, comando `foodsy:make-admin`, box demo nel login (5 lingue).
- 2.3 Sanctum (`statefulApi`), `GET /api/me`; header SPA con avatar e menu (account, ordini, esci), consumando `svg-avatar/vue`.

**F3 — Scheletro del backoffice**
- 3.1 Gruppo `/platform`, layout, tab, filtri condivisi, audit log (tabella e servizio), test di accesso (ospite, cliente, ristoratore, bannato → negato).

**F4 — Analytics**
- 4.1 `Period`, `OrderMetrics`, `SqlBuckets` con test unitari delle definizioni su dati noti.
- 4.2 Tab Grafici (endpoint JSON e Chart.js).
- 4.3 Tab Mappe: `npm install leaflet`, endpoint `/platform/data/map` e `GeoStats`, modulo lazy `jsBack/platform/map/`, misure di peso e verifiche (vedi «Tab Mappe: implementazione»).
- 4.4 Tab Tabelle (5 sotto-schede e dettagli).

**F5 — Gestione utenti**
- 5.1 Elenco, ricerca, filtri, dettaglio.
- 5.2 Creazione e modifica, con audit.
- 5.3 Ban e riattivazione, protezioni degli admin, account demo bloccati.

**F6 — Google OAuth**
- 6.1 Socialite, rotte `/auth/google` e callback, regole di collegamento, completamento profilo, collegamento da "Account"; test con il provider Socialite mockato.

**F7 — Ordini clienti e dataset**
- 7.1 `orders.user_id` (FK nullable, set null); il checkout da loggato collega l'ordine e precompila i dati; l'ospite resta com'è.
- 7.2 Pagina SPA «I miei ordini» (`/api/me/orders` paginato).
- 7.3 `CustomerProfilesSeeder` (Mario e clienti registrati), aggiornamento di `PIANO_DATASET.md`.

**F8 — Rifinitura e regressione**
- 8.1 UI/UX: stati vuoti, caricamento, responsive a 600/900/1200.
- 8.2 Aggiornamento di `dev/mappa.html` (FILES, nodi, `DB_TABLES`/`DB_RELS`, STYLE_PAGE), `README.md` e `AGENTS.md`; suite completa.

Stima indicativa: circa 14–18 giorni (v1: 5–6,5).

## Test previsti (PHPUnit su SQLite e Vitest)
- **Accesso**: matrice ruolo × area: guest, customer, restaurateur, admin, bannato su `/admin`, `/platform`, `/platform/data/*` e `/api/me`.
- **Ruoli e registrazione**:
  - `role=admin` nella registrazione viene rifiutato;
  - il mass assignment di `role` e `banned_at` non ha effetto.
- **Ban**: sessione attiva disconnessa, login rifiutato, API 403.
- **Policy sugli admin**:
  - auto-ban e ban di un altro admin vietati;
  - modifica di un admin vietata;
  - account demo bloccati;
  - l'ultimo admin resta.
- **Audit**: una riga per ogni azione, con la diff.
- **Mappa**: vedi le verifiche obbligatorie in «Tab Mappe: implementazione» (manifest, import lazy, record senza coordinate, solo aggregati).
- **Ricavi stimati**: con tasso di test noto, solo i completati contribuiscono; annullati, falliti e in corso = 0.
- **Ban e ordini**: `POST /api/orders` verso un ristorante di un ristoratore bannato → `{errors}` e nessun addebito.
- **Metriche**: dataset costruito nel test con valori noti, che verifica GMV, AOV, annullati, falliti e il confronto tra periodi; i bucket funzionano su SQLite.
- **Tabelle**: whitelist dell'ordinamento (una colonna non ammessa torna al default), paginazione, ricerca.
- **Google** (Socialite mockato):
  - `state` sbagliato, email non verificata, email esistente non collegata, `google_id` esistente, ruolo preservato, admin escluso, account bannato.
- **Clienti**: ordini solo propri; l'ordine da loggato ha `user_id`; il checkout da ospite resta invariato.
- **Lingue**: `LocaleTest` (tutte le nuove chiavi in 5 lingue).
- **Vitest**: header loggato e ospite, «I miei ordini», smoke delle rotte.
- **Chiusura**: `php artisan route:list`, `npm run build`, `npm run test:js`.

## Decisioni prese (2026-11-23)
- **D2 — Email di Federico Romiti: sì.** Diventa `federico.romiti@foodsy.test` **ovunque** compaia la vecchia `federico_romiti@gmail.com`: `database/seeders/UsersTableSeeder.php`, `database/seeders/RestaurantsTableSeeder.php`, `database/seeders/Support/CatalogPlan.php`, `tests/Feature/Concerns/SeededFixtures.php`, `dev/mappa.html` (solo blocco `DATI`); più una migration di dati che aggiorna il DB già popolato (idempotente: solo se la nuova email non esiste). Verifica finale: `grep -r "federico_romiti@gmail.com"` senza risultati fuori da `vendor/`/`node_modules/` (eccezioni: la migration, che contiene la vecchia email come valore da sostituire, e questo piano, che la cita come storia). Step **2.2a**.
- **D2b — Email degli account demo per ruolo (2026-11-24).** Su richiesta dell'utente le email demo prendono il nome del ruolo, non della persona: Federico Romiti `federico.romiti@foodsy.test` → **`restaurant_owner@foodsy.test`**, Mario Rossi `mario.rossi@foodsy.test` → **`customer@foodsy.test`** (admin invariato: `admin@foodsy.test`). Cambiate ovunque, `dev/mappa.html` compresa, più una nuova migration di dati per i DB già popolati (la 2.2a resta com'è: le migration esistenti non si modificano). Step **2.2b**.
- **D3 — Arricchimenti: sì.** `CustomerProfilesSeeder` crea Mario Rossi con lo storico ricco **e** registra come clienti una parte dei clienti sintetici degli ordini da ospite (~300 più frequenti, `created_at` = primo ordine), collegando i loro ordini tramite `user_id`. Step **7.3**.
- **D1 — Mappa: Leaflet + OpenStreetMap.** Caricamento lazy, tile esterne con attribuzione, dati aggregati, modulo isolato, peso misurato: dettagli in «Tab Mappe: implementazione». Step **4.3**.
- **D4 — Ristoratore bannato: approvata la proposta.** I suoi ristoranti restano visibili ma non accettano ordini (controllo lato server in `POST /api/orders`). Step **5.3**.
- **D5 — Ricavi della piattaforma: commissione simulata.** 25% (`PLATFORM_COMMISSION_RATE`) sul GMV degli ordini completati, calcolata nelle query ed etichettata come stima. Vedi «Metriche». Step **4.1**.
- **D6 — Demo pubblica: niente reset periodico.** I dati restano modificabili dai visitatori; sono protetti **solo gli account demo** (admin, Federico Romiti, Mario Rossi): non bannabili, non modificabili dal pannello, password non cambiabile da UI. Step **5.3**.

## Decisioni ancora aperte
7. **2FA per gli admin**: rinviata.
8. **Commissione reale per ordine**: oggi simulata (D5); salvarla sull'ordine al checkout è una decisione di prodotto futura.

## Trappole (dalla v1, ancora valide)
- **Codespace e Google**: Google accetta solo URL di callback registrati; l'URL del Codespace cambia. Registrare `localhost` e l'URL di produzione, gli altri a mano.
- **Sanctum**: `SANCTUM_STATEFUL_DOMAINS` coerente col dominio e `statefulApi()` in `bootstrap/app.php`.
- **Consenso Google**: in modalità test funziona solo per gli account aggiunti a mano; per il pubblico serve la verifica dell'app (gratuita, privacy policy obbligatoria).
- **SQL portabile**: dev su MySQL, test su SQLite: funzioni data/ora solo tramite `SqlBuckets`.

## Registro di avanzamento
### 0.1 — Riscrittura del piano (2026-11-23) ✅
- Stato: completato.
- Modifiche: piano v2 (backoffice, ban, audit, account demo, Mario Rossi, Google più rigido, fasi riordinate).
- Analisi eseguita in sola lettura: migration, seeder (`UsersTableSeeder`, `OwnersSeeder`, `OrderHistorySeeder`, `config/seeding.php`), DB MySQL di sviluppo (conteggi riportati in «Fatti verificati»), rotte, `bootstrap/app.php`, `User`, controller auth.
- File: `dev/PIANO_AUTH_CLIENTI.md`.
- Test: nessuno (solo documentazione).
- Problemi aperti: solo 7 (2FA admin, rinviata) e 8 (commissione reale, futura). D1–D6 decise dall'utente e integrate nelle fasi.
- Aggiornamento dello stesso giorno: integrate D1 (Leaflet + OSM con i 4 vincoli e le verifiche di peso), D4 (ban → niente ordini) e D5 (commissione simulata 25%: stima su dati attuali €99,152.05 su tutto lo storico).
- Prossimo step: 1.1.

### 1.1 — Fix SEC-1..SEC-4 (2026-11-23) ✅
- Stato: completato (branch `feat/auth-platform`).
- Modifiche: Policy `manage` per ristoranti e piatti. SEC-1: `PlateController@store` richiede `restaurant_id` e verifica che il ristorante sia dell'utente, poi lo imposta lato server. SEC-2: `StatsController@show` → `admin.notYours.orders` per i non proprietari (404 se il ristorante non esiste). SEC-3: `RestaurantController@update/destroy`. SEC-4: `PlateController@update/destroy`. Il controllo avviene **prima** della validazione e di qualsiasi scrittura.
- File: `app/Policies/RestaurantPolicy.php`, `app/Policies/PlatePolicy.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/Admin/{PlateController,RestaurantController,StatsController}.php`, `tests/Feature/Admin/OwnershipTest.php`, `AGENTS.md`, `README.md`, `dev/PIANO_MIGRAZIONE_L13.md`.
- Test: rimossi i 4 test `_CurrentBehaviour` delle vulnerabilità, attivati i 4 `testOtherUserCannot…`, aggiunti store senza ristorante o con ristorante inesistente e i casi positivi del proprietario (update ristorante, update/delete piatto). `php artisan test`: **118 passati, 0 skip** (21.831 asserzioni).
- Problemi aperti: nessuno.
- Prossimo step: 1.2.

### 1.2 — Ruoli e colonne account su `users` (2026-11-24) ✅
- Stato: completato. Migration applicata anche al DB MySQL di sviluppo (`php artisan migrate` nel container `app`): 60 utenti → `restaurateur`, nessun dato perso.
- Modifiche:
  - migration `2026_11_24_000000_add_roles_and_account_columns_to_users_table`: `role` (default `customer`, indice), `google_id` UNIQUE nullable, `avatar_url`, `banned_at` (indice), `ban_reason`, `banned_by` (FK `users`, `nullOnDelete`); `vat_number` e `password` nullable (gli UNIQUE restano); dati: utenti esistenti → `restaurateur`. `down()` verificato su SQLite (rollback e nuovo migrate).
  - `App\User`: costanti `CUSTOMER`/`RESTAURATEUR`/`ADMIN`, `ROLES`, `SIGNUP_ROLES` (mai admin), `hasRole()`, `isAdmin()`, `isBanned()`, `activeAdminCount()`, `bannedBy()`; cast `banned_at`; `google_id` nascosto. `role` e ban **non** fillable.
  - Seeder: `UsersTableSeeder` e `OwnersSeeder` scrivono `role = restaurateur`.
  - `RegisterController@create`: ruolo `restaurateur` impostato fuori dal mass assignment (fino alla registrazione a schede, step 2.1); **corretto** il bug storico che salvava il nome in `last_name`.
  - `dev/mappa.html`: migration, Policy, test e tabella `users` (ruolo, Google, ban).
- File: migration, `app/User.php`, `database/seeders/{UsersTableSeeder,OwnersSeeder}.php`, `app/Http/Controllers/Auth/RegisterController.php`, `tests/Feature/UserRolesTest.php` (nuovo), `tests/Feature/AuthTest.php`, `dev/mappa.html`.
- Test: `UserRolesTest` (7 test: colonne, seed tutti ristoratori, default customer senza P. IVA né password, role/ban/google_id non assegnabili in massa, helper e conteggio admin attivi, `google_id` nascosto, registrazione → restaurateur con cognome giusto); `AuthTest` aggiornato al cognome corretto. `php artisan test`: **125 passati**.
- Problemi aperti: nessuno.
- Prossimo step: 1.3.

### 1.3 — Middleware di ruolo e ban, UserPolicy, Gate `platform` (2026-11-24) ✅
- Stato: completato.
- Modifiche:
  - `EnsureRole` (alias `role:`): ospite → login (401 in JSON); ruolo sbagliato → `homePath()` del proprio ruolo con messaggio (403 in JSON). Gruppo `admin` ora `['auth', 'role:restaurateur']`: clienti e admin non entrano più nell'area partner.
  - `EnsureActive` (alias `active`, **in coda a tutto il gruppo web**): un account bannato viene disconnesso e la sessione invalidata alla richiesta successiva, anche su pagine pubbliche; in JSON 403 `{message}`.
  - `User::homePath()`: cliente `/`, ristoratore `/admin`, admin `/platform`.
  - `UserPolicy` (`viewAny`, `view`, `create`, `update`, `ban`, `unban`): solo admin attivi; mai su sé stessi né su altri admin (quindi gli admin attivi non possono scendere a zero dal pannello). Gate `platform` = admin non bannato. Registrati in `AppServiceProvider`.
  - Stringhe `auth.banned` e `auth.forbidden` in 5 lingue.
- File: `app/Http/Middleware/{EnsureRole,EnsureActive}.php`, `app/Policies/UserPolicy.php`, `app/User.php`, `app/Providers/AppServiceProvider.php`, `bootstrap/app.php`, `routes/web.php`, `resources/js/utils/strings.json`, `tests/Feature/AccessControlTest.php` (nuovo), `AGENTS.md`, `dev/mappa.html`.
- Test: `AccessControlTest` (10 test: ristoratore ok; cliente e admin respinti dall'area partner; scrittura con ruolo sbagliato senza effetti; JSON 403; ban → logout con errore anche su `/`; JSON 403 per i bannati; `homePath`; Gate `platform`; `UserPolicy` su account normali, sé stesso, altro admin, non admin). `php artisan test`: **135 passati**.
- Problemi aperti: la protezione degli account demo nella policy arriva con `config/demo.php` (step 2.2 / 5.3).
- Prossimo step: 1.4.

### 1.4 — Redirect per ruolo e login dei bannati (2026-11-24) ✅ — **fase F1 chiusa**
- Stato: completato.
- Modifiche:
  - `LoginController` e `RegisterController`: `redirectTo()` = `homePath()` dell'account (cliente `/`, ristoratore `/admin`, admin `/platform`, che esisterà dallo step 3.1); `redirect()->intended()` di laravel/ui resta.
  - `bootstrap/app.php`: `redirectUsersTo` con closure per ruolo (un utente loggato che apre `/login` o `/register` torna alla sua home).
  - Login di un account bannato: con password giusta → logout, sessione invalidata, errore `auth.banned`; con password sbagliata → il solito `auth.failed` (il form non rivela quali email sono bannate). Il throttling di laravel/ui resta attivo.
- File: `app/Http/Controllers/Auth/{LoginController,RegisterController}.php`, `bootstrap/app.php`, `tests/Feature/AuthTest.php`.
- Test: `AuthTest` + 3 (redirect di login e di `/login` per i 3 ruoli, bannato con password giusta, bannato con password sbagliata); l'utente di prova di `AuthTest` è ora esplicitamente `restaurateur`. Verifica di fine fase: `php artisan test` **138 passati** · `npm run test:js` **249 passati** · `npm run build` ok · `php artisan route:list` ok.
- Problemi aperti: `/platform` per ora finisce nella catch-all della SPA (arriva allo step 3.1).
- Prossimo step: 2.1.

### 2.1 — Registrazione a schede e CTA (2026-11-24) ✅
- Stato: completato.
- Modifiche:
  - `RegisterController`: `showRegistrationForm` sceglie la scheda da `old('role')` o `?role=` (whitelist `SIGNUP_ROLES`, altrimenti `customer`); validazione `role` `in:customer,restaurateur` (un `role=admin` inviato a mano → errore, nessun account); `vat_number` obbligatoria, 11 cifre e **unica** solo per i ristoratori (prima un duplicato dava errore 500 sull'indice UNIQUE), ignorata e NULL per i clienti.
  - `auth/register`: schede «Ordino cibo» / «Ho un ristorante» come **link** (niente JS, `aria-current`), campo P. IVA solo per i partner, testi, foto e pannello laterale per ruolo. Login generico (eyebrow «Il tuo account Foodsy», pannello clienti, link «Crea un account»).
  - `layouts/auth`: sezioni opzionali `metaSuffix`, `eyebrow`, `asidePhoto`, `asideTitle`, `asideNote` (default partner, invariati per reset/verify).
  - CSS `auth-tabs`/`auth-tab`/`auth-tab--active` (controllo segmentato: pozzetto `--oat`, scheda attiva `--surface` con `--shadow-sm`), una classe per selettore.
  - SPA: le CTA partner (sezione partner della home, drawer mobile, footer) puntano a `/register?role=restaurateur`; nell'header il link «Per i ristoranti» diventa «Accedi» generico (icona utente); nel drawer «Accesso partner» → «Accedi»; il footer tiene «Accesso partner». Homepage invariata nella struttura.
  - Stringhe nuove o aggiornate in 5 lingue: `partner.auth.account`, `customerAsideTitle/Note`, `login.lead`, `login.register`, `request.lead`, `register.already`, `register.tabs.*`, `register.customer.*`, `site.nav.login`, `site.header.login`; rimossa `site.header.partner` (non più usata).
- File: `app/Http/Controllers/Auth/RegisterController.php`, `resources/views/auth/{register,login}.blade.php`, `resources/views/layouts/auth.blade.php`, `resources/css/admin.css`, `resources/js/utils/strings.json`, `resources/js/pages/Home/Home.vue`, `resources/js/components/{Header/Header.vue,MenuMobile/MenuMobile.logic.js,MenuMobile/MenuMobile.vue,Footer/Footer.logic.js}`, `tests/Feature/{AuthTest,UserRolesTest}.php`, `dev/mappa.html`.
- Test: `AuthTest` + 5 (cliente di default senza P. IVA → `/`; P. IVA ignorata per i clienti; `role=admin` rifiutato; P. IVA mancante o duplicata per i partner; `?role=` preseleziona la scheda, `admin` ricade su cliente). `php artisan test` **143 passati** · `npm run test:js` **249** · `npm run build` ok · smoke sul server di sviluppo: `/register`, `/register?role=restaurateur`, `/register?role=admin`, `/login` → 200.
- Problemi aperti: verifica visiva nel browser delle schede (fatta solo con i test e l'HTML).
- Prossimo step: 2.2a.

### 2.2a — Email dell'account demo Federico Romiti (D2, 2026-11-24) ✅
- Stato: completato. `federico_romiti@gmail.com` → `federico.romiti@foodsy.test` ovunque.
- Modifiche: seeder (`UsersTableSeeder`, `RestaurantsTableSeeder` ×3, `Support/CatalogPlan::ROMITI`), test (`Concerns/SeededFixtures`, `UserRolesTest`), `dev/mappa.html` (blocco `DATI`: credenziali di avvio, più conteggio ristoranti corretto a 33); migration di dati `2026_11_24_010000_move_demo_restaurateur_email` (idempotente; i ristoranti seguono per `user_id`, ordini e voti non dipendono dall'email; rimuove eventuali token di reset della vecchia email). Password invariata (`foodsy-owner`).
- DB di sviluppo: migration applicata, l'utente id 9 ha la nuova email ed è `restaurateur`.
- Verifica: `grep -r "federico_romiti@gmail.com"` fuori da `vendor/`, `node_modules/`, `public/` → solo la migration (valore da sostituire) e questo piano (storia).
- Test: `php artisan test` **143 passati**.
- Prossimo step: 2.2.

### 2.2 — Account demo, comando admin e box nel login (2026-11-24) ✅
- Stato: completato.
- Credenziali demo (valori di sviluppo, `DEMO_*` in `.env.example`):

  | Ruolo | Account | Email | Password | Dopo il login |
  |---|---|---|---|---|
  | admin | Foodsy Admin | `admin@foodsy.test` | `foodsy-admin` | `/platform` (dallo step 3.1) |
  | restaurateur | Federico Romiti | `federico.romiti@foodsy.test` | `foodsy-owner` | `/admin` |
  | customer | Mario Rossi | `mario.rossi@foodsy.test` | `foodsy-demo` | sito |
- Modifiche:
  - `config/demo.php`: `enabled` (`DEMO_ACCOUNTS`, di default attivo fuori da `production`: un deploy pubblico lo accende esplicitamente) e i 3 account; `.env.example` con le variabili.
  - `DemoAccountsSeeder` (in `DatabaseSeeder` dopo i dati storici): crea admin e Mario se mancano (email verificata, ruolo impostato a mano), verifica che il ristoratore venga da `UsersTableSeeder`; mai sovrascritture.
  - `php artisan foodsy:make-admin {email}`: promuove (con conferma) o crea un admin (nome, cognome, password ≥ 8); rifiuta gli account bannati.
  - `User::isDemoAccount()` / `demoEmails()`; `UserPolicy` vieta modifica e ban degli account demo (D6); `ForgotPasswordController` e `ResetPasswordController` rifiutano il reset per gli account demo (`passwords.demo`); dopo un reset si va su `homePath()`.
  - `<x-demo-accounts>` sotto il form di login: eyebrow «Progetto portfolio», titolo, spiegazione, una riga per account (ruolo, nome, a cosa serve, email e password visibili, bottone «Entra come …» = form POST `/login`, senza JS). Stile `auth-demo*` con i token (pozzetto `--oat`, righe `--surface`). Testi in 5 lingue (`partner.auth.demo.*`).
- DB di sviluppo: `db:seed --class=DemoAccountsSeeder` → admin (id 61) e Mario Rossi (id 62) creati; Federico (id 9) invariato. `/login` mostra le 3 righe.
- File: `config/demo.php`, `.env.example`, `database/seeders/{DemoAccountsSeeder,DatabaseSeeder}.php`, `app/Console/Commands/MakeAdmin.php`, `app/User.php`, `app/Policies/UserPolicy.php`, `app/Http/Controllers/Auth/{ForgotPasswordController,ResetPasswordController}.php`, `resources/views/components/demo-accounts.blade.php`, `resources/views/auth/login.blade.php`, `resources/css/admin.css`, `resources/js/utils/strings.json`, test, `AGENTS.md`, `README.md`, `dev/mappa.html` (file, comandi, credenziali di avvio).
- Test: `DemoAccountsTest` (8: ruoli e password che funzionano, login di ogni account verso la sua home, seeder idempotente che non sovrascrive, box visibile solo se attivo, reset password bloccato per i demo e non per gli altri, policy con demo attivo/disattivo, `make-admin` con conferma sì/no, creazione e rifiuto dei bannati). Aggiornati `DatabaseSeederTest` (P. IVA e ristoranti solo per i ristoratori), `UserRolesTest`, `AccessControlTest` (ora il seed contiene un admin e un cliente). `php artisan test` **151 passati** · `npm run build` ok.
- Problemi aperti: Mario Rossi per ora non ha ordini (storico ricco allo step 7.3, che richiede `orders.user_id` dello step 7.1). Verifica visiva del box nel browser da fare.
- Prossimo step: 2.3.

### 2.3 — Sanctum, `/api/me` e account nell'header (2026-11-24) ✅ — **fase F2 chiusa**
- Stato: completato.
- Modifiche:
  - `composer require laravel/sanctum` (^4.5); `config/sanctum.php` con `Sanctum::currentRequestHost()` tra gli host stateful (la SPA è servita da Laravel stesso: funziona su Codespaces e in produzione senza elencare domini); `statefulApi()` in `bootstrap/app.php`. Nessuna tabella di token: solo modalità cookie.
  - `GET /api/me` (`Api\MeController`, middleware `active` + `throttle:60,1`): `{ user: null }` per gli ospiti (200, nessun errore in console), altrimenti id, nome, cognome, email, telefono, ruolo, `home`, `avatar_url`, `avatar_seed`, `avatar_overrides`; mai Google id, ban o password. Bannato → logout e 403.
  - SPA: `api/account.js` (`fetchMe`, `logout`), store `user` + `loadUser`/`logoutUser` (l'account **non** finisce in localStorage: `reducer` di `createPersistedState`), `App` chiede l'account a ogni caricamento.
  - `components/AccountMenu`: ospite → «Accedi»; account → `<details>` con avatar `svg-avatar/vue` (o foto Google con fallback se non carica), nome, email, link all'area del ruolo (ristoratore «Area partner» `/admin`, admin «Backoffice» `/platform`), «Esci» (POST `/logout`, poi ricarica `/`). Chiusura con Esc e clic fuori. Il drawer mobile nasconde «Accedi» se loggati. Rimossi gli stili `header-partner` (ora `account-*`).
  - Stringhe `site.account.*` in 5 lingue.
- Verifica manuale sul server di sviluppo (curl): ospite → `{"user":null}`; POST `/api/orders` con `Referer` del sito **senza** `X-XSRF-TOKEN` → 419, **con** il token → 200 (axios lo manda da solo); login di Mario dal form → redirect `/`, poi `/api/me` restituisce Mario.
- Peso: entry `vue` 284.44 → 289.13 kB (gzip 99.47 → 100.88 kB, +1.4 kB) per componente e avatar.
- File: `composer.json`/`composer.lock`, `config/sanctum.php`, `bootstrap/app.php`, `routes/api.php`, `app/Http/Controllers/Api/MeController.php`, `resources/js/{api/account.js,api/index.js,store.js,App.logic.js}`, `resources/js/components/{AccountMenu/*,Header/*,MenuMobile/MenuMobile.logic.js,index.js}`, `resources/js/utils/strings.json`, `tests/Feature/Api/MeTest.php`, `tests/js/{account.spec.js,app.smoke.spec.js}`, `AGENTS.md`, `dev/mappa.html`.
- Test: `MeTest` (6), `account.spec.js` (11). `php artisan test` **157 passati** · `npm run test:js` **260 passati** · `npm run build` ok.
- Problemi aperti: verifica visiva nel browser di header, menu, schede di registrazione e box demo (non ho un browser in questa sessione).
- Prossimo step: 2.2b, poi 3.1.

### 2.2b — Email degli account demo per ruolo (D2b, 2026-11-24) ✅
- Stato: completato. Credenziali demo attuali:

  | Ruolo | Account | Email | Password |
  |---|---|---|---|
  | admin | Foodsy Admin | `admin@foodsy.test` | `foodsy-admin` |
  | restaurateur | Federico Romiti | `restaurant_owner@foodsy.test` | `foodsy-owner` |
  | customer | Mario Rossi | `customer@foodsy.test` | `foodsy-demo` |
- Modifiche: `config/demo.php` e `.env.example` (`DEMO_RESTAURATEUR_EMAIL`, `DEMO_CUSTOMER_EMAIL`), seeder (`UsersTableSeeder`, `RestaurantsTableSeeder` ×3, `Support/CatalogPlan::ROMITI`), test (`Concerns/SeededFixtures`, `UserRolesTest`, `tests/js/account.spec.js`), `README.md`, `dev/mappa.html` (credenziali di avvio e nuova riga della migration). Migration di dati `2026_11_24_020000_rename_demo_account_emails` (idempotente, reversibile; elimina eventuali token di reset delle vecchie email). Le email delle sezioni storiche del registro (2.2a, 2.2) restano come erano.
- DB di sviluppo: migration applicata; id 9 → `restaurant_owner@foodsy.test`, id 62 → `customer@foodsy.test`.
- Verifica: le vecchie email compaiono solo nelle due migration (valori da sostituire), nella riga della mappa che descrive la migration 2.2a e in questo registro.
- Test: `php artisan test` **157 passati** · `npm run test:js` **260 passati**.
- Prossimo step: 3.1.

### 3.1 — Scheletro del backoffice `/platform` (2026-11-24) ✅ — **fase F3 chiusa**
- Stato: completato. Le 4 tab esistono, sono protette e hanno filtri reali; il contenuto arriva con F4 (Mappe, Grafici, Tabelle) e F5 (Utenti). Fino ad allora ogni tab mostra «Questa sezione è in costruzione» (`platform.common.comingSoon`, da rimuovere quando la tab è completa).
- Modifiche:
  - Rotte: gruppo `platform` (`auth`, `role:admin`, `can:platform`; `active` gira già su tutto il web): `/platform` → `/platform/maps`, `maps`, `charts`, `tables/{section?}` (whitelist `restaurants|orders|customers|zones|periods`), `users`; `{any}` finale → 404 (altrimenti la catch-all della SPA risponderebbe 200).
  - Controller `Platform\{Map,Chart,Table,User}Controller` (sottili; `UserController` chiama anche `authorize('viewAny')`).
  - `layouts/platform`: barra chiara (logo, badge «Platform» paprika, «Vedi il sito», lingua, menu account con logout) e **banda verde rosmarino** con le 4 tab (icone mappa, grafico, tabella, utenti; scorrono in orizzontale su mobile); `noindex`. Mappe, Grafici e Tabelle conservano i filtri passando dall'una all'altra.
  - `App\Analytics\Period`: `from`/`to` (formato `Y-m-d` rigoroso, invertiti se al contrario, mai nel futuro, al massimo 10 anni), `city` (solo città con ristoranti), `type` (solo id di cucine esistenti); qualsiasi altro valore → default (ultimi 12 mesi, tutto). `previous()` (stessa durata, subito prima), `days()`, `query()`, `cacheKey()`, intervalli rapidi 30 giorni / 90 giorni / 12 mesi / tutto.
  - `<x-platform.filters>`: form GET (ogni vista filtrata ha il suo URL), intervalli rapidi come chip.
  - Audit: tabella `platform_audit_logs` (migration `2026_11_24_030000`), modello `App\AuditLog` con `record()` (admin dalla sessione, IP, `meta` senza chiavi segrete). Il login di un admin scrive `admin.login`. Nota: il piano parlava di `App\Audit\AuditLog`; il modello sta in `app/` come gli altri (convenzione del progetto).
  - `resources/css/platform.css` (importa `admin.css`, aggiunge `plat-*`), in `vite.config.js`. Icone nuove: `map`, `table`, `users`, `ban`, `shield`.
  - Stringhe: nuovo gruppo `platform` di `strings.json` in 5 lingue.
- **Problema trovato e corretto**: lo script che usavo per inserire le stringhe in `strings.json` cercava le chiavi in tutto il resto del file invece che solo dentro l'oggetto padre. Negli step 2.1–3.1 aveva **sovrascritto** `partner.header.login` (fr/es diversi) e `validation.attributes.city` («city» → «City», rilevato da `CrudTest`) e **non aveva creato** `site.nav.login`, `site.header.login`, `site.account.logout` e `platform.filters.city` (header e menu del sito avrebbero mostrato la chiave grezza). Ripristinati i due valori originali (confronto foglia per foglia con il commit `b315437`), create le quattro chiavi e aggiunto `StringKeysTest`: ogni chiave statica `t("…")` del sito e `__('…')` di Blade/PHP deve esistere in `strings.json`.
- DB di sviluppo: migration applicata.
- Verifica manuale (curl, server di sviluppo): login admin → `/platform` → `/platform/maps`; maps, charts, `tables/zones?city=Winterthur`, users → 200; `/platform/nope` → 404; `/admin` da admin → `/platform`; una riga `admin.login` nell'audit con IP.
- Peso: `platform.css` 31.76 kB (gzip 5.85 kB), caricato solo dal backoffice.
- File: `routes/web.php`, `app/Http/Controllers/Platform/*`, `app/Http/Controllers/Auth/LoginController.php`, `app/Analytics/Period.php`, `app/AuditLog.php`, migration, `resources/views/layouts/platform.blade.php`, `resources/views/platform/*`, `resources/views/components/platform/filters.blade.php`, `resources/css/platform.css`, `vite.config.js`, `resources/icons/icons.json`, `resources/js/utils/strings.json`, `tests/Feature/Platform/PlatformAccessTest.php`, `tests/Feature/StringKeysTest.php`, `AGENTS.md`, `dev/mappa.html` (file, tabella `platform_audit_logs` e relazione).
- Test: `PlatformAccessTest` (9: admin su ogni tab e 404 interno; ospiti → login; cliente e ristoratore rimandati alla loro home e 403 in JSON; admin bannato disconnesso; audit del login admin e non del cliente; nessun segreto nei meta; filtri manomessi → default; filtri validi, inversione e periodo precedente), `StringKeysTest` (2). `php artisan test` **168 passati** · `npm run test:js` **260** · `npm run build` ok.
- Prossimo step: 4.1.

### 4.1 — Metriche: `OrderMetrics`, `SqlBuckets`, commissione simulata (2026-11-24) ✅
- Stato: completato.
- Modifiche:
  - `config/platform.php`: `commission_rate` (`PLATFORM_COMMISSION_RATE`, default 0.25, D5) e `cache_seconds` (600); `.env.example`.
  - `App\Analytics\SqlBuckets`: giorno, settimana (lunedì), mese, giorno della settimana, ora e minuti tra due date, per MySQL e SQLite; granularità automatica (≤ 62 giorni → giorni, ≤ 190 → settimane, poi mesi).
  - `App\Analytics\OrderMetrics`: definizioni come espressioni SQL costanti (`COMPLETED`, `IN_PROGRESS`, `CANCELLED`, `FAILED`, `GROSS`), ordini `pending` esclusi; `summary()` (totali, periodo precedente, variazione %, tempi di consegna mediana/p90), `series()` (bucket vuoti riempiti), `heatmap()` (7×24), `restaurants()` (classifica con voto medio di sempre: i voti non hanno periodo), `zones()` (città o CAP del ristorante), `cuisines()` (un ristorante con più cucine conta in ognuna, da dichiarare nella UI), `customers()` (per email: nuovi = primo ordine pagato di sempre nel bucket; distribuzione per numero di ordini; tasso di riacquisto), `registrations()` (clienti e ristoratori per bucket). Tutto in SQL, risultati in cache per combinazione di filtri.
  - Indice `orders(status, created_at)` (migration `2026_11_24_040000`, solo prestazioni).
- Verifica su MySQL (DB di sviluppo, ultimi 12 mesi 2025-11-22 → 2026-11-21): 5.711 completati per €203,739.00 → ricavi stimati €50,934.75 (uguali all'analisi), valore lordo €205,163.50, valore medio €35.67, annullati 104 (€3,397.50), falliti 157, 1.058 clienti, consegna mediana 43 min (p90 59), GMV +8.1% sul periodo precedente; 20 città, 35 CAP, 43 cucine; tasso di riacquisto 64.7%. Tutte le aggregazioni senza cache: circa 650 ms.
- Nota sui dati: `users.created_at` dei ristoratori è la data del seed, quindi «registrati nel tempo» avrà senso dopo lo step 7.3 (clienti registrati con data del primo ordine).
- File: `config/platform.php`, `.env.example`, `app/Analytics/{OrderMetrics,SqlBuckets}.php`, migration dell'indice, `tests/Feature/Platform/OrderMetricsTest.php`, `dev/mappa.html`.
- Test: `OrderMetricsTest` (11, dataset di gennaio 2030 con valori noti: definizioni, commissione da config, filtri città/cucina, confronto e percentili, serie giorno/settimana/mese, heatmap, classifiche/zone/cucine, nuovi e di ritorno, registrazioni, periodo vuoto senza divisioni per zero). `php artisan test` **179 passati**.
- Prossimo step: 4.2.

### 4.2 — Tab Grafici (2026-11-24) ✅
- Stato: completato. Tab funzionante sui dati reali.
- Contenuto:
  - nota unica «Variazioni rispetto al periodo precedente, <date>» e 8 KPI: valore lordo, **ricavi della piattaforma (stima, badge «Stima», commissione simulata 25%)**, completati, valore medio, clienti, annullati (con valore), pagamenti falliti (con importo, «mai contati come vendite»), tempo di consegna (mediana e 9 su 10). Variazione con freccia e colore per direzione × «su è buono» (annullati e falliti: su = rosso);
  - grafici: valore lordo nel tempo (linea con area), ordini completati (colonne), valore medio (linea), questo periodo vs precedente (linea arancio + grigio, allineati bucket per bucket), heatmap giorno × ora (HTML, scala monocromatica sui token `--brand`), ristoranti migliori, valore per città, per cucina (top 10, con la nota sui ristoranti con più cucine), clienti nuovi e di ritorno (colonne impilate con gap di 2px), ordini per cliente con tasso di riacquisto, nuovi account per ruolo (o «nessun account creato» se zero);
  - ogni grafico ha la sua definizione nel sottotitolo e **gli stessi numeri in tabella** («Mostra i dati in tabella»); il primo e l'ultimo mese o settimana tagliati dal periodo sono etichettati «(parziale)» con una nota;
  - stato vuoto «Nessun ordine in questo periodo».
- Grafica (skill `dataviz`): palette categoriale validata con lo script della skill (arancio `#d0682c`, blu ardesia `#2f6f9f`, oliva `#6b8e23`: tutte le verifiche superate, comprese quelle per daltonismo; l'oliva dei token `#56682b` era troppo grigia e troppo vicina all'arancio per i protanopi); una serie = nessuna legenda, ≥ 2 = legenda; linee 2px, barre ≤ 24px con estremità arrotondate, griglia orizzontale sottile, un solo asse; tooltip a colonna intera con il valore prima dell'etichetta; testi sempre nei colori dell'inchiostro. In `jsBack/platform/chartTheme.js` (funzioni pure).
- Architettura: `ChartController@index` (KPI e tabelle in Blade, serie in `<script type="application/json">` con `@json`) e `@data` → `GET /platform/data/charts` (stesso gruppo e stessa autorizzazione); `jsBack/platform/charts.js` caricato solo da questa tab.
- Correzioni dopo la verifica visiva: header del backoffice su mobile (il badge copriva il logo: sotto 600px resta solo il marchio, nuova prop `wide` di `<x-logo>` e classe `ui-logo-word--wide`; prima c'era 1px di scroll orizzontale a 390px, ora 0), celle della heatmap a altezza fissa, «Nuovi account» a mezza larghezza, data del confronto una sola volta invece che in ogni tile.
- Verifica visiva: screenshot reali con Chromium headless (cache di Playwright già presente, pilotato via DevTools Protocol da uno script Node nello scratchpad, login come admin) a 1280px e 390px: nessun errore in console, nessuno scroll orizzontale.
- Peso: `charts.js` 2.23 kB (gzip 1.17 kB) + Chart.js `auto.esm` 182.29 kB (gzip 63.87 kB), chunk condiviso con le statistiche partner e caricato solo su quelle pagine; `platform.css` ~35 kB (gzip ~6.3 kB).
- File: `app/Http/Controllers/Platform/ChartController.php`, `routes/web.php`, `resources/views/platform/charts.blade.php`, `resources/views/components/platform/{kpi,chart-card}.blade.php`, `resources/views/components/logo.blade.php`, `resources/views/layouts/platform.blade.php`, `resources/js/jsBack/platform/{charts,chartTheme}.js`, `vite.config.js`, `resources/css/{platform,ui}.css`, `resources/js/utils/strings.json` (`platform.charts.*`, 71 chiavi in 5 lingue; verificato che lo script abbia solo aggiunto chiavi), `tests/Feature/Platform/ChartsTabTest.php`, `tests/js/platformCharts.spec.js`, `dev/mappa.html`.
- Test: `ChartsTabTest` (5: pagina con KPI/grafici/tabelle, JSON uguale a `OrderMetrics` e serie che sommano ai totali, nessuna email nel payload, filtro città, stato vuoto, solo admin), `platformCharts.spec.js` (8). `php artisan test` **184 passati** · `npm run test:js` **268** · `npm run build` ok.
- Prossimo step: 4.3.

### 4.3 — Tab Mappe: Leaflet + OpenStreetMap (D1, 2026-11-24) ✅
- Stato: completato. Le 4 regole e le verifiche obbligatorie di D1 sono rispettate (dettagli e misure qui sotto).
- Contenuto: mappa del cantone con un cerchio per ristorante (area ∝ valore lordo del periodo; grigio piccolo = nessun ordine), livello attivabile **Zone (città)** con cerchi blu centrati sulla media delle coordinate reali dei ristoranti della città, popup con valore lordo, ordini completati, valore medio, ricavi stimati e voto; accanto, classifica delle zone (valore e quota, link che filtra per città) e riquadro **«Senza posizione (n)»**. Nota sempre visibile: le zone sono quelle dei ristoranti, gli ordini non hanno posizione del cliente. Usa la barra filtri condivisa.
- Dati: `App\Analytics\GeoStats::map()` → `GET /platform/data/map` (gruppo `/platform`, cache per filtro). Solo aggregati per ristorante e per zona + coordinate salvate sui ristoranti; mai ordini singoli né dati dei clienti. Un ristorante senza `latitude`/`longitude` non viene disegnato: è contato nella sua zona e elencato (primi 20 nomi); una zona senza ristoranti geolocalizzati resta solo in tabella.
- Le 4 regole:
  1. **Caricamento lazy**: entry `jsBack/platform/maps.js` di 0.95 kB (gzip 0.60 kB) con `IntersectionObserver`; quando il box è visibile fa `import("./map/platformMap.js")` (Leaflet + `leaflet/dist/leaflet.css`) insieme al fetch dei dati. Il box ha già la sua altezza (nessun salto di layout) e mostra «Caricamento della mappa…»; in caso di errore un messaggio, mentre la classifica a destra resta valida.
  2. **Tile OSM esterne**: `https://tile.openstreetmap.org/{z}/{x}/{y}.png` richieste dal browser, mai salvate o fatte passare dal backend; attribuzione «© OpenStreetMap contributors» con link al copyright sempre visibile; `maxZoom` 19, buffer di default, nessun precaricamento, zoom con la rotella disattivato (niente pagina «intrappolata»).
  3. **Rendering**: circa 261 cerchi, nessun clustering (soglia per ripensarci: circa 2.000 punti), nessuna libreria aggiuntiva.
  4. **Isolamento**: tutta la logica in `jsBack/platform/map/` (`loader.js` senza Leaflet, `mapData.js` funzioni pure, `platformMap.js` chunk lazy); popup e tooltip costruiti con nodi di testo; una sola dipendenza nuova, `leaflet` ^1.9.4.
- **Misure di peso** (`npm run build`): chunk lazy `platformMap` JS **151.15 kB raw / 44.44 kB gzip** + CSS **10.57 kB / 2.63 kB**; entry `maps.js` **0.95 kB / 0.60 kB**; bundle iniziale del sito `vue` invariato (287.30 kB / 100.22 kB, senza Leaflet); `platform.css` 35.81 kB / 6.41 kB.
- **Verifiche obbligatorie**:
  - manifest della build: Leaflet compare **solo** tra i `dynamicImports` di `maps.js`, in nessun `imports` di `vue.js`, `app.js`, `charts.js` o altri entry (test automatico in `tests/js/platformMap.spec.js`, attivo quando esiste `public/build/manifest.json`);
  - Vitest con il chunk mockato: nessun import né fetch finché il box non è visibile; import e fetch una sola volta quando lo diventa; nessun effetto sulle tab senza box; messaggio di errore se i dati non arrivano;
  - browser reale (Chromium headless pilotato via DevTools Protocol, login admin, richieste di rete registrate): **Mappe** → chunk Leaflet (JS + CSS) 2 richieste, dati 1, tile 12; **Grafici, Tabelle, Utenti** → 0 richieste di Leaflet, tile o dati della mappa; console senza errori, nessuno scroll orizzontale a 1280 e a 390 px;
  - dati reali (261 ristoranti tutti con coordinate) e record senza coordinate (`MapsTabTest`: ristorante con `latitude` NULL escluso dai punti, presente in «Senza posizione», contato nella zona, centro della zona dalla media dei soli geolocalizzati).
- Problema trovato con la verifica visiva: con `preferCanvas` i cerchi non si vedevano, perché la regola globale `canvas { max-width: 100% }` di `base.css` deforma il canvas di Leaflet (più largo del box). Passato al renderer SVG di Leaflet (protetto da `leaflet.css`, e ogni cerchio è un vero bersaglio DOM). Cerchi ridotti (3–14 px; zone 8–36 px) per non coprirsi. Il piano diceva `preferCanvas`: decisione aggiornata.
- File: `package.json`/`package-lock.json` (`leaflet`), `app/Analytics/GeoStats.php`, `app/Http/Controllers/Platform/MapController.php`, `routes/web.php`, `resources/views/platform/maps.blade.php`, `resources/js/jsBack/platform/{maps.js,map/loader.js,map/mapData.js,map/platformMap.js}`, `vite.config.js`, `resources/css/platform.css`, `resources/js/utils/strings.json` (`platform.maps.*`, 14 chiavi in 5 lingue), `tests/Feature/Platform/MapsTabTest.php`, `tests/js/platformMap.spec.js`, `AGENTS.md`, `dev/mappa.html`.
- Test: `MapsTabTest` (5), `platformMap.spec.js` (7). `php artisan test` **189 passati** · `npm run test:js` **275** · `npm run build` ok.
- Prossimo step: 4.4.

### 4.4 — Tab Tabelle (2026-11-24) ✅ — **fase F4 chiusa**
- Stato: completato. Tab funzionante sui dati reali.
- Sotto-schede (tutte con i filtri condivisi del periodo, città e cucina, che si conservano tra le sotto-schede):
  - **Ristoranti**: valore lordo, completati, valore medio, ricavi (stima), % annullati, voto; ricerca per nome; colonne ordinabili; paginazione da 20; link al dettaglio.
  - **Ordini**: numero, data, ristorante, cliente (nome ed email), stato (badge con testo: completato, in corso, annullato, pagamento fallito), importo; filtro per stato; ricerca per numero d'ordine, cliente, email, ristorante o Transaction ID; ordinabili per data e importo.
  - **Clienti** (per email, ospiti compresi): account registrato o ospite, valore lordo, completati, valore medio, primo e ultimo ordine; ricerca per nome o email.
  - **Zone**: per città o per CAP (del ristorante), con ristoranti, valore e quota, completati, valore medio, ricavi, % annullati; la città porta ai suoi ristoranti.
  - **Periodi**: per giorno, settimana o mese (giorni fino a un anno), dal più recente, con variazione sul periodo prima, clienti e nuovi clienti, annullati e falliti.
- Dettagli: **ordine** (righe al prezzo pagato, totale, gestore e Transaction ID, eventuale rifiuto, consegna, cliente e suo account; gli ordini `pending` danno 404), **ristorante** (KPI con confronto, andamento per periodo, proprietario, voto, 10 piatti più venduti, link al sito), **cliente** (totali del periodo e di sempre, account se registrato, ordini paginati).
- Sicurezza delle query: `App\Analytics\Tables` accetta come ordinamento solo le chiavi delle whitelist (qualsiasi altro valore → default) e solo `asc|desc`; la ricerca è sempre un parametro (`LIKE` con `%` e `_` dell'utente neutralizzati), lunga al massimo 100 caratteri; `section`, `state`, `by` e `unit` sono in whitelist. Ordinamento per nome con `LOWER()` (stesso risultato in MySQL e SQLite).
- Accessibilità: intestazioni ordinabili come link con `aria-sort`, stati come badge con testo, numero di risultati in `role="status"`, nota fissa sui ricavi stimati.
- Verifica su MySQL (server di sviluppo, curl da admin): 14 combinazioni di sezioni, ordinamenti, filtri, pagine e dettagli → tutte 200 e nessun errore SQL in modalità strict, tempi tra 0.02 e 0.24 s. Screenshot reali della tabella ristoranti e del dettaglio ordine: nessun errore in console.
- File: `app/Analytics/Tables.php`, `app/Http/Controllers/Platform/TableController.php`, `routes/web.php` (dettagli `tables/orders/{order}`, `tables/restaurants/{restaurant}`, `tables/customer`), `resources/views/platform/tables.blade.php`, `resources/views/platform/tables/{order,restaurant,customer}.blade.php`, `resources/views/components/platform/th.blade.php`, `resources/css/platform.css`, `resources/js/utils/strings.json` (`platform.tables.*`, 83 chiavi in 5 lingue, solo aggiunte), `tests/Feature/Platform/TablesTabTest.php`, `dev/mappa.html`.
- Test: `TablesTabTest` (8). `php artisan test` **197 passati** · `npm run test:js` **275** · `npm run build` ok.
- Prossimo step: 5.1.

### 5.1–5.3 — Tab Utenti: elenco, creazione, modifica, ban (2026-11-24) ✅ — **fase F5 chiusa**
- Stato: completato. Le 4 tab del backoffice sono tutte funzionanti; rimossa la stringa provvisoria `platform.common.comingSoon`.
- **5.1 Elenco e scheda**: conteggi per ruolo e bannati; ricerca per parole (ogni parola deve comparire in nome, cognome, email o ragione sociale: «mario rossi» trova Mario Rossi; jolly `%`/`_` neutralizzati; niente concatenazioni SQL non portabili); filtri ruolo (cliente, ristoratore, admin) e stato (attivo, bannato); ordinamento in whitelist (nome, email, ruolo, data di creazione); paginazione da 20; ultime 8 attività dell'audit. Scheda: ruolo, dati, stato, data di creazione, email verificata, Google collegato sì/no, ordini con quella email (link al dettaglio cliente delle Tabelle), ristoranti del ristoratore, attività dell'account; motivo per cui un'azione non è disponibile (il proprio account, un admin, un account demo).
- **5.2 Creazione e modifica** (`StoreUserRequest`, `UpdateUserRequest`): nuovo account **solo cliente o ristoratore** (`role=admin` → errore, nessun account), email unica, password ≥ 8 con conferma, P. IVA obbligatoria e unica solo per i ristoratori. Modifica di nome, cognome, email (unica), telefono, ragione sociale e P. IVA (solo ristoratori); ruolo, password, ban e campi interni inviati a mano vengono ignorati. Audit `user.created` (ruolo, email) e `user.updated` con i campi cambiati vecchio → nuovo (mai password). Salvataggio senza modifiche → nessuna riga di audit e messaggio «Niente da salvare».
- **5.3 Ban e riattivazione**: motivazione obbligatoria (3–500 caratteri), salvate data, motivazione e admin; l'account viene disconnesso alla richiesta successiva (middleware `active`) e il login è rifiutato; **D4**: `POST /api/orders` verso un ristorante di un ristoratore bannato risponde `{errors: {restaurant_id}}` (`api.order.notAccepting`, 5 lingue) senza addebito, il ristorante resta visibile; la riattivazione toglie tutto e registra il motivo precedente. Pannello del ban evidenziato con il bordo `--tomato`.
- **Protezioni** (`UserPolicy`, verificate anche lato server con 403): nessuna modifica né ban del proprio account, di altri admin e degli account demo (D6); gli admin attivi quindi non possono diminuire dal pannello (`activeAdminCount` invariato nei test). Rotte di scrittura con `throttle:30,1`.
- Nota: `restaurants.accepting_orders` esiste ma il checkout non lo controllava già prima; resta fuori da questo step (solo il ban blocca gli ordini).
- Verifica visiva (Chromium headless, dati MySQL reali): elenco, scheda di un ristoratore con 28 ristoranti e pannello del ban, form del nuovo account; console senza errori. Conteggi riformulati come «Clienti: 1 · Ristoratori: 60…» (evita i plurali sbagliati).
- File: `app/Http/Controllers/Platform/UserController.php`, `app/Http/Requests/Platform/{StoreUserRequest,UpdateUserRequest}.php`, `app/Http/Controllers/Api/OrderController.php`, `routes/web.php`, `resources/views/platform/users/{index,show,form}.blade.php`, `resources/views/platform/users/partials/audit.blade.php` (rimossa `platform/users.blade.php`), `resources/css/platform.css`, `resources/js/utils/strings.json` (`platform.users.*`, 74 chiavi, `api.order.notAccepting`), `tests/Feature/Platform/UsersTabTest.php`, `dev/mappa.html`.
- Test: `UsersTabTest` (10). `php artisan test` **207 passati** · `npm run test:js` **275** · `npm run build` ok.
- Prossimo step: 6.1.

### 6.1 — Google OAuth (2026-11-24) ✅ — **fase F6 chiusa** (manca solo la prova con un client Google vero)
- Stato: completato e testato con Socialite finto; la prova end-to-end richiede un client OAuth nella Google Cloud console (vedi «Problemi aperti»).
- Modifiche:
  - `composer require laravel/socialite` (^5.33); `config/services.php` → `google` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, callback relativo `/auth/google/callback` che segue l'host della richiesta); `.env.example`. **Spento** finché il client non è configurato: rotte 404 e bottoni nascosti.
  - `Auth\GoogleController`: `redirect` (solo ospiti, `?role=` in whitelist salvato in sessione, scope `openid email profile`), `link` (da loggati), `callback` (rate limit 20/min). Regole: **state OAuth in sessione** (mai `stateless()`, un callback falsificato → «accesso scaduto o non valido»); **email Google verificata** obbligatoria; chiave = **Google id** (`sub`), mai l'email; **email già registrata → nessun collegamento automatico** («accedi con la password, poi collega Google dal tuo account»), così un indirizzo Google non può prendere un account esistente; nuovo account con il ruolo della scheda (mai admin), email verificata, senza password, foto Google in `avatar_url`; ruolo degli account esistenti **mai cambiato**; **admin esclusi** (login e collegamento); **bannati rifiutati**; sessione rigenerata dopo il login.
  - `AccountController` + `account/show` (`/account`, tutti i ruoli): dati, stato Google, **collega** (via OAuth, rifiuta un Google id già usato da un altro account) e **scollega** (solo se l'account ha una password, altrimenti resterebbe senza accesso), completamento della **P. IVA** per il ristoratore creato con Google.
  - Middleware `profile.complete` sul gruppo `admin`: ristoratore senza P. IVA → `/account`.
  - `<x-google-button>` (G multicolore di Google, «oppure» separatore) nel login e nella registrazione, con il ruolo della scheda. Link «Il tuo account» nel menu del sito (`AccountMenu`), dell'area partner e del backoffice.
  - Box demo: i bottoni ora dicono «Entra come Admin della piattaforma / Ristoratore / Cliente» (prima «Entra come Foodsy» per l'admin).
  - Stringhe `partner.auth.google.*`, `partner.account.*`, `site.account.settings` in 5 lingue (solo aggiunte).
- Verifica visiva: login con box demo, registrazione partner a schede, header del sito con l'avatar di Mario (nessun errore in console).
- File: `composer.json`/`composer.lock`, `config/services.php`, `.env.example`, `app/Http/Controllers/Auth/GoogleController.php`, `app/Http/Controllers/AccountController.php`, `app/Http/Middleware/EnsureProfileComplete.php`, `bootstrap/app.php`, `routes/web.php`, `resources/views/account/show.blade.php`, `resources/views/components/{google-button,demo-accounts}.blade.php`, `resources/views/auth/{login,register}.blade.php`, `resources/views/layouts/{app,platform}.blade.php`, `resources/js/components/AccountMenu/AccountMenu.vue`, `resources/css/admin.css`, `resources/js/utils/strings.json`, `tests/Feature/GoogleAuthTest.php`, `tests/js/account.spec.js`, `AGENTS.md`, `dev/mappa.html`.
- Test: `GoogleAuthTest` (13: spento senza client; bottoni con il ruolo; state in sessione e ruolo admin rifiutato; **callback falsificato respinto dal provider reale**; nuovo account; ristoratore → P. IVA prima dell'area partner; email non verificata; email esistente mai collegata; account collegato che entra con il suo ruolo anche con un'email Google diversa; admin e bannati; collegamento e Google id già usato; scollegamento solo con password; admin senza collegamento). `php artisan test` **220 passati** · `npm run test:js` **275**.
- Problemi aperti: **prova manuale con un client Google** (creare credenziali OAuth «Web application» nella Google Cloud console, URI di reindirizzamento `http://localhost:8000/auth/google/callback` più quelli del Codespace e della produzione, schermata di consenso in modalità test con gli account di prova; poi `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` nel `.env`).
- Prossimo step: **7.1 `orders.user_id`** (il checkout da loggato collega l'ordine e precompila i dati), poi 7.2 «I miei ordini» e 7.3 dataset di Mario Rossi e clienti registrati.

### 7.1 — `orders.user_id` e checkout da loggato (2026-11-25) ✅
- Stato: completato. Migration applicata anche al DB MySQL di sviluppo (container `app`): 11.534 ordini, tutti da ospite (`user_id` NULL), nessun dato toccato.
- Modifiche:
  - migration `2026_11_25_000000_add_user_id_to_orders_table`: `user_id` FK nullable verso `users`, `nullOnDelete` (l'account eliminato lascia lo storico del ristorante), indice `(user_id, created_at)` per «I miei ordini». `down()` verificato su SQLite.
  - `Order::user()` e `User::orders()`; `user_id` **non** fillable.
  - `Api\OrderController@store`: `user_id` dalla sessione (Sanctum SPA) **solo per i clienti**; ristoratori e admin ordinano come ospiti (decisione: «I miei ordini» è una funzione del cliente, le altre aree hanno la loro home). Collegati anche gli ordini `failed`/`pending`: lo storico del cliente è completo, «I miei ordini» deciderà cosa mostrare. Un `user_id` mandato dal client è ignorato.
  - `POST /api/orders` con il middleware `active`: un account bannato viene disconnesso e riceve 403 invece di ordinare (D4 esteso ai clienti).
  - Checkout: `customerFromUser`/`prefillCustomer` in `Payment.utils.js` e un `watch` immediato su `state.user` in `Payment.logic.js` (anche quando `/api/me` risponde dopo il mount). Si riempiono solo i campi vuoti; mai l'indirizzo (l'account non lo ha); telefono senza spazi per stare negli 11 caratteri del campo. Partner e admin non vengono precompilati. Ospite invariato.
  - `UserFactory`: `role = customer` esplicito (uguale al default della colonna), perché `actingAs` usa il modello in memoria senza rileggere il DB.
- File: migration, `app/{Order,User}.php`, `app/Http/Controllers/Api/OrderController.php`, `routes/api.php`, `database/factories/UserFactory.php`, `resources/js/pages/Payment/{Payment.utils,Payment.logic}.js`, `tests/Feature/Api/CustomerOrdersTest.php` (nuovo), `tests/js/payment.spec.js`, `AGENTS.md`, `dev/mappa.html` (relazione `users → orders`, file nuovi).
- Test: `CustomerOrdersTest` (7: ospite senza account; cliente collegato; pagamento rifiutato collegato; account mai preso dal client; partner e admin come ospiti; bannato 403 e disconnesso; account eliminato → `user_id` NULL). Vitest: mappatura account → campi, solo campi vuoti, precompilamento anche con `/api/me` tardivo, partner non precompilati. `php artisan test` **227 passati** · `npm run test:js` **279** · `route:list` e `npm run build` ok.
- Problemi aperti: nessuno. Il backoffice identifica ancora i clienti per email (`Tables::customers`): va bene finché gli ordini storici sono da ospite; si rivede al 7.3 se serve.
- Prossimo step: **7.2** pagina SPA «I miei ordini» (`GET /api/me/orders` paginato, `auth:sanctum` + `active`, solo ordini propri).

### 7.2 — Pagina SPA «I miei ordini» (2026-11-25) ✅
- Stato: completato. Provato sull'app di sviluppo (container `app`, porta 8000): ospite → 401; login di Mario → 200 con lista vuota (il suo storico arriva al 7.3); `/my-orders` servita dalla catch-all.
- Modifiche:
  - `GET /api/me/orders` (`Api\MyOrdersController`, `auth:sanctum` + `active` + `throttle:60,1`): ordini dell'account **solo per `user_id`** (mai per email), dal più recente, `paginate(5)`; per ordine `id`, `placed_at`, `delivered_at`, `price`, `status`, `fulfillment_status`, `address`, `restaurant` (`name`, `slug`, `img`; `null` se eliminato), `lines` (nome, quantità, prezzo pagato; piatti eliminati inclusi). Niente dati di pagamento (`transaction_id`, `payment_error`, driver) né dati cliente.
  - Pagina `pages/MyOrders` su `/my-orders` (nome `myOrders`): stati caricamento, ospite (401 → invito ad accedere, così funziona anche prima di `/api/me`), errore con «Riprova», vuoto con «Trova un ristorante», lista a card (foto 3:2 del ristorante, link al menu, data nella lingua del sito, **uno stato** da pagamento + consegna: In corso / Consegnato / Annullato / Pagamento non riuscito / In attesa, righe, totale, «Ordina di nuovo» verso il menu). Ricarica se l'account cambia (login da ospite o un altro account), senza doppia richiesta quando `/api/me` arriva dopo il mount.
  - `PaginateEl`: modalità `local` (emette `go`, la pagina carica da sé) e finestra di pagine con «…» (`pageWindow` in `utils/pagination.js`, come la vista Blade `pagination/foodsy`): con 250 ordini sono 50 pagine, prima comparivano 50 bottoni. Le liste corte (≤ 7 pagine) restano identiche.
  - Link «I miei ordini» nel menu account (solo clienti, chiude il menu) e nella pagina di successo (clienti con il login).
  - Stringhe `site.account.orders` e `site.orders.*` in 5 lingue (solo aggiunte).
- File: `app/Http/Controllers/Api/MyOrdersController.php` (nuovo), `routes/api.php`, `resources/js/api/{account,index}.js`, `resources/js/pages/MyOrders/*` (nuovi), `resources/js/pages/index.js`, `resources/js/routes.js`, `resources/js/components/PaginateEl/{PaginateEl.vue,PaginateEl.logic.js}`, `resources/js/utils/{pagination,index}.js`, `resources/js/components/AccountMenu/AccountMenu.vue`, `resources/js/pages/SuccessPage/{SuccessPage.vue,SuccessPage.logic.js,SuccessPage.css}`, `resources/js/utils/strings.json`, `tests/Feature/Api/CustomerOrdersTest.php`, `tests/js/{myOrders,account,app.smoke}.spec.js`, `AGENTS.md`, `dev/mappa.html`.
- Test: `CustomerOrdersTest` +4 (ospite 401; solo ordini propri, dal più recente, righe e totale coerenti, nessun dato di pagamento o cliente; paginazione e ordini di ristoranti/piatti eliminati; bannato 403 e sessione web chiusa). Vitest: `myOrders.spec.js` (13), menu account (link solo ai clienti, chiusura), smoke di `/my-orders`. `php artisan test` **231 passati** · `npm run test:js` **293** · `route:list` e `npm run build` ok.
- Nota: con `auth:sanctum` la guard di default della richiesta diventa `sanctum`; `EnsureActive` chiude comunque la guard `web` (nei test: `assertGuest('web')`).
- Problemi aperti: verifica visiva nel browser da fare (nessun browser disponibile in questa sessione); diventa significativa dopo il 7.3, quando Mario avrà uno storico.
- Prossimo step: **7.3** `CustomerProfilesSeeder` (Mario ~250 ordini 2025-01 → 2026-11 e ~300 clienti sintetici registrati con i loro ordini collegati), aggiornamento di `dev/PIANO_DATASET.md`; poi `migrate:fresh --seed` sul DB di sviluppo **solo dopo conferma**.

### 7.3 — `CustomerProfilesSeeder`: clienti registrati e storico di Mario (2026-11-25) ✅ — **fase F7 chiusa**
- Stato: completato. Applicato al DB MySQL di sviluppo con `php artisan db:seed --class=CustomerProfilesSeeder` nel container `app`: **non è servito `migrate:fresh`**, il seeder è idempotente e aggiunge soltanto (rilanciato: nessuna differenza).
- Modifiche:
  - `CustomerProfilesSeeder` (in `DatabaseSeeder` dopo `OrderHistorySeeder`): A) i clienti sintetici più frequenti diventano account `customer` (`created_at` al primo ordine) e i loro ordini ricevono `user_id`; B) storico di Mario Rossi secondo il profilo in `dev/PIANO_DATASET.md`.
  - **Cambio rispetto al piano**: l'abitudine alimentare di Mario è **vegetariano**, non «senza lattosio». Il piano lo citava solo come esempio; i locali di pizza e giapponesi di Zurigo non dichiarano quasi mai piatti senza lattosio, quindi l'abitudine non sarebbe stata leggibile dai dati.
  - `OrderHistorySeeder`: `FROM`/`TO`, `DRINK_COURSES` e `insert()` pubblici, condivisi con il nuovo seeder (dati prodotti identici).
  - `config/seeding.php`: `customer_password`, `registered_customers` (full 300, test 5), `mario_scale` (full 1.0, test 0.15).
  - Backoffice, tab Tabelle → Ordini: una ricerca di sole cifre mette **prima l'ordine con quell'ID esatto**, poi i Transaction ID che contengono le stesse cifre (prima, con gli ordini di Mario, l'ID cercato poteva finire oltre la prima pagina).
- Numeri sul DB dev: 301 clienti (300 sintetici + Mario), 8.930 ordini su 11.805 collegati a un account; Mario 271 ordini (2025-01-16 → 2026-11-21) su 33 ristoranti, 125 pranzi, 258 consegnati, 5 annullati, 5 falliti, 3 in preparazione; 92% di piatti vegetariani (41% nei menu degli stessi locali). `/api/me/orders` di Mario: 271 ordini su 55 pagine; nel backoffice risulta «Registered».
- File: `database/seeders/CustomerProfilesSeeder.php` (nuovo), `database/seeders/{DatabaseSeeder,OrderHistorySeeder}.php`, `config/seeding.php`, `app/Analytics/Tables.php`, `tests/Feature/CustomerProfilesSeederTest.php` (nuovo), `tests/Feature/{DatabaseSeederTest,UserRolesTest}.php`, `tests/Feature/Platform/TablesTabTest.php`, `dev/PIANO_DATASET.md`, `README.md`, `AGENTS.md`, `dev/mappa.html`.
- Test: `CustomerProfilesSeederTest` (6: storico coerente con indirizzo per pasto, pranzi solo feriali, totali, niente stellati, account creato prima del primo ordine; locali abituali; abitudine leggibile dai tag, oltre 20 punti sopra i menu; clienti registrati senza ordini da ospite rimasti; solo clienti con ordini e stessa email; rilancio senza modifiche). `DatabaseSeederTest` include il nuovo seeder (doppio seed, ogni seeder da solo due volte). `UserRolesTest`: l'unico admin è quello demo, i clienti sono Mario più i sintetici registrati. `TablesTabTest`: l'ID esatto è il primo risultato. `php artisan test` **237 passati** (20 s) · `npm run test:js` **293**.
- Problemi aperti: verifica visiva nel browser di «I miei ordini» con lo storico di Mario (nessun browser in questa sessione).
- Prossimo step: **8.1** rifinitura UI/UX (stati vuoti, caricamento, responsive a 600/900/1200) e **8.2** aggiornamento finale di mappa, README e AGENTS con suite completa.

### 8.1 — Rifinitura UI/UX e verifica responsive (2026-11-25) ✅
- Stato: completato. Verifica **visiva** con Chromium headless (Playwright nello scratchpad, fuori dal progetto) sul server di sviluppo: screenshot a 375/600/900/1200 e controllo automatico dello scroll orizzontale (`scrollWidth`) su sito (home, menu, carrello, I miei ordini), login, registrazione, `/account`, area partner e backoffice (grafici, mappe, tabelle, utenti), da ospite, cliente, ristoratore e admin. Risultato finale: nessun overflow, nessun errore o warning in console.
- Problemi trovati e corretti:
  - **header del sito a 375 px con account loggato**: l'avatar faceva uscire il menu dallo schermo (4 px di scroll orizzontale, logo schiacciato in «Foods»). Sotto i 600 px l'avatar esce dall'header e l'account va nel menu laterale (`MenuMobile`: nome, «I miei ordini» per i clienti, «Il tuo account», area del ruolo, «Esci»; il drawer ora scorre se è più alto dello schermo);
  - **header a 900 px con account loggato**: il nome accanto all'avatar mandava a capo «Guest favourites» e «All restaurants». Il nome compare da 1200 px (come «Accedi» per gli ospiti) e i link dell'header non vanno più a capo;
  - **header dell'area partner a 375 px**: il badge «PARTNER» copriva «Foodsy» e il menu account usciva a destra. Sotto i 600 px solo la cloche (`<x-logo wide />`, come il backoffice);
  - **paginazione a 375 px su due righe**: gap più stretto sotto i 600 px e bottoni «prima/ultima» nascosti (`ui-page--edge`: la finestra mostra già la pagina 1 e l'ultima);
  - **«I miei ordini», caricamento**: la riga di testo è diventata 3 card scheletro della stessa forma (pulsano solo in `opacity`, testo per i lettori di schermo) e la pagina ha un'altezza minima, così il footer non salta;
  - **checkout**: ora propone anche l'**indirizzo dell'ultimo ordine** (`last_address` in `/api/me`, solo per i clienti e solo dai propri ordini), sempre solo nei campi vuoti;
  - **dati di Mario**: tre ordini «in corso» sparsi su due giorni non erano credibili: resta in preparazione solo la cena dell'ultimo giorno (storico rigenerato sul DB dev, stessi numeri: 271 ordini, 92% vegetariano).
- Verificati e già a posto: stati ospite, errore e vuoto di «I miei ordini», menu account desktop, `/account` su telefono, checkout precompilato.
- File: `resources/js/components/MenuMobile/{MenuMobile.vue,MenuMobile.logic.js,MenuMobile.css}`, `resources/js/components/AccountMenu/AccountMenu.css`, `resources/js/components/Header/Header.css`, `resources/js/components/PaginateEl/PaginateEl.vue`, `resources/css/ui.css`, `resources/js/pages/MyOrders/{MyOrders.vue,MyOrders.css}`, `resources/js/pages/Payment/Payment.utils.js`, `resources/views/layouts/app.blade.php`, `app/Http/Controllers/Api/MeController.php`, `database/seeders/CustomerProfilesSeeder.php`, `tests/Feature/Api/MeTest.php`, `tests/js/{account,payment}.spec.js`.
- Test: `MeTest` +1 (indirizzo dell'ultimo ordine al cliente, `null` al ristoratore); Vitest +4 (blocco account del menu mobile: ospite, cliente, ristoratore, «Esci») e indirizzo nel precompilamento.

### 8.2 — Documentazione e regressione finale (2026-11-25) ✅ — **fase F8 chiusa, piano completato**
- `README.md`: funzioni dell'account cliente (ordini collegati, checkout precompilato, «I miei ordini», Google), paragrafo sul backoffice `/platform` (4 tab, commissione simulata del 25% indicata come stima), API `/api/me` e `/api/me/orders`, relazione `users` 1─N `orders`.
- `AGENTS.md`: ordini dei clienti, «I miei ordini», `PaginateEl` `local`/`pageWindow`, account nel menu mobile sotto i 600 px, `last_address`, `CustomerProfilesSeeder`.
- `dev/mappa.html`: file nuovi (pagina, controller, seeder, test), relazione `users → orders` in `DB_LINKS`/`DB_RELS`, `MenuMobile`/`AccountMenu`, `STYLE_PAGE` (barre affollate sui telefoni, paginazione, stati di una lista, checklist a 375/600/900/1200 con account loggato). Aperta in Chromium: nessun errore, tutti i 657 `go`/`rel` puntano a file presenti in `FILES`.
- Regressione: `php artisan test` **238 passati** · `npm run test:js` **297** · `php artisan route:list` ok · `npm run build` ok.
- **Prove manuali che restano** (non automatizzabili qui):
  1. Google OAuth con un client vero (vedi 6.1: credenziali «Web application», URI di callback di localhost, Codespace e produzione, `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET`);
  2. un pagamento in sandbox Braintree da cliente loggato, per vedere l'ordine comparire in «I miei ordini» (con il driver `fake` è verificato dai test);
  3. decisioni ancora aperte del piano: 7 (2FA admin, rinviata) e 8 (commissione reale, futura).
- Prossimo passo: revisione del branch `feat/auth-platform` e merge su `main` (non fatto: serve la tua conferma).

### Ritocchi dopo il piano (2026-11-25)
- **Password dell'account demo ristoratore**: `foodsy-owner` (era la password storica del seed), in linea con `foodsy-admin` e `foodsy-demo`. Cambiata in `config/demo.php`, `.env.example`, `UsersTableSeeder`, test, README, mappa e in questo documento; migration di dati `2026_11_25_010000_set_demo_restaurateur_password` per i DB già popolati (i seeder non sovrascrivono un account). DB dev: migration applicata e `db:seed` rilanciato (idempotente, conteggi invariati); i tre account entrano con le nuove credenziali.
- **Login**: pagina alta uno schermo (layout `auth` variante `fit`), «Use test users» sotto «Log in» con la finestra degli account demo. La finestra non scorre mai in verticale (verificato in 5 lingue da 360×640 a 1440×900; solo a 320×568 resta lo scroll interno come rete di sicurezza): fila di card con snap e puntini sotto i 900 px, tre colonne da 900 px.
- **Mappa del backoffice, tile in 403** (segnalato dall'utente, non riproducibile da qui: le tile rispondono 200 con e senza Referer): `referrerPolicy` esplicita sulle tile e avviso sulla mappa se vengono rifiutate. Se il 403 continua, la causa è il contesto in cui si apre la pagina (anteprima incorporata, privacy del browser): alternativa da decidere un fornitore di tile diverso (cambierebbe la decisione D1).

