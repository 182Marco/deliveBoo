# Piano dataset Foodsy — file di avanzamento

> File solo di sviluppo (come `PIANO_MIGRAZIONE_L13.md`). Per riprendere il lavoro con un contesto vuoto basta leggere **Stato** e poi la sezione del passo corrente.

## Stato
- **Passo corrente:** 8.1 fatto. Prossimo: 8.2, migration dello schema (punto 1) e modelli (punto 2).
- **Passi fatti:**
  - 0. File di avanzamento creato.
  - 8.1 BUG-1: `Restaurant::uniqueSlug()` e `unique:restaurants,name` in store; test `_CurrentBehaviour` rimosso, skip tolto, nuovo test sullo slug con suffisso `-2`.
- **Baseline test:** `php artisan test` = 35.0s, 55 passed e 5 skipped (2026-11-22). Budget: al massimo circa 45s.
- **Findings:** (nessuno per ora)
- **Problemi aperti / da decidere:** (nessuno)
- **Decisioni prese:**
  - I marchi reali storici restano.
  - Scala circa 60 proprietari e circa 250 ristoranti.
  - DECIMAL con cast float.
  - Alla fine si ricostruisce il DB dev con `migrate:fresh --seed`.
  - Profilo `test`: 2 nuovi proprietari, +2 ristoranti per Romiti, menu da 6 a 12 piatti.

---

# Piano — Espansione database Foodsy (dataset realistico + AI-agent readiness)

## Context
Oggi il DB ha 12 proprietari, 16 ristoranti, 9 cucine e circa 128 piatti. `plates.ingredients` è testo libero e per i fast food elenca allergeni, non ingredienti. `order_plate` non ha quantità né prezzo storico. Mancano allergeni, diete, disponibilità e stagionalità. Obiettivo: un dataset sintetico ricco e coerente, con relazioni normalizzate e interrogabili da un futuro agente (che **non** implementiamo adesso), senza rompere checkout, SPA e area partner.

Scelte confermate dall'utente: i ristoranti/marchi esistenti restano **intatti**; scala di circa **60 proprietari totali e circa 250 ristoranti**; prezzi **DECIMAL con cast float**; alla fine si ricostruisce il DB dev con **`migrate:fresh --seed`**.

Vincoli trovati nel codice e da rispettare:
- I test eseguono `$this->seed()` a ogni test (SQLite in memoria): serve un **profilo di seed ridotto** per i test.
- `PlateController@update` valida `Rule::unique('plates')` sul nome **globale**: con molte "Margherita" la modifica fallisce. Va reso unico per ristorante.
- `PlateController@store` usa `Str::slug(name)` senza unicità, ma l'admin apre i piatti per slug. Serve uno slug unico (helper del seeder `uniqueSlug` spostato nel modello).
- `restaurants.slug` e `plates.slug` non hanno un indice UNIQUE. BUG-1 (`RestaurantController@store` senza slug) va corretto prima di aggiungere l'indice: commit `fix` separato.
- `Api\OrderController@store` accetta piatti non `visible`, e il carrello manda un id per ogni porzione.
- La forma delle API è fissata da `tests/Feature/Api/PublicApiTest.php`: si aggiungono solo campi, nessuno viene rimosso.

## 0. Tracciamento avanzamento (primo passo dopo l'approvazione)
- Copio questo piano in **`dev/PIANO_DATASET.md`**, file solo di sviluppo come `dev/PIANO_MIGRAZIONE_L13.md`, mai referenziato dall'app. In testa c'è una sezione **Stato** con: passo corrente, passi fatti (con hash del commit), findings, problemi aperti e decisioni prese.
- Lo aggiorno **a ogni passo completato** e prima di ogni commit, così il lavoro si può riprendere con un contesto vuoto leggendo solo quel file.
- Aggiungo alla memoria un puntatore al file.

