import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
        initialized: false,
        loading: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        isAdmin: (state) => state.user?.subscription_tier === 'admin' || state.user?.admin_override === true,
        subscriptionTier: (state) => state.user?.subscription_tier || 0,
        canCreatePrivateContent: (state) => {
            const tier = state.user?.subscription_tier || 0;
            return tier >= 2 || state.user?.admin_override === true;
        },
        subscriptionLimits: (state) => {
            const tier = state.user?.subscription_tier || 0;
            if (state.user?.admin_override) {
                return { recipes: -1, cookbooks: -1 }; // Unlimited
            }
            
            switch (tier) {
                case 0: // Free
                    return { recipes: 25, cookbooks: 1 };
                case 1: // Paid Tier 1
                    return { recipes: -1, cookbooks: 10 };
                case 2: // Paid Tier 2
                    return { recipes: -1, cookbooks: -1 };
                default:
                    return { recipes: 25, cookbooks: 1 };
            }
        }
    },

    actions: {
        async initialize() {
            const token = localStorage.getItem('auth_token');
            const userData = localStorage.getItem('user');
            
            if (token && userData) {
                this.token = token;
                this.user = JSON.parse(userData);
                
                try {
                    // Verify token is still valid
                    const response = await axios.get('/auth/user');
                    this.user = response.data.user;
                    this.updateLocalStorage();
                } catch (error) {
                    // Token is invalid, clear everything
                    this.logout();
                }
            }
            
            this.initialized = true;
        },

        async login(credentials) {
            this.loading = true;
            try {
                const response = await axios.post('/auth/login', credentials);
                
                this.token = response.data.token;
                this.user = response.data.user;
                
                this.updateLocalStorage();
                
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Login failed' 
                };
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            try {
                const response = await axios.post('/auth/register', userData);
                
                this.token = response.data.token;
                this.user = response.data.user;
                
                this.updateLocalStorage();
                
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Registration failed',
                    errors: error.response?.data?.errors || {}
                };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post('/auth/logout');
                }
            } catch (error) {
                // Ignore logout errors
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
            }
        },

        async updateProfile(profileData) {
            try {
                const response = await axios.put('/users/profile', profileData);
                this.user = response.data.user;
                this.updateLocalStorage();
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Profile update failed',
                    errors: error.response?.data?.errors || {}
                };
            }
        },

        async forgotPassword(email) {
            try {
                await axios.post('/auth/password/email', { email });
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to send reset email' 
                };
            }
        },

        async resetPassword(resetData) {
            try {
                const response = await axios.post('/auth/password/reset', resetData);
                
                this.token = response.data.token;
                this.user = response.data.user;
                
                this.updateLocalStorage();
                
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Password reset failed' 
                };
            }
        },

        updateLocalStorage() {
            if (this.token) {
                localStorage.setItem('auth_token', this.token);
            }
            if (this.user) {
                localStorage.setItem('user', JSON.stringify(this.user));
            }
        }
    }
});
