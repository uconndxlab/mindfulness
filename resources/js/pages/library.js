function initLibraryPage() {
    //init
    const root = document.getElementById('library-root');
    const baseParam = (root && root.getAttribute('data-library-base-param')) || 'main';
    const journalPage = baseParam === 'journal';
    let isFavorites = (root && root.getAttribute('data-library-favorites') === 'true') || baseParam === 'favorited';
    let slider = null;
    let applyFilterButton = null;

    let startTimeInput, endTimeInput, collapseTime, collapseCategory, collapseModule, collapseFilter;
    let showFilterTextEl = null;
    let filterOpen = false;

    function openFilters() {
        if (showFilterTextEl) showFilterTextEl.innerHTML = 'Hide Filters';
        filterOpen = true;
        if (collapseFilter) collapseFilter.show();
    }

    if (!isFavorites) {
        applyFilterButton = document.getElementById('apply_filter_button');
        slider = document.getElementById('time_range_slider');
        startTimeInput = document.getElementById('start_time_input');
        endTimeInput = document.getElementById('end_time_input');

        //set up accordion (guard elements for favorites page)
        const elTime = document.getElementById('collapseTime');
        const elCat = document.getElementById('collapseCategory');
        const elMod = document.getElementById('collapseModule');
        const elFilter = document.getElementById('collapseFilter');
        if (elTime) collapseTime = new window.bootstrap.Collapse(elTime, { toggle: false });
        if (elCat) collapseCategory = new window.bootstrap.Collapse(elCat, { toggle: false });
        if (elMod) collapseModule = new window.bootstrap.Collapse(elMod, { toggle: false });
        if (elFilter) collapseFilter = new window.bootstrap.Collapse(elFilter, { toggle: false });

        //show/hide filter accordion
        const showFilterBtn = document.getElementById('showFilterButton');
        showFilterTextEl = document.getElementById('showFilterText');
        if (showFilterBtn && showFilterTextEl) {
            showFilterBtn.addEventListener('click', function() {
                if (filterOpen) {
                    showFilterTextEl.innerHTML = 'Show Filters';
                    filterOpen = false;
                }
                else {
                    showFilterTextEl.innerHTML = 'Hide Filters';
                    filterOpen = true;
                }
            });
        }

        //when apply search with filters
        if (applyFilterButton) {
            applyFilterButton.addEventListener('click', function() {
                search(true, false, false, true);
            });
        }
        const clearFilterButton = document.getElementById('clear_filter_button');
        if (clearFilterButton) {
            clearFilterButton.addEventListener('click', clearFilters);
        }
    }

    const sfForm = document.getElementById('search_filter_form');
    const searchBar = document.getElementById('search_bar');

    //saved page number
    let _page = 1;

    const wipeFilters = !!(root && root.getAttribute('data-library-wipe-filters') === 'true');

    //load in old filter values
    function loadFilters() {
        console.log('loading values');
        let filters = null;
        if (baseParam === 'main') {
            filters = JSON.parse(sessionStorage.getItem('main_filters'));
            console.log('main filters:', filters);
        } else if (baseParam === 'favorited') {
            filters = JSON.parse(sessionStorage.getItem('favorite_filters'));
        } else if (journalPage) {
            filters = JSON.parse(sessionStorage.getItem('journal_filters'));
        }
        if (filters) {
            //search
            searchBar.value = filters.search || '';
            searchBar.focus();

            if (!isFavorites) {
                //remove transitions temporarily
                document.querySelectorAll('.collapse, .arrow-selector').forEach(function(element) {
                    element.classList.add('no-transition');
                });

                //time
                if (startTimeInput) startTimeInput.value = filters.start || 0;
                if (endTimeInput) endTimeInput.value = filters.end || 30;
                if (collapseTime && (filters.end != 30 || filters.start != 0)) {
                    collapseTime.show();
                    openFilters();
                }

                //categories
                document.querySelectorAll('input[name="category[]"]').forEach(checkbox => {
                    checkbox.checked = (filters.categories || []).includes(checkbox.value);
                    if (checkbox.checked) {
                        if (collapseCategory) collapseCategory.show();
                        openFilters();
                    }
                });
                //modules
                document.querySelectorAll('input[name="module[]"]').forEach(checkbox => {
                    checkbox.checked = (filters.modules || []).includes(checkbox.value);
                    if (checkbox.checked) {
                        if (collapseModule) collapseModule.show();
                        openFilters();
                    }
                });
            }

            //page
            _page = filters.page;

            //get smooth transitions back
            setTimeout(function() {
                document.querySelectorAll('.collapse, .arrow-selector').forEach(function(element) {
                    element.classList.remove('no-transition');
                });
            }, 10);
        }
    }
    if (!wipeFilters) {
        loadFilters();
    } else {
        if (baseParam === 'main') {
            sessionStorage.removeItem('main_filters');
        } else if (baseParam === 'favorited') {
            sessionStorage.removeItem('favorite_filters');
        } else if (journalPage) {
            sessionStorage.removeItem('journal_filters');
        }
    }

    if (slider && window.noUiSlider) {
        //SLIDER INIT
        const startVal = startTimeInput.value || 0;
        const endVal = endTimeInput.value || 30;
        function minutesToTime(minutes) {
            return `${String(minutes)} mins`;
        }
        window.noUiSlider.create(slider, {
            start: [0, 30],
            connect: true,
            range: { 'min': 0, 'max': 30 },
            step: 1,
            format: {
                to: function (value) { return minutesToTime(Math.round(value)); },
                from: function (value) { return value; }
            }
        });
        const startLabel = document.getElementById('start_time_label');
        const endLabel = document.getElementById('end_time_label');
        const bubble = document.getElementById('slider_value_bubble');
        
        // slider interaction state
        let isInteracting = false;

        //SLIDER CHANGE
        slider.noUiSlider.on('update', function (values, handle) {
            //update labels
            startLabel.textContent = values[0];
            endLabel.textContent = values[1];

            // update bubble during interaction
            if (isInteracting) {
                updateBubblePosition(handle, values[handle]);
            }

            //convert the # mins to #
            const timeToMinutes = (time) => {
                const [mins, _] = time.split(' ').map(Number);
                return mins;
            };

            //update hidden input
            const startConverted = timeToMinutes(values[0]);
            const endConverted = timeToMinutes(values[1]);
            startTimeInput.value = startConverted;
            endTimeInput.value = endConverted;
            //remove the inputs if default values
            if (startConverted === 0 && endConverted === 30) {
                startTimeInput.remove();
                endTimeInput.remove();
            } else {
                //add inputs back on change
                if (!sfForm.contains(startTimeInput)) sfForm.appendChild(startTimeInput);
                if (!sfForm.contains(endTimeInput)) sfForm.appendChild(endTimeInput);
            }
        });

        // bubble positioning function
        function updateBubblePosition(handle, value) {
            if (!bubble || !slider) return;
            
            const handles = slider.querySelectorAll('.noUi-handle');
            const activeHandle = handles[handle];
            
            if (activeHandle) {
                if (bubble.parentElement !== activeHandle) {
                    activeHandle.appendChild(bubble);
                }
                bubble.textContent = value;
                bubble.classList.remove('d-none');
            }
        }

        // show bubble when interaction starts
        slider.noUiSlider.on('start', function (values, handle) {
            isInteracting = true;
            if (bubble) {
                updateBubblePosition(handle, values[handle]);
            }
        });

        // hide bubble when interaction ends
        slider.noUiSlider.on('end', function () {
            isInteracting = false;
            if (bubble) {
                bubble.classList.add('d-none');
            }
        });
        slider.noUiSlider.set([parseInt(startVal), parseInt(endVal)]);
    }

    // APPLY/SAVE FILTERS - vars
    let _categories = [];
    let _modules = [];
    let _start = null;
    let _end = null;
    function saveFilters() {
        _categories = getChecked('categories');
        _modules = getChecked('modules');
        if (startTimeInput) _start = startTimeInput.value;
        if (endTimeInput) _end = endTimeInput.value;
    }

    function getQueryParams() {
        const params = new URLSearchParams();
        params.append('search', searchBar.value);
        if (!isFavorites) {
            if (_end != 30 || _start != 0) {
                params.append('start_time', _start);
                params.append('end_time', _end);
            }
            _categories.forEach(category => params.append('category[]', category));
            _modules.forEach(module_ => params.append('module[]', module_));
        }
        params.append('base_param', baseParam);
        params.append('page', _page);

        const filters = { search: searchBar.value, page: _page };
        if (!isFavorites) {
            filters.categories = _categories;
            filters.modules = _modules;
            filters.start = _start;
            filters.end = _end;
        }
        if (baseParam === 'main') {
            console.log('Saving filters to session:', filters);
            sessionStorage.setItem('main_filters', JSON.stringify(filters));
        } else if (baseParam === 'favorited') {
            console.log('Saving filters to session:', filters);
            sessionStorage.setItem('favorite_filters', JSON.stringify(filters));
        } else if (journalPage) {
            console.log('Saving filters to session:', filters);
            sessionStorage.setItem('journal_filters', JSON.stringify(filters));
        }
        return params.toString();
    }

    function getChecked(catOrMod) {
        let checkboxes = null;
        if (catOrMod === 'modules') {
            checkboxes = document.querySelectorAll('input[name="module[]"]:checked');
        } else {
            checkboxes = document.querySelectorAll('input[name="category[]"]:checked');
        }
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }

    function search(filters=false, first=false, isSearch=false, applyFilters=false) {
        const resultsContainer = document.getElementById('resultsContainer');
        resultsContainer.classList.add('d-none');
        const throbber = document.getElementById('throbber');
        throbber.classList.remove('d-none');

        const clearFilterButton = document.getElementById('clear_filter_button');
        if (clearFilterButton) clearFilterButton.disabled = true;
        if (applyFilterButton) applyFilterButton.disabled = true;
        // build a valid absolute URL from from data attribute
        let searchUrl;
        try {
            const route = root ? root.getAttribute('data-library-search-route') : '';
            searchUrl = new URL(route, window.location.origin);
        } catch (e) {
            console.error('Invalid search route', e);
            throbber.classList.add('d-none');
            return;
        }
        if (!isFavorites && filters) saveFilters();
        if ((filters || isSearch) && !first) _page = 1;

        if (clearFilterButton) clearFilterButton.classList.toggle('d-none', !checkFilters());

        const params = getQueryParams();
        searchUrl.search = params;
        console.log(params);
        fetch(searchUrl, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('AJAX success');
            resultsContainer.innerHTML = data.html;
            throbber.classList.add('d-none');
            resultsContainer.classList.remove('d-none');
            if ((applyFilters || isSearch) && !data.empty && window.$ && window.$.fn?.effect) {
                window.$(resultsContainer).stop(true, true).effect('highlight', {color: '#d4edda'}, 1500);
            }
            if (first) {
                document.getElementById('search_page_throbber').classList.add('d-none');
                document.getElementById('filterResultDiv').classList.remove('d-none');
            }
            if (journalPage) {
                initReadMore();
            }
            attachPaginationSearch();
        })
        .catch(error => {
            throbber.classList.add('d-none');
            console.error('Error performing search', error);
        })
        .finally(() => {
            throbber.classList.add('d-none');
            if (applyFilterButton) applyFilterButton.disabled = false;
            if (clearFilterButton) clearFilterButton.disabled = false;
        });
    }

    //search on page load with filters
    search(true, true, false, true);

    function checkFilters() {
        if ((_categories && _categories.length !== 0) || (_modules && _modules.length !== 0) || _start != '0' || _end != '30') return true;
        return false;
    }
    function checkSearch() { return searchBar.value !== ''; }

    function attachPaginationSearch() {
        document.querySelectorAll('.pagination a').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const page = this.href.split('page=')[1];
                _page = page;
                search(false);
            });
        });
    }
    attachPaginationSearch();

    sfForm.addEventListener('submit', function(event) {
        event.preventDefault();
        search(true);
    });

    // keyboard submit on input - ignores filters
    let timeout = null;
    searchBar.addEventListener('input', function() {
        clearTimeout(timeout);
        const clearSearch = document.getElementById('clear_search_button');
        if (checkSearch()) {
            clearSearch.style.visibility = 'visible';
            clearSearch.disabled = false;
        } else {
            clearSearch.style.visibility = 'hidden';
            clearSearch.disabled = true;
        }
        timeout = setTimeout(function() {
            search(true, false, true, false);
        }, 750);
    });

    const moduleDiv = document.getElementById('module_check');
    const categoryDiv = document.getElementById('category_check');
    function clearFilters() {
        if (moduleDiv) moduleDiv.querySelectorAll('.form-check-input').forEach(checkbox => { checkbox.checked = false; });
        if (categoryDiv) categoryDiv.querySelectorAll('.form-check-input').forEach(checkbox => { checkbox.checked = false; });
        if (slider && slider.noUiSlider) slider.noUiSlider.set([0, 30]);
        search(true, false, false, true);
    }

    document.getElementById('clear_search_button')?.addEventListener('click', function clearSearch() {
        searchBar.value = '';
        searchBar.focus();
        search(true, false, true, false);
    });

    // NOTES: READ MORE
    function initReadMore() {
        document.querySelectorAll('.note-content-extra').forEach(readMoreDiv => {
            const readMoreBtn = readMoreDiv.querySelector('.read-more-btn');
            const dots = readMoreDiv.querySelector('.dots');
            const moreText = readMoreDiv.querySelector('.more-text');
            readMoreBtn.addEventListener('click', function() {
                if (moreText.style.display === 'none') {
                    moreText.style.display = 'inline';
                    dots.classList.add('d-none');
                    readMoreBtn.textContent = 'Read Less';
                } else {
                    moreText.classList.add('d-none');
                    dots.style.display = 'inline';
                    readMoreBtn.textContent = 'Read More...';
                }
            });
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLibraryPage);
} else {
    initLibraryPage();
}


