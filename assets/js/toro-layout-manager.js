/**
 * TORO AG Layout Manager JavaScript
 * Gestisce smooth scroll ottimizzato e funzionalità del layout manager
 */

(function() {
    'use strict';
    
    /**
     * Smooth scroll ottimizzato per anchor links
     * Velocità controllata e offset per header fissi
     */
    function initSmoothScroll() {
        // Seleziona tutti i link che puntano ad anchor interni
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Ignora link vuoti o solo #
                if (!href || href === '#') return;
                
                const target = document.querySelector(href);
                if (!target) return;
                
                // Previeni comportamento default
                e.preventDefault();
                
                // Calcola offset per header fisso (se presente)
                const headerOffset = getHeaderOffset();
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;
                
                // Smooth scroll ottimizzato
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Aggiorna URL senza jump
                if (history.pushState) {
                    history.pushState(null, null, href);
                }
            });
        });
    }
    
    /**
     * Calcola offset per header fisso/sticky
     * @returns {number} Pixel di offset
     */
    function getHeaderOffset() {
        // Header Divi standard
        const mainHeader = document.querySelector('#main-header');
        if (mainHeader && window.getComputedStyle(mainHeader).position === 'fixed') {
            return mainHeader.offsetHeight + 20; // 20px padding extra
        }
        
        // Header custom o altri
        const stickyHeaders = document.querySelectorAll('[style*="position: fixed"], .sticky-header, .fixed-header');
        let maxHeight = 0;
        
        stickyHeaders.forEach(header => {
            if (header.offsetTop <= 50) { // Solo header in cima
                maxHeight = Math.max(maxHeight, header.offsetHeight);
            }
        });
        
        return maxHeight > 0 ? maxHeight + 20 : 80; // Default 80px se non trovato
    }
    
    /**
     * Inizializza debug WPML per traduzioni
     */
    function initWPMLDebug() {
        if (window.location.search.includes('toro_debug_wpml=1')) {
            console.log('🌍 TORO WPML Debug Mode Enabled');
            
            // Trova tutti gli elementi con traduzioni WPML
            const translatedElements = document.querySelectorAll('[data-wpml-string]');
            translatedElements.forEach(el => {
                console.log('WPML String:', el.textContent, 'Context:', el.dataset.wpmlString);
            });
        }
    }
    
    /**
     * Inizializza performance monitoring
     */
    function initPerformanceMonitoring() {
        if (window.location.search.includes('toro_debug_perf=1')) {
            console.log('⚡ TORO Performance Debug Mode Enabled');
            
            // Monitora caricamento layout
            const layoutContainers = document.querySelectorAll('.toro-layout-container');
            layoutContainers.forEach((container, index) => {
                console.log(`Layout ${index + 1}:`, {
                    'Sezioni caricate': container.querySelectorAll('[class*="toro-layout-"][class*="-section"]').length,
                    'Sidebar attiva': container.classList.contains('toro-layout-sidebar-left') || container.classList.contains('toro-layout-sidebar-right'),
                    'Tipo layout': container.className.match(/toro-layout-(\w+)/)?.[1] || 'unknown'
                });
            });
        }
    }
    
    /**
     * Inizializza tutto quando DOM è pronto
     */
    function init() {
        // Aspetta che DOM sia completamente caricato
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        // Inizializza funzionalità
        initSmoothScroll();
        initWPMLDebug();
        initPerformanceMonitoring();
        
        console.log('🚀 TORO Layout Manager JavaScript Initialized');
    }
    
    // Avvia inizializzazione
    init();
    
})();
