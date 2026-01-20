import axios from 'axios';

const instance = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    }
});

export default instance;

instance.interceptors.response.use(
    response => response,
    error => {
        // if (error.response?.status === 401) {
        //     window.location.href = '/login';
        // }
        // Don't auto-redirect for now to allow viewing dashboard with mock data
        return Promise.reject(error);
    }
);
