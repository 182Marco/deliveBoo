# Piano statistiche — file di avanzamento

> File solo di sviluppo. Per riprendere il lavoro con contesto azzerato: leggere **Regole**, **Stato** e il primo passaggio non ✅.
> Legenda: ✅ fatto e verificato · 🔄 in corso · ⬜ da fare · ⚠️ problema/decisione.

## Obiettivo
1. **Statistiche dell'area partner** rifatte (`/admin/stats` = tutti i locali del ristoratore, `/admin/stats/{id}` = un locale): filtro di periodo, andamenti per giorno/settimana/mese su più anni, aggregati calcolati in SQL (oggi il browser riceve tutti gli ordini, ~3 MB, e somma i mesi di anni diversi).
2. **Backoffice `/platform/charts` veloce**: oggi ~11 s a cache vuota con ~185k ordini.

## Regole vincolanti (dall'utente)
1. **Generalizzazione**: funziona per qualsiasi ristoratore e numero di locali (0, 1, molti). Nessun riferimento a Romiti o al seed nel codice.
2. **Privacy**: al browser solo aggregati; niente email, telefono, indirizzo dei clienti; «clienti migliori» = nome + iniziale del cognome, ordini, spesa. Ownership in ogni query (gruppo: `r.user_id`) e controller (singolo: `cannot('manage', …)`).
3. **Performance**: niente N+1; EXPLAIN delle query nuove/modificate annotato qui; cache separata per proprietario, ristorante e periodo.
4. **Correttezza**: metriche additive del gruppo = somma dei singoli; clienti unici del gruppo con `COUNT(DISTINCT)` sugli ordini del gruppo (mai somma); medie del gruppo pesate (mai media delle medie).
5. **Compatibilità**: i numeri del backoffice non cambiano; test aggiornati e di non regressione.
6. **Questo file**: aggiornato dopo ogni passaggio; uno step è ✅ solo dopo verifica; un imprevisto si scrive qui prima di cambiare strada.
7. **Niente complessità inutile**: nessuna tabella precalcolata; al massimo un indice in una migration nuova.

## Misure di partenza (MySQL dev, 2026-11-24, ~185k ordini, buffer pool 128 MB)
- `/platform/charts` a cache vuota: ~11,5 s, 18 query. Le più lente: `cuisines` 2,8 s, `restaurants` 2,3 s, `customers` 1,4 s + 0,4 s, `zones` 1,2 s, `heatmap` 0,6 s, `totals` 0,45 s ×2, `deliveryTimes` ~1,1 s.
- Causa principale: `COUNT(DISTINCT customer_email)` in ogni `GROUP BY` (temp table), join `restaurant_type` per le cucine, sottoquery «primo ordine per email» su tutta la tabella, percentili con OFFSET.
- `/admin/stats/{id}`: 0,8–1 s, 2,8–3,5 MB di HTML (tutti gli ordini in JSON).

## Stato
- **Lavoro completato** (2026-11-24): tutte le fasi ✅. Possibili sviluppi (non fatti, vedi Decisioni): ridurre ancora il primo caricamento del gruppo grande.
- Baseline numerica salvata fuori dal repo: `<scratchpad>/snap_before.json` (script `snap.php`: summary, series, restaurants ×2 ordinamenti, zones città/CAP, cuisines, customers, heatmap su 5 periodi: 12 mesi, tutto, Zürich, Pizzeria Q1 2026, 20 giorni). Se lo scratchpad è sparito: ricrearla da `git stash` del codice prima della fase A.

## Fasi e passaggi

### Fase 0 — Preparazione
- ✅ 0.1 Creato questo file.
- ✅ 0.2 Collegarlo da `dev/mappa.html` (riga `F(...)`) e `AGENTS.md` (fatto insieme alla documentazione, fase D).