## 1. Schema (sole nuove migration, `2026_11_23_*`)
1. **Prezzi**: `plates.price` DECIMAL(8,2), `orders.price` DECIMAL(10,2). Solo MySQL, come `restore_price_column_types`. Cast `'price' => 'float'` in `Plate`/`Order`. Aggiorno la trappola in AGENTS.md.
2. **restaurants** (+): `description` text null, `segment` string(20) indicizzato (`budget|fast_food|street_food|trattoria|traditional|ethnic|premium|fine_dining|starred`), `price_range` tinyint 1-4 indicizzato, `postal_code`, `province` (2), `latitude`/`longitude` DECIMAL(9,6) null, `email` null, `delivery_fee` DECIMAL(5,2) null, `min_order` DECIMAL(6,2) null, `prep_minutes` smallint null, `accepting_orders` bool default 1. Indice `city`. UNIQUE `slug` (dopo un controllo dei duplicati).
3. **opening_hours** (nuova): `restaurant_id` FK cascade, `weekday` 0-6, `opens_at`, `closes_at` (turni pranzo/cena su più righe), indice (`restaurant_id`, `weekday`).
4. **types**: UNIQUE `name`. I nuovi tipi sono inseriti dal seeder.
5. **users** (+): `company_name` null, `phone` null.
6. **ingredients** (nuova): `name` UNIQUE, `category` (vegetable, fruit, grain, dairy, meat, fish, shellfish, mollusc, egg, legume, nut, seed, herb_spice, condiment, sweetener, beverage, alcohol…), `origin` (`plant|meat|fish|shellfish|dairy|egg|honey|other`), `contains_lactose` bool null, `nickel_level` (`low|medium|high`) null, `info_status` (`verified|unverified`, default `unverified`: un ingrediente creato dal backoffice non ha dati affidabili).
   - `nickel_level` è un **dato indicativo del dataset** (commento nella migration e nei dati): niente garanzie né certificazioni. Resta `null` quando non lo conosco.
