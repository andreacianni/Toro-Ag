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

        console.log(`🔗 Found ${anchorLinks.length} anchor links to handle`);

        anchorLinks.forEach((link, index) => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                console.log(`🎯 Click on anchor ${index + 1}: ${href}`);

                // Ignora link vuoti o solo #
                if (!href || href === '#') {
                    console.log('❌ Ignored: empty or # only');
                    return;
                }

                const target = document.querySelector(href);
                if (!target) {
                    console.log('❌ Target not found:', href);
                    return;
                }

                // Previeni comportamento default
                e.preventDefault();
                console.log('✅ Default prevented');

                // Calcola offset per header fisso (se presente)
                const headerOffset = getHeaderOffset();
                const targetRect = target.getBoundingClientRect();
                const targetPosition = targetRect.top + window.pageYOffset - headerOffset;

                console.log(`📏 Calculations:`, {
                    'Header Offset': headerOffset + 'px',
                    'Target Top': targetRect.top + 'px',
                    'Page Y Offset': window.pageYOffset + 'px',
                    'Final Position': targetPosition + 'px',
                    'Current Scroll': window.scrollY + 'px'
                });

                // Verifica se CSS scroll-behavior sta interferendo
                const htmlScrollBehavior = getComputedStyle(document.documentElement).scrollBehavior;
                if (htmlScrollBehavior === 'smooth') {
                    console.log('⚠️ CSS scroll-behavior: smooth detected - may conflict!');
                }

                // Smooth scroll ottimizzato
                const startTime = performance.now();
                console.log('🚀 Starting scroll to:', targetPosition);

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });

                // Monitor scroll completion
                let scrollCheckCount = 0;
                const scrollCheck = setInterval(() => {
                    scrollCheckCount++;
                    const currentScroll = window.scrollY;
                    const distance = Math.abs(currentScroll - targetPosition);

                    console.log(`📍 Scroll check ${scrollCheckCount}: ${currentScroll}px (distance: ${distance}px)`);

                    if (distance < 5 || scrollCheckCount > 50) {
                        clearInterval(scrollCheck);
                        const endTime = performance.now();
                        console.log(`✅ Scroll completed in ${(endTime - startTime).toFixed(2)}ms`);
                    }
                }, 50);

                // Aggiorna URL senza jump
                if (history.pushState) {
                    history.pushState(null, null, href);
                    console.log('🔗 URL updated to:', href);
                }
            });
        });
    }
    
    /**
     * Calcola offset intelligente per header fisso/sticky
     * @returns {number} Pixel di offset
     */
    function getHeaderOffset() {
        // Header Divi standard (priorità massima)
        const mainHeader = document.querySelector('#main-header');
        if (mainHeader && window.getComputedStyle(mainHeader).position === 'fixed') {
            return mainHeader.offsetHeight + 20; // 20px padding extra
        }

        // Header Top Bar Divi
        const topHeader = document.querySelector('#top-header');
        if (topHeader && window.getComputedStyle(topHeader).position === 'fixed') {
            return topHeader.offsetHeight + 20;
        }

        // Header custom o altri elementi fissi in top
        const stickyHeaders = document.querySelectorAll('[style*="position: fixed"], .sticky-header, .fixed-header, .navbar-fixed-top');
        let maxHeight = 0;

        stickyHeaders.forEach(header => {
            const rect = header.getBoundingClientRect();
            // Solo elementi davvero in cima e visibili
            if (rect.top <= 10 && rect.height > 0 && window.getComputedStyle(header).display !== 'none') {
                maxHeight = Math.max(maxHeight, rect.height);
            }
        });

        // Fallback intelligente basato su viewport
        if (maxHeight === 0) {
            // Desktop: header più grandi, Mobile: header più piccoli
            const viewportWidth = window.innerWidth;
            if (viewportWidth >= 992) {
                return 120; // Desktop - header Divi standard
            } else if (viewportWidth >= 768) {
                return 80;  // Tablet
            } else {
                return 60;  // Mobile
            }
        }

        return maxHeight + 20; // Header trovato + padding
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

        // Debug per testing
        if (window.location.search.includes('toro_debug_scroll=1')) {
            console.log('🎯 TORO Smooth Scroll Debug Enabled');
            console.log('Header Offset Calculated:', getHeaderOffset() + 'px');

            // Test automatico con primo link anchor trovato
            const firstAnchor = document.querySelector('a[href^="#"]:not([href="#"]):not([href="#top"])');
            if (firstAnchor) {
                console.log('Test anchor found:', firstAnchor.getAttribute('href'));
                setTimeout(() => {
                    console.log('Auto-testing smooth scroll...');
                    firstAnchor.click();
                }, 2000);
            }
        }

        console.log('🚀 TORO Layout Manager JavaScript Initialized');
    }
    
    // Avvia inizializzazione
    init();
    
})();
