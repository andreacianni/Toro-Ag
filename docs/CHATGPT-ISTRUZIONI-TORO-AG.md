
# 📘 ISTRUZIONI PER CHATGPT – Progetto AC Toro-AG

## 🔧 Contesto tecnico

- **Builder WordPress**: Divi ≥ 4.27.4  
- **Tema attivo**: Child Theme `Toro-AG`  
- **Plugin richiesti**:
  - Pods ≥ 3.3.1
  - WPML Multilingual CMS ≥ 4.7.4
  - WPML String Translation ≥ 3.3.3
  - WPML CMS Nav ≥ 1.5.5

> 💡 *Non indicare versioni esatte nei file, ma lavora assumendo che i plugin siano aggiornati o comunque >= a quelle sopra.*

---

## 🛠️ Obiettivo del progetto

Ricostruzione e migrazione in WordPress del sito [https://www.toro-ag.it](https://www.toro-ag.it)  
Il tema include:
- Gestione utenti personalizzata (ruolo **agente**) con PODS
- Area riservata frontend con accesso condizionato
- Sistema di download sicuro per i documenti (file Pods + restrizione su `agente_attivo`)
- Shortcode personalizzati (`ricerca-agenti`, `documenti`, `video-card`, ecc.)
- Layout responsive Bootstrap 5
- Traduzione con WPML

---

## 💻 Ambiente di sviluppo

- VS Code con LiveSync attivo
- Repository Git attivo e **pubblico**:
  ➡️ [https://github.com/andreacianni/Toro-Ag](https://github.com/andreacianni/Toro-Ag)

---

## 📦 File principali

I file chiave del progetto sono tutti versionati nel repo GitHub.  
I più rilevanti per l'interazione con ChatGPT sono:

- `functions.php`  
- `inc/helpers/secure-download.php`  
- `inc/shortcodes/*.php`  
- `inc/views/*.php`  
- `assets/js/ricerca-agenti.js`  
- `DOCUMENTAZIONE-STRUTTURA.txt` (struttura file + logica PODS)

---

## 🔍 Come interagire in ChatGPT

> Quando chiedo modifiche, analisi o aiuto in questo progetto, **fai sempre riferimento al repository GitHub Toro-AG**.  
> Puoi assumere che il codice attuale sia aggiornato all’ultima commit nel branch `main`.

✔️ Posso chiederti:
- Refactoring di shortcode o helper PHP
- Aggiunte a funzioni esistenti
- Debug di logiche WPML o PODS
- Suggerimenti per WP best practices
- Generazione di codice frontend (Bootstrap, SCSS, JS)


---

## 🌿 Come comunicare il branch attivo a ChatGPT

Se stai lavorando in un branch diverso da `main`, ti basta scrivere una frase come una di queste:

- “Sto lavorando sul branch `feature/nuovo-shortcode`”
- “Controlla `secure-download.php` nel branch `fix/secure-access`”
- “Analizza questa modifica in `refactor/card-agente`”
- “Il codice che sto usando è nel branch `dev-download-multiplo`”

In questo modo ChatGPT può:
- Considerare il contesto aggiornato
- Analizzare o suggerire codice **coerente con il tuo ambiente**
- Evitare conflitti con la versione stabile in `main`

> Puoi indicarlo all’inizio, oppure durante una richiesta specifica.

