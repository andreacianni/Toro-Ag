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