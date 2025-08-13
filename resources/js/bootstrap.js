import $ from 'jquery';
window.$ = window.jQuery = $;

import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
import noUiSlider from 'nouislider';
import axios from 'axios';

// expose required libs in a controlled manner
window.axios = axios;
window.noUiSlider = noUiSlider;
window.bootstrap = bootstrap;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Load plugins that depend on jQuery after jQuery is on window to avoid ReferenceError in dev
// Using dynamic imports defers execution until after the above global assignments
Promise.resolve()
    .then(() => import('jquery-ui-dist/jquery-ui.js'))
    .then(() => import('round-slider/dist/roundslider.min.js'))
    .catch((e) => {
        console.error('Failed to load jQuery plugins', e);
    });
