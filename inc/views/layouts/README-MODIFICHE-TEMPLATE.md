# 📝 GUIDA MODIFICA TEMPLATE - TORO AG Layout Manager

## 🎯 **COME MODIFICARE I LAYOUT**

I layout sono ora **completamente modificabili** tramite file template PHP.
**Non serve più toccare il codice della classe!**

## 📁 **DOVE SONO I FILE**

```
/inc/views/layouts/
├── layout-prodotto.php          ← Template principale prodotto
├── layout-tipo-prodotto.php     ← Template tipo prodotto (futuro)
├── layout-coltura.php           ← Template coltura (futuro)
└── partials/
    ├── main-content.php         ← Contenuto principale (con sidebar)
    ├── main-content-full.php    ← Contenuto full-width (senza sidebar)
    └── sidebar-content.php      ← Contenuto sidebar
```

## ✏️ **MODIFICHE COMUNI**

### **1. Cambiare Ordine Sezioni**

**File:** `inc/views/layouts/partials/main-content.php`

```php
<!-- PRIMA: immagine → contenuto → colture -->
<?php if (isset($sections['image'])): ?>
    <div class="toro-layout-image-section mb-4">
        <?php echo $sections['image']; ?>
    </div>
<?php endif; ?>

<?php if (isset($sections['content'])): ?>
    <div class="toro-layout-content-section">
        <?php echo $sections['content']; ?>
    </div>
<?php endif; ?>

<!-- DOPO: contenuto → immagine → colture -->
<?php if (isset($sections['content'])): ?>
    <div class="toro-layout-content-section mb-4">
        <?php echo $sections['content']; ?>
    </div>
<?php endif; ?>

<?php if (isset($sections['image'])): ?>
    <div class="toro-layout-image-section mb-4">
        <?php echo $sections['image']; ?>
    </div>
<?php endif; ?>
```

### **2. Aggiungere Sezione Personalizzata**

**File:** `inc/views/layouts/partials/sidebar-content.php`

```php
<!-- Aggiungi dopo le sezioni esistenti -->
<div class="toro-custom-cta mt-4 p-3 bg-primary text-white rounded">
    <h5><i class="bi bi-telephone"></i> Hai bisogno di supporto?</h5>
    <p class="mb-2">I nostri esperti sono a tua disposizione</p>
    <a href="tel:+390123456789" class="btn btn-light btn-sm">
        <i class="bi bi-telephone"></i> Chiama ora
    </a>
</div>
```

### **3. Modificare Layout Colonne**

**File:** `inc/views/layouts/layout-prodotto.php`

```php
<!-- PRIMA: sidebar 4/12, contenuto 8/12 -->
<div class="col-lg-4 col-md-12 order-lg-1 order-2">
<div class="col-lg-8 col-md-12 order-lg-2 order-1">

<!-- DOPO: sidebar 3/12, contenuto 9/12 -->
<div class="col-lg-3 col-md-12 order-lg-1 order-2">
<div class="col-lg-9 col-md-12 order-lg-2 order-1">
```

### **4. Aggiungere HTML Personalizzato**

**File:** `inc/views/layouts/partials/main-content-full.php`

```php
<!-- Sezione promozionale personalizzata -->
<div class="toro-promo-banner mt-5 p-4 bg-light border-start border-5 border-primary">
    <div class="row align-items-center">
        <div class="col-md-2 text-center">
            <i class="bi bi-award display-4 text-primary"></i>
        </div>
        <div class="col-md-7">
            <h4 class="mb-1">Garanzia TORO</h4>
            <p class="mb-0 text-muted">Qualità e affidabilità da oltre 50 anni</p>
        </div>
        <div class="col-md-3 text-end">
            <a href="/garanzia" class="btn btn-outline-primary">
                Scopri di più
            </a>
        </div>
    </div>
</div>
```

## 🎨 **PERSONALIZZAZIONI AVANZATE**

### **1. CSS Classes Personalizzate**

Puoi aggiungere classi CSS custom:

```php
<div class="toro-layout-container toro-layout-prodotto mia-classe-custom">
```

### **2. Condizioni Logiche**

```php
<!-- Mostra banner solo per prodotti specifici -->
<?php 
$product_categories = get_the_terms(get_the_ID(), 'tipo_di_prodotto');
$is_premium = $product_categories && in_array('premium', wp_list_pluck($product_categories, 'slug'));
?>

<?php if ($is_premium): ?>
<div class="premium-badge">
    <span class="badge bg-gold">Prodotto Premium</span>
</div>
<?php endif; ?>
```

### **3. Contenuto Dinamico**

```php
<!-- Mostra informazioni aggiuntive dinamiche -->
<div class="product-meta mt-3">
    <small class="text-muted">
        Pubblicato il <?php echo get_the_date(); ?> | 
        Ultima modifica: <?php echo get_the_modified_date(); ?>
    </small>
</div>
```

## 🚀 **BEST PRACTICES**

### **✅ Cosa Fare:**
- Fai backup prima di modificare
- Testa le modifiche su staging
- Usa classi CSS semantiche
- Mantieni la struttura Bootstrap
- Commenta le modifiche personalizzate

### **❌ Cosa Evitare:**
- Non modificare la classe ToroLayoutManager.php
- Non rimuovere le variabili `$sections`
- Non dimenticare i controlli `if (isset($sections['...']))`
- Non usare `echo` diretto senza escape

## 🛠️ **TESTING MODIFICHE**

1. **Modifica il template**
2. **Vai su una pagina prodotto**
3. **Aggiungi `debug="true"` al shortcode**
4. **Verifica che tutto funzioni**
5. **Rimuovi debug quando soddisfatto**

## 📞 **SUPPORTO**

Se hai dubbi sulle modifiche:
1. Controlla questa guida
2. Fai backup prima di modificare
3. Testa sempre su staging
4. Contatta il team tecnico se necessario

**I template sono il tuo parco giochi! 🎨**
