# Responsività griglie shortcode

## Scopo

Questa roadmap considera gli shortcode trovati in uso corrente nel database e quelli con uso noto documentato, individua quelli che generano griglie e ne classifica il rischio responsive. Ogni shortcode sarà analizzato singolarmente; eventuali fix saranno definiti e applicati solo dopo la discovery dedicata.

## Shortcode in uso con griglia

| Shortcode | Evidenza d’uso | Responsività attuale | Livello di rischio | Motivo sintetico | Stato |
| --------- | -------------- | -------------------- | ------------------ | ---------------- | ----- |
| `[toro_layout_prodotto]` | 2 contenuti / 2 occorrenze | completa | basso | Orchestratore `.toro-grid` già responsive. | chiuso come già responsive |
| `[toro_layout_tipo_prodotto]` | 1 / 1 | completa | basso | Orchestratore `.toro-grid` già responsive. | chiuso come già responsive |
| `[toro_layout_coltura]` | 1 / 1 | completa | basso | Orchestratore `.toro-grid` già responsive. | chiuso come già responsive |
| `[elenco_prodotti_con_dettagli]` | 2 / 2 | parziale | medio | Uso corrente `layout="card"`; shortcode con layout alternativi a colonne. | da analizzare |
| `[area_agenti_unificato]` | 1 / 1 | non verificata pubblicamente | fuori scope | Pagina non pubblicamente raggiungibile. | sospeso fino a pubblicazione |
| `[toro_tipi_prod]` | 2 / 2 | completa | basso | CSS Grid `.toro-grid` con media query. | da analizzare |
| `[toro_colture]` | 2 / 2 | completa | basso | CSS Grid `.toro-grid` con media query. | da analizzare |
| `[toro_prodotti_tipo]` | 1 / 1 | completa | basso | CSS Grid `.toro-grid` con media query. | da analizzare |
| `[toro_culture_prodotto]` | 3 / 3 | completa | basso | CSS Grid `.toro-grid` con media query. | da analizzare |
| `[toro_prodotti_page]` | 2 / 2 | completa | basso | CSS Grid con colonne dinamiche e media query. | da analizzare |
| `[toro_colture_page]` | 2 / 2 | completa | basso | CSS Grid con colonne dinamiche e media query. | da analizzare |
| `[video_pagina]` | 4 / 4 | verificata e implementata | chiuso | Griglia fino a 2 colonne, basata sulla larghezza reale del contenitore. | step chiuso |
| `[video_tipo_prodotto_standalone]` | 2 / 2 (uso noto) | completa | basso | Griglia Bootstrap: 1 colonna, da `md` fino a 2 e da `lg` fino a 3; `columns` limita a 1–3. | verificato dal codice |
| `[doc_plus]` | 12 / 14 | verificata e implementata | chiuso | Default Bootstrap per numero elementi; `griglia` esplicita invariata. | step chiuso |

## Esito finale della pratica

- `[video_pagina]`: chiuso, implementato e approvato.
- `[doc_plus]`: chiuso, implementato e approvato.
- `[toro_layout_prodotto]`, `[toro_layout_tipo_prodotto]`, `[toro_layout_coltura]`: chiusi come già responsive.
- `[area_agenti_unificato]`: sospeso e fuori scope finché la relativa pagina non sarà pubblicamente raggiungibile.
- Gli altri shortcode non sono stati selezionati per questa pratica: nessun prossimo shortcode è pianificato.
- **Pratica complessiva chiusa.**

## Stato step `[video_pagina]`

- **Analisi completata, requisiti approvati, fix implementato e verifica visiva approvata.**
- La griglia usa al massimo 2 colonne e si adatta alla larghezza reale del contenitore; passa a una colonna quando il contenitore è stretto.
- Il singolo elemento e l'ultima card dispari sono centrati senza espandere la card nel layout a due colonne.
- La scelta `centrato` / `sinistra` è centralizzata nella callback ed è facilmente reversibile.
- I moduli Divi duplicati nelle pagine interne sono residui nascosti e sono esclusi dallo scope del fix.
- **Step chiuso.** Nessun prossimo shortcode è previsto nella pratica conclusa.

## Stato step `[doc_plus]`

- **Pagine analizzate:** 3415 IT — *I Vantaggi della Goccia* e 3442 EN — *The Drip’s Advantages*.
- **Problemi iniziali:** i layout senza `griglia` esplicita mantenevano 3 (`clean`) o 4 (`modern`/`card-imgsup`) colonne fino a tablet e mobile; card e testi risultavano compressi, con una riga finale anomala in `card-imgsup` EN.
- **Decisione approvata:** default responsive Bootstrap centralizzato in `inc/views/doc-plus-view.php`, determinato dal numero di elementi. L'attributo `griglia` ha priorità assoluta e non viene modificato.
  - 1 elemento: `row row-cols-1`.
  - 3 elementi: `row row-cols-1 row-cols-md-2 row-cols-lg-3`.
  - 4 elementi: `row row-cols-1 row-cols-md-2 row-cols-xl-4`.
  - 2 elementi o oltre 4: fallback `row`, comportamento preesistente.
- **Test e verifica visiva:** PASS a 1440, 1024, 768 e 390 px; nessun overflow orizzontale rilevato. Desktop invariato; tablet e mobile leggibili con 2/1 colonne secondo le regole.
- **Screenshot:** `docs/references/Screenshot-responsive/doc-plus-advantages/` (serie iniziale, `-test` e `-test2`; non versionati).
- **Deploy:** nessuno eseguito. **Step chiuso.**
- **Punti aperti:** 2 elementi e oltre 4 conservano intenzionalmente il comportamento precedente; richiederanno una decisione dedicata solo se emergeranno casi reali problematici. La logica non deduce la larghezza del contenitore Divi.

