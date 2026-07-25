# Responsività griglie shortcode

**Stato:** In attesa di approvazione cliente per deploy
**Branch reale:** `feature/shortcode-grid-responsiveness`
**Commit coinvolti:**
- `23c64ef` — `fix: improve video pagina responsive grid`
- `772e193` — `fix: improve doc plus responsive grid`

## Obiettivo della pratica

Verificare le griglie shortcode in uso, correggere le criticità responsive confermate per `[video_pagina]` e `[doc_plus]`, e chiudere gli shortcode già responsive o fuori scope.

## Modifiche applicate

### `[video_pagina]`

- Rimosso il `flex: 0 0 50%` inline rigido.
- Introdotte classi componente per wrapper, griglia e item.
- Griglia basata sulla larghezza reale del contenitore tramite container query: fino a due colonne, una colonna nei contenitori stretti.
- Elemento singolo e ultima riga dispari centrati nel layout a due colonne; allineamento centralizzato e facilmente reversibile a sinistra nella callback.
- Bootstrap resta usato per struttura flex, wrap, padding, card e utility di allineamento; il CSS custom è limitato alla container query del componente.

### `[doc_plus]`

- I default Bootstrap responsive sono centralizzati in `inc/views/doc-plus-view.php` e dipendono dal numero di elementi.
- L'attributo `griglia` esplicito ha priorità assoluta e non viene alterato.
- Regole default senza `griglia` esplicita:
  - 1 elemento: `row row-cols-1`.
  - 3 elementi: `row row-cols-1 row-cols-md-2 row-cols-lg-3`.
  - 4 elementi: `row row-cols-1 row-cols-md-2 row-cols-xl-4`.
  - 2 elementi o oltre 4: fallback `row`, comportamento preesistente.

## File modificati

- `inc/shortcodes/video-card.php`
- `style.scss`
- `style.css`
- `style.css.map`
- `inc/views/doc-plus-view.php`

## Verifiche

- PHP lint: PASS.
- Compilazione SCSS: PASS.
- `git diff --check` e `git diff --cached --check`: PASS.
- Playwright: PASS per `[video_pagina]` con 4/2/1 elementi e per `[doc_plus]` nelle pagine 3415 IT e 3442 EN a desktop, 1024, 768 e 390 px.
- Nessun overflow orizzontale rilevato nei casi verificati.
- Screenshot locali di riferimento non sono versionati: `docs/references/Screenshot-responsive/video-pagina-fix/` e `docs/references/Screenshot-responsive/doc-plus-advantages/`.

## Decisioni ed esclusioni finali

- `[video_pagina]` e `[doc_plus]` sono approvati.
- `[toro_layout_prodotto]`, `[toro_layout_tipo_prodotto]` e `[toro_layout_coltura]` sono considerati già responsive e chiusi senza ulteriori analisi.
- `[area_agenti_unificato]` è fuori scope fino alla pubblicazione della pagina, poiché non è pubblicamente raggiungibile.
- I moduli Divi duplicati/nascosti sono residui dei vecchi layout e non sono stati modificati.
- Nessun deploy è stato eseguito.

## Deploy in produzione dopo approvazione

**Deploy solo dopo approvazione cliente.**

**File esatti da deployare:**
- `inc/shortcodes/video-card.php`
- `inc/views/doc-plus-view.php`
- `style.css`
- `style.css.map`

**Sorgente SCSS da non deployare salvo diversa procedura del progetto:** `style.scss`.
**Documentazione da non deployare:** tutti i file in `docs/active/` e `docs/done/`, incluso questo documento.

**Nessun deploy già eseguito.**
