import 'bootstrap';
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
import toastr from "toastr";
import ApexCharts from 'apexcharts'
import Swiper from 'swiper/bundle';

import "toastr/build/toastr.min.css";
import 'swiper/css/bundle';

window.axios = axios;

axios.defaults.withCredentials = true;
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Pastikan axios default punya Accept JSON
axios.defaults.headers.common['Accept'] = 'application/json';
// Jika kamu pakai meta tag csrf di blade, ambil tokennya:
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
// Pusher.logToConsole = true;
window.Pusher = Pusher;

const cfg = window.__APP_ECHO_CONFIG ?? {};

window.Echo = new Echo({
    broadcaster: cfg.broadcaster,
    key: cfg.key,
    cluster: cfg.cluster,
    wsHost: cfg.host,
    wsPort: cfg.wsPort ?? (window.location.protocol === 'https:' ? 443 : 80),
    wssPort: cfg.wssPort,
    forceTLS: cfg.forceTLS,
    enabledTransports: ['ws', 'wss'],
    auth: {
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        // jika gunakan Bearer token (API) uncomment:
        // 'Authorization': `Bearer ${localStorage.getItem('api_token')}`
    }
    }
});

// Konfigurasi toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-bottom-right",
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};

window.toastr = toastr;

window.ApexCharts = ApexCharts;

window.Swiper = Swiper;