## Metodo per le analisi successive

Per ogni shortcode verrà fatta una discovery dedicata che documenti:

- file e funzione coinvolti;
- markup generato;
- CSS/SCSS applicato;
- framework o librerie coinvolte;
- breakpoint attuali;
- comportamento atteso per numero di elementi e dimensione viewport;
- problemi rilevati;
- proposta di fix;
- file da modificare;
- test necessari;
- stato approvazione e implementazione.

## Riferimenti per verifica visiva frontend

Sono incluse solo occorrenze correnti in `post_content`; revisioni, storico e meta derivati sono esclusi. Gli URL usano `WP_HOME` locale (`http://localhost/toro-ag.itbk`) e hanno restituito HTTP 200 alla verifica.

### `[video_pagina]`

| Shortcode | ID contenuto | Titolo | Tipo contenuto | Stato | Lingua | Parametri shortcode | Numero elementi stimato | URL frontend locale | Note |
| --------- | -----------: | ------ | -------------- | ----- | ------ | ------------------- | ----------------------: | ------------------- | ---- |
| `[video_pagina]` | 59 | Homepage | page | publish | it | `titolo="Testimonianze"` | 4 | http://localhost/toro-ag.itbk/ | 8 video associati; 4 italiani filtrati. |
| `[video_pagina]` | 1050 | Homepage | page | publish | en | `titolo="Testimonials"` | 4 | http://localhost/toro-ag.itbk/en/ | 8 video associati; 4 non italiani filtrati. |
| `[video_pagina]` | 3466 | Sub-Irrigazione | page | publish | it | `titolo="Testimonianze"` | 2 | http://localhost/toro-ag.itbk/approfondimenti/sub-irrigazione/ | Occorrenza 1 di 2 identiche nel contenuto; 2 video italiani filtrati. |
| `[video_pagina]` | 3466 | Sub-Irrigazione | page | publish | it | `titolo="Testimonianze"` | 2 | http://localhost/toro-ag.itbk/approfondimenti/sub-irrigazione/ | Occorrenza 2 di 2 identiche nel contenuto; 2 video italiani filtrati. |
| `[video_pagina]` | 5225 | Sub-Surface Drip Irrigation | page | publish | en | `titolo="Testimonials"` | 1 | http://localhost/toro-ag.itbk/en/resources/sub-surface-drip-irrigation/ | Occorrenza 1 di 2 identiche nel contenuto; 1 video non italiano filtrato. |
| `[video_pagina]` | 5225 | Sub-Surface Drip Irrigation | page | publish | en | `titolo="Testimonials"` | 1 | http://localhost/toro-ag.itbk/en/resources/sub-surface-drip-irrigation/ | Occorrenza 2 di 2 identiche nel contenuto; 1 video non italiano filtrato. |

### `[doc_plus]`

Il numero è una stima delle schede `doc_plus` candidate: il rendering può escludere una scheda senza allegati nella lingua corrente.

