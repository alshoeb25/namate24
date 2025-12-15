// Axios bootstrap and basic auth handling
import axios from 'axios';

// Base axios defaults
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || '/';
axios.defaults.withCredentials = true; // for Sanctum cookie auth; false if token-based

// Attach token from localStorage (if using token auth)
const token = localStorage.getItem('api_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Response interceptor for 401
axios.interceptors.response.use(
  res => res,
  err => {
    if (err.response && err.response.status === 401) {
      // Could dispatch logout event or redirect
      window.dispatchEvent(new CustomEvent('unauthorized'));
    }
    return Promise.reject(err);
  }
);

export default axios;