7. **plate_ingredient** (pivot): `plate_id` FK cascade, `ingredient_id` FK restrict, `is_main` bool, `position` smallint; UNIQUE (`plate_id`, `ingredient_id`), indice `ingredient_id`. Nessuna quantità/unità: non servono a un menu delivery.
8. **allergens** (nuova, i 14 dell'Allegato II del Reg. UE 1169/2011): `code` UNIQUE, `name`, `annex_number`, `description`.
9. **allergen_ingredient** (pivot di riferimento): un ingrediente contiene l'allergene. UNIQUE.
10. **Due livelli distinti, mai fusi:**
    - **Derivati** (dato di riferimento): allergeni degli ingredienti tramite `plate_ingredient` → `allergen_ingredient`. Si calcolano con una query e non si copiano sul piatto. Sono affidabili solo se ogni ingrediente del piatto è `verified`.
    - **Dichiarati dal ristorante** in `allergen_plate`, con `status` `contains` (presente nella ricetta) oppure `may_contain` (contaminazione crociata o possibile contatto: un'informazione separata, che solo il ristorante conosce e che non si deduce mai dagli ingredienti). UNIQUE (`plate_id`, `allergen_id`), indice (`allergen_id`, `status`).
    - **plates.allergen_info** (`complete|partial`, null = non dichiarato) esprime **solo la dichiarazione del ristorante**. Non diventa mai `complete` per il solo fatto che gli ingredienti sono mappati.
    - Un piatto è "free from X" solo se valgono tutte e quattro le condizioni: `allergen_info=complete` dichiarato; nessuna riga `contains` o `may_contain` per X; nessun allergene X derivato dagli ingredienti; tutti gli ingredienti `verified`. Se una condizione manca, il piatto è "non determinabile" e non "sicuro".
    - Se dichiarato e derivato non coincidono (es. glutine negli ingredienti ma non dichiarato), il piatto non è mai considerato libero. Il report conta questi casi come incoerenze.
11. **dietary_tags** (nuova): `code` UNIQUE, `name`, `kind` (`diet|free_from|restriction`), `description` ("declared by the restaurant, not a certification"). Valori: vegan, vegetarian, pescatarian, fruitarian, gluten_free, lactose_free, low_nickel, halal, spicy… (estendibile).
12. **dietary_tag_plate**: `status` (`yes|no|unknown`), `source` (`restaurant|ingredients`), UNIQUE (`plate_id`, `dietary_tag_id`), indice (`dietary_tag_id`, `status`). I quattro stati: nessuna riga = non dichiarato; `unknown` = dichiarato "non verificato"; `yes`/`no` = verificato.
13. **plates** (+): `course` string(20) indicizzato (starter, salad, soup, pasta, risotto, pizza, burger, main, side, sushi, ramen, poke, dessert, bakery, gelato, tasting_menu, water, soft_drink, beer, wine_white, wine_red, wine_rose, sparkling, champagne, dessert_wine, spirit, cocktail_mocktail, hot_drink), `availability` (`available|sold_out|unavailable`, indicizzato), `available_from_month`/`available_to_month` tinyint null (stagionalità, con gestione degli intervalli a cavallo d'anno), `serves` tinyint default 1, `calories` smallint null, `allergen_info`, `deleted_at` (SoftDeletes, così lo storico degli ordini sopravvive). Backfill: `visible=0` diventa `unavailable`. `visible` resta e il modello lo tiene allineato (`saving`: `visible = availability === available`), così SPA e API non cambiano. UNIQUE `slug`; indice (`restaurant_id`, `availability`).
14. **order_plate** (+): `quantity` smallint unsigned default 1, `unit_price` DECIMAL(8,2). Migration: fonde le righe duplicate in `quantity`, backfill di `unit_price` da `plates.price`, poi UNIQUE (`order_id`, `plate_id`).
15. **orders** (+): `fulfillment_status` (`received|preparing|out_for_delivery|delivered|cancelled`) null, `delivered_at` null. Indici `customer_email` e (`restaurant_id`, `created_at`).

## 2. Modelli (`app/`)
- Nuovi: `Ingredient`, `Allergen`, `DietaryTag`, `OpeningHour`, con attributi `#[Fillable]` e relazioni tipizzate.
- `Plate`: `ingredients()` (il nome collide con la colonna testo: la relazione si chiama `ingredientList()`), `allergens()->withPivot('status')`, `dietaryTags()->withPivot('status','source')`, `orders()->withPivot('quantity','unit_price')`.
  - Scope: `orderable()` (available + in stagione), `withDiet($codes)` (status yes per ogni codice), `freeFrom($allergenCodes)` (le quattro condizioni del punto 1.10: dichiarato `complete`, nessun `contains`/`may_contain`, nessun allergene derivato, tutti gli ingredienti `verified`), `derivedAllergens()` (query sugli ingredienti, separata dalle dichiarazioni), `withoutIngredients($names)`.
  - Statico `uniqueSlug()` (preso da `PlatesTableSeeder`), costanti dei course.
- `Order::plates()->withPivot(...)`. `Restaurant`: `openingHours()`, scope `withRating()` (withAvg + withCount).
- La colonna `plates.ingredients` resta come **etichetta di visualizzazione** (l'API e `Plate.vue` la leggono). Viene rigenerata dai nomi degli ingredienti strutturati a ogni sync (`Plate::syncIngredients(array $names)`).

## 3. Backoffice (Blade, nessun restyling, solo `x-field` e classi `ui-*`/`adm-*` esistenti)
- Form dei piatti (create/edit):
  - "Ingredients" resta un campo a virgole, **obbligatorio**. Il controller lo trasforma in `firstOrCreate` (nuovi = `unverified`) e fa il sync della pivot.
  - Il radio `visible` diventa una select `availability`. Aggiungo `course`, i mesi di stagione e `serves`, tutti facoltativi.
  - Fieldset facoltativo "Dietary information": per ogni tag una select *Not declared / Yes / No / Not verified*. Allergeni: select `allergen_info` (*Not declared / Complete / Partial*) e checkbox "contains"/"may contain". **Nessun default negativo**: "Not declared" non scrive righe.
- `PlateController@update`: nome unico **per ristorante**. `store` usa `Plate::uniqueSlug`. `destroy` fa soft delete e non stacca più gli ordini.
- `RestaurantController`: commit separato `fix(admin): BUG-1` (slug unico), poi campi facoltativi `description` e `price_range` nel form.
- Ordini (`admin/orders/show`): righe con `quantity × unit_price` dalla pivot. Statistiche: invariate (usano `orders.price`).
- SEC-1..4 restano fuori: commit separati già previsti.

## 4. API e checkout (compatibili)
- `POST /api/orders`:
  - raggruppa gli id ripetuti in `quantity` e salva `unit_price` dal DB;
  - rifiuta i piatti non `orderable()` con un `{errors:{plates:[...]}}` a 200, nel formato attuale;
  - imposta `fulfillment_status=received` quando l'ordine è pagato.
  - La risposta `plates` resta l'array di id inviato.
- `Plate.vue`: il bottone "add" è disabilitato quando `availability !== 'available'` (si aggiunge solo l'attributo `disabled`). Il flag mostra "Sold out" oppure "Currently unavailable".
- `restMunu` ordina per `course` e poi per `id`, e restituisce anche `ingredient_list` (nomi), `availability` e `course`. Nessun campo viene rimosso.
- `restTopRated`: invariato (il test fissa l'ordinamento per media).

## 5. Seeder (idempotenti, deterministici, per responsabilità)
Struttura:
```
database/seeders/
  DatabaseSeeder.php           -> Reference -> Legacy -> Catalog -> Commerce
  Reference/  AllergensSeeder, DietaryTagsSeeder, IngredientsSeeder, TypesTableSeeder (esteso)
  Legacy/     (i 6 attuali, invariati nei dati) + LegacyPlateDetailsSeeder
  Catalog/    OwnersSeeder, RestaurantsSeeder, MenusSeeder, OpeningHoursSeeder
  Commerce/   OrderHistorySeeder, RatingsSeeder
  data/       ingredients.php, allergens.php, cuisines/<cucina>.php (ricette),
              drinks.php (acque, bibite, birre, vini, champagne, distillati per fascia),
              images.php (pool di URL Unsplash verificati per categoria), names.php, cities.php
  Support/    CatalogGenerator.php (piano puro in memoria), SeedProfile.php
```
Mantengo i nomi di classe attuali (`UsersTableSeeder`…), quindi `db:seed --class=` e i test continuano a funzionare. Lo spostamento in sottocartelle avviene solo se il namespace resta `Database\Seeders`; altrimenti restano dove sono e si aggiungono i nuovi file.

- **Determinismo**: `Random\Randomizer(new Xoshiro256StarStar(seed))`, con un seme per entità derivato dalla chiave naturale (es. `crc32('owner:'.$email)`). Il piano dipende solo dai dati e mai dagli ID o dallo stato del DB.
- **Idempotenza**: chiavi naturali (email, nome ristorante, (ristorante, nome piatto), (cliente, ristorante, created_at)). Si inserisce solo ciò che manca, con `insert` a blocchi di 500 in transazione (le prestazioni su MySQL contano), poi gli ID si risolvono per slug/chiave. Nessuna cancellazione.
- **Profili** (`config/seeding.php`, `SEED_PROFILE`): `full` (dev) e `test` (forzato in `phpunit.xml`: vedi budget al punto 6), così la suite resta veloce. Stesso generatore: il profilo `test` è un prefisso del piano `full`.
- **Legacy**: i 16 ristoranti e i 128 piatti esistenti non si toccano nei dati.
  - `LegacyPlateDetailsSeeder` collega ingredienti reali, course e dichiarazioni a ogni piatto storico, tramite una mappa (ristorante, nome) → ingredienti, così ogni piatto ne ha almeno uno.
  - Il testo `ingredients` dei piatti storici cambia solo nei dati sorgente di `PlatesTableSeeder` (per i DB nuovi): oggi contiene allergeni, non ingredienti.
- **Proprietari**: circa 48 nuovi, totale circa 60, con `@example.com`/`.net`/`.org` (mai domini reali). P.IVA a 11 cifre con check digit valido, unica e senza zero iniziale (la colonna è bigint). Ragione sociale, telefono, una password demo con un solo hash.
  - Distribuzione dei ristoranti: ~70% 1-2, ~20% 3-8, ~8% 9-20, 1-2 catene da 25-40 (totale circa 220).
- **Romiti**: +30 nuovi ristoranti (totale 33, sotto il tetto di 40), multi-città e multi-segmento (pizzerie, burger, poke, trattorie e un fine dining), per mostrare la gestione di più locali. I suoi 3 ristoranti esistenti restano intatti.
- **Ristoranti**:
  - Nome da pattern per cucina (es. "Trattoria da Nives", "Ramen Kaze"), verificato unico (anche rispetto ai legacy), al massimo 50 caratteri.
  - Città italiane reali con CAP, provincia e coordinate plausibili. Via e numero inventati.
  - `segment` → `price_range`, dimensione del menu, fascia di prezzi, carta dei vini e orari coerenti: i fast food non hanno champagne; gli stellati hanno menu degustazione, vini pregiati e champagne fino a €400-900.
  - 1-3 tipi coerenti con cucina e segmento.
- **Cucine**: circa 45 tipi in tutto. I 9 esistenti restano (inclusi "Ethnic" e "Starred Restaurant") e si aggiungono quelli dell'elenco del brief più alcuni sensati (Peruvian, Sardinian/Sicilian come "Italian regional"?, Seafood, Steakhouse, Brunch, Bakery…). Ognuno ha un'immagine 4:5 verificata.
- **Menu**: libreria di ricette per cucina (circa 30-60 ricette ciascuna), scritte a mano e non composte da frammenti.
  - Ogni ricetta ha nome, course, `serves`, eventuali mesi di stagione e la categoria dell'immagine.
  - Ogni ricetta ha 2-4 **varianti di sostanza**, ciascuna con ingredienti propri e descrizione propria, legate ai segmenti in cui ha senso. Esempio: Margherita con fior di latte (pizzeria di quartiere), con bufala DOP e pomodoro San Marzano (gourmet), con pomodorino giallo e basilico fritto (premium). Cambiano ingredienti, allergeni, prezzo e testo, non solo le parole.
  - Ricette premium e stellate riservate (degustazioni, crudi, tartufo, caviale) con ingredienti coerenti, mai assegnate ai segmenti economici. Ogni ristorante premium ha 1-3 **piatti firma** propri, unici nel dataset.
  - Il ristorante sceglie un sottoinsieme in base al segmento: small 5-15, contained 15-35, traditional 30-70, structured 70-120, premium 50-150, fine dining/starred 100-300 (le bevande sono comprese).
  - Prezzo dalla fascia della variante per segmento, arrotondato a .50/.90.
  - **Metriche di qualità nel report**: rapporto descrizioni distinte/piatti, numero massimo di ristoranti che condividono la stessa descrizione e coerenza ricetta-segmento. Un test controlla che nessuna ricetta premium compaia nei segmenti budget/fast_food.
  - Disponibilità: circa 92% available, 5% sold_out, 3% unavailable.
- **Dichiarazioni alimentari**: dati derivati e dichiarazioni restano separati.
  - Ogni ristorante ha una propria "politica di dichiarazione" simulata. Circa 60% dichiara `complete`: righe `contains` scritte dal ristorante e `may_contain` decise dal **contesto della cucina** (friggitrice condivisa nei fast food, farine nelle pizzerie e pasticcerie, frutta a guscio nelle gelaterie, sesamo e soia nei locali asiatici), mai dagli ingredienti del piatto. Circa 20% dichiara `partial`, circa 20% niente.
  - Una piccola quota di dichiarazioni `complete` è volutamente incoerente con gli ingredienti (un allergene dimenticato), così test e futuro agente verificano che quei piatti non risultino sicuri.
  - Tag: `vegan`/`vegetarian`/`pescatarian` dall'`origin` degli ingredienti; `gluten_free` dall'allergene glutine; `lactose_free` da `contains_lactose` (il Parmigiano stagionato è senza lattosio ma contiene latte); `fruitarian` solo se tutto è frutta, frutta a guscio o semi.
  - `low_nickel`:
    - `yes` (`source=ingredients`) **solo** se tutti gli ingredienti sono `verified` e hanno `nickel_level=low`;
    - `no` se almeno un ingrediente verificato è `high`;
    - altrimenti nessuna riga, e mai dedotto da ingredienti `unverified` o con livello `null`.
    - La descrizione del tag dice "indicative dataset information, not a medical guarantee or certification". Nessun ristorante lo dichiara come garanzia.
  - Ristoranti che non dichiarano: nessuna riga, così l'agente vede piatti "sconosciuti".
- **Immagini**: solo URL `https://images.unsplash.com/photo-…?auto=format&fit=crop&w=480&q=70` (piatti 4:3), `w=900` (ristoranti 3:2), `w=600` (cucine).
  - Pool per categoria (pizza, sushi, ramen, burger, pasta, insalata, dessert, vino rosso, champagne…), con circa 250-350 URL. Ogni URL passa tre controlli, con uno script nello scratchpad:
    - risposta 200 con `Content-Type: image/*`;
    - **controllo visivo della pertinenza**: scarico le miniature (w=160) in una cartella dedicata dello scratchpad, le unisco in fogli di contatto per categoria e le guardo. Le foto fuori tema si scartano;
    - esito registrato in `dev/PIANO_DATASET.md`.
  - **Fonte e condizioni**: in testa a `images.php` e nel piano documento la fonte (Unsplash, servizio `images.unsplash.com`) e la licenza ([Unsplash License](https://unsplash.com/license): uso gratuito, attribuzione non obbligatoria ma gradita, vietato rivendere le foto o compilarle in un servizio concorrente). Per ogni URL salvo l'ID della foto per poter risalire alla pagina e all'autore.
  - Una risposta 200 **non è una prova di licenza**: nel report finale segnalo come limite che la licenza delle singole foto non è stata verificata una per una.
  - Il seeder sceglie a rotazione nel pool della categoria, per evitare la stessa foto ovunque. Se manca un'immagine resta `null` (fallback `FoodImage`/`<x-photo>`).
- **Ordini** (`OrderHistorySeeder`):
  - circa 1,500 clienti sintetici (`@example.*`), alcuni abituali (legge di potenza), per le query "piatti più ordinati da un cliente";
  - circa 8-10k ordini da gen. 2025 a nov. 2026, con orari di pranzo/cena e volume proporzionale a rating e segmento;
  - 1-6 righe con `quantity` 1-3, `unit_price` = prezzo del piatto e `orders.price` = Σ qty×unit_price;
  - stati: storici `paid` + `delivered`, pochi `cancelled`/`failed`, gli ultimissimi `preparing`/`out_for_delivery`; `payment_driver='fake'` come oggi.
- **Voti**: per ristorante, una qualità latente (dal segmento più rumore) e un numero di voti (0-5 per i nuovi locali, fino a circa 400 per i popolari). Distribuzione credibile 1-5, con ricarica idempotente come l'attuale `RatesTableSeeder`.

## 6. Test (PHPUnit, profilo `test`)
- **Budget del profilo `test`**:
  - 2 nuovi proprietari, +2 ristoranti per Romiti, menu da 6 a 12 piatti (un ristorante per segmento chiave: pizzeria, vegano, stellato ridotto con champagne), circa 30 ordini, circa 40 voti;
  - stessa logica del profilo `full`, cambiano solo le quantità;
  - prima di iniziare misuro il tempo attuale di `php artisan test` come baseline e lo annoto nel file di avanzamento. Obiettivo: al massimo +30%. Se lo supero, riduco il profilo, non la copertura.
- Le query alimentari si testano su **fixture minime costruite nel test** (un piatto per caso), indipendenti dal volume del seed.
- `DatabaseSeederTest` esteso (vale anche per le tabelle nuove):
  - seed doppio senza duplicati, ogni seeder rilanciabile da solo;
  - ogni piatto ha almeno un ingrediente; ogni proprietario ha almeno un ristorante; Romiti ha più di 3 ristoranti e i 3 originali restano invariati (slug e proprietario);
  - slug unici, tipi unici, nessuna pivot duplicata, nessuna FK orfana;
  - prezzi > 0, `quantity` > 0, `orders.price` = Σ qty×unit_price, righe dell'ordine dello stesso ristorante;
  - i piatti con dichiarazione incoerente con gli allergeni derivati non risultano mai "free from";
  - `low_nickel=yes` solo se tutti gli ingredienti sono verificati e `low`.
- Nuovo `tests/Feature/DietaryQueriesTest.php` con fixture costruite a mano:
  - vegan sotto i €15; gluten_free e lactose_free insieme;
  - senza peanuts e soy che **escluda** i piatti `partial`/non dichiarati, quelli con `may_contain` e quelli con ingredienti `unverified`; un piatto non dichiarato non risulta mai compatibile;
  - low_nickel non implicato dall'assenza di allergeni;
  - ricerca di alternative senza un ingrediente; champagne sopra i €100; ristoranti con almeno 50 piatti `orderable`; più cucine insieme; piatti più ordinati per `customer_email`;
  - piatti stagionali fuori stagione non `orderable`.
- `PublicApiTest`/`CrudTest`:
  - il checkout con id ripetuti salva `quantity=2` e `unit_price`; un piatto sold_out viene rifiutato (200 + errors);
  - il JSON di `price` è numerico (cast DECIMAL);
  - la modifica di un piatto con un nome usato in un altro ristorante passa; la creazione con un ingrediente nuovo crea l'ingrediente `unverified`;
  - nessuna dichiarazione alimentare = nessuna riga.
- I test esistenti che contano le righe di `order_plate` vanno adattati alla quantità.
- JS: `npm run test:js` (smoke; aggiorno i mock se `Plate` usa `availability`).

## 7. Documentazione
- `dev/mappa.html` (solo blocco DATI): nuove righe `F(...)` per modelli, migration, seeder e data; `DB_TABLES`/`DB_LINKS`/`DB_RELS` con le nuove tabelle e pivot; `CMDS` con `SEED_PROFILE`.
- `README.md` e `AGENTS.md`: profili di seed, trappola dei prezzi (DECIMAL + cast), semantica dei 4 stati alimentari, `visible` derivato da `availability`, plate SoftDeletes.
- Memoria: aggiornare `seeders-idempotent.md`.

## 8. Commit (su `upgrade/laravel-13`)
1. `fix(admin): BUG-1 slug unico in RestaurantController@store`
2. `feat(db): schema ingredienti, allergeni, diete, disponibilità, quantità ordini, decimal`
3. `feat(admin): ingredienti strutturati e info alimentari facoltative nei piatti`
4. `feat(checkout): quantità e prezzo storico nelle righe, solo piatti ordinabili`
5. `feat(data): dataset realistico (proprietari, ristoranti, menu, ordini, voti)`
6. `test: integrità dataset e query alimentari`
7. `docs: mappa, README, AGENTS`

## Verifica finale
1. `php artisan migrate:fresh --seed` su MySQL dev (confermato), poi di nuovo `php artisan db:seed`: i conteggi non cambiano.
2. Script di report in tinker o SQL:
   - conteggi (proprietari, ristoranti, ristoranti di Romiti, piatti, ingredienti, allergeni, tag, ordini);
   - distribuzioni: ristoranti per proprietario, piatti per ristorante e segmento, voti;
   - piatti con 0 ingredienti = 0;
   - le 12 query di esempio del brief eseguite davvero (con i tempi).
3. `php artisan test`, `npm run test:js`, `php artisan route:list`, `npm run build` verdi.
4. Prova manuale (`php artisan serve` + `npm run watch`):
   - home con molte cucine, filtro per tipi e paginazione, menu e immagini/fallback, checkout con 2 porzioni;
   - login come Romiti: lista ristoranti paginata, crea/modifica un piatto con ingredienti nuovi e senza info alimentari, ordine con le quantità.
5. Report finale con i 12 punti richiesti, i problemi rimasti (SEC-1..4, `restaurants.address` a 50 caratteri contro `max:80` della validazione, P.IVA come bigint) e i limiti (descrizioni da template con varianti, nichel indicativo e non medico, immagini per categoria e non per singolo piatto, marchi reali storici lasciati per scelta).