| Shortcode | ID contenuto | Titolo | Tipo contenuto | Stato | Lingua | Parametri shortcode | Numero elementi stimato | URL frontend locale | Note |
| --------- | -----------: | ------ | -------------- | ----- | ------ | ------------------- | ----------------------: | ------------------- | ---- |
| `[doc_plus]` | 59 | Homepage | page | publish | it | `ids="3062,3183,3187" layout="card-imgsx" title="Scarica i nostri cataloghi" griglia="row row-cols-1"` | 3 | http://localhost/toro-ag.itbk/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 1050 | Homepage | page | publish | en | `ids="3062,3183,3187" layout="card-imgsx" title="Download our catalogs" griglia="row row-cols-1"` | 3 | http://localhost/toro-ag.itbk/en/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3109 | Dichiarazione sulla schiavitù moderna | page | publish | it | `layout="modern" title=""` | 2 | http://localhost/toro-ag.itbk/dichiarazione-sulla-schiavitu-moderna/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3134 | Statement on modern slavery | page | publish | en | `layout="modern" title=""` | 2 | http://localhost/toro-ag.itbk/en/statement-on-modern-slavery/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3415 | I Vantaggi della Goccia | page | publish | it | `ids="2966,2981,2996" layout="clean"` | 3 | http://localhost/toro-ag.itbk/approfondimenti/i-vantaggi-della-goccia/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3415 | I Vantaggi della Goccia | page | publish | it | `ids="2759,2836,2951,6994" layout="modern"` | 4 | http://localhost/toro-ag.itbk/approfondimenti/i-vantaggi-della-goccia/ | Seconda occorrenza nel contenuto. |
| `[doc_plus]` | 3442 | The Drip’s Advantages | page | publish | en | `ids="2966,2981,2996" layout="clean"` | 3 | http://localhost/toro-ag.itbk/en/resources/the-drips-advantages/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3442 | The Drip’s Advantages | page | publish | en | `ids="2759,2836,2951,6994" layout="card-imgsup"` | 4 | http://localhost/toro-ag.itbk/en/resources/the-drips-advantages/ | Seconda occorrenza nel contenuto. |
| `[doc_plus]` | 3466 | Sub-Irrigazione | page | publish | it | `ids="3187" layout="card-imgsx" title="" griglia="row row-cols-1 g-3"` | 1 | http://localhost/toro-ag.itbk/approfondimenti/sub-irrigazione/ | Occorrenza 1 di 2 identiche. |
| `[doc_plus]` | 3466 | Sub-Irrigazione | page | publish | it | `ids="3187" layout="card-imgsx" title="" griglia="row row-cols-1 g-3"` | 1 | http://localhost/toro-ag.itbk/approfondimenti/sub-irrigazione/ | Occorrenza 2 di 2 identiche. |
| `[doc_plus]` | 3564 | Nuovo imballo Aqua-Traxx PBX | page | publish | it | `layout="card-imgsx"` | 1 | http://localhost/toro-ag.itbk/approfondimenti/nuovo-imballo-aqua-traxx-pbx/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 3566 | Avvio e manutenzione dell’impianto | page | publish | it | `layout="card-imgsx" title=""` | 1 | http://localhost/toro-ag.itbk/approfondimenti/avvio-e-manutenzione-dellimpianto/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 5225 | Sub-Surface Drip Irrigation | page | publish | en | `ids="3187" layout="card-imgsx" title="" griglia="row row-cols-1 g-3"` | 1 | http://localhost/toro-ag.itbk/en/resources/sub-surface-drip-irrigation/ | Occorrenza 1 di 2 identiche. |
| `[doc_plus]` | 5225 | Sub-Surface Drip Irrigation | page | publish | en | `ids="3187" layout="card-imgsx" title="" griglia="row row-cols-1 g-3"` | 1 | http://localhost/toro-ag.itbk/en/resources/sub-surface-drip-irrigation/ | Occorrenza 2 di 2 identiche. |
| `[doc_plus]` | 5505 | New packaging for Aqua-Traxx PBX | page | publish | en | `layout="card-imgsx"` | 1 | http://localhost/toro-ag.itbk/en/resources/new-packaging-for-aqua-traxx-pbx/ | Pubblico, HTTP 200. |
| `[doc_plus]` | 5609 | System Start Up & Maintenance | page | publish | en | `layout="card-imgsx" title=""` | 1 | http://localhost/toro-ag.itbk/en/resources/system-start-up-maintenance/ | Pubblico, HTTP 200. |

Nessun contenuto elencato è non pubblicato, protetto, privato o richiede login secondo i dati disponibili.

## Verifica visiva homepage

**URL verificato:** `http://localhost/toro-ag.itbk/` (homepage raggiungibile).
**Viewport testate:** 1440 × 900, 1024 × 768, 768 × 1024, 390 × 844.

### Sezioni/griglie osservate

- Le quattro card di navigazione **Prodotti / Applicazioni / Approfondimenti / Documentazione** (row Divi `.et_pb_row_4col`, non attribuita qui a uno shortcode).
- **Scarica i nostri cataloghi**: con ragionevole certezza `[doc_plus]`, coerente con la configurazione homepage `layout="card-imgsx"`, `griglia="row row-cols-1"` e tre elementi.
- **Testimonianze**: con ragionevole certezza `[video_pagina titolo="Testimonianze"]`, con quattro card video.

### Osservazioni visive

- **1440 × 900:** navigazione a 4 colonne (~262 px/card, ~35 px tra card); cataloghi in una colonna laterale e video in matrice 2 × 2 (~355 px iframe). Spaziature e testi appaiono leggibili.
- **1024 × 768:** la navigazione resta a 4 colonne (~186 px/card). Le intestazioni `APPROFONDIMENTI` e `DOCUMENTAZIONE` appaiono tagliate sul lato destro; i cataloghi laterali diventano stretti e i titoli lunghi sono molto spezzati. I video restano 2 × 2 (~248 px iframe), con titoli su più righe ma leggibili.
- **768 × 1024:** navigazione a 2 × 2 (~290 px/card); cataloghi a una colonna larga; video a 2 × 2 (~289 px iframe). Non sono visibili overflow o testi compressi rilevanti; nella cattura due anteprime video inferiori risultano bianche/non renderizzate.
- **390 × 844:** navigazione e cataloghi a una colonna (~312 px). I video restano a 2 colonne (~138 px iframe/card): titoli fortemente compressi su molte righe, anteprime bianche/non renderizzate nella cattura e composizione visivamente irregolare nelle ultime due card (righe incomplete).

### Verifiche DOM (distinte dalle osservazioni visive)

- Nessuna delle viewport ha overflow orizzontale di pagina (`documentElement.scrollWidth === clientWidth`).
- A **1024 px**, le card `Approfondimenti` e `Documentazione` hanno overflow interno misurato (rispettivamente 196/184 px e 192/184 px `scrollWidth/clientWidth`), confermando il taglio visibile dei titoli.
- La griglia principale passa da 4 colonne (1440 e 1024), a 2 (768), a 1 (390). `[doc_plus]` è a una colonna nella sua area a tutte le dimensioni osservate.
- `[video_pagina]` è 2 × 2 fino a 768; a 390 conserva due colonne, ma le terza e quarta card hanno entrambe coordinata sinistra (la quarta su una nuova riga): DOM che conferma le righe incomplete osservate. Gli iframe sono presenti e dimensionati (138–141 × 78–79 px a 390); il DOM non determina la causa delle anteprime bianche.
- Console: una risorsa Font Awesome restituisce HTTP 403; non è stata collegata causalmente ai problemi di griglia.

