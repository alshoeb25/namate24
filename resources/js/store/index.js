import { defineStore } from 'pinia';
import axios from '../bootstrap';

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    token: localStorage.getItem('api_token') || null,
    activeRole: localStorage.getItem('active_role') || 'tutor', // Default to tutor for dual-role users
  }),
  actions: {
    setUser(user) {
      this.user = user;
    },
    setToken(token) {
      this.token = token;
      if (token) {
        localStorage.setItem('api_token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } else {
        localStorage.removeItem('api_token');
        delete axios.defaults.headers.common['Authorization'];
      }
      window.dispatchEvent(new CustomEvent('auth:token-set'));
    },
    setActiveRole(role) {
      this.activeRole = role;
      localStorage.setItem('active_role', role);
    },
    async login(credentials) {
      const res = await axios.post('/api/login', credentials);
      const token = res.data.token;
      this.setToken(token);
      this.setUser(res.data.user);
      return res;
    },
    async register(payload) {
      const res = await axios.post('/api/register', payload);
      const token = res.data.token;
      this.setToken(token);
      this.setUser(res.data.user);
      return res;
    },
    async fetchUser() {
      // If using token approach, fetch user
      if (!this.token) return null;
      const res = await axios.get('/api/user');
      this.setUser(res.data);
      return res.data;
    },
    async enrollAsTeacher() {
      try {
        const res = await axios.post('/api/user/enroll-teacher');
        await this.fetchUser(); // Refresh user data
        return res.data;
      } catch (error) {
        console.error('Error enrolling as teacher:', error);
        throw error;
      }
    },
    async enrollAsStudent() {
      try {
        const res = await axios.post('/api/user/enroll-student');
        await this.fetchUser(); // Refresh user data
        return res.data;
      } catch (error) {
        console.error('Error enrolling as student:', error);
        throw error;
      }
    },
    logout() {
      // If Sanctum cookie-auth, call logout API; otherwise just clear token
      try {
        axios.post('/api/logout').catch(()=>{});
      } catch (e) {}
      this.setToken(null);
      this.setUser(null);
    },
  }
});

export const useWalletStore = defineStore('wallet', {
  state: () => ({
    packages: [],
    wallet: null,
  }),
  actions: {
    async loadPackages() {
      const res = await axios.get('/api/credit-packages');
      this.packages = res.data;
    },
    async loadWallet() {
      const res = await axios.get('/api/wallet');
      this.wallet = res.data;
    },
    async buyPackage(packageId) {
      const res = await axios.post('/api/wallet/buy', { package_id: packageId });
      return res.data;
    }
  }
});