### Fase A — Backoffice veloce (stesso motore usato poi dal partner)
- ✅ A.1 Censire dove si legge `customers` di `series/restaurants/zones/cuisines` (viste, JS, GeoStats, Tables) e fissare la baseline numerica (snapshot JSON di `/platform/data/charts` e di `Tables` sul DB di test e su quello dev) per il confronto.
- ✅ A.2 `OrderMetrics::aggregates(bool $customers = true)`; `series()` senza distinct se `customers` della serie non è letto.
- ✅ A.3 `OrderMetrics::byRestaurant()` (una query `GROUP BY restaurant_id`) e `restaurants/zones/cuisines` combinati in PHP; `customers` per zona/cucina/locale con query dedicata solo se mostrato (A.1).
- ✅ A.4 `deliveryTimes()` con istogramma dei minuti + percentili in PHP (stessa regola `floor((n-1)·q)`).
- ✅ A.5 Migration nuova con indice `orders(customer_email, status, created_at)`; EXPLAIN prima/dopo.
- ✅ A.6 `docker-compose.yml`: buffer pool MySQL dev 512 MB.
- ✅ A.7 Verifica: numeri identici alla baseline, test verdi, tempi a cache vuota (obiettivo < 3 s).

### Fase B — Motore per il partner
- ✅ B.1 `Period`: campi `ownerId`/`restaurantId`, factory `forPartner`, `previous/query/cacheKey` li includono; `presets()` con «Tutto» dal primo ordine del partner.
- ✅ B.2 `OrderMetrics::orders()` filtra per proprietario/ristorante; `customers()` con primo ordine nello stesso ambito.
- ✅ B.3 `App\Analytics\RestaurantMetrics`: `ratings`, `deliverySeries`, `topDishes`, `topCustomers`, `restaurantsTable` (EXPLAIN annotati).
- ✅ B.4 Costante condivisa per i corsi «bevanda» (oggi `OrderHistorySeeder::DRINK_COURSES`).

### Fase C — Pagina partner
- ✅ C.1 `StatsController`: `index` (gruppo) e `show` (singolo, policy), `build()` condiviso; helper condivisi per etichette e bucket parziali.
- ✅ C.2 CSS condiviso `resources/css/analytics.css` (KPI, chart card, delta, tabelle, heatmap) importato da `admin.css`; `platform.css` invariato nell'aspetto.
- ✅ C.3 Vista `admin/stats/show.blade.php` (selettore locale, filtri senza città/cucina, KPI, grafici, classifica locali, clienti migliori); `<x-platform.filters :places="false">`.
- ✅ C.4 `jsBack/partnerStats.js` con `chartTheme.js`; `vite.config.js`; rimozione `jsBack/chart*.js`.
- ✅ C.5 Testi `partner.stats` in 5 lingue (chiavi vecchie rimosse).
- ✅ C.6 Link «Statistiche» nel menu partner e sulle card dei ristoranti.

### Fase D — Test, documentazione, verifica
- ✅ D.1 Test: non regressione `OrderMetrics`, nuovo `Admin/StatsTest`, `OwnershipTest` aggiornato.
- ✅ D.2 `php artisan test`, `npm run test:js`, `route:list`, `npm run build`.
- ✅ D.3 Misure finali (cache vuota) e controllo a vista dei dati.
- ✅ D.4 Documentazione: `dev/mappa.html`, `README.md`, `AGENTS.md`, `dev/PIANO_DATASET.md`.

