# Login clienti, Google OAuth e backoffice admin `/platform` — piano v2

Stato: **v2 approvata il 2026-11-23 · fase F0 completata · nessun codice applicativo modificato**.
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
  - **Federico Romiti**: `federico_romiti@gmail.com` / `12345678` (`UsersTableSeeder`, ristoratore storico);
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
  - **Mario Rossi** `mario.rossi@foodsy.test` (cliente);
  - **Federico Romiti** `federico.romiti@foodsy.test` (D2, email migrata ovunque), password storica `12345678`, `role=restaurateur`.
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
- Modifiche: seeder (`UsersTableSeeder`, `RestaurantsTableSeeder` ×3, `Support/CatalogPlan::ROMITI`), test (`Concerns/SeededFixtures`, `UserRolesTest`), `dev/mappa.html` (blocco `DATI`: credenziali di avvio, più conteggio ristoranti corretto a 33); migration di dati `2026_11_24_010000_move_demo_restaurateur_email` (idempotente; i ristoranti seguono per `user_id`, ordini e voti non dipendono dall'email; rimuove eventuali token di reset della vecchia email). Password invariata (`12345678`).
- DB di sviluppo: migration applicata, l'utente id 9 ha la nuova email ed è `restaurateur`.
- Verifica: `grep -r "federico_romiti@gmail.com"` fuori da `vendor/`, `node_modules/`, `public/` → solo la migration (valore da sostituire) e questo piano (storia).
- Test: `php artisan test` **143 passati**.
- Prossimo step: **2.2 `config/demo.php`, `DemoAccountsSeeder`, comando `foodsy:make-admin`, box demo nel login**.
