# AVATAR_IMPLEMENTATION_PLAN: avatar SVG proprietario (`svg-avatar`)

> **Fonte unica e persistente** di obiettivi, architettura, decisioni e avanzamento del sistema avatar.
> Ultimo aggiornamento: 2026-11-23. **Stato: fasi 1–9 completate.**

## Come usare questo file
**Aggiornamento (obbligatorio a ogni step completato, non a fine fase):**
- segnare lo step come fatto nella tabella *Stato*;
- scrivere cosa è stato realizzato, i file creati o modificati, i test eseguiti con il loro esito reale, i problemi e le decisioni aperte;
- indicare il prossimo step.

Uno step pianificato o discusso **non** è completato.

**Ripresa da una nuova sessione:**
1. Leggere tutto il file.
2. Verificare lo stato reale del repo (`git status`, file elencati negli step, test).
3. Correggere il file se non corrisponde al codice.
4. Riprendere dal primo step davvero incompleto, senza rifare quelli verificati.

**Regole d'esecuzione:**
- Nessuna dipendenza nuova.
- Niente modifiche estranee al sistema avatar.
- Si passa alla fase successiva solo se la precedente non ha problemi bloccanti.
- La qualità grafica viene prima dei tempi.

## Stato
| Step | Stato | Note |
|---|---|---|
| 1.1 Analisi auth salvata | ✅ fatto | `dev/PIANO_AUTH_CLIENTI.md` |
| 1.2 Questo piano | ✅ fatto | creato in root |
| 2.1 Parti prototipo | ✅ fatto | era `dev/avatar-prototype.html`, rimosso alla Fase 8 (resta l'artifact della 2.4) |
| 2.2 6 avatar composti | ✅ fatto | Luca, Giulia, Samuel, Amara, Nora, Elena |
| 2.3 Verifica visiva + pesi | ✅ fatto | screenshot headless a 340/160/64/32/24 px; parti 7,0 kB (2,6 kB gzip) |
| 2.4 Pubblicazione + STOP | ✅ fatto, approvato dall'utente il 2026-11-23 («prosegui») | artifact privato https://claude.ai/artifact/U3QANYMFFg8JJJvMnX7Ccb |
| 3.1 `assets/parts.json` completo | ✅ fatto | 4 visi, 12 capelli, 5 occhi, 4 sopracciglia, 5 bocche, 8 vestiti, 6 accessori |
| 3.2 `palettes/default.js` | ✅ fatto | gruppi bg, skin, hair, cloth, accent, iris, fixed; dalla 6.1 i dati stanno in `default.json` |
| 3.3 Griglia di verifica `dev/avatar-grid.html` + correzioni | ✅ fatto | 14 sezioni, screenshot riletti |
| 3.4 Ottimizzazione + pesi | ✅ fatto | 13,2 kB min, 3,9 kB gzip |
| 4.1 `core/random` + test | ✅ fatto | 5 test verdi |
| 4.2 `rules/` + `core/traits` + test | ✅ fatto | 17 test verdi in totale |
| 4.3 `core/layers` + `renderAvatarSvg` + test | ✅ fatto | 67 test verdi in totale |
| 4.4 Entry `index.js`, README, fixture | ✅ fatto | 87 test verdi in totale |
| 4.5 Alias Vite + `boundaries.spec.js` | ✅ fatto | 111 test del pacchetto, 167 JS totali, build ok |
| 5.1 Componente Vue + entry `vue.js` + test | ✅ fatto | 118 test del pacchetto |
| 5.2 `<x-avatar>` + `jsBack/avatar.js` nell'header partner | ✅ fatto | test PHP 2 + JS 1, verifica visiva |
| 5.3 Aggancio SPA documentato | ✅ fatto | in `dev/PIANO_AUTH_CLIENTI.md` |
| 6.1 Migration `users.avatar` + modello (`avatarSeed`, `avatarOverrides`) | ✅ fatto | |
| 6.2 `AvatarCatalog` (whitelist da `parts.json`) + rotta "Nuovo avatar" + voce di menu + test | ✅ fatto | 6 test PHP |
| 7 Test completi, build e misure | ✅ fatto | PHP 94 + 4 skip preesistenti, JS 175, build ok |
| 8 Documentazione (AGENTS, README, mappa) e pulizia | ✅ fatto | |
| 9.0 Snapshot di compatibilità (prima di ogni modifica) | ✅ fatto | 517 seed + 76 override congelati |
| 9.1 3 nasi (`seeded: false`), tag `look`, `looksOf`, test dei dati, `options.json` | ✅ fatto | 133 test del pacchetto |
| 9.2 `editor/groups`, `labels`, `state/*` + test | ✅ fatto | 155 test del pacchetto |
| 9.3 `editor/ui/*` + `editor.css` + test di interazione | ✅ fatto | 9 test di interfaccia |
| 9.4 `mount.js`, entry `editor.js`, alias, README, confini + test di contratto e ciclo di vita | ✅ fatto | 184 test del pacchetto |
| 9.5 Laravel: rotte, validazione, redirect sicuro, vista, script, menu, stringhe + test | ✅ fatto | 27 test PHP avatar, 6 JS pagina |
| 9.6 Verifica visiva e accessibilità | ✅ fatto | screenshot 1280/390, 3 correzioni |
| 9.7 Documentazione, test completi, build e pesi | ✅ fatto | Fase 9 completata |

**Prossimo step:** nessuno: fasi 1–9 completate.
- Da fare sul DB di sviluppo: `php artisan migrate` (colonna `users.avatar`).
- Da fare manualmente:
  - aprire «Personalizza avatar», provare ogni gruppo, il look, Sorprendimi, Ripristina e Salva;
  - provare la pagina con un lettore di schermo reale.
- Lavori futuri:
  - wrapper Vue dell'editor e avatar nella SPA con il login clienti (`dev/PIANO_AUTH_CLIENTI.md`);
  - sovrapposizione logo/badge nell'header partner a 390 px (preesistente).

## Fase 9: pagina «Personalizza avatar» (piano approvato rev. 3, 2026-11-23)
- **Obiettivo:** «Personalizza avatar» sostituisce «Nuovo avatar». Apre una pagina dedicata con un editor che compone l'avatar parte per parte; con Salva si torna alla pagina di provenienza.
- **Editor:** vive interamente nel pacchetto (`editor/`, entry `svg-avatar/editor`), in JS puro e senza framework. L'app lo monta (`jsBack/avatarEditor.js`), valida e salva.
- **Decisioni:**
  - il look Tutti/Maschile/Femminile riordina le opzioni e orienta «Sorprendimi», senza mai cambiare i tratti;
  - 3 nasi con `seeded: false`, cioè non estratti dal seed, quindi gli avatar esistenti restano identici;
  - formato invariato `{ seed, overrides }`, con override minimi.
- **Contratto** `mountAvatarEditor(el, { value, palette, labels, onChange })` → `{ getValue, setValue, isDirty, reset, destroy }`. Semantica:
  - il riferimento iniziale non cambia mai;
  - `setValue` non chiama `onChange`;
  - `reset` chiama `onChange` se il valore cambia;
  - `isDirty` confronta i tratti risolti;
  - `onChange` scatta solo per modifiche nate nell'editor.
- **Server:** valida ogni valore e rifiuta le coppie vietate scritte per intero negli override. Il risultato è valido per costruzione, perché `normalizeTraits` sposta solo i tratti non bloccati; lo dimostra un test di proprietà. La provenienza per il redirect viene accettata solo se è dello stesso host e sotto `/admin`.
- **Livelli:** 5 `editor/state`, `groups`, `labels` → 6 `editor/ui` → 7 `editor/mount` → 8 entry.

## Registro step
- **1.1 (2026-11-23)**: salvata l'analisi login clienti / Google già approvata in `dev/PIANO_AUTH_CLIENTI.md`. È solo documentazione: nessun test necessario.
- **1.2 (2026-11-23)**: creato questo file con l'architettura rev. 3, già approvata.
  - Lo stato del repo è stato verificato: `packages/` non esisteva e nessun avatar era presente, a parte l'iniziale `.adm-avatar` in `resources/views/layouts/app.blade.php:40`.
  - Scostamenti motivati da AGENTS.md: CSS puro invece di SCSS, JavaScript + JSDoc invece di TypeScript.

- **2.1 (2026-11-23)**: disegnate da zero le parti del prototipo in `dev/avatar-prototype.html`. I dati stanno in `<script type="application/json" id="avatar-data">`, insieme a un renderer di prova (~40 righe) che non è il generatore del pacchetto.
  - Parti: visi `oval`/`round`/`square`; capelli `crop`/`quiff`/`long`/`bob`/`bun`/`curly`; occhi `open`/`happy`/`wink`; sopracciglia `soft`/`bold`; bocche `laugh`/`smile`/`smirk`; vestiti `tee`/`hoodie`/`shirt`/`vneck`; accessori `none`/`glasses`/`earrings`.
  - Fissi: collo, orecchie, guance, naso.
  - Forme: `d` (path), `c` (cerchio) o `e` (ellisse), con `f` slot riempimento, `s` slot tratto, `w` spessore e `o` opacità.
  - Prima regola: `coversEars` sui capelli (`long`, `bob`) nasconde le orecchie.
  - Palette di prova: bg 6, pelle 6 coppie (base + ombra), capelli 6 coppie, vestiti 6 coppie, iride 4, colori fissi (ink, mouth, white, blush, metal).
- **2.2 (2026-11-23)**: composti 6 avatar con combinazioni diverse di viso, capelli, occhi, vestiti, accessori, pelle e colore capelli.
- **2.3 (2026-11-23)**: render con Chromium headless (Playwright) e screenshot riletti a 340 px e alle misure piccole.
  - Corretti: naso (da "U" a ombra laterale), ombra del collo (bordo curvo) e una giuntura antialias tra collo e scollo (scollo alzato a y 85,2).
  - Pesi misurati sul JSON compatto di parti + palette: 7 142 B grezzi, 2 599 B gzip. Un SVG composto pesa in media ~2,8 kB.
  - Nessun test automatico in questa fase: il prototipo non tocca il codice dell'app.
- **2.4 (2026-11-23)**: prototipo pubblicato come artifact privato (versione con tema chiaro e scuro): https://claude.ai/artifact/U3QANYMFFg8JJJvMnX7Ccb.
  - **STOP**: si aspetta l'approvazione dell'utente.
  - Problemi aperti da valutare alla Fase 3:
    - `bob` grigio rischia l'effetto "casco";
    - i capelli `long` coprono le orecchie, quindi gli orecchini vanno sopra i capelli (resa accettabile);
    - nel layer order effettivo il collo sta **prima** del vestito (`bg → hairBack → neck → body → …`), e lo scollo è una forma `skin` del vestito.

- **3.1 (2026-11-23)**: creato `packages/svg-avatar/assets/parts.json` (versione 1).
  - Contiene `viewBox`, `layers` (ordine dei livelli), `slots` (i 15 slot colore ammessi), `base` (sfondo, collo, orecchie, guance, naso) e `categories`.
  - Ogni variante è un oggetto `{ tags?, <livello>: [forme] }`, così una variante può disegnare su più livelli (es. capelli `hairBack` + `hairFront`).
  - Forme come nel prototipo: `d`/`c`/`e` + `f`/`s`/`w`/`o`.
  - Varianti:
    - viso `oval` `round` `square` `heart`;
    - capelli `buzz` `crop` `sidePart` `quiff` `curly` `afro` `pixie` `bob` `long` `wavy` `bun` `ponytail`;
    - occhi `open` `lashes` `happy` `wink` `sleepy`;
    - sopracciglia `soft` `bold` `arched` `flat`;
    - bocca `laugh` `grin` `smile` `smirk` `calm`;
    - vestiti `tee` `stripes` `vneck` `hoodie` `shirt` `blazer` `turtleneck` `strapTop`;
    - accessori `none` `roundGlasses` `squareGlasses` `sunglasses` `earrings` `beanie`.
  - Tag usati dalle regole: `short`, `volume`, `long`, `updo`, `coversEars` (capelli) e `hat` (beanie).
  - Nuovo slot `accent`/`accentShade` per beanie ed elastico della coda.
  - `bob` ridisegnato con riga centrale e ciocche ai lati del viso, per togliere l'effetto casco.
- **3.2 (2026-11-23)**: `packages/svg-avatar/palettes/default.js` esporta `defaultPalette`.
  - Gruppi: `bg` (6), `skin`/`hair`/`cloth` (6 coppie base + ombra), `accent` (4 coppie), `iris` (4), `fixed` (ink, mouth, white, blush, metal).
  - Nessun riferimento all'app: verificato con `grep -ci foodsy`, risultato 0.
- **3.3 (2026-11-23)**: `dev/avatar-grid.html` legge il JSON e la palette del pacchetto. Va aperta da un server o con Chromium `--allow-file-access-from-files`; `?s=` filtra le sezioni, `?z=` ingrandisce il catalogo.
  - Mostra il catalogo per categoria e le matrici capelli × viso, capelli × accessori, vestiti × pelle e vestiti × colore, più 24 miscele a 48/32/24 px su chiaro e scuro.
  - Usa un renderer locale minimo, da sostituire con l'API del pacchetto alla Fase 8.
  - Screenshot riletti e corretti: dolcevita (era un blocco scuro, ora colletto in tinta con bordo e coste), `strapTop` (top più alto, spalline più sottili), linea d'ombra di `wavy` (sembrava un cerchietto).
  - Combinazioni da vietare nelle regole (Fase 4.2): `beanie` con `afro`, `curly` o `bun`, che sporgono dal berretto. `beanie` nasconde `hairFront`; `coversEars` nasconde `ears`.
- **3.4 (2026-11-23)**: coordinate con al massimo 1 decimale (verificato con grep: solo le opacità hanno 2 decimali).
  - Pesi: `parts.json` 20,4 kB indentato, **13,2 kB minificato, 3,9 kB gzip**; `default.js` 1,6 kB.
  - Il JSON resta indentato per la leggibilità: Vite lo minifica nel bundle.

- **4.1 (2026-11-23)**: creati `core/random/hash.js` (cyrb53), `prng.js` (mulberry32), `seed.js` (`seededRandom(seed)` = mulberry32 sui 32 bit bassi di cyrb53; `randomSeed()` = 16 hex da `crypto.getRandomValues`) e il barrel `index.js`.
  - Già scritti, in vista dei passi successivi, i moduli di livello 0 che servono a 4.2/4.3:
    - `assets/catalog.js`: `CATEGORIES`, `LAYERS`, `BASE`, `variantNames`, `variantOf`, `hasVariant`, `weightOf`, `tagsOf`;
    - `palettes/resolve.js`: `COLOR_TRAITS`, `colorKeys`, `resolveColors`, con fallback al primo colore del gruppo;
    - il barrel `palettes/index.js`.
  - In `parts.json` la variante `accessory.none` ha `weight: 5`, così circa metà degli avatar è senza accessorio.
  - `vite.config.js`: `test.include` esteso a `packages/*/tests/**/*.spec.js`.
  - Test `tests/determinism.spec.js`: `npx vitest run packages/svg-avatar` → 5/5 verdi. Valori di riferimento: cyrb53("") = 3338908027751811, cyrb53("a") = 7929297801672961.
  - Nota per la gerarchia: `core/layers` importa anche `rules/` (livelli nascosti) e `core/traits` (normalizza prima di disegnare). La tabella è aggiornata di conseguenza: livello 3 → 0–2.

- **4.2 (2026-11-23)**: regole e tratti.
  - `rules/rules.json`:
    - `hideLayers`: `coversEars` → nasconde `ears`, `hat` → nasconde `hairFront`;
    - `exclude`: `beanie` × `afro`/`curly`/`bun`, con `prefer: "accessory"`.
  - `rules/apply.js`:
    - `applyRules(traits, locked)` cambia la categoria preferita se non è bloccata da un override, altrimenti l'altra; se entrambe sono bloccate cambia comunque la preferita;
    - il sostituto è la prima variante non vietata (`none` per gli accessori);
    - non modifica l'input;
    - `hiddenLayers(traits)` restituisce i livelli da nascondere.
  - `core/traits/`:
    - `pick.js` (`traitsFromSeed`): un'estrazione pesata per categoria nell'ordine `CATEGORIES`, poi una uniforme per colore nell'ordine di `COLOR_TRAITS`;
    - `normalize.js` (`normalizeTraits`, `validOnly`, `TRAIT_NAMES`);
    - `create.js` (`avatarTraits(seed, overrides, { palette })`): gli override non validi vengono ignorati, quelli validi sono "locked";
    - `options.js` (`avatarOptions(palette)` → `{ shapes, colors }`);
    - `index.js`.
  - I tratti sono 13: 7 forme + `bgColor`, `skinColor`, `hairColor`, `clothColor`, `accentColor`, `irisColor`.
  - Test `tests/rules.spec.js` (6) e `tests/determinism.spec.js` esteso (+6):
    - nessuna combinazione vietata su 1000 seed;
    - override bloccati e input non modificato;
    - livelli nascosti;
    - determinismo;
    - un override non sposta gli altri tratti;
    - fallback;
    - ogni variante e colore raggiunti su 3000 seed, con "senza accessorio" tra il 40 e il 60%;
    - palette personalizzata.
  - `npx vitest run packages/svg-avatar` → 17/17 verdi.

- **4.3 (2026-11-23)**: composizione dei livelli e output SVG.
  - `core/layers/compose.js` (`composeLayers(traits, palette)`):
    - ordine `LAYERS`, salta i livelli di `hiddenLayers`;
    - cerchi ed ellissi convertiti in path (ogni forma diventa un solo `<path>`, coordinate arrotondate a 2 decimali);
    - restituisce un array congelato di attributi `{ d, fill, stroke?, stroke-width?, stroke-linecap?, stroke-linejoin?, opacity? }`;
    - cache in una `WeakMap` per palette → `Map` per chiave dei tratti, massimo 500 voci (FIFO).
  - `core/layers/svg.js` (`toSvgString(paths, { size, title, shape })`):
    - escape di tutti gli attributi e del title;
    - decorativo (`aria-hidden`) senza title, `role="img"` + `aria-label` + `<title>` con title;
    - `shape: "circle"` (default) ritaglia con `style="clip-path:circle(50%)"`: niente `clipPath` con id, quindi nessun conflitto tra più SVG inline. `"square"` non ritaglia.
  - `core/layers/render.js`: `avatarPaths(traits, palette)` normalizza e compone (lo userà il componente Vue); `renderAvatarSvg(traits, { palette, size, title, shape })`.
  - Test `tests/variants.spec.js` (50):
    - ogni variante di ogni categoria produce un SVG ben formato (DOMParser) con colori risolti;
    - orecchie nascoste sotto i capelli lunghi;
    - escape di title e palette (niente `<script>` iniettabile);
    - ritaglio circolare o quadrato;
    - cache (stesso array congelato);
    - output identico per lo stesso seed.
  - `npx vitest run packages/svg-avatar` → 67/67 verdi.
  - Controllo visivo: 30 seed (`user-1`…`user-30`) renderizzati con l'API vera a 120 e 32 px, screenshot riletto. Resa uguale alla griglia, ritaglio circolare corretto in Chromium.

- **4.4 (2026-11-23)**: entry point, README e fixture.
  - `index.js` esporta solo `avatarTraits`, `normalizeTraits`, `avatarOptions`, `randomSeed`, `renderAvatarSvg`, `defaultPalette`.
  - `core/types.js`: typedef JSDoc `Traits`, `Overrides`, `Palette`, `PathAttrs`.
  - `README.md` (in inglese, pronto per l'estrazione): API, palette personalizzata, contratto dati passo per passo (cyrb53 su unità UTF-16, mulberry32, ordine delle estrazioni, pesi, regole, livelli), formato di `parts.json`, gerarchia delle dipendenze, crediti. Documenta già le props del componente Vue (Fase 5): `seed`, `overrides`, `traits`, `palette`, `size`, `label`.
  - `tests/fixtures/seeds.json`: 20 seed → tratti attesi (inclusi `""`, un'email e una stringa con caratteri non ASCII), generati con `npx vite-node` dall'API pubblica. Li verifica `tests/determinism.spec.js` con 20 casi.
  - **Attenzione:** cambiare l'ordine o i pesi delle varianti in `parts.json` cambia gli avatar esistenti, e la fixture lo segnala. Aggiungere varianti in fondo a una categoria cambia comunque le estrazioni (cambia il totale), quindi va rigenerata la fixture consapevolmente.
  - `npx vitest run packages/svg-avatar` → 87/87 verdi.

- **4.5 (2026-11-23)**: alias e test di confine.
  - `vite.config.js`: `resolve.alias` con due regex esatte, `^svg-avatar$` → `packages/svg-avatar/index.js` e `^svg-avatar/vue$` → `packages/svg-avatar/vue.js`. I deep import (`svg-avatar/core/...`) non si risolvono, per scelta. `vue.js` arriva alla Fase 5: l'alias c'è già ma nessuno lo importa.
  - `tests/boundaries.spec.js` (24 test):
    - ogni file sorgente ha un livello;
    - gli import salgono solo verso livelli inferiori o uguali;
    - nessun percorso esce dal pacchetto;
    - nessun modulo interno importa `index.js`/`vue.js`;
    - l'unico esterno ammesso è `vue`, e solo dal livello 4;
    - nessun file del pacchetto contiene il nome del prodotto (il test lo compone a pezzi per non contenerlo);
    - `resources/js` usa solo `svg-avatar` e `svg-avatar/vue`, mai il percorso `packages/svg-avatar`.
  - Prova di mutazione: un file temporaneo `palettes/zz-bad.js` che importava `rules/` ha fatto fallire il test ("goes up a level"); il file è stato rimosso.
  - Esiti: `npx vitest run packages/svg-avatar` → 111/111; `npm run test:js` → 167/167 (10 file); `npm run build` ok.

- **5.1 (2026-11-23)**: componente Vue nel pacchetto.
  - `components/Avatar/`: `Avatar.vue` (solo template: `<svg>` con un `<path v-for>` + `v-bind`, `<title>` se c'è `label`), `Avatar.logic.js` (Options API), `Avatar.css` (`.sa-avatar` con `clip-path: circle(50%)`, `.sa-avatar--square`), `index.js`; entry `vue.js`.
  - Props: `seed`, `overrides`, `traits` (salta il seed), `palette`, `size` (default 40), `label`, `shape` (`circle`/`square`).
  - Calcoli: computed `resolvedTraits` → `composeLayers`, quindi doppia cache (Vue + pacchetto). Niente `v-html`.
  - Test `tests/vue.spec.js` (3, con `@vue/test-utils`): stessi path di `renderAvatarSvg` per seed, override e traits espliciti; accessibilità; aggiornamento al cambio del seed; forma quadrata.
  - Esiti: `npx vitest run packages/svg-avatar` → 118/118. Il test di confine copre anche `components/` e `vue.js`.

- **5.2 (2026-11-23)**: avatar nell'header dell'area partner (idratazione JS).
  - `resources/views/components/avatar.blade.php` (`<x-avatar :seed :overrides :initial :size>`) emette `<span class="adm-avatar" data-avatar-seed data-avatar-overrides data-avatar-size aria-hidden>` con l'iniziale.
  - `resources/js/jsBack/avatar.js`: importa solo `svg-avatar`, sostituisce l'iniziale con `renderAvatarSvg(avatarTraits(seed, overrides), { size })` e aggiunge `adm-avatar--drawn`. Override non validi → `{}`. Esporta `hydrateAvatars`.
  - `vite.config.js`: nuovo input `resources/js/jsBack/avatar.js`.
  - `layouts/app.blade.php`: `@vite('resources/js/jsBack/avatar.js')` solo dentro `@auth`; nel menu account `<x-avatar>` al posto dello span. Il seed provvisorio è `'user-'.Auth::id()`, sostituito alla 6.1 dal metodo del modello.
  - `admin.css`: modificatore `.adm-avatar--drawn` (sfondo trasparente).
  - Test:
    - `tests/Feature/Admin/AvatarTest.php` (2): seed, override vuoti e iniziale nell'header; nessun avatar per gli ospiti;
    - `tests/js/avatarHydrate.spec.js` (1): markup uguale a `renderAvatarSvg` e fallback sugli override rotti.
    - Tutti verdi.
  - Peso del chunk `avatar-*.js` nella build: **18,46 kB (6,80 kB gzip)**. Comprende generatore, `parts.json`, palette e regole; viene caricato solo dalle pagine partner autenticate.
  - Verifica visiva: HTML della pagina partner ottenuto da un test temporaneo (in scratchpad, non nel repo) + asset buildati, servito con `php -S` e screenshot a 2x. L'avatar appare nel trigger del menu. File temporanei rimossi da `public/`.
- **5.3 (2026-11-23)**: aggancio SPA documentato in `dev/PIANO_AUTH_CLIENTI.md` (punto 7).
  - Header Vue: `import { Avatar } from "svg-avatar/vue"` e `<Avatar :seed="user.avatar_seed" :overrides="user.avatar_overrides" :size="34" :label="user.name" />` quando non c'è `avatar_url` di Google.
  - I dati arriveranno da `GET /api/me`, che esporrà `avatar_seed`/`avatar_overrides` dal modello (6.1).
  - Nessun codice SPA finché non esiste il login clienti.

- **6.1 (2026-11-23)**: persistenza.
  - Migration `2026_11_23_110000_add_avatar_to_users_table.php`: `users.avatar` JSON nullable, dopo `phone`.
  - `App\User`: cast `'avatar' => 'array'`, **non** fillable (lo scrive solo il controller della 6.2). Metodi `avatarSeed()` (seed salvato oppure `user-{id}`) e `avatarOverrides()` (override filtrati).
  - `App\Avatar\AvatarCatalog` legge **solo dati** del pacchetto: `assets/parts.json` e `palettes/default.json`.
    - Metodi: `options()` (tratto → valori ammessi, letto una volta per richiesta con `once`), `sanitizeOverrides()`, `randomSeed()` (16 hex, come il JS).
  - Pacchetto: la palette predefinita passa in `palettes/default.json`, così il server la può leggere. `default.js` la importa e la congela in profondità, perché la cache dei path è per oggetto palette e una mutazione la invaliderebbe.
  - Layout partner: `<x-avatar :seed="Auth::user()->avatarSeed()" :overrides="Auth::user()->avatarOverrides()" …>`.
  - Esiti: `php artisan test --filter=AvatarTest` → 2/2; `npx vitest run packages/svg-avatar` → 118/118.

- **6.2 (2026-11-23)**: bottone "Nuovo avatar".
  - `App\Http\Controllers\Admin\AvatarController@refresh`: salva `{ seed: AvatarCatalog::randomSeed(), overrides: [] }` sull'utente loggato e torna indietro. Agisce solo sul proprio record, quindi non serve un controllo di proprietà.
  - Rotta `POST admin/avatar/refresh` (`admin.avatar.refresh`) nel gruppo `admin` (middleware `auth`).
  - Menu account del layout partner: nuova voce `<form>` con icona `shuffle`, aggiunta in `resources/icons/icons.json`.
  - Stringa `partner.header.newAvatar` nelle 5 lingue, inserita a mano per non riformattare `strings.json`.
  - Test `tests/Feature/Admin/AvatarTest.php` (6 in totale):
    - seed casuale salvato e mostrato nell'header;
    - gli ospiti vengono rediretti al login;
    - gli override tengono solo tratti e valori noti (scartati nomi sconosciuti, valori inesistenti, array);
    - `AvatarCatalog` legge i dati del pacchetto (13 tratti, 12 capelli, `none`, `deep`).
  - Esito: 6/6 verdi.

- **7 (2026-11-23)**: test completi e misure.
  - `php artisan test`: 94 passati, 4 skipped. Gli skipped sono i noti SEC-1..SEC-4, preesistenti e non legati agli avatar.
  - `npm run test:js`: 175/175 (12 file: 6 del pacchetto, 6 dell'app).
  - `php artisan route:list`: 42 rotte, nessun errore; nuova `admin.avatar.refresh`.
  - `npm run build`: ok.
  - Pesi:
    - chunk partner `avatar-*.js` **18,55 kB (6,83 kB gzip)**, caricato solo dalle pagine partner autenticate;
    - bundle SPA `vue-*.js` da 284,51 a 284,67 kB (+0,16 kB, solo la nuova icona `shuffle` in `icons.json`, che è condiviso). Il pacchetto non entra nel bundle SPA finché la SPA non importa `svg-avatar/vue`;
    - asset grafici: `parts.json` 13,2 kB minificato / 3,9 kB gzip.
  - **Dockerfile** (correzione necessaria): lo stage `assets` copiava solo `resources/`, quindi la build non avrebbe trovato `svg-avatar`. Aggiunto `COPY packages ./packages` prima di `npm run build`. Simulato copiando solo `package.json`, `vite.config.js`, `resources` e `packages` in una cartella temporanea: build ok. `packages/` non è in `.dockerignore` e lo stage PHP (`COPY . .`) lo include, quindi `AvatarCatalog` trova i JSON.
  - Accessibilità:
    - nell'header partner l'avatar è decorativo (`aria-hidden`) perché il nome è scritto accanto;
    - il componente e `renderAvatarSvg` hanno nome accessibile solo con `label`/`title` (testato);
    - resa a 24/32 px verificata nella griglia (Fase 3).

- **8 (2026-11-23)**: documentazione e pulizia.
  - `AGENTS.md`:
    - riga "Avatar utente" in *Dove mettere cosa*;
    - regola su `packages/` nella sezione mappa: indipendenza, nessun nome del prodotto, solo entry pubblici, Dockerfile, rigenerazione consapevole della fixture.
  - `README.md`: paragrafo **Avatar** in *Stile*, voce nelle funzionalità del ristoratore, `packages/` nella struttura cartelle, nota sul Dockerfile.
  - `dev/mappa.html`, solo blocco DATI:
    - 33 nuove righe `F(...)`: `packages/` e il pacchetto, `app/Avatar`, `AvatarController`, `jsBack/avatar.js`, `<x-avatar>`, migration, test, `dev/PIANO_AUTH_CLIENTI.md`, `dev/avatar-grid.html`, questo piano;
    - blocco "Avatar degli utenti" in `STYLE_PAGE`;
    - tutti i `rel`/`go` verificati come presenti in `FILES`; la mappa si apre in Chromium headless senza errori.
  - Pulizia:
    - rimosso `dev/avatar-prototype.html`, superato da `parts.json` (il prototipo approvato resta consultabile nell'artifact della 2.4);
    - `dev/avatar-grid.html` tiene di proposito un renderer locale minimo: si apre senza Vite (il pacchetto importa moduli JSON) e mostra anche le combinazioni vietate dalle regole. Commento aggiornato.
  - Verifica finale:
    - `php artisan test` → 94 passati + 4 skipped (SEC-1..4, preesistenti);
    - `npm run test:js` → 175/175;
    - `php artisan route:list` ok;
    - `npm run build` ok (chunk avatar 18,55 kB / 6,83 kB gzip).

- **9.0 (2026-11-23)**: `tests/fixtures/compat.json` congelato **prima** di ogni modifica a `parts.json`. Non va mai rigenerato.
  - Contenuto:
    - piano di estrazione (13 estrazioni in ordine);
    - SHA-256 del JSON canonico di ogni categoria esistente (ordine delle varianti compreso), di `layers`, di `base` senza `nose` e della palette;
    - le forme del vecchio naso;
    - hash di `seeds.json`;
    - hash dell'SVG di 517 seed deterministici (20 di `seeds.json` + `user-1`…`user-500`, con 3 sovrapposti);
    - hash di 76 campioni di override (un seed fisso per ogni valore di ogni tratto salvabile prima della Fase 9).
  - Il campo editor-only `look` è escluso dall'hash.
  - Helper `tests/compat.helpers.js`; generato con `npx vite-node` da uno script in scratchpad.
  - Test `tests/compat.spec.js` (6):
    - un generatore spia (`vi.mock` di `core/random`) conta 13 estrazioni nello stesso ordine;
    - dati invariati, vecchio naso, `seeds.json` invariato, SVG identici.
  - Esito: `npx vitest run packages/svg-avatar` → 124/124.

- **9.1 (2026-11-23)**: nasi, look e dati condivisi.
  - `parts.json`:
    - `base.nose` è diventato la categoria `nose` (`soft`, con le forme identiche al vecchio naso, poi `button` e `long`);
    - nuovo campo `unseededCategories: ["nose"]`, che è la realizzazione del `seeded: false` del piano: un campo a livello di file evita di confondere un flag con un nome di variante;
    - campo `look` (`["m"]`, `["f"]` o entrambi) su tutte le varianti di capelli, vestiti e accessori.
  - `assets/catalog.js`: `CATEGORIES` ora deriva dalle chiavi di `parts.json` (con `nose` in fondo); nuovi `SEEDED_CATEGORIES`, `UNSEEDED_CATEGORIES` e `looksOf()`.
  - `core/traits/pick.js`: estrae solo le categorie seeded, poi i colori; le unseeded prendono la prima variante senza consumare estrazioni.
  - Test:
    - `determinism.spec.js` adattato: i tratti sono 14, la fixture `seeds.json` è confrontata con `{ ...tratti congelati, nose: "soft" }`, il naso dal seed è sempre `soft`;
    - `compat.spec.js` verde: 13 estrazioni, dati invariati e SVG identici per 517 seed e 76 override;
    - nuovo `tests/data.spec.js` (6): coerenza `rules.json`/`parts.json` (categorie, varianti, livelli, tag), slot e forme, palette che risolve ogni slot, `unseededCategories` e `look`, `options.json` uguale ai dati, **test di proprietà** (2000 casi deterministici con mulberry32: nessuna coppia vietata nel risultato e ogni override mantenuto).
  - Fixture condivisa `tests/fixtures/options.json` (79 valori in 14 tratti + 3 coppie vietate), generata da `tests/data.helpers.js` con `npx vite-node`. La userà il test PHP alla 9.5.
  - `dev/avatar-grid.html`: categorie lette da `parts.json` (compare la sezione nasi); palette letta con `fetch` di `default.json`, perché `default.js` ora importa un modulo JSON, che un browser senza Vite non carica. Screenshot dei 3 nasi controllato.
  - Esito: `npx vitest run packages/svg-avatar` → 133/133.

- **9.2 (2026-11-23)**: dati, etichette e stato puro dell'editor.
  - `editor/groups.js`: `SECTIONS` (9 sezioni: viso + pelle, capelli + colore, occhi + iride, sopracciglia, naso, bocca, vestito + colore, accessorio + colore, sfondo), `LOOKS`, `LOOK_TRAITS` (derivati da `looksOf`: hair, clothes, accessory), `kindOf`.
  - `editor/labels.js`:
    - `defaultEditorLabels` (UI + nomi inglesi di **tutte** le 79 varianti e i colori);
    - `resolveLabels` (merge profondo che accetta solo stringhe non vuote dove il default ha una stringa; le varianti accettano anche valori nuovi);
    - `interpolate` (`:nome` → testo), `humanize`, `variantLabel`, `traitLabel`.
  - `editor/state/`:
    - `store.js` (`createStore`: `getState`/`dispatch`/`subscribe`, notifica solo se lo stato cambia);
    - `actions.js` (reducer puro `editorReducer` con `choose`, `setLook`, `surprise` con seed nel payload, `reset`, `setValue`; `initialState`);
    - `selectors.js` (`traitsOf`, `valueOf` con override minimi, `isDirty` sui tratti risolti, `optionsFor` con ordinamento per look, `previewTraits` per le miniature, `sideChanges` per gli annunci).
  - **Bug trovato dal test di proprietà e corretto:** gli override minimi erano calcolati rispetto ai tratti del seed *dopo* le regole, mentre `avatarTraits` parte dalle estrazioni *grezze*.
    - Caso: seed con berretto e ricci → il berretto sparisce; poi si scelgono capelli compatibili e il berretto ricompare in `avatarTraits`.
    - Ora lo stato usa `traitsFromSeed` (grezzo) come base, sia per risolvere sia per gli override minimi.
  - `tests/boundaries.spec.js`: livelli 5 (`editor/state`, `groups`, `labels`), 6 (`editor/ui`), 7 (`editor/mount.js`), 8 (entry `index.js`, `vue.js`, `editor.js`).
  - Test:
    - `tests/editor.state.spec.js` (12): sezioni che coprono ogni tratto una volta; inizio e valori non validi; `choose` con lock e regole; no-op; look che riordina senza filtrare; Sorprendimi con look; `reset`/`setValue`; `isDirty`; `previewTraits`; store; **proprietà su 500 sequenze di azioni**;
    - `tests/editor.labels.spec.js` (4): etichette complete e distinte, merge sicuro, interpolazione solo testo, fallback.
  - Esito: 155/155.

- **9.3 (2026-11-23)**: interfaccia dell'editor.
  - `editor/ui/`:
    - `dom.js`: `h()` scrive i testi solo con nodi di testo e gli attributi con `setAttribute`; `setAttr`; `setSvg`, l'unico `innerHTML`, solo per SVG di `renderAvatarSvg`, saltato se invariato;
    - `optionGroup.js`: radiogroup generico secondo il pattern WAI-ARIA. Un solo tab stop (roving tabindex); frecce, Home ed End spostano e selezionano, con ritorno all'inizio; contenuto a miniatura, campione o testo; divisore «Altre opzioni» `aria-hidden`; riordino senza ricreare i bottoni;
    - `swatchGroup.js` e `lookFilter.js`: varianti del radiogroup;
    - `preview.js`, `toolbar.js` (Sorprendimi, Ripristina disabilitato se non ci sono modifiche), `liveRegion.js` (`role="status"`);
    - `index.js`.
  - Tutti i listener usano il `signal` dell'istanza.
  - `editor/editor.css`: classi `sa-editor-*`, una per selettore. Tema tramite `--sa-ink`, `--sa-muted`, `--sa-surface`, `--sa-well`, `--sa-line`, `--sa-accent`, `--sa-radius`, `--sa-font`, `--sa-heading-font`, con fallback neutri. Due colonne da 900 px con anteprima sticky; bersagli di almeno 44 px; `focus-visible`; `prefers-reduced-motion`.
  - `editor/mount.js` e l'entry `editor.js` sono già scritti qui, perché servivano ai test di interfaccia; contratto e ciclo di vita si verificano alla 9.4.
  - `boundaries.spec.js`:
    - `editor/editor.css` al livello 5;
    - nuovo test: `editor/` non usa `document.querySelector`, `getElementById`, `getElementsBy*`, `body` né `forms`.
  - Test `tests/editor.ui.spec.js` (9):
    - 9 sezioni e 15 radiogroup;
    - anteprima `role=img`;
    - nomi accessibili descrittivi e unici (es. «Hairstyle: Bun», «Skin tone: Porcelain»), mai posizionali;
    - clic → anteprima + `onChange`;
    - tastiera;
    - il look riordina senza `onChange`, con il divisore;
    - annuncio quando una regola interviene;
    - Sorprendimi e Ripristina;
    - etichette dell'host inserite come testo (`<img onerror>` resta testo);
    - miniature decorative.
  - Esito: 175/175 nel pacchetto.

- **9.4 (2026-11-23)**: montaggio, contratto e documentazione.
  - `editor/mount.js` (`mountAvatarEditor`):
    - controlla che riceva un elemento e ne sostituisce il contenuto;
    - id per istanza (`sa-editor-N`), `AbortController` unico, store;
    - primo render sincrono, poi uno per frame (rAF, o `setTimeout` se manca);
    - `act()` per le intenzioni nate nell'editor: annunci delle regole e `onChange` solo se il valore cambia;
    - handle `{ getValue, setValue, isDirty, reset, destroy }` con la semantica del piano;
    - riusa `COLOR_TRAITS` per i campioni. L'entry `editor.js` esporta solo `mountAvatarEditor` e `defaultEditorLabels`.
  - **Correzione degli override minimi:** `valueOf` prima prova la differenza dall'avatar che il seed dà da solo e la usa se `avatarTraits` la riproduce; altrimenti usa la differenza dalle estrazioni grezze. Prima, un avatar non toccato il cui seed era stato corretto da una regola risultava `{ accessory: "none" }`; ora `{}`. Il test di proprietà resta verde.
  - `vite.config.js`: alias `^svg-avatar/editor$`. `boundaries.spec.js`: l'app può importare anche `svg-avatar/editor`.
  - README del pacchetto:
    - sezione *Editor* con la tabella del contratto (riferimento iniziale immutabile, `getValue`, `setValue`, `reset`, `isDirty`, `onChange`, `destroy`), comportamento, accessibilità, tema, wrapper Vue, salvataggio a carico dell'host;
    - contratto dati aggiornato (`unseededCategories`, `look`) e fixture `compat.json`/`options.json`;
    - tabella dei livelli 0–8.
  - Test `tests/editor.contract.spec.js` (9):
    - solo i 2 export;
    - elemento obbligatorio e contenuto sostituito;
    - `getValue` nuovo e minimo;
    - `onChange` mai al montaggio né con `setValue`;
    - `setValue` non tocca il riferimento;
    - `reset`, `isDirty` sui tratti;
    - `destroy` idempotente, che rilascia i listener e annulla il rAF;
    - due istanze indipendenti con id distinti.
  - Esiti: 184/184 nel pacchetto; `npm run build` ok.

- **9.5 (2026-11-23)**: integrazione Laravel.
  - Rotte `GET admin/avatar` (`admin.avatar.edit`) e `PUT admin/avatar` (`admin.avatar.update`); rimossi `admin.avatar.refresh` e `refresh()`.
  - `App\Http\Controllers\Admin\AvatarController`:
    - `edit`: provenienza sicura salvata in sessione (`avatar.return`); dopo un submit respinto, valore preso da `old()`; etichette = `__('partner.avatar.editor')`, un array dalle traduzioni;
    - `update`: salva `{ seed, overrides }` validati, poi redirect alla provenienza con il flash `avatarSaved`.
    - `safeReturnUrl()` accetta `url()->previous()` solo se ha lo stesso schema, host e porta della richiesta, il percorso è `/admin…`, non è `/admin/avatar…` e non contiene `//`; altrimenti `admin.restaurants.index`.
  - `App\Http\Requests\UpdateAvatarRequest`: `seed` `^[A-Za-z0-9_-]{1,64}$`, `overrides` stringa di al massimo 2000 caratteri + `App\Rules\AvatarOverrides` (oggetto JSON, al massimo 14 chiavi, `AvatarCatalog::overrideErrors()` vuoto). Messaggio unico `partner.avatar.invalid`.
  - `AvatarCatalog`:
    - `excludedPairs()` legge `rules.json` ignorando `prefer`;
    - `overrideErrors()` segnala tratti e valori sconosciuti e coppie vietate scritte per intero.
    - **Bug trovato dal test e corretto:** un valore non stringa (array) causava un errore 500 nel controllo delle coppie; ora la verifica delle coppie avviene solo se i singoli valori sono validi.
  - Vista `admin/avatar/edit.blade.php`:
    - form PUT con campi nascosti e `<div class="adm-avatar-editor" data-avatar-editor data-value data-labels>`;
    - JSON con `JSON_HEX_TAG|AMP|APOS|QUOT` dentro `{{ }}`, mai `{!! !!}`;
    - avviso di errore, `data-avatar-unsaved` dopo un errore;
    - «Salva» disabilitato all'inizio e «Annulla» verso la provenienza sicura; `<noscript>`.
  - `layouts/app.blade.php`: voce di menu «Personalizza avatar» (link con icona `user`) al posto del form «Nuovo avatar»; flash «Avatar salvato» in `<main>`.
  - `resources/js/jsBack/avatarEditor.js` (importa solo `svg-avatar/editor`):
    - `JSON.parse` sicuro, con fallback sui campi nascosti;
    - sincronizza i campi;
    - abilita «Salva» se ci sono modifiche, o sempre dopo un errore;
    - gestisce `beforeunload` (tolto al submit);
    - esporta `setupAvatarEditorPage`.
    - Nuovo input in `vite.config.js`.
  - `admin.css`: `.adm-avatar-form` e `.adm-avatar-editor`, che mappa le `--sa-*` sui token.
  - `strings.json`: `partner.header.newAvatar` → `customizeAvatar`; nuovo gruppo `partner.avatar` (pagina + `editor` con UI, 9 sezioni, 14 tratti e **79 varianti e colori**) in 5 lingue, con nomi unici per gruppo (verificato alla generazione).
  - Test:
    - `tests/Feature/Admin/AvatarTest.php` (27): menu; ospiti; escape di un seed `"><script>` (non esce dall'attributo e il JSON torna identico); update valido con redirect alla provenienza e flash; **12 payload manomessi** respinti con il DB invariato; riapertura sul valore inviato; nessun effetto su altri utenti; **4 provenienze non sicure** (sito esterno, `//evil.test`, `/login`, la pagina stessa) → default; `AvatarCatalog` uguale a `options.json`; `nose` ammesso;
    - `tests/js/avatarEditorPage.spec.js` (6).
  - Esiti: `php artisan test` tutto verde (vedi 9.7 per i numeri finali); `npm run test:js` 247/247; build ok.

- **9.6 (2026-11-23)**: verifica visiva.
  - Metodo: HTML della pagina editor ottenuto da un test temporaneo (in scratchpad), più gli asset buildati, servito con `php -S` e fotografato con Chromium headless a 1280 e 390 px. File temporanei rimossi da `public/`.
  - Correzioni dopo il primo giro:
    1. etichette doppie: nelle sezioni con un solo tratto, o quando l'etichetta è uguale al titolo della sezione, l'etichetta del gruppo diventa solo per lettori di schermo (`sa-editor-sr`, che resta il nome del radiogroup); il tratto `eyes` si chiama «Eye shape» / «Forma degli occhi»;
    2. le miniature di viso, occhi, sopracciglia, naso e bocca erano indistinguibili a 64 px: ora inquadrano il viso (`THUMB_VIEWBOX` in `editor/groups.js`, opzione interna `viewBox` di `toSvgString`; `renderAvatarSvg` non la espone e gli snapshot di compatibilità restano identici);
    3. su mobile le miniature sono di 56 px (mobile-first, 64 px da 600 px), quindi 4 per riga.
  - Secondo giro di screenshot: OK su desktop e mobile.
  - Nuovi test UI: etichetta nascosta ma presente, `viewBox` delle miniature. Pacchetto 186/186.
  - **Accessibilità:** verificata con i test jsdom (ruoli, `aria-checked`, nomi unici e descrittivi, roving tabindex, frecce/Home/End, live region, `role=img` sull'anteprima, `focus-visible` nel CSS). **Non** è stata fatta una prova con un lettore di schermo reale né un dump dell'albero di accessibilità di Chromium: lo strumento headless disponibile non lo espone. Resta una verifica manuale consigliata.
  - Osservazione fuori ambito: a 390 px, nell'header partner, il logo «Foodsy» si sovrappone al badge «PARTNER». È un limite del layout esistente, non dell'editor; non è stato toccato.

- **9.7 (2026-11-23)**: documentazione e verifica finale.
  - `AGENTS.md`:
    - riga "Avatar utente" aggiornata (tre entry, pagina editor, `unseededCategories`, `options.json` da rigenerare, `compat.json` mai, etichette in `partner.avatar.editor`);
    - regola su `packages/` aggiornata (il test di compatibilità fallisce se cambiano le varianti esistenti; l'editor non cerca elementi nella pagina ospite).
  - `README.md`: paragrafo Avatar (editor e validazione server), funzionalità del ristoratore, struttura cartelle.
  - `dev/mappa.html`, solo blocco DATI:
    - 14 nuove righe `F(...)` (editor e sottocartelle, fixture, `avatarEditor.js`, vista editor, `UpdateAvatarRequest`, `AvatarOverrides`, test della pagina);
    - aggiornate le righe del pacchetto, di `AvatarCatalog`, `AvatarController` e `AvatarTest`, e il blocco Avatar di `STYLE_PAGE`;
    - tutti i `rel`/`go` validi; la mappa si apre senza errori.
  - Verifica finale:
    - `php artisan test` → 115 passati + 4 skipped (SEC-1..4, preesistenti);
    - `npm run test:js` → 249/249 (19 file, di cui 11 del pacchetto con 186 test);
    - `php artisan route:list` ok;
    - `npm run build` ok.
  - Pulizia: rimossa da `resources/icons/icons.json` l'icona `shuffle`, aggiunta alla 6.2 e ora inutilizzata (la voce di menu usa `user`).
  - Pesi della build:
    - chunk condiviso del core `layers-*.js` 19,45 kB (7,03 kB gzip), usato dall'header avatar e dall'editor;
    - `avatar-*.js` 0,35 kB;
    - pagina editor `avatarEditor-*.js` 12,6 kB (5,07 kB gzip) + `avatarEditor-*.css` 2,74 kB (0,93 kB gzip), caricati solo da `admin/avatar`.

## Direzione artistica (Fase 1)
- **Linguaggio**: cartoon flat e morbido, ispirato al *linguaggio* di Avataaars (mezzo busto, teste grandi, tratti semplici, colori pastello). Nessun asset né forma copiati: tutte le geometrie sono disegnate da zero.
- **Tela**: viewBox `0 0 120 120`, mezzo busto. Lo sfondo pieno è ritagliato a cerchio dal contenitore (`border-radius: 50%`), così niente `clipPath` né id che si scontrano tra SVG inline.
- **Proporzioni**:
  - testa da y≈24 a y≈76 (~46% dell'altezza), centrata su x=60, larga ~42;
  - occhi a y≈51, sopracciglia a y≈42, naso a y≈58, bocca a y≈66;
  - spalle da y≈85 fino al bordo.
- **Leggibilità**: tratti ≥ 2 unità (≈ 0,5 px a 32 px) e contrasti forti tra pelle, capelli e vestiti; nessun dettaglio sotto 1,5 unità che abbia un significato.
- **Colori**: solo slot semantici (`bg`, `skin`, `skinShade`, `hair`, `hairShade`, `cloth`, `clothShade`, `ink`, `mouth`, `white`, `iris`, `blush`, `metal`), risolti da una palette.
- **Livelli** (dal fondo): `bg` → `hairBack` → `neck` → `body` (vestito, con lo scollo come forma `skin`) → `ears` → `face` → `cheeks` → `eyes` → `brows` → `nose` → `mouth` → `hairFront` → `accessory`.

## Contesto
L'utente ha scelto un avatar SVG originale disegnato da noi, ispirato al linguaggio visivo di Avataaars senza copiarne gli asset, e senza librerie esterne.

La revisione 2 chiede di trattarlo come una **libreria indipendente**, estraibile in futuro: niente Laravel, utenti, DB o design system dentro. Foodsy ne diventa un semplice consumatore.

Obiettivi grafici, quantità di varianti e fasi restano quelli della rev. 1. La Fase 2 (6 avatar prototipo) termina con uno STOP: si prosegue solo dopo l'approvazione dell'utente.

## Modifiche rispetto alla rev. 1 (sintesi)
1. **Codice spostato da `resources/` a `packages/svg-avatar/`.**
   - Il repo non ha già una convenzione `packages/`. `resources/` è codice applicativo, `dev/` è escluso dall'immagine Docker: serve quindi una cartella nuova, inclusa nella build.
2. **Due entry pubbliche** invece di un solo `utils/avatar.js` + componente in `resources/js/components/`:
   - `index.js`: core puro, senza Vue;
   - `vue.js`: il componente.
   Così l'idratazione Blade e i consumatori non Vue non trascinano Vue.
3. **Alias Vite unico** `svg-avatar` → `packages/svg-avatar`. L'app importa solo `svg-avatar` e `svg-avatar/vue`: è lo stesso import che si userebbe con il pacchetto pubblicato su npm. Non servono workspace, `package.json` del pacchetto né build dedicata.
4. **Palette come configurazione del pacchetto** (`palettes/default.js`), non letta da `tokens.css`. Il consumatore può passarne una sua.
5. **Blade: decisione cambiata.** Prima pensavo a un generatore PHP gemello, ora propongo l'idratazione JS.
   - Il layout partner carica già un entry JS. Rende l'avatar da `data-avatar-seed` con il core del pacchetto.
   - Finché lo script non parte resta visibile l'iniziale di oggi, che fa da segnaposto.
   - Motivo: zero logica duplicata. Un adapter PHP con fixture di equivalenza si aggiunge solo se servirà rendering server (email, PDF), e il contratto per farlo è già previsto (vedi sotto).
6. **Test di indipendenza** del pacchetto e del verso delle dipendenze, oltre ai test funzionali.
7. **Il whitelist lato Laravel legge i dati del pacchetto** (`assets/parts.json`), non la logica: dipendenza app → pacchetto, mai il contrario.
8. **Nome agnostico (rev. 3)**: il pacchetto si chiama `svg-avatar` (cartella `packages/svg-avatar/`, alias `svg-avatar` e `svg-avatar/vue`). Dentro il pacchetto non compare mai "Foodsy": nomi, commenti, README, palette e classi CSS (`sa-avatar`) sono neutri. Il nome del prodotto resta solo negli adapter lato app.

## Struttura proposta
```
packages/svg-avatar/
├── README.md              API pubblica, contratto dati, regole di dipendenza, come estrarlo
├── index.js               entry core (senza Vue)
├── vue.js                 entry Vue: export { Avatar }
├── assets/
│   └── parts.json         varianti per categoria: forme { d, slot, layer }, solo slot colore, nessun colore
├── palettes/
│   └── default.js         palette predefinita a toni caldi e morbidi (pelle, capelli, vestiti, iride, tratti), compatibile con l'app ma senza riferimenti a lei
├── rules/
│   ├── rules.json         compatibilità come dati (es. cappello → solo capelli corti davanti)
│   └── apply.js           applyRules(traits): corregge le combinazioni non valide
├── core/
│   ├── types.js           solo typedef JSDoc: Traits, Overrides, Layer, Palette (nessun runtime)
│   ├── random/
│   │   ├── hash.js        cyrb53(seed) → intero
│   │   ├── prng.js        mulberry32(int) → () => float
│   │   └── index.js       barrel interno del modulo random
│   ├── traits/
│   │   ├── pick.js        traitsFromSeed(seed): una scelta per categoria
│   │   ├── normalize.js   normalizeTraits(traits, overrides): validazione nomi, fallback, applyRules
│   │   └── index.js
│   └── layers/
│       ├── compose.js     composeLayers(traits, palette): ordine dei livelli, colori risolti, cache Map
│       ├── svg.js         toSvgString(layers, { size, title }): escape del title
│       └── index.js
├── components/
│   └── Avatar/            Avatar.vue (template) · Avatar.logic.js · Avatar.css · index.js
└── tests/
    ├── determinism.spec.js
    ├── variants.spec.js
    ├── rules.spec.js
    └── boundaries.spec.js  indipendenza e verso delle dipendenze
```
- Niente `utils/`: oggi non c'è una funzione che non appartenga a uno dei moduli sopra.
- Niente `core/index.js`: le entry importano direttamente dai moduli, così si evita una catena di re-export.

Fuori dal pacchetto, lato Foodsy, solo adapter:
- `vite.config.js`: `resolve.alias` + `test.include` esteso a `packages/**/tests/**/*.spec.js`.
- `resources/js/jsBack/avatar.js`: idratazione Blade, nuovo input in `vite.config.js`.
- `resources/views/components/avatar.blade.php` (`<x-avatar :seed>`).
- `app/Avatar/AvatarCatalog.php`: legge `parts.json` per il whitelist.
- Migration `users.avatar`.

## Gerarchia delle dipendenze (niente cicli)
| Livello | Modulo | Può importare |
|---|---|---|
| 0 | `assets/*.json`, `palettes/`, `core/random/`, `core/types.js` | nulla |
| 1 | `rules/` | livello 0 |
| 2 | `core/traits/` | 0–1 |
| 3 | `core/layers/` | 0–2 (assets, palettes, rules, traits) |
| 4 | `components/Avatar` | 2–3 e `vue` (peer, unica dipendenza esterna) |
| 5 | `index.js`, `vue.js` | qualsiasi livello inferiore |

Regole:
- Si importa solo verso livelli inferiori.
- Dentro il pacchetto si importa dal file proprietario o dal barrel del modulo, **mai** da `index.js`/`vue.js`.
- Nessun import fuori dal pacchetto (né `resources/`, né `@/`, né alias).
- L'app importa solo le due entry: niente deep import.

`boundaries.spec.js` lo verifica leggendo gli import di ogni file:
- livello di chi importa ≥ livello dell'importato;
- percorsi relativi che restano dentro `packages/svg-avatar/`;
- unico pacchetto esterno ammesso: `vue`;
- nessun file del pacchetto contiene la parola "foodsy" (nome agnostico);
- in `resources/` nessun import di `svg-avatar/<percorso interno>`.

## API pubblica
```js
// svg-avatar  (core, nessuna dipendenza)
avatarTraits(seed, overrides?)        // → Traits normalizzati; stesso seed → stessi tratti
randomSeed()                          // → stringa nuova (crypto.getRandomValues)
normalizeTraits(traits)               // → Traits validi (fallback sui nomi sconosciuti + regole)
renderAvatarSvg(traits, { palette?, size?, title? })  // → stringa <svg> (consumatori non Vue, idratazione Blade)
avatarOptions()                       // → { categoria: [nomiVarianti], colori: { slot: [chiavi] } } per editor futuro e whitelist
defaultPalette                        // palette predefinita, da estendere o sostituire

// svg-avatar/vue
Avatar   // props: seed (String), overrides (Object), palette (Object), size (Number|String), label (String)
```
- **Interno, non esportato:** `cyrb53`, `mulberry32`, `applyRules`, `composeLayers`, cache, struttura grezza di `parts.json`.
- Il pacchetto non conosce gli utenti: il seed è una stringa qualsiasi fornita dal consumatore.
- `Avatar` rende i path come elementi `<path>` del template: niente `v-html`.

### Contratto dati (per un futuro adapter PHP)
- `parts.json` + `rules.json` + l'algoritmo documentato nel README: cyrb53 → mulberry32 → ordine fisso delle categorie.
- `tests/fixtures/seeds.json` (seed → tratti attesi), generato alla Fase 4 e verificato dal test JS. Un eventuale adapter PHP dovrà superare la stessa fixture.

## Responsabilità dell'app (Foodsy consumatore)
- **Laravel**:
  - colonna nullable `users.avatar` (JSON `{ seed, overrides }`); se vuota il seed è `"user-{id}"`, quindi nessuna scrittura obbligatoria;
  - validazione dei nomi con `AvatarCatalog` (whitelist da `parts.json`);
  - rotta "nuovo avatar" che salva un seed casuale.
- **Decisione d'uso**: foto Google se presente (quando arriverà l'auth clienti), altrimenti avatar dal seed.
- **Vue**: `import { Avatar } from "svg-avatar/vue"`, aggancio nell'header SPA quando esisterà il login clienti.
- **Blade**: `<x-avatar>` emette `<span class="adm-avatar" data-avatar-seed="…" data-avatar-overrides="…">` con l'iniziale; `jsBack/avatar.js` lo sostituisce con `renderAvatarSvg`.

## Fasi e step (copiati in `AVATAR_IMPLEMENTATION_PLAN.md` con stato, file, test, prossimo step)
**Fase 1 – Analisi e progettazione** (invariata nei contenuti)
- 1.1 Salvare l'analisi auth clienti già approvata in `dev/PIANO_AUTH_CLIENTI.md`.
- 1.2 Creare `AVATAR_IMPLEMENTATION_PLAN.md`:
  - questa architettura;
  - direzione artistica: mezzo busto, viewBox 120×120, testa ~46% dell'altezza, tratti spessi leggibili a 32 px, colori morbidi;
  - varianti target: viso 4, capelli 12, occhi 5, sopracciglia 4, bocche 5, vestiti 8, accessori 6 incluso "nessuno";
  - regole di aggiornamento e ripresa da una nuova sessione.

**Fase 2 – Prototipo grafico** (invariata, poi STOP)
- 2.1 Disegnare un sottoinsieme originale di parti:
  - 2–3 visi, 6 capelli, 3 occhi, 2 sopracciglia, 3 bocche, 4 vestiti, 2 accessori;
  - metterle nel prototipo autonomo `dev/avatar-prototype.html`: SVG inline, nessun codice del pacchetto ancora.
- 2.2 Comporre 6 avatar diversi, a 160, 64 e 32 px su fondo chiaro e scuro.
- 2.3 Controllare con screenshot headless, se disponibile, e misurare i pesi (byte grezzi e gzip).
- 2.4 Pubblicare il prototipo come artifact privato, aggiornare il piano. **STOP per approvazione.**

**Fase 3 – Asset** (rivista: destinazione)
- Varianti definitive in `packages/svg-avatar/assets/parts.json`, solo slot e nessun colore.
- `palettes/default.js`.
- Griglia di verifica delle combinazioni critiche in `dev/`.
- Path ottimizzati (1 decimale).

**Fase 4 – Core del pacchetto** (rivista: moduli)
- 4.1 `core/random` + test di determinismo.
- 4.2 `rules/` + `core/traits` + test sulle regole (1000 seed senza combinazioni vietate).
- 4.3 `core/layers` + `renderAvatarSvg` + test sulle varianti (ognuna rende un SVG valido) e sull'escape del title.
- 4.4 Entry `index.js`, README, fixture `seeds.json`.
- 4.5 Alias in `vite.config.js` + `boundaries.spec.js`.

**Fase 5 – Componente Vue e integrazione front** (rivista)
- 5.1 `components/Avatar` + entry `vue.js` + test di montaggio (Vitest).
- 5.2 Lato app: `<x-avatar>` + `jsBack/avatar.js` nell'header partner al posto dell'iniziale.
- 5.3 Punto d'aggancio SPA documentato, da attivare con il login clienti.

**Fase 6 – Laravel** (rivista)
- Migration `users.avatar`, `#[Fillable]`, `AvatarCatalog`, validazione, rotta "nuovo avatar" + bottone nel menu account partner.
- Test PHP: whitelist, seed predefinito, rifiuto dei nomi sconosciuti.

**Fase 7 – Test e misure** (invariata + indipendenza)
- `php artisan test`, `npm run test:js` (incluso `boundaries.spec.js`).
- `npm run build`: peso del chunk prima e dopo, peso di `parts.json` grezzo e gzip.
- Accessibilità e resa a 24/32 px.

**Fase 8 – Documentazione** (invariata + pacchetto)
- README del pacchetto.
- AGENTS.md: riga "Avatar" in "Dove mettere cosa" e regola "il pacchetto non importa mai l'app".
- `dev/mappa.html` (`F(...)`, `STYLE_PAGE`) e README del progetto.
- Pulizia del prototipo.

## Decisioni
- ✅ **Blade: idratazione JS** (confermata dall'utente il 2026-11-23). Nessun adapter PHP per ora; se servirà (email, PDF) dovrà superare `tests/fixtures/seeds.json`.
- ✅ **Bottone "Nuovo avatar" nel menu account dell'area partner** (confermato il 2026-11-23), finché non esiste la pagina account cliente.

## Verifica delle Fasi 1–2
- Il prototipo si apre senza errori ed è stato riletto da screenshot alle 3 dimensioni.
- Pesi misurati con `wc -c` e `gzip -c | wc -c`.
- `git status` mostra solo `AVATAR_IMPLEMENTATION_PLAN.md`, `dev/PIANO_AUTH_CLIENTI.md` e `dev/avatar-prototype.html`.
