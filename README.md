# RSSFlow - Sistema di Aggregazione Feed RSS con Autenticazione e Gestione Ruoli

## Descrizione
RSSFlow è un'applicazione PHP che permette la gestione di feed RSS organizzati in categorie, con autenticazione utente e gestione di ruoli (admin e utente normale). Supporta l'aggregazione di feed RSS, la rimozione di duplicati, e l'aggiornamento automatico tramite cron job.

## Caratteristiche principali
- Login con email e password.
- Ruoli: `admin` (gestione completa) e `utente` (gestione categorie e feed propri).
- Gestione categorie con nome e slug univoco.
- Gestione feed RSS associati a categorie.
- Feed aggregato pubblico per ogni categoria, con opzione per rimuovere duplicati.
- Cache dei feed aggiornata automaticamente ogni 15 minuti tramite cron job.
- Interfaccia utente responsive e pulita basata su Bootstrap 5.
- Protezione delle pagine con middleware per autenticazione e autorizzazione.
- Multi-utente con controllo accessi: amministratori gestiscono tutto, utenti gestiscono solo i propri feed e categorie.
- Possibilità per gli utenti di modificare il proprio account (email e password).

## Struttura del progetto
- `src/` - Codice PHP principale
  - `init.php` - Connessione DB, creazione tabelle, sessione
  - `auth.php` - Funzioni autenticazione e gestione ruoli
  - `middleware.php` - Protezione pagine
  - `login.php`, `logout.php` - Autenticazione
  - `categorie.php` - Elenco categorie (card Bootstrap)
  - `categoria_edit.php` - Creazione/modifica categoria
  - `categoria_delete.php` - Cancellazione categoria
  - `feed_gestione.php` - Gestione feed per categoria (tabella + modale)
  - `feed_delete.php` - Cancellazione feed
  - `feed.php` - Feed RSS aggregato pubblico
- `cron/aggiorna.php` - Script per aggiornare cache feed (da cron)
- `docker/cron.d/update_feeds` - Configurazione cron per Docker

## Database
- SQLite in `data/app.sqlite`
- Tabelle principali: `utenti`, `categorie`, `feed`, `feed_cache`

## Installazione e avvio
1. Clona il repository.
2. Assicurati che PHP e SQLite siano installati.
3. Avvia il server PHP di sviluppo:
   ```
   php -S localhost:8282 -t src
   ```
4. Accedi a `http://localhost:8282/login.php` con l'admin di default:
   - Email: `admin@example.com`
   - Password: `admin123`
5. Gestisci categorie e feed tramite l'interfaccia.

## Aggiornamento automatico feed
- Lo script `cron/aggiorna.php` aggiorna la cache dei feed ogni 15 minuti.
- Configurazione cron in `docker/cron.d/update_feeds` per esecuzione nel container Docker.

## Note
- L'interfaccia è completamente in italiano.
- Usa Bootstrap 5 per un design moderno e responsive.
- La rimozione duplicati è configurabile per ogni categoria.

## Contatti
Per domande o contributi, apri un issue o contatta lo sviluppatore.
