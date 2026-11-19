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

## 4. Front-end
```bash
npm install
npm run dev          # oppure: npm run watch
```

## 5. Avvio
```bash
php artisan serve --port=8000
```
La porta **deve** essere 8000: `http://127.0.0.1:8000` è scritto fisso in `resources/js/store.js` e `resources/js/pages/Payment.vue`.

- Sito clienti: http://127.0.0.1:8000
- Pannello ristoratori: http://127.0.0.1:8000/login (utenti di prova in `database/seeds/UsersTableSeeder.php`)

## 6. (Facoltativo) Immagini caricate dall'admin
```bash
php artisan storage:link
```

## Problemi comuni
- **Errore 500** → manca `.env` o `APP_KEY`; leggi `storage/logs/laravel.log`.
- **Pagina bianca** → non hai eseguito `npm run dev`.
- **Liste vuote** → server non sulla porta 8000 o seed non eseguito.
- **"could not find driver"** → manca l'estensione PHP `pdo_sqlite`.
- **Pagamento** → usa Braintree con un token nel codice, non verificato: potrebbe non funzionare in locale.
