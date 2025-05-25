### 1) Modifiche e test su staging

```powershell
# 1. Spostati sul branch di sviluppo
git checkout develop

# 2. Modifica i file in VS Code… (apri/edita file, salva)

# 3. Controlla cosa hai modificato
git status

# 4. Aggiungi le modifiche all’area di staging
git add .

# 5. Salva il commit con un messaggio descrittivo
git commit -m "flusso di lavoro"

# 6. Pubblica subito su staging (mappa develop → main)
git push staging develop:main --force
```

**Verifica su staging**

```bash
# Apri una sessione SSH su staging\sssh staging

# Vai nella cartella del tema\cd ~/www/staging22.toro-ag.spaziodemo.xyz/public_html/wp-content/themes/toro-ag-template

git status    # conferma che sia up-to-date
exit           # torna a PowerShell locale
```

Apri il browser su:

```
https://staging22.toro-ag.spaziodemo.xyz
```

---

### 2) Allineare GitHub (origin)

```powershell
# 1. Torna al ramo principale locale
git checkout main

# 2. Integra il lavoro di develop in main
git merge develop

# 3. Pubblica su GitHub
git push origin main
```

Controlla su:

```
https://github.com/andreacianni/Toro-Ag
```

---

### 3) Rientrare nella fase di test

```powershell
# 1. Torna su develop
git checkout develop

# 2. (Opzionale) Aggiorna da origin se lavori in team
git pull origin develop

# 3. Fai nuove modifiche e committale
git add .
git commit -m "Nuova modifica da testare"

# 4. Ridistribuisci su staging
git push staging develop:main --force
```

Poi ripeti la verifica su staging come al punto 1.

> Con questo workflow: lavori sempre su `develop`→ test su staging → merge in `main` → push su `origin` quando sei pronto per GitHub.