### Dubbi residui

- Le anteprime video mancanti nelle catture strette potrebbero dipendere dal caricamento/risposta dell'embed YouTube, non dalla sola geometria della griglia; la verifica non stabilisce la causa.
- Le card di navigazione sono markup Divi e non sono state ricondotte a uno shortcode specifico.

### Screenshot salvati

Directory: `docs/references/Screenshot-responsive/home/`

- `home-1440x900-full.png`
- `home-1024x768-full.png`
- `home-768x1024-full.png`
- `home-390x844-full.png`

La barra di navigazione sticky visibile sopra alcuni contenuti nelle catture full-page è un artefatto della cattura full-page e non rientra nell'analisi delle griglie.

## Analisi attuale `[video_pagina]`

### Implementazione attuale

- **File e funzione:** `inc/shortcodes/video-card.php`, `ac_video_pagina_shortcode($atts = [])`, registrata da `add_shortcode('video_pagina', 'ac_video_pagina_shortcode')`; il file è incluso da `functions.php`.
- **Dati:** disponibile solo per `is_page()`, legge il campo Pods `video_pagina` della pagina, usa fallback WPML e filtra gli ID con `toroag_filtra_per_lingua_aggiuntiva()`.
- **Markup prodotto:** titolo opzionale `h3`, quindi `#video-pagina-wrapper > #video-pagina-grid.d-flex.flex-wrap.justify-content-start`; per ogni video valido, `div.p-2[style="flex: 0 0 50%;"] > div.card.h-100 > div.embed-responsive.embed-responsive-16by9.w-100` (oEmbed) e `.card-body > h5.card-title.text-center.py-2.mb-0 > a`.
- **CSS/SCSS:** non risultano selettori dedicati a `#video-pagina-wrapper` o `#video-pagina-grid` nei CSS/SCSS del tema. `functions.php` carica Bootstrap 5.3.3: le classi Bootstrap applicano flex, wrap, padding `p-2` (8 px), card e tipografia. `embed-responsive*` non ha una regola dedicata nel tema individuata dalla ricerca.
- **Stile inline:** ogni item impone `flex: 0 0 50%`; non sono presenti breakpoint nello shortcode. La capacità resta quindi due item per riga, in relazione alla larghezza effettiva del contenitore e non a una media query dello shortcode.
- **Contenitori reali:** in homepage il wrapper è nel modulo Divi `#video.et_pb_code_1`, nella colonna `et_pb_column_2_3`; nelle due pagine di confronto è in un modulo `et_pb_code_5` entro una colonna Divi `4_4`. A 768/390 Divi porta entrambe le pagine a una colonna di 614/312 px; in homepage la colonna video misura rispettivamente 614/312 px. A desktop la differenza del layout padre è rilevante: homepage 747/531 px (colonna 2/3), confronto 1152/819 px (colonna 4/4).

### Rilievi DOM Playwright

`Larghezza card` indica il wrapper diretto `.p-2` (la `.card` interna è 16 px più stretta per il padding). `Colonne` indica card effettivamente sulla riga / capacità derivata dal 50%.

| Pagina | Viewport | Elementi | Larghezza contenitore | Colonne | Larghezza card | Ultima riga | Osservazioni |
| ------ | -------: | -------: | --------------------: | ------: | -------------: | ----------- | ------------ |
| Homepage | 1440 | 4 | 747 px | 2 / 2 | 373 px | completa (2+2) | Colonna Divi 2/3; `.card` 357 px. |
| Homepage | 1024 | 4 | 531 px | 2 / 2 | 266 px | completa (2+2) | Colonna Divi 2/3; `.card` 250 px. |
| Homepage | 768 | 4 | 614 px | 2 / 2 | 307 px | completa (2+2) | Divi impila le colonne; `.card` 291 px. |
| Homepage | 390 | 4 | 312 px | 2 / 2, poi 1 | 156 px | incompleta: quarta card sola | Terza e quarta card hanno entrambe coordinata sinistra; `.card` 140–143 px e titoli molto alti. |
| Sub-Irrigazione | 1440 | 2 | 1152 px | 2 / 2 | 576 px | completa (2) | Colonna Divi 4/4; `.card` 560 px. |
| Sub-Irrigazione | 1024 | 2 | 819 px | 2 / 2 | 410 px | completa (2) | Colonna Divi 4/4; `.card` ~394 px. |
| Sub-Irrigazione | 768 | 2 | 614 px | 2 / 2 | 307 px | completa (2) | Colonna Divi 4/4; `.card` 291 px. |
| Sub-Irrigazione | 390 | 2 | 312 px | 1 / 2 | 156 px | incompleta: seconda card sola | Entrambe le card sono nel flusso sinistro; `.card` 140 px e titoli 160/176 px. |
| Sub-Surface Drip Irrigation | 1440 | 1 | 1152 px | 1 / 2 | 576 px | incompleta: unico item a sinistra | Colonna Divi 4/4; metà destra vuota. |
| Sub-Surface Drip Irrigation | 1024 | 1 | 819 px | 1 / 2 | 410 px | incompleta: unico item a sinistra | Colonna Divi 4/4; metà destra vuota. |
| Sub-Surface Drip Irrigation | 768 | 1 | 614 px | 1 / 2 | 307 px | incompleta: unico item a sinistra | Colonna Divi 4/4; `.card` 291 px. |
| Sub-Surface Drip Irrigation | 390 | 1 | 312 px | 1 / 2 | 156 px | incompleta: unico item a sinistra | `.card` 140 px; titolo 112 px di altezza. |

