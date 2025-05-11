
# 🗂️ Linee guida per l'uso dei branch – Progetto Toro-AG

Questo progetto utilizza Git con una struttura di branch semplice ed efficace per garantire stabilità, tracciabilità e ordine nello sviluppo.

---

## ✅ Branch principale: `main`

- Contiene **solo codice funzionante e testato**
- Ogni commit in `main` deve essere stabile e pronto per la produzione
- È il riferimento anche per ChatGPT durante analisi e suggerimenti

---

## 🛠️ Sviluppo: usare branch secondari

Per ogni nuova funzionalità, fix o refactoring:

### 🔹 1. Crea un nuovo branch

```bash
git checkout -b feature/nome-funzionalita
# oppure
git checkout -b fix/bug-descrizione
```

Esempi:
- `feature/shortcode-banner-info`
- `fix/download-documento-errore`
- `refactor/utils-indirizzo`

---

### 🔹 2. Lavora e testa nel branch

- Modifica i file necessari
- Testa le modifiche localmente
- Fai commit descrittivi:

```bash
git add .
git commit -m "Implementa nuovo shortcode banner informativo"
```

---

### 🔹 3. Unisci nel branch `main` solo quando funziona

```bash
git checkout main
git merge feature/nome-funzionalita
```

(Se desideri, elimina il branch secondario:)
```bash
git branch -d feature/nome-funzionalita
```

---

### 🔹 4. Pusha su GitHub

```bash
git push origin main
```

Oppure se lavori in team o vuoi revisionare:
- Pusha il branch secondario: `git push origin feature/xxx`
- Apri una Pull Request su GitHub

---

## 📌 Note finali

- Evita di lavorare direttamente su `main`
- Usa nomi chiari e descrittivi per i branch
- Comunica a ChatGPT quando hai pushato codice in un branch (es. `feature/...`)

---

## 🧠 Supporto ChatGPT

> Se vuoi assistenza sul codice in un branch, specifica sempre il nome del branch  
> Esempio: “Sto lavorando in `feature/refactor-utils`, mi aiuti a sistemare il file `area-agenti-utils.php`?”

