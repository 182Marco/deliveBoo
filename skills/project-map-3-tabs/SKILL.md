---
name: project-map-3-tabs
description: Costruisce la "mappa del progetto" interna, un unico file HTML autonomo (senza librerie) con SEMPRE 3 tab - Grafico concettuale (SVG a nodi e frecce), File e cartelle (esplora risorse stile VS Code con spiegazioni), Link di avvio (matrice di URL + compositore). Grafica dark neon pixel-perfect con palette fissa. Usare quando l'utente chiede una mappa/overview/documentazione visuale interna di un progetto, "la mappa", "mappa.html", o di replicare la mappa di myLink3 in un altro progetto.
---

# Mappa del progetto a 3 tab (agnostica)

Un solo file `mappa.html` (HTML + CSS + JS inline, zero dipendenze, funziona con
doppio clic o da server statico). Vive fuori dal codice di produzione (es.
`dev/mappa.html`) e non viene mai pubblicata né referenziata dal sito.

## Come usarla (procedura)

1. Copia `map.template.html` (accanto a questo file) in `dev/mappa.html` (o
   cartella interna equivalente). **Non toccare CSS, markup né la parte
   `RENDER`**: sono identici in ogni progetto, e garantiscono la resa
   pixel-perfect.
2. Riscrivi **solo** il blocco `DATI`: `META`, `FILES`, `KINDS` (invariato se
   possibile), `NODES`, `EDGES`, `GROUPS`, `STEPS`, `TARGETS`, `QUERIES`,
   `QUERY_LABEL`.
3. Studia il progetto reale prima di scrivere i dati: ogni file/cartella
   deve essere spiegato davvero, ogni freccia deve corrispondere a un
   passaggio reale di dati/chiamate.
4. Verifica in browser: tre tab funzionanti, nessun errore in console,
   ogni `go` dei nodi e ogni `rel` dei file risolve a un file esistente
   nell'albero.
5. Aggiorna la mappa ogni volta che cambia la struttura del progetto: a un
   nuovo file corrisponde una riga `F(...)`, a un nuovo flusso un nodo/arco.

La mappa segue la stessa filosofia del progetto che descrive: **contenuti = dati,
HTML/JS = renderer che li itera**. Mai testo di progetto nel markup.

## Le 3 tab (SEMPRE tutte e tre, sempre in quest'ordine)

Ordine e id fissi: `graph`, `code`, `links`. Navigazione con click, frecce
←/→ (ruolo `tablist`, con wrap) e hash URL (`#graph`, `#code/src/js/main.js`,
`#links`) che ricorda tab e file aperto; `replaceState`, ascolta `hashchange`.
Nessun hash valido → apre `graph` (tab 1) con il pannello "Da dove iniziare"
nella tab 2.

### Tab 1 - "Grafico concettuale" (`#panel-graph`)

Risponde a: **come funziona il progetto in un colpo d'occhio?**
Contenuto, dall'alto:
1. `h1` titolo + `.lead` (2-3 righe, `<b>` per "Clicca un riquadro").
2. Riquadro `.golden`: la regola/invariante d'oro del progetto (una frase).
3. `.legend`: una voce per ogni `KINDS` (quadratino colore + etichetta).
4. Grafico SVG (`viewBox="0 0 1260 790"`) con nodi, frecce etichettate,
   gruppi tratteggiati. Nodo cliccabile → salta alla tab 2 sul file `go`.
5. `.hint` linea continua = passa dati/chiama codice; tratteggiata = indiretto o
   solo sviluppo.
6. `h2` + `.steps`: "Cosa succede quando si avvia" - 5-8 passi numerati
   in card a griglia.

Regole del grafico: nodi 220x92 su griglia a 4 colonne (x = 40, 360, 680,
1000) e righe (y = 40, 260, 470, 665); gruppo 1 (codice di produzione) in
alto, gruppo 2 (build/dev) in basso; kind `data` al centro-sinistra, kind `out`
(risultato nel browser) a destra; frecce da sinistra a destra, niente incroci
inutili. Titolo nodo ≤ ~22 caratteri, sottotitolo ≤ 2 righe da 30 caratteri
(spezzato automaticamente). Un nodo senza `go` (es. "Pagina nel browser") non
è cliccabile.

### Tab 2 - "File e cartelle" (`#panel-code`)

