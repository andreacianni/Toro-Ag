document.addEventListener('DOMContentLoaded', function() {
    // Seleziona tutti i toggle privacy (per gestire più form nella stessa pagina)
    const privacyToggles = document.querySelectorAll('.privacy-toggle');
    
    privacyToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Trova il target specifico per questo toggle
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Toggle delle classi
                this.classList.toggle('expanded');
                targetElement.classList.toggle('expanded');
                
                // Cambia icona Bootstrap
                const arrow = this.querySelector('.privacy-arrow');
                if (arrow) {
                    if (this.classList.contains('expanded')) {
                        arrow.className = 'bi bi-chevron-up privacy-arrow';
                    } else {
                        arrow.className = 'bi bi-chevron-down privacy-arrow';
                    }
                }
            }
        });
    });
    
    // Gestione accessibilità con tastiera
    privacyToggles.forEach(function(toggle) {
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});