## Registro (modifiche, test, problemi, decisioni)
- A.1 — Censimento `customers`: mostrato solo nei KPI (`summary.current.customers`), nella serie di `customers()` (Charts e Tables › Periodi) e nelle pagine del singolo cliente. **Non** mostrato per locale/zona/cucina/bucket di `series()` (Charts, Tables › Locali e Zone, Maps). Decisione: togliere il `COUNT(DISTINCT)` solo nei percorsi di `series/restaurants/zones/cuisines` di `OrderMetrics`; `GeoStats`, `Tables::restaurants`, `TableController` restano come sono (nessun cambio di comportamento, nessun rischio).
- 2026-11-24 — Piano approvato dall'utente con le regole sopra; scelta: vista di gruppo + singolo locale.
- A.2–A.4 — `OrderMetrics`: `aggregates(bool $customers = true)`, `series()` senza distinct, `byRestaurant()` (una query) + `sum()` (importi arrotondati ai centesimi), `restaurants/zones/cuisines` combinati in PHP, `deliveryTimes()` con istogramma. **Verifica**: confronto con la baseline (script `cmp.py`, ignorando `customers` per gruppo che non è mostrato) = 0 differenze su 5 periodi; primo tentativo dava differenze di 1 centesimo nella commissione (somma float) → risolto arrotondando in `sum()`. `php artisan test` 294 verdi.
- A.5 — Migration `2026_11_27_000000_add_first_order_index_to_orders_table`: indice `(status, customer_email, created_at)`. ⚠️ Decisione: l'indice singolo `orders_customer_email_index` **resta** (lo usano le ricerche del singolo cliente), diversamente da quanto scritto nel piano iniziale. EXPLAIN sottoquery primo ordine: prima `orders_status_index` + `Using temporary` (~0,9 s), dopo `orders_status_customer_email_created_at_index` + `Using index` (~0,23 s).
- A.6 — `docker-compose.yml`: `command: --innodb-buffer-pool-size=512M` sul servizio `db`; `docker compose up -d db` ricrea il container, i dati restano nel volume (verificato: 184.782 ordini, pool 512 MB).
- A.7 (in corso) — `/platform/charts` a cache vuota: **~5,0 s, 16 query** (prima 11,5 s, 18 query). Restano lente le scansioni dei ~120k ordini dell'anno (0,6–1,1 s l'una: totals ×2, series ×2, customers, heatmap, istogramma). Ipotesi: leggono le righe intere. Esperimento: indice coprente sulle colonne delle metriche, misurato sul DB dev prima di decidere se metterlo nella stessa migration.
- A.7 — Esperimento indice coprente `(created_at, status, fulfillment_status, restaurant_id, price, customer_email, delivered_at)` creato a mano sul DB dev: pagina 5,0 → ~4,5 s, guadagno minimo → **indice rimosso** dal DB dev, non messo in migration. Misura del motivo: la stessa query dei totali costa 406 ms con il join, 319 ms senza, 49 ms il solo `COUNT(*)`: il costo è il calcolo delle `CASE` su ~120k righe, non la lettura. ⚠️ **Decisione**: obiettivo «< 3 s» non raggiunto; risultato accettato **11,5 → ~5,0 s a cache vuota (16 query, 9 scansioni dell'anno da 0,4–0,8 s)**, poi cache di 10 minuti. Scendere ancora richiederebbe tabelle pre-aggregate (escluse dalla regola 7) o fondere metriche diverse in una query (meno leggibile). Numeri identici alla baseline (verificato in A.2–A.4).
- Decisioni per la fase B (prese prima di scrivere il codice):
  - ambito partner in `Period` (`ownerId`, `restaurantId`), **non** in `query()` (che produce gli URL dei filtri): la chiave di cache diventa `partner:o{owner}:r{restaurant}:<nome>:<md5 dei filtri>` quando c'è un ambito, altrimenti resta `platform:<nome>:<md5>` (backoffice invariato);
  - filtro del gruppo con `o.restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?)` (usa l'indice `(restaurant_id, created_at)`), il singolo con `o.restaurant_id = ?`;
  - la pagina partner riusa `jsBack/platform/charts.js` (stesso schema JSON `#platform-charts`): nessun JS nuovo; i vecchi `jsBack/chart*.js` si eliminano;
  - etichette dei bucket, bucket parziali e righe della heatmap passano da metodi privati di `Platform\ChartController` a `App\Analytics\ChartFormat` (comportamento invariato) per usarli anche dal partner; la heatmap Blade diventa il componente `<x-platform.heatmap>`.
- B.1–B.2 — `Period`: campi `ownerId`/`restaurantId`, `forPartner()`, `scoped()`, `applyScope()` (gruppo: `o.restaurant_id IN (SELECT id FROM restaurants WHERE user_id = ?)`), `range()` condiviso con `fromRequest()`, `cacheKey()` con prefisso `partner:o…:r…`, `presets(?Period)`. `OrderMetrics::orders()` applica l'ambito; `customers()` calcola il primo ordine nello stesso ambito.
- B.4 — `Plate::DRINK_COURSES`; `OrderHistorySeeder::DRINK_COURSES` ora è un alias (i seeder e i test continuano a usarlo).
- B.3 — `App\Analytics\RestaurantMetrics`: `rating`, `ratings`, `deliverySeries`, `topDishes` (per nome del piatto: nel gruppo un piatto di più sedi è una riga), `topCustomers` (nome + iniziale, l'email resta nell'SQL), `restaurants` (tutti i locali del gruppo, anche senza ordini; 3 query in tutto), `shortName`.
  - **Verifica (tinker, DB dev, 12 mesi)**: singolo locale tutte le metriche in ~0,4 s a cache vuota; gruppo di 33 locali: summary 1,07 s, customers 1,75 s, dishes 1,55 s, tabella locali 1,1 s, rating 0,98 s, altre 0,25–0,45 s → **~8 s**. Somma dell'incasso della tabella locali = incasso del gruppo (1.872.494,90 = 1.872.494,90).
  - EXPLAIN totali del gruppo: `o` via `orders_restaurant_id_created_at_index` (semi-join sulla sottoquery dei locali), `r` eq_ref PRIMARY. Con gli ID scritti a mano MySQL sceglie il full scan (più lento): la sottoquery resta. Senza join e senza distinct la stessa query costa 236 ms invece di 463: il costo è il calcolo, come in A.7.
- C.1 — `App\Analytics\ChartFormat` (`labels`, `heatmap`, `partialBuckets`, `bucketLabel`) estratto da `Platform\ChartController` (che ora lo usa; `ChartController::bucketLabel` resta come delega per le viste Tables). `Admin\StatsController`: `index` = gruppo del proprietario loggato (con `?restaurant=ID` fa redirect a `show`, che controlla la proprietà), `show` = singolo con `cannot('manage')` → `admin.notYours.orders`; `build()` unico; nessun ordine passato alla vista. Test del backoffice (Charts/OrderMetrics/Tables/Maps) verdi dopo l'estrazione.
- C.2 — `resources/css/analytics.css` (filtri, preset, note, pannelli, KPI, chart card, tabelle dati, heatmap, più `.plat-stack` e `.plat-scope` nuovi) importato da `admin.css`; tolto da `platform.css`. Verifica sul CSS compilato: ogni selettore spostato compare una volta sola in `platform-*.css` e in `admin-*.css`.
- C.3 — Vista unica `admin/stats/show.blade.php` (selettore del locale, `<x-platform.filters :places="false">`, KPI, grafici con tabelle accessibili, heatmap, confronto locali solo nel gruppo, clienti migliori). Componenti: `filters` con prop `places` e preset «Tutto» dell'ambito, nuovo `<x-platform.heatmap>` (usato anche da `platform/charts.blade.php`).
- C.4 — La pagina usa `jsBack/platform/charts.js` (incluso nel contenuto: `layouts.app` non ha uno stack `scripts`). Eliminati `jsBack/chart.js`, `chart.config.js`, `chart.utils.js` e la voce in `vite.config.js`; `npx vite build` ok.
- C.5 — `partner.stats` riscritto in 5 lingue (chiavi vecchie rimosse) + `partner.header.statistics`; modifica testuale del blocco (il file ha un formato compatto proprio: niente riscrittura con un encoder JSON).
- C.6 — Link «Statistiche» nella barra e nel menu account dell'area partner (attivo su `admin.stats.*`) e icona grafico su ogni card in `admin/restaurants/index`.
- Verifica manuale (tinker, DB dev): `/admin/stats` gruppo 200, 102 KB; `/admin/stats/{id}` 200, 73–91 KB, 0,2–0,4 s a cache vuota (prima 0,8–1 s e 2,8–3,5 MB); `?restaurant=ID` → 302 verso il singolo con le date; locale di un altro proprietario → pagina «non è tuo»; nessun indirizzo email nell'HTML del gruppo.
- D.3 (in corso) — gruppo di 33 locali a cache vuota ~7 s (19 query, 6,5 s di SQL). Le più lente: `customers()` 1,24 s, piatti 0,70 s, totali 0,55 + 0,39 s, serie 0,39 + 0,31 s, voti 0,3 s ciascuna.
  - Esperimento indice `(status, customer_email, restaurant_id, created_at)` per la sottoquery del primo ordine nel gruppo: MySQL continua a scegliere `orders_restaurant_id_created_at_index`, nessun guadagno (gruppo 1,49 → 1,60 s, backoffice 1,07 → 1,39 s) → **indice rimosso**, migration invariata.
  - Fatto: il voto del periodo si ricava dalla serie (`RestaurantMetrics::total`: somma stelle / recensioni) invece di una query in più; verificato identico a `rating()` su gruppo e singolo, 3 periodi (12 mesi, tutto, 20 giorni a granularità giornaliera).
  - ⚠️ Decisione: altre riduzioni cambierebbero le definizioni (es. clienti nuovi calcolati dal primo ordine senza la condizione «ordine non annullato nel bucket») o richiederebbero pre-aggregati → non fatte. Dopo il primo caricamento la pagina è in cache 10 minuti (61 ms).
- D.1 — Test nuovi: `tests/Feature/Admin/StatsTest.php` (7 test su dati noti del 2030, tempo fissato al 15/02/2030: gruppo = somma dei locali per le metriche additive, clienti distinti 2 + 2 → 3 nel gruppo, voto pesato 11/3, pagina del singolo con piatti senza bevande e mediana consegna, proprietà su pagina/gruppo/selettore, ospite → login, cache separata per proprietario/locale/periodo, nessuna email/telefono/indirizzo/cognome nell'HTML, ristoratore senza locali, periodo senza ordini, preset «Tutto» dal primo ordine dell'ambito). `OrderMetricsTest`: `testGroupingsAddUpToTheTotals` e `testDeliveryPercentilesFromTheHistogram`. `OwnershipTest`: la pagina del locale non riceve più `related_orders` e l'incasso lordo coincide con la somma del DB.
- D.2 — `php artisan test` **303 passed**; `npm run test:js` **336 passed**; `php artisan route:list` 78 rotte (`admin.stats.index`, `admin.stats.show`); `npm run build` ok.
- D.3 — Misure finali a cache vuota (DB dev, ~185k ordini, pool 512 MB): `/platform/charts` **3,8–4,0 s** (era 11,5 s); `/admin/stats` gruppo di 33 locali **~6,1 s**, poi 60–75 ms in cache; `/admin/stats/{id}` 12 mesi **0,16 s**, tutto lo storico **0,28 s**; pagine 73–102 KB (prima 2,8–3,5 MB). HTML del gruppo con tutto lo storico: 8 grafici (vendite, ordini, voti, consegne, confronto, clienti, piatti, locali), 35 mesi da gen 2024 a nov 2026 (ultimo marcato «parziale»), nessuna email. Un ristoratore storico con soli ordini del 2021 vede «nessun ordine nel periodo» sui 12 mesi e i dati con «Tutto».
- D.4 — Documentazione: `dev/mappa.html` (righe nuove: RestaurantMetrics, ChartFormat, heatmap, analytics.css, StatsTest, migration, questo file; aggiornate StatsController, vista, filtri, platform.css, admin.css; tolte le 3 righe dei vecchi `jsBack/chart*.js`; nessun `rel` verso file inesistenti), `README.md` (funzioni del ristoratore), `AGENTS.md` (riga «Statistiche» in «Dove mettere cosa» e trappola sulla cache), `dev/PIANO_DATASET.md` (nota prestazioni chiusa).
- Controllo visivo nel browser **non fatto** in questa sessione (nessun browser disponibile): verificati struttura HTML, JSON dei grafici e build.
