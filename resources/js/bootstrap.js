import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
import noUiSlider from 'nouislider';
import axios from 'axios';

window.axios = axios;
window.noUiSlider = noUiSlider;
window.bootstrap = bootstrap;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

function applyCsrfToken(token) {
    if (!token) return null;
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) meta.setAttribute('content', token);
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    document.querySelectorAll('input[name="_token"]').forEach((input) => {
        input.value = token;
    });
    return token;
}

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    applyCsrfToken(csrfMeta.getAttribute('content'));
}

let csrfRefreshPromise = null;
window.refreshCsrfToken = function refreshCsrfToken() {
    if (!csrfRefreshPromise) {
        csrfRefreshPromise = axios.get('/session/ping')
            .then((response) => {
                applyCsrfToken(response.data?.token);
                return response.data?.token;
            })
            .finally(() => { csrfRefreshPromise = null; });
    }
    return csrfRefreshPromise;
};

window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const config = error.config;
        if (error.response?.status === 419 && config && !config._csrfRetried) {
            config._csrfRetried = true;
            window.dispatchEvent(new CustomEvent('csrf:refreshing'));
            try {
                const token = await window.refreshCsrfToken();
                if (!token) return Promise.reject(error);
                if (config.headers?.set) {
                    config.headers.set('X-CSRF-TOKEN', token);
                } else {
                    config.headers = config.headers || {};
                    config.headers['X-CSRF-TOKEN'] = token;
                }
                if (config.data instanceof FormData && config.data.has('_token')) {
                    config.data.set('_token', token);
                }
                return window.axios(config);
            } catch {
                return Promise.reject(error);
            }
        }
        return Promise.reject(error);
    }
);

function bindStaleCsrfFormRefresh() {
    const page = document.body?.dataset?.page;
    if (!page?.startsWith('auth')) return;

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            window.refreshCsrfToken?.();
        }
    });

    document.querySelectorAll('form').forEach((form) => {
        const method = (form.getAttribute('method') || 'get').toLowerCase();
        if (method !== 'post') return;
        if (!form.querySelector('input[name="_token"]')) return;

        form.addEventListener('submit', async (event) => {
            if (form.dataset.csrfSubmitting === '1') return;
            event.preventDefault();
            form.dataset.csrfSubmitting = '1';
            event.submitter?.setAttribute('disabled', 'disabled');
            try {
                await window.refreshCsrfToken();
            } catch {
                // Submit with the token already on the page if refresh fails
            }
            form.submit();
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindStaleCsrfFormRefresh);
} else {
    bindStaleCsrfFormRefresh();
}
