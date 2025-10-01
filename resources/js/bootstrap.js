import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
import noUiSlider from 'nouislider';
import axios from 'axios';

// expose required libs in a controlled manner
window.axios = axios;
window.noUiSlider = noUiSlider;
window.bootstrap = bootstrap;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensure CSRF header is sent on all same-origin requests (in addition to Axios XSRF cookie support)
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
}
