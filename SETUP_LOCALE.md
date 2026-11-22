# Avvio in locale (macOS)

Esegui tutto dalla cartella del progetto: `cd ~/Desktop/deliveBoo`

## Cosa serve
| Strumento | Stato sul Mac |
|---|---|
| PHP 7.4–8.1 (non 8.2+: Laravel 7) | da installare |
| Composer | da installare |
| Node 16 + npm 8 | già presenti |
| MySQL | non necessario (si usa SQLite) |

## 1. PHP e Composer
```bash
brew install shivammathur/php/php@8.0 composer
brew link --overwrite --force php@8.0
php -v && composer -V
```

## 2. Database SQLite e file `.env`
```bash
touch database/database.sqlite
cp .env.example .env
```

Modifica `.env` sostituendo queste righe:
```
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=/Users/filippo.milzagmail.com/Desktop/deliveBoo/database/database.sqlite
MAIL_MAILER=log
```
`MAIL_MAILER=log` scrive le email di conferma in `storage/logs/laravel.log`; senza, l'ordine va in errore per mancanza di credenziali SMTP.

## 3. Dipendenze PHP, chiave, dati di prova
```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
```
`migrate:fresh` cancella e ricrea tutte le tabelle: va bene per il primo avvio. Dopo, per aggiungere i dati di prova mancanti senza perdere nulla, basta `php artisan db:seed` (si può rilanciare quante volte vuoi).

## 4. Front-end
```bash
npm install
npm run dev          # oppure: npm run watch
```

## 5. Avvio
```bash
php artisan serve --port=8000
```
Il front usa URL relativi, quindi funziona su qualsiasi porta; i link qui sotto assumono la 8000.

> Alternativa più semplice, senza installare PHP né MySQL: Docker. Vedi i riquadri in cima alla tab "Link di avvio" di `dev/mappa.html`.

- Sito clienti: http://127.0.0.1:8000
- Pannello ristoratori: http://127.0.0.1:8000/login (utenti di prova in `database/seeds/UsersTableSeeder.php`)

## 6. (Facoltativo) Immagini caricate dall'admin
```bash
php artisan storage:link
```

## Problemi comuni
- **Errore 500** → manca `.env` o `APP_KEY`; leggi `storage/logs/laravel.log`.
- **Pagina bianca** → non hai eseguito `npm run dev`.
- **Liste vuote** → seed non eseguito, oppure `public/js` non ricompilato (`npm run dev`).
- **"could not find driver"** → manca l'estensione PHP `pdo_sqlite`.
- **Pagamento** → usa Braintree con un token nel codice, non verificato: potrebbe non funzionare in locale.