### Stato rilevato, senza proposta tecnica

- La regola effettiva è invariata a tutte le viewport: ogni item dichiara inline `flex: 0 0 50%`; il numero di colonne potenziale è due, non una regola responsive dello shortcode.
- Il layout padre Divi determina la larghezza disponibile: a pari viewport 1440/1024 la homepage ha 747/531 px contro 1152/819 px nelle pagine a colonna 4/4. A 768/390 le larghezze osservate convergono a 614/312 px.
- L'ultima riga non viene riequilibrata: il singolo elemento rimane a sinistra. A 390 il comportamento osservato con 4 elementi homepage e 2 elementi Sub-Irrigazione genera inoltre un item successivo in una riga sinistra separata, pur avendo tutti gli item lo stesso `flex-basis` calcolato.
- Il DOM non mostra overflow orizzontale di pagina nelle viewport verificate. Rimangono da chiarire l'origine esatta del wrapping anomalo a 390 (interazione tra contenuto oEmbed, altezza/titoli e layout flex) e l'assenza di regole locali per le classi `embed-responsive*`.

### Chiarimenti tecnici residui

#### Wrapping a 390 px

- **DOM reale:** `#video-pagina-grid.d-flex.flex-wrap.justify-content-start` contiene i quattro `div.p-2`; ciascuno contiene `.card.h-100`, quindi `.embed-responsive.embed-responsive-16by9.w-100`, al cui interno Divi/FitVids inserisce a runtime `div.fluid-width-video-wrapper[style="padding-top: 56.2963%"] > iframe[name^="fitvid"]`.
- **Contenitore flex:** a 390 px misura 312 px, con `display:flex`, `flex-wrap:wrap`, `justify-content:flex-start`, `gap:normal` (nessun gap esplicito), padding e margin a 0. La riga Divi padre misura 312 px, ha `margin: 0 39px`, `padding: 30px 0` e `max-width:1325px`; colonna e `.et_pb_code_inner` misurano anch'essi 312 px, senza padding aggiuntivo.
- **Item/card:** ogni `.p-2` ha `flex: 0 0 50%`, `flex-basis:50%`, `flex-grow:0`, `flex-shrink:0`, `width:156px`, `min-width:auto`, `max-width:none`, margin 0 e padding 8 px. La `.card` interna è larga 140 px, ha `min-width:0`; il wrapper FitVids/iframe è largo 138 px. Non è presente una regola locale che imposti gap, margin o max-width diversi tra gli item.
- **Causa rilevata:** il contenitore usa il wrapping standard flex e l'allineamento iniziale; quindi ogni riga nuova inizia a sinistra. A 390 px l'ultimo item homepage ha una larghezza effettiva di 158,984 px, pur con `flex-basis:50%`: insieme all'item precedente da 156 px supera i 312 px disponibili, viene quindi posto sulla riga successiva e resta a sinistra. Lo stesso effetto si osserva con il secondo item in Sub-Irrigazione. Il fattore che alza la dimensione ipotetica dell'item è il minimo automatico `min-width:auto` del flex item, calcolato dal suo contenuto; non è una regola di wrapping inattesa né proviene dal contenitore Divi.
- **Conseguenza:** il risultato è coerente con il CSS corrente: `flex-wrap:wrap` e `justify-content:flex-start` non riequilibrano l'ultima riga. La differenza di larghezza del layout padre determina quanto facilmente l'arrotondamento/minimo contenuto fa superare lo spazio disponibile.

#### `embed-responsive*`: provenienza e ruolo

- La ricerca in `style.scss`, `style.css` e nel resto del CSS locale non trova selettori `.embed-responsive` o `.embed-responsive-16by9`. `functions.php` carica Bootstrap **5.3.3**; Bootstrap 5 non fornisce queste classi (usa `.ratio`), quindi i nomi sono residui di convenzione Bootstrap 4 e nel rendering corrente non hanno regole Bootstrap attive.
- Nel DOM non risultano CSS rule applicate ai selettori `embed-responsive*`; il relativo `div` resta un blocco `w-100` largo quanto la card interna. La classe `w-100` proviene da Bootstrap 5 e imposta la larghezza al 100%.
- Il rapporto video effettivo viene invece gestito da **Divi FitVids**: è caricato `wp-content/themes/Divi/includes/builder/feature/dynamic-assets/assets/js/jquery.fitvids.js?ver=4.27.7`. Il runtime aggiunge `.fluid-width-video-wrapper` con padding-top inline 56,2963%; una regola inline di Divi assegna a `.fluid-width-video-wrapper iframe` `position:absolute`, `top/left:0`, `width:100%`, `height:100%`. L'iframe ha inoltre `max-width:100%` dal CSS Divi.
- FitVids determina il rapporto e le dimensioni dell'area video interna, non il wrapping della `.p-2`: il wrapping è determinato dal flex inline dello shortcode e dal minimo automatico dell'item. Le classi `embed-responsive*` sono quindi compatibili/residuali nel markup, non il meccanismo attivo del rapporto.

## Requisiti funzionali approvati per `[video_pagina]`

### Comportamento richiesto

