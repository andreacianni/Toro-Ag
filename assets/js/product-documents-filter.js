document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-product-documents-sidebar]').forEach(function (sidebar) {
        var filter = sidebar.querySelector('[data-product-documents-filter]');
        var documentItems = Array.prototype.slice.call(
            sidebar.querySelectorAll('[data-product-documents-group]')
        ).filter(function (item) {
            return item.dataset.lang;
        });
        // Video: blocco isolato, rimuovibile dal filtro senza toccare i documenti.
        var videoItems = Array.prototype.slice.call(
            sidebar.querySelectorAll('[data-product-video]')
        ).filter(function (item) {
            return item.dataset.lang;
        });
        var filterItems = documentItems.concat(videoItems);

        if (!filter) {
            return;
        }

        var documentCard = sidebar.querySelector('[data-product-documents-card]');
        var videoSection = sidebar.querySelector('.toro-layout-videos-section');

        if (!filterItems.length) {
            if (documentCard) {
                documentCard.hidden = true;
            }
            if (videoSection) {
                videoSection.hidden = true;
            }
            return;
        }

        var languages = [];
        filterItems.forEach(function (item) {
            if (languages.indexOf(item.dataset.lang) === -1) {
                languages.push(item.dataset.lang);
            }
        });

        var controls = filter.querySelector('[data-product-documents-filter-controls]');
        if (!controls) {
            return;
        }
        controls.setAttribute('role', 'group');

        var config = window.toroProductDocumentsFilter || {};
        controls.setAttribute('aria-label', config.chooseLanguage || 'Choose language');

        function updateItemsVisibility(items, language, showAll) {
            items.forEach(function (item) {
                item.hidden = !showAll && item.dataset.lang !== language;
            });
        }

        function updateVideoVisibility(language, showAll) {
            updateItemsVisibility(videoItems, language, showAll);
            if (videoSection) {
                videoSection.hidden = !videoSection.querySelector('[data-product-video]:not([hidden])');
            }
        }

        function selectLanguage(language) {
            var showAll = language === '';
            updateItemsVisibility(documentItems, language, showAll);

            if (documentCard) {
                Array.prototype.forEach.call(
                    documentCard.querySelectorAll('[data-product-documents-section]'),
                    function (section) {
                        section.hidden = !section.querySelector('[data-product-documents-group]:not([hidden])');
                    }
                );
                documentCard.hidden = !documentCard.querySelector('[data-product-documents-section]:not([hidden])');
            }

            updateVideoVisibility(language, showAll);

            Array.prototype.forEach.call(controls.querySelectorAll('button'), function (button) {
                button.classList.toggle('active', button.dataset.lang === language);
                button.setAttribute('aria-pressed', button.dataset.lang === language ? 'true' : 'false');
            });
        }

        languages.forEach(function (language) {
            var item = filterItems.find(function (candidate) {
                return candidate.dataset.lang === language;
            });
            var flag = item ? item.querySelector('img.lang-flag') : null;
            var label = flag && flag.alt ? flag.alt : language;
            var button = document.createElement('button');

            button.type = 'button';
            button.className = 'filter-flag btn btn-sm btn-outline-secondary p-1';
            button.dataset.lang = language;
            button.title = label;
            button.setAttribute('aria-label', label);
            button.setAttribute('aria-pressed', 'false');
            button.appendChild(flag ? flag.cloneNode(true) : document.createTextNode(label));
            button.addEventListener('click', function () {
                selectLanguage(language);
            });
            controls.appendChild(button);
        });

        var allLanguagesButton = document.createElement('button');
        allLanguagesButton.type = 'button';
        allLanguagesButton.className = 'filter-flag btn btn-sm btn-outline-secondary p-1';
        allLanguagesButton.dataset.lang = '';
        allLanguagesButton.title = config.allLanguages || 'All languages';
        allLanguagesButton.setAttribute('aria-label', config.allLanguages || 'All languages');
        allLanguagesButton.setAttribute('aria-pressed', 'false');
        allLanguagesButton.innerHTML = '<i class="bi bi-globe2"></i>';
        allLanguagesButton.addEventListener('click', function () {
            selectLanguage('');
        });
        controls.appendChild(allLanguagesButton);

        filter.hidden = false;

        var initialLanguage = config.currentLang === 'en' && languages.indexOf('inglese') !== -1
            ? 'inglese'
            : languages[0];

        selectLanguage(initialLanguage);
    });
});
