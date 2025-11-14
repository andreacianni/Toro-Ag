# Guida Amministratore - Toro-AG WordPress Theme

**Versione**: 1.2.5
**Tipo**: Child Theme Divi
**Sito**: www.toro-ag.it

---

## Indice

1. [Panoramica Template](#panoramica-template)
2. [Gestione Agenti](#gestione-agenti)
3. [Gestione Prodotti e Colture](#gestione-prodotti-e-colture)
4. [Gestione Documenti](#gestione-documenti)
5. [Shortcode Essenziali](#shortcode-essenziali)
6. [Importazione News](#importazione-news)
7. [Funzionalità Multilingua](#funzionalità-multilingua)
8. [FAQ e Risoluzione Problemi](#faq-e-risoluzione-problemi)

---

## Panoramica Template

### Cosa fa questo tema?

Toro-AG è un child theme WordPress specializzato per la gestione di:
- **Rete di agenti/rivenditori** con area riservata
- **Catalogo prodotti** con tipologie e colture associate
- **Documenti multilingua** (schede tecniche, brochure)
- **Import automatico news** da file Excel
- **Layout intelligenti** che si adattano al contenuto

### Requisiti Tecnici

Plugin richiesti:
- **Divi** (parent theme)
- **Pods** ≥ 3.3.1
- **WPML Multilingual CMS** ≥ 4.7.4
- **WPML String Translation** ≥ 3.3.3

---

## Gestione Agenti

### Creare un Nuovo Agente

1. **WordPress → Utenti → Aggiungi nuovo**
2. Compila i dati base (username, email, password)
3. Seleziona **Ruolo: Agente**
4. Compila i campi aggiuntivi (Pods):
   - **Indirizzo**: Via, Numero civico, CAP, Città, Provincia
   - **Contatti**: Telefono fisso, Cellulare
   - **Territori**: Seleziona le zone di competenza
5. Salva l'utente

### Attivare/Disattivare un Agente

1. **WordPress → Utenti**
2. Trova la colonna **"Attivo"**
3. Clicca sul toggle (✅/❌) per cambiare lo stato
4. Gli agenti disattivati NON possono:
   - Accedere all'area riservata
   - Scaricare documenti protetti

### Area Riservata Agenti

**URL**: `/area-agenti/`

L'area include automaticamente:
- **Login** per agenti non autenticati
- **Dati personali** (con form modifica)
- **Cambio password**
- **Elenco documenti** associati all'agente
- **Logout**

**Shortcode**: `[area_agenti_unificato]`

> **Nota**: Gli agenti che tentano di accedere al backend WordPress vengono automaticamente reindirizzati all'area riservata.

### Profilo Semplificato

Per nascondere i campi social media ai profili agente:

1. **Utenti → Modifica agente**
2. Sezione **"Preferenze Profilo"**
3. Spunta: ☑️ **"Modalità semplificata profilo"**
4. Salva

---

## Gestione Prodotti e Colture

### Struttura Catalogo

```
Prodotto
  ├── Tipo di Prodotto (tassonomia)
  ├── Colture (tassonomia, multiple)
  ├── Galleria immagini
  ├── Video
  └── Documenti associati
```

### Creare un Prodotto

1. **WordPress → Prodotti → Aggiungi nuovo**
2. Compila:
   - **Titolo** e **Descrizione**
   - **Immagine in primo piano**
3. Assegna le **Tassonomie**:
   - **Tipo di Prodotto** (es. "Diserbanti", "Fungicidi")
   - **Colture** (es. "Mais", "Vite", "Frumento")
4. Campi aggiuntivi (Pods):
   - **Galleria prodotto**: Seleziona immagini da media library
   - **Video**: Associa video (se presenti)
5. Pubblica

### Visualizzare Prodotti nelle Pagine

#### Layout Completo Prodotto
```
[toro_layout_prodotto]
```
Mostra il layout intelligente con:
- Immagine/Galleria
- Contenuto principale
- Sidebar con correlati
- Video e documenti

#### Griglia Prodotti Custom
```
[toro_prodotti_page title="I Nostri Prodotti" columns="3"]
```
Parametri:
- `title`: Titolo della sezione
- `columns`: Numero colonne (1-4)

#### Griglia Tutti i Tipi di Prodotto
```
[toro_tipi_prod]
```

#### Griglia Tutte le Colture
```
[toro_colture]
```

### Creare una Coltura/Tipo di Prodotto

1. **WordPress → Prodotti → Tipi di Prodotto** (o Colture)
2. Aggiungi nuovo termine
3. Compila Nome, Slug, Descrizione
4. **Immagine in primo piano** (Pods)
5. Salva

---

## Gestione Documenti

### Tipi di Documenti

| Tipo | Custom Post Type | Uso |
|------|------------------|-----|
| **Scheda Prodotto** | `scheda_prodotto` | Schede tecniche prodotti |
| **Brochure Coltura** | `brochure_coltura` | Brochure per colture |
| **Doc Plus** | `doc_plus` | Documenti avanzati multilingua |
| **Documento Agente** | `documento_agente` | Documenti riservati agenti |

### Creare un Documento Plus

1. **WordPress → Doc Plus → Aggiungi nuovo**
2. Compila:
   - **Titolo** e **Descrizione**
   - Upload **File PDF** (campo Pods)
3. Seleziona **Lingua aggiuntiva** (se diversa dall'italiano)
4. Pubblica
5. Associa ai prodotti/colture tramite relationship fields

### Visualizzare Documenti

#### Elenco Documenti su Pagina
```
[documenti_pagina]
```
Mostra documenti associati alla pagina corrente

#### Documenti per Prodotto
```
[product_docs]
```

#### Documenti per Coltura
```
[coltura_docs]
```

#### Elenco Completo Prodotti con Documenti
```
[elenco_prodotti_con_dettagli]
```
Mostra schema gerarchico:
- Prodotto
  - Schede tecniche
  - Documenti (raggruppati per lingua)

### Download Sicuro per Agenti

I documenti associati agli agenti usano un sistema di download protetto:

**URL**: `?secure_download=[ID_DOCUMENTO]`

**Requisiti**:
- Utente deve essere loggato
- Ruolo: Agente
- Stato: **Attivo** (✅)

Se l'agente è disattivato, viene reindirizzato con messaggio di errore.

---

## Shortcode Essenziali

### Area Agenti

| Shortcode | Funzione |
|-----------|----------|
| `[area_agenti_unificato]` | Area riservata completa (login + dati) |
| `[agente_card]` | Card con dati agente loggato |
| `[ricerca_agenti ordinamento="N-S"]` | Ricerca agenti con filtri regione/provincia |
| `[documenti_agente]` | Elenco documenti dell'agente |

**Esempio Ricerca Agenti**:
```
[ricerca_agenti ordinamento="A-Z"]
```
Parametri `ordinamento`:
- `A-Z`: Ordine alfabetico
- `N-S`: Nord-Sud (geografico, default)

### Prodotti

| Shortcode | Funzione |
|-----------|----------|
| `[toro_layout_prodotto]` | Layout intelligente prodotto singolo |
| `[toro_layout_tipo_prodotto]` | Layout tipo di prodotto |
| `[toro_layout_coltura]` | Layout coltura |
| `[scheda_prodotto_dettaglio]` | Dettagli scheda prodotto |
| `[toro_tipi_prod]` | Griglia tutti i tipi di prodotto |
| `[toro_colture]` | Griglia tutte le colture |

### Griglie Personalizzate

| Shortcode | Funzione |
|-----------|----------|
| `[toro_prodotti_page title="" columns="3"]` | Griglia prodotti da pagina |
| `[toro_colture_page title="" columns="3"]` | Griglia colture da pagina |
| `[toro_prodotti_tipo]` | Prodotti filtrati per tipo (in archivio termine) |
| `[toro_tipi_per_coltura]` | Tipi di prodotto per coltura |

### Video

| Shortcode | Funzione |
|-----------|----------|
| `[video_prodotto_v2]` | Video singolo prodotto (WPML-fixed) |
| `[video_tipo_prodotto_v2]` | Video tipo di prodotto |
| `[video_pagina]` | Video in pagina standard (griglia 2 colonne) |
| `[carosello_video_pagina]` | Carosello video Swiper |

### Documenti

| Shortcode | Funzione |
|-----------|----------|
| `[documenti_pagina]` | Documenti della pagina corrente |
| `[doc_plus]` | Documenti Plus avanzati |
| `[product_docs]` | Documenti prodotto |
| `[coltura_docs]` | Documenti coltura |
| `[elenco_prodotti_con_dettagli]` | Elenco prodotti con schede e documenti |

### Utility

| Shortcode | Funzione |
|-----------|----------|
| `[my_breadcrumbs]` | Breadcrumb navigation |
| `[hero_tipo_prodotto_e_coltura]` | Hero section prodotto/coltura |

---

## Importazione News

### Preparare il File Excel

1. Usa il template: `/import/DB_News_da importare.xlsx`
2. Colonne richieste:
   - **ID**: ID univoco news
   - **Titolo** (IT/EN/ES/FR)
   - **Contenuto** (IT/EN/ES/FR)
   - **Immagine URL**
   - **Data pubblicazione**

### Importare News

1. **WordPress → Tools → Importa News**
2. Seleziona opzioni:
   - ☑️ **Force Update**: Aggiorna post esistenti con stesso ID
   - ☑️ **Import Media**: Scarica immagini da URL
   - ☑️ **Connect Translations**: Collega traduzioni WPML
   - ☐ **Dry Run**: Simula import senza salvare (test)
3. Clicca **"Importa News"**
4. Attendi completamento

### Monitorare l'Import

L'importatore mostra:
- Numero post importati
- Post aggiornati
- Errori (se presenti)

### Reset Import (Solo Debug Mode)

Se `WP_DEBUG` è attivo, appare il bottone **"Reset Import"**:
- Elimina tutti i post con meta `news_id_originale`
- Usa con cautela!

**File di configurazione**: `/inc/news-import-functions.php`

---

## Funzionalità Multilingua

### Stringhe Auto-tradotte (WPML)

Le seguenti stringhe sono registrate automaticamente in WPML String Translation:

- "Chiedi informazioni sul prodotto"
- "Scarica la Brochure"
- Altre stringhe di sistema

**Dominio**: `Toro Layout Manager`

### Fallback Lingua

Se un campo non è tradotto in una lingua secondaria:
- Il sistema usa **fallback a Italiano** (lingua default)
- Applicato a: Prodotti, Colture, Documenti

### Collegare Traduzioni News

Durante l'import, attivando **"Connect Translations"**:
- Post IT/EN/ES/FR con stesso ID vengono collegati automaticamente
- WPML riconosce le traduzioni

### Documenti Multilingua

Per documenti in più lingue:

1. Crea il documento principale (IT)
2. Duplica con WPML
3. Traduci titolo e descrizione
4. Carica PDF nella lingua target
5. Opzionale: Usa campo **"Lingua aggiuntiva"** per tag

---

## FAQ e Risoluzione Problemi

### Un agente non riesce ad accedere all'area riservata

**Verifica**:
1. L'utente ha ruolo **"Agente"**?
2. Lo stato è **Attivo** (✅)?
3. Password corretta?

**Soluzione**:
- WordPress → Utenti → Trova l'agente
- Verifica ruolo e clicca toggle "Attivo"

### I documenti non si vedono nella griglia

**Causa**: Relationship fields non configurati

**Soluzione**:
1. WordPress → Pods Admin → Pods
2. Verifica relationship tra `prodotto` ↔ `scheda_prodotto`
3. Salva e svuota cache

### Layout prodotto mostra colonne vuote

**Non dovrebbe succedere!** Il Layout Manager elimina automaticamente colonne vuote.

**Debug**:
1. Verifica che lo shortcode sia: `[toro_layout_prodotto]`
2. Svuota cache (transient cache 1 ora)
3. Controlla console browser per errori JS

### Import news fallisce

**Verifica**:
1. File Excel è in `/import/DB_News_da importare.xlsx`?
2. Il formato delle colonne è corretto?
3. URL immagini sono accessibili?

**Debug**:
1. Attiva **Dry Run** per test
2. Controlla log PHP per errori
3. Verifica permessi file

### Come modificare l'ordine delle sezioni nel layout?

**File**: `/inc/views/layouts/layout-prodotto.php` (o varianti)

**Guida**: Leggi `/inc/views/layouts/README-MODIFICHE-TEMPLATE.md`

### Video non si caricano

**Verifica**:
1. Video è associato al prodotto tramite Pods?
2. Campo `video_url` è compilato?
3. Provider supportato (YouTube, Vimeo)?

**Shortcode corretto**: Usa `[video_prodotto_v2]` (versione WPML-fixed)

### Cache layout non si aggiorna

Il Layout Manager usa **transient cache** (1 ora).

**Forzare refresh**:
1. WordPress → Plugins → Pods → Clear Cache
2. Oppure: Salva di nuovo il prodotto/post

**Disabilitare cache** (temporaneo):
```php
// In /inc/classes/ToroLayoutManager.php cerca:
$cache_key = "toro_layout_{$layout_type}_{$post_id}_{$current_lang}";
// Commenta delete_transient e get_transient
```

---

## Risorse Avanzate

### Documentazione Sviluppatori

- **Layout Customization**: `/inc/views/layouts/README-MODIFICHE-TEMPLATE.md`
- **News Import Logic**: `/inc/news-import-functions.php` (commenti dettagliati)
- **Area Agenti Utils**: `/inc/helpers/area-agenti-utils.php`

### File di Configurazione Chiave

| File | Scopo |
|------|-------|
| `/functions.php` | Main orchestrator (carica tutti i moduli) |
| `/inc/classes/ToroLayoutManager.php` | Classe layout intelligente |
| `/inc/area-agenti-frontend.php` | Logica area riservata |
| `/inc/admin-agente-elenco.php` | Toggle attivo/disattivo admin |
| `/inc/shortcodes/*.php` | Tutti gli shortcode |

### Aggiornamenti Automatici

Il tema supporta **aggiornamenti da GitHub** tramite:
- Classe: `/inc/github-updater/ToroGitHubUpdater.php`
- Repository: `andreacianni/Toro-Ag`

**Non richiedere intervento manuale** per update minori.

---

## Checklist Onboarding Amministratore

Verifica di sapere come:

- [ ] Creare e attivare un nuovo agente
- [ ] Aggiungere un prodotto con tipo e colture
- [ ] Associare documenti a un prodotto
- [ ] Importare news da Excel
- [ ] Usare gli shortcode base (`[toro_layout_prodotto]`, `[ricerca_agenti]`)
- [ ] Verificare stato attivo/disattivo agenti
- [ ] Gestire documenti multilingua
- [ ] Collegare traduzioni WPML

---

## Supporto

**Repository GitHub**: https://github.com/andreacianni/Toro-Ag
**Documentazione WordPress**: https://wordpress.org/documentation/
**Documentazione Pods**: https://docs.pods.io/
**Documentazione WPML**: https://wpml.org/documentation/

---

**Ultimo aggiornamento**: 2025-01-14
**Autore guida**: Claude Code
**Tema versione**: 1.2.5