Risponde a: **cosa fa ogni file e cartella, in parole semplici?**
Layout a 2 colonne `300px 1fr`, stile VS Code:
- **Sidebar** `.side`: header sticky "Esplora · nome" + link "comprimi tutto";
  albero `role="tree"` con chevron ▶ che ruota, icona per estensione (vedi
  `FILE_ICON`), `badge` opzionale a destra (es. "solo dev", "generata"),
  voce corrente evidenziata in turchese.
- **Main** `.main`: breadcrumb sticky (`root › cartella › file`, ultimo in
  grassetto) + articolo `.doc` con: pill del tipo ("Cartella"/"File js"/
  "Generato"), titolo monospace 26px (le cartelle terminano con `/`), frase
  `one` 17px, sezione "Cosa fa, in parole semplici" (punti), "Esempio" (codice
  + `|||` perché), "Contenuto" (figli cliccabili, solo cartelle), "Collegato a"
  (chip dei `rel`).
- Aperti all'avvio: radice e le 1-2 cartelle principali del codice.
- Cartelle/file non ancora esistenti o generati: `{ badge, ghost: true }`.
- Scrittura: italiano semplice per non esperti, i nomi tecnici tra backtick
  (`` `x` `` → `<code>` turchese). Ogni file ha almeno la frase `one`; i file
  importanti hanno punti + esempio + `rel`. I `rel` sono percorsi completi
  dalla radice (non nomi di file).
- Su mobile (≤800px): sidebar sopra (38vh), documento sotto.

### Tab 3 - "Link di avvio" (`#panel-links`)

Risponde a: **in quanti modi posso far partire il progetto, e con quale URL?**
Contenuto:
1. `h1` + `.lead` che spiega le scelte indipendenti (es. sorgente/pubblicata,
   con/senza un flag `?x`, modalità simulazione/mock). I link sono relativi
   al file mappa: funzionano da doppio clic e da server locale.
2. **Matrice** `.grid`: colonna sinistra `150px` con righe = destinazioni
   (`TARGETS`), 2 colonne = varianti della query (`QUERIES`, es. con/senza flag).
   Ogni cella è una card-link con URL assoluto monospace turchese + nota.
   Una destinazione non disponibile → `.is-na` (opacità .45, bordo tratteggiato).
3. **Compositore** "Costruisci il tuo link": radio per destinazione, checkbox
   per il flag, riga di output con `<code>` URL, bottone "Apri" e "Copia URL"
   (feedback "Copiato ✓" per 1.6 s).
4. **Note** `.notes` con bordo sinistro ambra: server locale (`python3 -m
   http.server`), comandi di build/rigenerazione, avvertenze (cosa non
   esiste in produzione, il flag è solo un interruttore, ecc.).

Se il progetto non ha query/flag: mantieni comunque due colonne con
"Predefinito" e "Con parametro" o la variante più sensata (es. dev/prod,
lingua). La tab 3 non si omette mai.

## Grafica pixel-perfect

**Principio:** dark-mode "neon-tech" a 4 livelli di profondità di blu-notte, un
solo colore d'accento turchese per interazione/focus/enfasi, bordi sottilissimi
blu-ghiaccio semitrasparenti, angoli arrotondati, nessuna ombra pesante.
Tutto è CSS vanilla; i colori sono variabili in `:root` o hex fissi riportati
sotto. **Non inventare altri colori.**

### Font
- Sans: `system-ui, -apple-system, 'Segoe UI', sans-serif`, `14px/1.55` sul body.
- Mono: `ui-monospace, 'SF Mono', Menlo, Consolas, monospace` (var `--mono`)
  per codice, breadcrumb, titoli file, URL.