- La griglia deve adattare il numero di colonne alla **larghezza reale del contenitore disponibile**, comprese le colonne Divi di larghezza diversa; la sola viewport non è un criterio sufficiente.
- Deve mostrare al massimo due colonne e non deve forzare due colonne quando le card diventano troppo strette: in un contenitore stretto passa a una sola colonna.
- Il numero di colonne effettive non deve superare il numero di elementi disponibili.
- Con un solo elemento, la card mantiene la larghezza prevista per una card della griglia e viene centrata; non si espande a tutta la riga.
- Con un numero dispari di elementi, l'ultima card conserva la stessa larghezza delle altre card della griglia ed è centrata nella riga finale; non si espande per occupare lo spazio residuo.

### Scelta iniziale approvata

- L'allineamento del singolo elemento e dell'ultima riga dispari è **centrato**.
- Questo comportamento deve essere uguale per le occorrenze da 1, 2, 4 o altro numero di elementi e per i contenitori Divi osservati.

### Scelta reversibile su richiesta cliente

- L'allineamento del singolo elemento e dell'ultima riga potrà essere cambiato in futuro a **allineato a sinistra**.
- La futura implementazione dovrà rendere questa scelta modificabile intervenendo nel minor numero possibile di punti, centralizzandola in una sola regola, variabile, classe o configurazione coerente.

### Preferenza per Bootstrap

- Si preferiscono classi, componenti e utility Bootstrap già caricati nel progetto, quando coprono correttamente il comportamento richiesto.
- Non va introdotto CSS custom per aspetti che Bootstrap gestisce già in modo adeguato.
- Bootstrap non deve essere imposto qualora non consenta di reagire alla larghezza reale del contenitore o non soddisfi le regole di allineamento e conservazione della larghezza delle card.

### Vincoli di manutenibilità

- Usare CSS/SCSS custom solo per gli aspetti non gestibili correttamente con Bootstrap.
- Evitare logica duplicata tra PHP, stili inline, SCSS e breakpoint.
- La futura soluzione deve mantenere in un solo punto la scelta di allineamento e una fonte coerente per le regole di layout.

### Aspetti tecnici ancora da progettare

- Il meccanismo con cui rilevare o rispettare la larghezza reale del contenitore, anziché applicare solo breakpoint di viewport.
- La composizione Bootstrap e, solo se necessaria, la minima regola custom in grado di gestire il passaggio a una colonna e l'allineamento centrato senza espandere l'ultima card.
- La collocazione della configurazione centralizzata dell'allineamento, da definire senza duplicarne la logica nel markup, negli inline style o negli stylesheet.

## Progettazione tecnica `[video_pagina]`

### Contesti di rendering verificati

- Lo shortcode è registrato in `inc/shortcodes/video-card.php` con callback `ac_video_pagina_shortcode()` e incluso da `functions.php`. La ricerca nel repository non trova orchestratori, view o template PHP che richiamino direttamente o indirettamente `[video_pagina]`.
- `ToroLayoutManager::load_section_content()` non richiama `[video_pagina]`: per prodotti usa `[video_prodotto_v2]` e per tipi prodotto `[video_tipo_prodotto_v2]`. I layout in `inc/views/layouts/` rendono quindi altri shortcode video nelle sezioni main/sidebar e non devono essere coinvolti dal fix.
- Gli usi correnti da verifiche precedenti sono in contenuto Divi: Homepage (4 elementi), Sub-Irrigazione (2), Sub-Surface Drip Irrigation (1), oltre alle rispettive varianti linguistiche documentate.
- DOM reale: Homepage nel modulo Divi Code `#video.et_pb_code_1`, colonna `et_pb_column_2_3`, accanto alla colonna cataloghi `1_3`; larghezze wrapper misurate 747/531/614/312 px a viewport 1440/1024/768/390. Le due pagine interne sono in `et_pb_code_5`, colonna `4_4`; 1152/819/614/312 px. Nelle pagine interne il markup Divi contiene anche una copia nascosta del modulo Code, alternata dalla visibilità responsive del builder: il test deve sempre considerare solo l'istanza visibile.

### Vincoli rilevati nel layout manager

- Nessun wrapper del layout manager circonda gli usi correnti di `[video_pagina]`; non sono previste modifiche a `ToroLayoutManager.php`, ai partial o ai layout `prodotto`, `tipo_prodotto` e `coltura`.
- Il layout manager contiene sidebar Bootstrap `col-lg-3` e colonne main `col-lg-9` nei propri layout. Poiché `[video_pagina]` potrebbe in futuro essere collocato in tali contenitori stretti, il layout del shortcode deve restare autosufficiente rispetto alla larghezza del proprio wrapper e non assumere una colonna Divi 4/4.

### Vincoli rilevati nei template Divi

- I template/pagine Divi attuali impongono solo il contenitore disponibile: 2/3 in Homepage, 4/4 nelle due pagine interne e stack a 768/390. Non impongono classi sul markup interno del shortcode.
- La duplicazione di moduli Code con condizioni responsive nelle pagine interne è un vincolo operativo del builder: un cambiamento alla configurazione del shortcode deve essere mantenuto identico nelle copie, se entrambe restano necessarie. Non è un motivo per spostare la logica di griglia nei template Divi.

### Soluzione tecnica consigliata

