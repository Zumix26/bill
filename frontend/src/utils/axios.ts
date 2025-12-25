import axios from 'axios';

const baseURL = import.meta.env.VITE_SERVER_URL || 'http://localhost:8000/api/';

const instance = axios.create({
  baseURL: baseURL,
  timeout: 30000
});

instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default instance;