- Nessun web-font, nessuna icona esterna (le icone sono caratteri unicode: ▶ ▸ ↗ ✓ ▣ # $ {} <> M JS).

### Variabili (`:root`)
| Variabile | Valore | Uso |
|---|---|---|
| `--bg` | `#0b0f14` | sfondo pagina, tab attiva, area documento |
| `--panel` | `#101d29` | card (step, link, chip, composer) |
| `--panel-2` | `#152535` | riservato (superficie rialzata; definito, uso raro) |
| `--line` | `rgba(110,180,210,.17)` | TUTTI i bordi 1px |
| `--accent` | `#00dccb` | turchese: accenti, focus, hover bordo, titoli sezione doc, code |
| `--blue` | `#1685ff` | gruppo "src/" e kind shell |
| `--amber` | `#f5b84b` | note, gruppo build/dev, kind dev |
| `--violet` | `#b18cff` | kind output browser |
| `--text` | `#f5f7fa` | testo principale |
| `--muted` | `#aab8c7` | testo secondario |

### Superfici hex fuori dalle variabili (usarle ESATTAMENTE così)
| Hex | Dove |
|---|---|
| `#080b0f` | barra tab (il più scuro) |
| `#0a1118` | sfondo del riquadro grafico SVG, alone del testo etichetta archi |
| `#0d141b` | sidebar albero + sua header sticky |
| `#08101a` | blocchi codice (`.example`, `.out`, `pre` nelle note) |
| `#0b0f14ee` | breadcrumb sticky (con `backdrop-filter: blur(6px)`) |

### Colori testo/dettaglio fuori dalle variabili
`#cdd9e4` voci albero · `#e6eef6` frase `one` del doc · `#d7e6f3` testo codice ·
`#d9e2ea` testo note · `#cfe0ee` etichette archi · `#6f8aa0` label dei gruppi SVG ·
`#5d7a92` colore archi (linee) · `#8fb0c8` punta di freccia (marker).

### Trasparenze del turchese (`#00dccb` = `rgb(0,220,203)`)
`.06` sfondo `.golden` · `.08` sfondo `<code>` inline · `.14` voce albero
corrente, cerchio numerato degli step, bottone `.btn` · `.26` bottone `.btn`
hover · `.35` bordo `.golden` · `.4` bordo `.btn`.
Altre: `rgba(255,255,255,.04)` hover tab · `rgba(255,255,255,.05)` hover voce albero.

### Palette dei 6 kind del grafico (bordo / riempimento)
| kind | Significato | Bordo (stroke) | Riempimento (fill) |
|---|---|---|---|
| `data` | dati / fonte unica | `#00dccb` | `#0b2d2f` |
| `shell` | pagina e stile (HTML/CSS) | `#1685ff` | `#0e2340` |
| `code` | codice (logica) | `#8ea4b8` | `#142231` |
| `out` | risultato nel browser | `#b18cff` | `#241b3d` |
| `dev` | solo sviluppo / build | `#f5b84b` | `#33270f` |
| `assets` | immagini e video | `#6fcf97` | `#10301f` |

Regola: il riempimento è il colore del bordo scurito a ~20% di luminosità,
cosi' il nodo resta leggibile e il bordo "brilla". Se servono kind
aggiuntivi, deriva così (non oltre 7-8 kind totali).
Gruppi SVG: contorno tratteggiato `3 6`, `stroke-opacity .35`, `rx 16`, colore
del gruppo (blu `#1685ff` per codice di produzione, ambra `#f5b84b` per
build/dev).

### Colori icone file (`FILE_ICON`, testo carattere nella sidebar)
`js` `JS` #f0db4f · `html` `<>` #e8825a · `css` `#` #58a6ff · `md` `M` #9bb7d1 ·
`sh` `$` #6fcf97 · `json` `{}` #f5b84b · `avif` `▣` #c58af9 · `svg` `▣` #f5b84b ·
`ico` `▣` #c58af9 · altro `·` / cartelle `▸` #8ea4b8. Estendere con le stesse
tinte per nuove estensioni (ts blu #58a6ff, py verde #6fcf97, ecc.).

### Anatomia e misure, componente per componente
- **Barra tab** `.tabs`: flex, gap 4, padding `10px 16px 0`, sfondo `#080b0f`,
  bordo inferiore 1px `--line`, allineata in basso. A sinistra il brand
  (12px, letter-spacing .04em, `--muted`; `<b>` in turchese; margin-right 16;
  nascosto ≤800px). Tab: padding `9px 18px`, radius `10px 10px 0 0`,
  testo `--muted`; hover testo `--text` + bg bianco .04; **attiva**: testo
  `--text`, sfondo `--bg`, bordo `--line` (senza il basso), `top:1px` e
  `box-shadow 0 2px 0 --bg` (si "fonde" col pannello) + barretta turchese
  2px in alto (`left/right 14px`, radius 2).
- **Focus** ovunque: `outline: 2px solid --accent; outline-offset: 2px`.
- **Pannelli**: `flex:1`, mostrati con `.is-active`. Tab 1 e 3: padding
  `22px clamp(16px,3vw,40px) 48/60px`, contenuto in `.wrap` max 1280px centrato.
  `h1` 22px, `h2` 16px, `.lead` 78ch `--muted`.
- **Golden** `.golden`: bordo turchese .35, sfondo turchese .06, radius 12,
  padding `12px 16px`, max 90ch, `<b>` turchese.
- **Riquadro grafico** `.graph`: bordo `--line`, radius 14, sfondo `#0a1118`,
  svg `min-width: 980px` (scroll orizzontale su schermi piccoli).
- **Nodo SVG**: `rect` 220x92 rx 12, fill/stroke del kind, stroke-width 1.5;
  titolo 15px/600 `--text` a (16,38); sottotitoli 12px `--muted` a y 60 e 77.
  Hover/focus: `filter: brightness(1.35)` + stroke-width 2.5 (transizione .15s).
- **Archi**: linee dritte da bordo a bordo (clip al rettangolo + 4px), colore
  `#5d7a92`, spessore 1.6, tratteggio `6 5` per i secondari, punta di freccia
  8x8 `#8fb0c8`. Etichetta arco 11.5px `#cfe0ee`, centrata a metà, con halo
  (`paint-order: stroke; stroke: #0a1118; stroke-width: 5px`) per staccarsi
  dalla linea.
- **Label gruppo**: 11px/600 `#6f8aa0`, MAIUSCOLO, letter-spacing .08em, in
  basso a sinistra del gruppo (x+16, y+h-12).
- **Steps**: griglia `repeat(auto-fit, minmax(280px,1fr))` gap 10; card `--panel`,
  bordo `--line`, radius 12, padding `12px 14px 12px 50px`; cerchio numerato
  25px turchese .14, numero 700 turchese a (13,12).
- **Sidebar**: voce 13px, padding `3px 10px 3px calc(10px + var(--d)*14px)`
  (rientro 14px per livello), chevron 9px 10px largo, icona 15px, nome con
  ellissi, badge 10px `--muted` a destra. Header sticky 11px MAIUSCOLO
  letter-spacing .1em.
- **Doc**: max 860px, padding `26px 28px 70px`; pill tipo 11px MAIUSCOLO, bordo
  `--line`, radius 999; `h3` 12px MAIUSCOLO letter-spacing .1em turchese;
  `.example`: sfondo `#08101a`, bordo `--line` + **bordo sinistro 3px
  turchese**, radius 8, 12.5px mono, `.why` 13px sans `--muted`.
- **Chip**: `--panel`, bordo `--line`, radius 999 (10 nei "Contenuto", full
  width con la descrizione `small` a destra), mono 12px, hover bordo turchese.
- **Card link** (tab 3): `--panel`, bordo `--line`, radius 12, padding
  `13px 15px`, hover bordo turchese + `translateY(-1px)`; URL mono 13px turchese;
  descrizione 12.5px `--muted`; `↗` a destra.
- **Composer**: `--panel`, bordo, radius 14, padding `16px 18px`, max 820px;
  `accent-color: --accent`; output su `#08101a` radius 10; bottone `.btn`
  turchese .14 / bordo .4 / hover .26, radius 8, padding `6px 14px`.
- **Note**: bordo sinistro 3px **ambra**, padding-left 12; `pre` come `.example`.
- **Responsive** (`max-width: 800px`): tab 2 in colonna (`38vh 1fr`), sidebar con
  bordo sotto, matrice tab 3 a 1 colonna, brand nascosto. Unico breakpoint.
- **Reset**: `* { box-sizing: border-box; margin:0; padding:0 }`, `html, body
  { height:100% }`, body `display:flex; flex-direction:column`.

## Checklist finale (pixel-perfect)
- [ ] CSS e `RENDER` copiati dal template senza modifiche.
- [ ] 3 tab presenti, ordine `graph`, `code`, `links`, tutte popolate.
- [ ] Solo colori di questa palette; nessun colore nuovo nel CSS.
- [ ] Tutti i `go` dei nodi e i `rel` risolvono a percorsi presenti in `FILES`.
- [ ] Ogni arco è reale (esiste davvero quel passaggio nel progetto).
- [ ] Nessun errore in console; navigazione da tastiera ok (Tab, ←/→, Invio).
- [ ] La mappa non è referenziata dal codice di produzione né copiata nel build.
