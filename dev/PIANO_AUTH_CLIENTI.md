# Login clienti + "Accedi con Google": analisi approvata (non ancora implementata)

Stato: **analisi approvata il 2026-11-23, nessuna implementazione**. L'avatar degli utenti è un progetto a parte: `AVATAR_IMPLEMENTATION_PLAN.md` (root).

## Contesto
Oggi il login esiste solo per i ristoratori: `laravel/ui` (`Auth::routes()` in `routes/web.php`), con sessione su cookie (`SESSION_DRIVER=file`) e pagine Blade. Dopo il login si va su `/admin`. I clienti ordinano come ospiti: l'ordine ha `orders.customer_email` ma nessun `user_id`.

Obiettivi:
- account clienti dentro la SPA Vue;
- accesso con Google per tutti;
- predisposizione per un futuro admin di piattaforma.

## Risposte in breve
- **L'autenticazione attuale vale anche per i clienti?** Sì. La SPA è servita dallo stesso dominio di Laravel (rotta catch-all), quindi il cookie di sessione è lo stesso. Basta aggiungere **Laravel Sanctum** (gratuito, ufficiale) in modalità "SPA con cookie".
- **Google per ristoratori e clienti?** Sì, con **Laravel Socialite** (gratuito, ufficiale). Lo stesso codice serve per entrambi.
- **Servizi a pagamento?** No. Google OAuth è gratuito (si crea un client OAuth nella Google Cloud Console). Auth0, Clerk e Firebase non servono.

## Opzioni valutate
| Opzione | Costo | Pro | Contro | Verdetto |
|---|---|---|---|---|
| **A. Sessione Laravel + Sanctum (cookie)** | 0 € | Riusa quello che c'è; un solo login per tutti; sicura (cookie httpOnly, nessun token nel browser) | Richiede che SPA e API stiano sullo stesso dominio (oggi è così) | **Scelta** |
| B. Token API (Sanctum token o Passport) | 0 € | Serve a un'eventuale app mobile | Token salvati nel browser, più codice | Solo se arriva un'app mobile; Sanctum lo permette anche dopo |
| C. Servizio esterno (Firebase, Auth0, Clerk, Supabase) | gratis fino a una soglia | Login social e interfaccia già pronti | Utenti fuori dal nostro DB, dipendenza da un fornitore | No |
| D. Tabelle o guard separati per cliente e ristoratore | 0 € | Separazione netta | Codice doppio; una persona non può avere entrambi i ruoli con la stessa email | No |

## Organizzazione (opzione A)
1. **Una tabella `users` con la colonna `role`**: `customer`, `restaurateur`, in futuro `admin`. Il default è `customer`; gli utenti già presenti diventano `restaurateur`.
2. **Middleware `role:restaurateur` sul gruppo `admin`**: oggi basta `auth`, quindi un cliente loggato potrebbe entrare nell'area partner. Questo è obbligatorio. Prima conviene chiudere SEC-1..SEC-4.
3. **Login e registrazione**:
   - una sola pagina di login: il ruolo si legge dal DB e decide il redirect (cliente → SPA, ristoratore → `/admin`, in futuro admin → `/platform`);
   - una sola pagina di registrazione con un selettore a schede, «Ordino cibo» / «Ho un ristorante», che cambia titolo, testo, foto laterale e campi in più;
   - `?role=restaurateur` preseleziona la scheda: le CTA «Diventa partner» puntano a `/register?role=restaurateur`;
   - si riusano le viste Blade di `laravel/ui` e il layout `auth-*`; l'admin non si registra da solo.
4. **Header SPA**: «Accedi» per gli ospiti; da loggato, avatar con menu (account, ordini, esci). Lo stesso avatar compare nell'area partner. Si aggiunge `GET /api/me`.
5. **Ordini collegati all'account**: `orders.user_id` nullable. Il checkout da ospite resta; da loggati i dati sono precompilati e compare lo storico ordini.
6. **Google**:
   - Socialite con le rotte `/auth/google` e `/auth/google/callback`;
   - `google_id` nullable, `password` nullable, `avatar_url` per la foto Google;
   - se l'email esiste già, l'account Google viene collegato a quello;
   - il ruolo dipende dalla scheda da cui parte il clic.
7. **Avatar**: foto Google se esiste, altrimenti avatar SVG proprietario (`AVATAR_IMPLEMENTATION_PLAN.md`).
   - `GET /api/me` restituisce `avatar_url` (Google), `avatar_seed` e `avatar_overrides` (da `User::avatarSeed()` / `avatarOverrides()`).
   - Nell'header Vue: `import { Avatar } from "svg-avatar/vue"` e `<Avatar :seed="user.avatar_seed" :overrides="user.avatar_overrides" :size="34" :label="user.name" />`, oppure `<img>` con la foto Google. Per gli errori di caricamento della foto si usa lo stesso schema di fallback di `FoodImage`.
   - Mai deep import del pacchetto: solo `svg-avatar` e `svg-avatar/vue` (lo verifica `boundaries.spec.js`).

## Predisposizione per l'admin di piattaforma (non ora)
- Il ruolo `admin` è già previsto: il futuro backoffice sarà un gruppo di rotte (es. `/platform`) con `role:admin`.
- I permessi passeranno a Gate/Policy, che sostituiscono i controlli `user_id == auth()->id()` sparsi nei controller.
- Per gli admin servirà l'autenticazione a due fattori: Laravel Fortify (gratuito) la offre già pronta.
- Se una persona dovrà avere più ruoli, `role` diventerà una tabella `role_user`.

## Effort stimato
| Blocco | Giorni |
|---|---|
| Ruolo + middleware area partner + test | 0,5 |
| Sanctum + login, registrazione a schede, logout e reset password (stringhe in 5 lingue, test) | 2,5–3,5 |
| Ordini collegati all'account + "I miei ordini" | 1 |
| Google (Socialite + configurazione) | 1–1,5 |

## Trappole
- **Codespace e Google**: Google accetta solo gli URL di ritorno registrati prima, e l'URL del Codespace cambia. Si registrano `localhost` e l'URL di produzione; altri URL si aggiungono a mano.
- **Sanctum**: servono `SANCTUM_STATEFUL_DOMAINS` coerente con il dominio e `statefulApi()` in `bootstrap/app.php`.
- **Consenso Google**: in modalità test funziona solo per gli account aggiunti a mano. Per il pubblico serve la verifica dell'app, gratuita ma con privacy policy obbligatoria.