- Mantenere il recupero dati, WPML, oEmbed e card nella sola callback `ac_video_pagina_shortcode()`; il fix può quindi essere risolto nel codice dello shortcode più uno stile dedicato e non richiede adeguamenti al layout manager o ai template Divi.
- Usare Bootstrap per gli aspetti già disponibili: struttura flex, wrap, padding/card e utility di allineamento. Il markup non dovrà più affidarsi al `flex: 0 0 50%` inline come unica regola di layout.
- Aggiungere una sola responsabilità CSS dedicata al componente: rendere il wrapper un contesto basato sulla sua larghezza e far passare gli item da due colonne a una quando il **contenitore** è sotto la soglia di leggibilità approvata. Una container query è preferibile a breakpoint Bootstrap di viewport, perché gestisce le differenze 2/3, 4/4 e future sidebar senza duplicare regole.
- Nello stato a due colonne, gli item mantengono una base del 50% e un minimo che consenta il restringimento coerente; nello stato stretto diventano una colonna. Il contenitore usa l'allineamento orizzontale configurabile per centrare un solo elemento e l'ultimo elemento dispari, senza modificarne la larghezza.
- Centralizzare l'allineamento in un solo valore configurato dalla callback e tradotto in una sola classe Bootstrap/di componente sul grid container: valore iniziale `centrato`, alternativa futura `sinistra`. CSS e markup non devono contenere decisioni duplicate sull'allineamento.

### Alternative scartate

- **Solo utility Bootstrap `row`/`col-12 col-sm-6`:** soddisfa il massimo di due colonne, ma decide usando la viewport e non la larghezza reale del contenitore; non garantisce il comportamento richiesto nelle colonne Divi di ampiezza diversa.
- **Flex inline invariato:** causa già confermata di due colonne forzate e del wrapping irregolare a 390 px; duplica la decisione di layout nel markup e non centralizza l'allineamento.
- **Gestire il layout nei moduli/colonne Divi:** le pagine hanno contenitori differenti e moduli duplicati per responsività; spostare lì la logica creerebbe configurazioni da replicare e non coprirebbe usi futuri nel layout manager.
- **CSS Grid automatico senza logica aggiuntiva:** può reagire alla larghezza ma non soddisfa in modo diretto il centraggio dell'ultima riga dispari mantenendo invariata la larghezza della card; richiederebbe ulteriore logica non necessaria rispetto al flex del componente.

### File del repository coinvolti

| File | Intervento previsto | Motivazione |
| ---- | ------------------- | ----------- |
| `inc/shortcodes/video-card.php` | Modificare solo il markup/classi del componente e centralizzare la scelta di allineamento; nessuna variazione al recupero dati. | Fonte unica del markup `[video_pagina]`. |
| `style.scss` | Aggiungere le sole regole del componente non coperte da Bootstrap, incluse quelle basate sul contenitore. | Adattamento alla larghezza reale e conservazione della larghezza card. |
| `style.css` | Aggiornare come output compilato coerente con `style.scss`, secondo il workflow del tema. | CSS frontend effettivamente caricato. |

Non sono previsti interventi in `ToroLayoutManager.php`, `inc/views/layouts/` o negli altri shortcode video.

### Modifiche manuali Divi

| Template o pagina | Elemento Divi | Modifica manuale suggerita | Motivo | Stato |
| ----------------- | ------------ | -------------------------- | ------ | ----- |
| Homepage | Modulo Code `#video` nella colonna 2/3 | Nessuna. | Il componente reagisce alla larghezza del wrapper esistente. | Non necessaria. |
| Sub-Irrigazione | Moduli Code contenenti `[video_pagina]` | Nessuna per il fix. Verificare solo che le eventuali copie desktop/mobile mantengano lo stesso shortcode e attributi. | Le copie condizionali sono gestite da Divi; la griglia resta nel componente. | Verifica consigliata, modifica non necessaria. |
| Sub-Surface Drip Irrigation | Moduli Code contenenti `[video_pagina]` | Nessuna per il fix. Verificare solo coerenza delle eventuali copie responsive. | Stesso motivo della pagina italiana. | Verifica consigliata, modifica non necessaria. |

Non è consigliato rimuovere o modificare manualmente colonne/wrapper Divi per semplificare il codice: non è obbligatorio per i requisiti e altererebbe la composizione editoriale esistente.

### Test da eseguire

- Test DOM e visivo nelle viewport 1440 × 900, 1024 × 768, 768 × 1024 e 390 × 844 per Homepage, Sub-Irrigazione e Sub-Surface Drip Irrigation, selezionando il modulo Divi visibile.
- Verificare contenitori misurati 2/3, 4/4, stacked e, se disponibile, una sidebar del layout manager; confermare passaggio a una colonna in base alla larghezza del wrapper.
- Verificare 1, 2, 3 e 4 elementi: massimo due colonne; singolo/ultima riga dispari centrati; nessuna espansione dell'ultima card; nessun overflow orizzontale.
- Verificare lingua italiana e inglese, embed FitVids/oEmbed, titoli lunghi e presenza di moduli Divi duplicati/nascosti.

### Rischi e compatibilità

- Le container query richiedono browser moderni; la compatibilità dei browser target e la soglia minima di leggibilità devono essere confermate prima dell'implementazione.
- Divi/FitVids continua a gestire esclusivamente il rapporto dell'iframe; il fix non deve alterare `.fluid-width-video-wrapper` né i suoi stili runtime.
- La compilazione coerente di `style.scss` e `style.css` è necessaria per evitare divergenze tra sorgente e CSS distribuito.
- Le copie responsive nel Divi Builder possono mascherare una modifica se non aggiornate/coerenti; per questo il test deve controllare il modulo effettivamente visibile.

## Implementazione applicata `[video_pagina]`

### Modifica applicata

