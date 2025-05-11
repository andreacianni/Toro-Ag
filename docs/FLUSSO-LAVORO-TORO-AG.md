**# 🚀 Flusso di lavoro Git + SSH + Staging – Progetto Toro-AG**

Questa guida descrive il flusso operativo ottimizzato per lavorare sul tema WordPress `toro-ag-template` tramite Git, staging remoto e senza uso di SFTP o LiveSync.

---

**## 📁 STRUTTURA**

* Lavoro locale: `VSCode` sulla cartella `toro-ag-template`
* Repository GitHub: `https://github.com/andreacianni/Toro-Ag`
* Staging remoto (SiteGround): `https://staging22.toro-ag.spaziodemo.xyz/`
* Produzione: TBD (in futuro, stesso meccanismo con branch `main`)

---

**## 🔁 FLUSSO STANDARD**

**### 🔹 1. Modifica file in locale**
Lavora nel tuo editor VSCode in locale.

**### 🔹 2. Crea branch (se nuova funzionalità o fix)**

```bash
git checkout -b feature/nome-branch
```

**### 🔹 3. Committa le modifiche**

```bash
git add .
git commit -m "Descrizione chiara della modifica"
```

**### 🔹 4. Push al repository remoto**

```bash
git push origin feature/nome-branch
```

**### 🔹 5. Accedi al server via SSH**

```bash
ssh -p 18765 -i ~/.ssh/andrea.code u996-hh9emyr0bbn6@ssh.spaziodemo.xyz
```

**### 🔹 6. Vai nella cartella del tema**

```bash
cd ~/www/staging22.toro-ag.spaziodemo.xyz/public_html/wp-content/themes/toro-ag-template
```

**### 🔹 7. Passa al branch e aggiorna i file**

```bash
git fetch origin
git checkout feature/nome-branch
# oppure, se già nel branch
git pull
```

---

**## 🔁 RIPRISTINO MODIFICHE (REVERT)**

**### Caso: una modifica non funziona**

1. In locale:

```bash
git revert ID_COMMIT
git push origin feature/nome-branch
```

2. Sul server:

```bash
git pull
```

---

**## 📤 AGGIUNTA FILE DIRETTAMENTE AL BRANCH `main`**

**### Quando vuoi aggiungere file (es. documentazione) direttamente al main:**

1. Passa al branch `main`:

```bash
git checkout main
```

2. Sposta il file nel progetto (es. da `DOC` a `docs/`):

```bash
mv DOC/FLUSSO-LAVORO-TORO-AG.md docs/
```

3. Committa e pusha:

```bash
git add docs/FLUSSO-LAVORO-TORO-AG.md
git commit -m "Aggiunge guida operativa del flusso Git + SSH + Staging"
git push origin main
```

---

**## 🖱️ OPERAZIONI GIT TRAMITE INTERFACCIA VS CODE (GUI)**

Puoi gestire tutte le operazioni Git direttamente dall'interfaccia grafica di VSCode:

### ✅ Per aggiungere un nuovo file e aggiornarlo su GitHub e sul server:

1. **Trascina o crea il file** nella cartella del progetto (`wpml-config.xml`, `README.md`, ecc.)
2. Vai nel pannello **Source Control** (icona ramo Git nella sidebar)
3. **Stage del file**:

   * Clicca sull’icona ➕ accanto al file
   * Il file verrà spostato da "Changes" a "Staged Changes"
4. **Scrivi un messaggio di commit**, ad esempio:

   ```
   Aggiunge file di configurazione WPML
   ```
5. **Commit & Push**:

   * Clicca la freccia accanto a "✓ Commit"
   * Seleziona **"Commit & Push"**
6. **Sul server (SSH)**, per aggiornare la cartella:

```bash
cd ~/www/staging22.toro-ag.spaziodemo.xyz/public_html/wp-content/themes/toro-ag-template
git pull
```

✅ Ora il file è presente sia su GitHub che sul server staging.

---

**## 🧠 TERMINALI**

| Azione                    | Terminale da usare           |
| ------------------------- | ---------------------------- |
| Modifica file             | VSCode / PowerShell (locale) |
| Git commit / push         | VSCode / PowerShell (locale) |
| Deploy (`pull`)           | Terminale SSH (remoto)       |
| Cambiare branch su server | Terminale SSH (remoto)       |

---

**## ✅ NOTE**

* Nessun uso di SFTP o LiveSync: tutto è versionato
* `main` è il branch stabile, `feature/...` per sviluppo/test
* Il tema deve trovarsi in `wp-content/themes/toro-ag-template`

---
