# Recensioni dei clienti (stelle 1–5 + testo) — piano e stato

> File di ripresa: se la sessione si interrompe, si riparte da **Stato** qui sotto.
> Aggiornato a ogni passo. Branch: `feat/auth-platform`. **Lavoro completato, non ancora committato** (in attesa dell'utente).

## Decisioni (confermate dall'utente)
- **Una recensione per ordine consegnato**, da «I miei ordini» (bottone «Recensisci» a destra di «Ordina di nuovo»), entro **30 giorni** dalla consegna (`config/reviews.php`), modificabile nella stessa finestra.
- Nella media conta **solo l'ultima recensione visibile di ogni cliente** (`reviews.is_current`).
- Tre colonne su `restaurants`: `rating_avg` (media aritmetica, quella mostrata), `rating_count`, `rating_score` (bayesiana `C=10, m=3.8`, **solo** per la classifica «Guest favourites»).
- Unico scrittore: `App\Reviews\RestaurantRating` (`write()` = transazione + `lockForUpdate` sul ristorante + `refresh()` che ricalcola sempre da zero).
- Recensione **nascosta** dall'admin: il cliente non può più modificarla (403, `OrderPolicy@review`).
- Seed: i ~19k voti anonimi sono stati **sostituiti** da recensioni legate agli ordini (`ReviewsSeeder`). Testi normali curati in `database/seeders/data/reviews.php`; recensioni **problematiche** (volgari, fuori tema, nonsense, flirt) in `data/reviews_flagged.php`, ~2% dei commenti (28 nel dataset full), max 1 per ristorante, **tutte visibili** (`hidden_at = null`): le nasconde solo l'admin da `/platform`.
- Perimetro: modale nei miei ordini, recensioni nella pagina menu, area partner (lista + risposta pubblica), moderazione admin (Tabelle > Recensioni, AuditLog).
- Niente librerie nuove.

## Stato

### FATTO (backend, test PHP tutti verdi: 289 + nuovi)
- [x] `config/reviews.php`; migration `2026_11_26_000000_turn_rates_into_reviews` (rates→reviews, rate→rating, user_id, order_id unique, comment, is_current, reply, replied_at, hidden_at, hidden_by; rating_* su restaurants; backfill).
- [x] Modello `app/Review.php` (eliminato `app/Rate.php`), relazioni su Restaurant/Order/User, scope `Restaurant::topRated()`.
- [x] `app/Reviews/RestaurantRating.php`, comando `foodsy:refresh-ratings`.
- [x] `app/Policies/OrderPolicy.php` (`review`), registrata in `AppServiceProvider`.
- [x] API: `PUT /api/me/orders/{order}/review` (`Api\ReviewController`, `SaveReviewRequest`), `GET /api/restaurants/{slug}/reviews` (`Api\RestaurantReviewController`), `MyOrdersController` aggiunge `review` + `can_review`; `restTopRated` per score (campi `average`, `count`); `restMunu` ora dà `rating: {average, count}` (**tolto `averageRate`**); search usa `rating_avg`.
- [x] Analytics (GeoStats, Tables, OrderMetrics, Platform TableController) leggono le colonne.
- [x] Area partner: `Admin\ReviewController` (index/reply/deleteReply), vista `admin/reviews/index.blade.php`, link da scheda e lista ristoranti, CSS `adm-review*`/`adm-dist*`/`adm-reply*` in `admin.css`.
- [x] Platform: sezione `reviews` in Tabelle (filtri stelle/visibilità/solo testo/ricerca testo), `Platform\ReviewController` hide/unhide + `AuditLog::REVIEW_HIDDEN/UNHIDDEN`; partial audit corretto per target Review.
- [x] Componente Blade `<x-stars>` + primitive `ui-stars`, `ui-star`, `ui-star--on`, `ui-star-half(-fill)` in `ui.css`.
- [x] Stringhe in 5 lingue: `site.reviews`, `partner.reviews`, `platform.reviews`, `platform.tables.sections/placeholder.reviews`, `platform.users.audit.action.review.*`.
- [x] Seeder: `ReviewsSeeder` + `Support/ReviewWriter` (composizione testi + `flag()` puri e deterministici); eliminati `RatesTableSeeder`, `RatingsSeeder`, `rates_scale`.
- [x] DB di sviluppo aggiornato **senza** migrate:fresh: `migrate`, cancellati i voti anonimi, `db:seed --class=ReviewsSeeder`. Risultato: 3734 recensioni, 1496 commenti, 28 problematiche (7 per categoria, 28 ristoranti diversi, fasce low/mid/high), 248 ristoranti con voto, rilancio idempotente e deterministico.
- [x] Test: `Api/ReviewTest`, `Api/RestaurantReviewsTest`, `Admin/ReviewReplyTest`, `Platform/ReviewModerationTest`, `RestaurantRatingTest`, `Seeders/ReviewsSeederTest`, `Seeders/ReviewTextsTest`; aggiornati PublicApiTest, DatabaseSeederTest, DietaryQueriesTest, OrderMetricsTest, CustomerOrdersTest, TablesTabTest, RoutesTest.

### FATTO (frontend Vue, da verificare in browser)
- [x] `StarsBox`: mezze stelle (classi `ui-stars` di ui.css), prop `count` opzionale; via `Math.ceil` da `TopRated.utils.js` (`withStarsAverage`) e `RestMenu.logic.js`.
- [x] `RestMenu`: usare `restaurant.rating.average/count` (non più `averageRate`); aggiornare `tests/js/app.smoke.spec.js` e `tests/js/store.spec.js` (mock `averageRate` → `rating`).
- [x] `api/reviews.js`: `saveReview(orderId, { rating, comment })` (PUT), `fetchRestaurantReviews(slug, page)`; export in `api/index.js`.
- [x] `components/StarInput/` (radiogroup accessibile, hover/focus preview, frecce/Home/End, etichette `reviews.star` e `reviews.level.N`).
- [x] `components/ReviewModal/` (ui-modal, StarInput, textarea con contatore, Pubblica/Annulla, Esc, focus).
- [x] `pages/MyOrders`: bottone «Recensisci»/«Modifica recensione» in `orders-foot`, blocco recensione + risposta + nota moderazione; helper `reviewAction(order)` in `MyOrders.utils.js`.
- [x] `components/RestReviews/` sotto il menu in RestMenu (riepilogo, barre, lista con avatar, risposta, PaginateEl local, vuoto/errore).
- [x] Mock aggiornati (smoke, store): `npm run test:js` verde (321).
- [x] Test JS nuovi `tests/js/reviews.spec.js` (15). Suite: PHP 289 verdi, JS 336 verdi, `npm run build` ok.: StarInput, reviewAction, ReviewModal (API mock), smoke.

### DA FARE (chiusura)
- [x] Verifica finale: `php artisan test` 289 verdi, `npm run test:js` 336 verdi, `npm run build` ok, `php artisan route:list` ok.
- [x] Verifica nell'app reale con screenshot headless (playwright-core nello scratchpad, non nel progetto): pagina ristorante, recensioni, Guest favourites, I miei ordini + modale (desktop e mobile), area partner, /platform. Nessun errore in console.
- [x] Documentazione: AGENTS.md (riga «Dove mettere cosa» + trappola sulle medie), README.md, dev/mappa.html (file nuovi, tabella `reviews`, relazione 1─1 ordine → recensione), dev/PIANO_DATASET.md.
- [ ] Prova manuale dell'utente nel browser (consigliata): Mario Rossi → I miei ordini → Recensisci; partner risponde; admin nasconde da Tabelle > Recensioni (cercare per esempio "idiot").