- In `inc/shortcodes/video-card.php` è stata rimossa la regola inline rigida `flex: 0 0 50%`. Il wrapper ora usa la classe componente `.video-pagina`, la griglia `.video-pagina__grid` e gli item `.video-pagina__item`; Bootstrap resta responsabile di `d-flex`, `flex-wrap`, padding `p-2`, card e utility di allineamento.
- L'allineamento è centralizzato nella sola callback tramite `$grid_alignment`: valore iniziale `center`, convertito in `justify-content-center`; il futuro passaggio a sinistra richiede la modifica di quel solo valore (`start`). Non sono presenti decisioni duplicate di allineamento in inline style o SCSS.
- In `style.scss`/`style.css`, il componente usa `.video-pagina { container-type: inline-size; }`. Il fallback di base è una colonna leggibile. Nei browser che supportano container query gli item sono al 50% solo oltre 599 px di larghezza del contenitore e passano al 100% a 599 px o meno; `min-width:0` evita il minimo automatico che causava il wrapping irregolare. Il selettore è circoscritto al componente e non modifica gli altri shortcode video.
- `style.css` e `style.css.map` sono stati rigenerati con `sass --style=expanded --source-map style.scss style.css`.

### Comportamento osservato con Playwright

| Pagina | Viewport | Elementi | Contenitore | Colonne osservate | Larghezza item | Centratura / overflow |
| ------ | -------: | -------: | ----------: | ----------------: | -------------: | --------------------- |
| Homepage IT | 1440 | 4 | 747 px | 2 + 2 | 373 px | Righe complete, nessun overflow. |
| Homepage IT | 1024 | 4 | 531 px | 1 + 1 + 1 + 1 | 531 px | Passaggio per container stretto, nessun overflow. |
| Homepage IT | 768 | 4 | 614 px | 2 + 2 | 307 px | Righe complete, nessun overflow. |
| Homepage IT | 390 | 4 | 312 px | 1 + 1 + 1 + 1 | 312 px | Una colonna leggibile, nessun overflow. |
| Sub-Irrigazione IT | 1440 | 2 | 1152 px | 2 | 576 px | Riga completa, nessun overflow. |
| Sub-Irrigazione IT | 1024 | 2 | 819 px | 2 | 410 px | Riga completa, nessun overflow. |
| Sub-Irrigazione IT | 768 | 2 | 614 px | 2 | 307 px | Riga completa, nessun overflow. |
| Sub-Irrigazione IT | 390 | 2 | 312 px | 1 + 1 | 312 px | Una colonna leggibile, nessun overflow. |
| Sub-Surface Drip Irrigation EN | 1440 | 1 | 1152 px | 1 | 576 px | Card centrata, stessa larghezza di una colonna su due. |
| Sub-Surface Drip Irrigation EN | 1024 | 1 | 819 px | 1 | 410 px | Card centrata, stessa larghezza di una colonna su due. |
| Sub-Surface Drip Irrigation EN | 768 | 1 | 614 px | 1 | 307 px | Card centrata, stessa larghezza di una colonna su due. |
| Sub-Surface Drip Irrigation EN | 390 | 1 | 312 px | 1 | 312 px | Stato a una colonna del contenitore stretto, nessun overflow. |

Le misure DOM confermano `documentElement.scrollWidth === clientWidth` in tutti i 12 casi. I test con 4, 2 e 1 elementi soddisfano il massimo di due colonne; l'ultimo elemento dispari usa l'allineamento centrato e non si espande nel layout a due colonne. Non era disponibile un uso corrente con 3 elementi: la regola flex centrata applicata alla griglia è la stessa che centrerà la sua ultima card.

### Screenshot e limiti residui

- Sono stati salvati solo screenshot full-page in `docs/references/Screenshot-responsive/video-pagina-fix/`: 12 file, uno per pagina/viewport testata.
- Il fallback senza supporto alle container query resta a una colonna e quindi leggibile; non offre due colonne adattive nei browser privi di supporto.
- I moduli Divi duplicati nelle pagine interne sono residui nascosti dei layout precedenti, non sono stati modificati e restano fuori scope del fix.

## Esclusioni

- **Shortcode non usati nel database:** `[test_debug_toro]`, `[agente_card]`, `[documenti_agente]`, `[product_docs]`, `[coltura_docs]`, `[toro_tipi_per_coltura]`, `[carosello_video_pagina]`, `[brochure_coltura_dettaglio]`, `[doc_plus_coltura]`, `[include_php]`, `[test_workflow]`, `[scheda_prodotto]`, `[video_prodotto]`.
- **Shortcode usati ma senza griglia:** `[ricerca_agenti]`, `[hero_tipo_prodotto_e_coltura]`, `[video_prodotto_v2]`, `[video_tipo_prodotto_v2]`, `[scheda_prodotto_dettaglio]`, `[scheda_prodotto_tipo_dettaglio]`, `[my_breadcrumbs]`, `[documenti_pagina]`.
- **Uso noto documentato senza nuova scansione database:** `[video_tipo_prodotto_standalone]`, presente nei template Divi Builder del Tipo di Prodotto “Manichette Aqua-Traxx®” e della versione inglese “aqua-traxx-dripline”; genera una griglia Bootstrap responsive.
- **Dati esclusi dalla discovery d’uso:** revisioni e record `inherit`, storico di `[doc_plus]`, meta `_et_pb_truncate_post` duplicato/derivato di `[video_pagina]` e altri meta duplicati; nessuno shortcode è risultato esclusivo dei meta testuali.
