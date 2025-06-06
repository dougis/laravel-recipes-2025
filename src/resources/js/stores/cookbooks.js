import { defineStore } from 'pinia';
import axios from 'axios';

export const useCookbookStore = defineStore('cookbooks', {
    state: () => ({
        cookbooks: [],
        publicCookbooks: [],
        currentCookbook: null,
        loading: false
    }),

    getters: {
        getCookbookById: (state) => (id) => {
            return state.cookbooks.find(cookbook => cookbook._id === id) ||
                   state.publicCookbooks.find(cookbook => cookbook._id === id);
        }
    },

    actions: {
        async fetchCookbooks() {
            this.loading = true;
            try {
                const response = await axios.get('/cookbooks');
                this.cookbooks = response.data.cookbooks;
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch cookbooks' 
                };
            } finally {
                this.loading = false;
            }
        },

        async fetchPublicCookbooks() {
            this.loading = true;
            try {
                const response = await axios.get('/cookbooks/public');
                this.publicCookbooks = response.data.cookbooks;
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch public cookbooks' 
                };
            } finally {
                this.loading = false;
            }
        },

        async fetchCookbook(id) {
            this.loading = true;
            try {
                const response = await axios.get(`/cookbooks/${id}`);
                this.currentCookbook = response.data.cookbook;
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch cookbook' 
                };
            } finally {
                this.loading = false;
            }
        },

        async createCookbook(cookbookData) {
            try {
                const response = await axios.post('/cookbooks', cookbookData);
                this.cookbooks.unshift(response.data.cookbook);
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to create cookbook',
                    errors: error.response?.data?.errors || {}
                };
            }
        },

        async updateCookbook(id, cookbookData) {
            try {
                const response = await axios.put(`/cookbooks/${id}`, cookbookData);
                const index = this.cookbooks.findIndex(cookbook => cookbook._id === id);
                if (index !== -1) {
                    this.cookbooks[index] = response.data.cookbook;
                }
                if (this.currentCookbook && this.currentCookbook._id === id) {
                    this.currentCookbook = response.data.cookbook;
                }
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to update cookbook',
                    errors: error.response?.data?.errors || {}
                };
            }
        },

        async deleteCookbook(id) {
            try {
                await axios.delete(`/cookbooks/${id}`);
                this.cookbooks = this.cookbooks.filter(cookbook => cookbook._id !== id);
                if (this.currentCookbook && this.currentCookbook._id === id) {
                    this.currentCookbook = null;
                }
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to delete cookbook' 
                };
            }
        },

        async toggleCookbookPrivacy(id) {
            try {
                const response = await axios.put(`/cookbooks/${id}/privacy`);
                const index = this.cookbooks.findIndex(cookbook => cookbook._id === id);
                if (index !== -1) {
                    this.cookbooks[index] = response.data.cookbook;
                }
                if (this.currentCookbook && this.currentCookbook._id === id) {
                    this.currentCookbook = response.data.cookbook;
                }
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to toggle cookbook privacy' 
                };
            }
        },

        async addRecipesToCookbook(cookbookId, recipeIds) {
            try {
                const response = await axios.post(`/cookbooks/${cookbookId}/recipes`, {
                    recipe_ids: recipeIds
                });
                
                const index = this.cookbooks.findIndex(cookbook => cookbook._id === cookbookId);
                if (index !== -1) {
                    this.cookbooks[index] = response.data.cookbook;
                }
                if (this.currentCookbook && this.currentCookbook._id === cookbookId) {
                    this.currentCookbook = response.data.cookbook;
                }
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to add recipes to cookbook' 
                };
            }
        },

        async removeRecipeFromCookbook(cookbookId, recipeId) {
            try {
                const response = await axios.delete(`/cookbooks/${cookbookId}/recipes/${recipeId}`);
                
                const index = this.cookbooks.findIndex(cookbook => cookbook._id === cookbookId);
                if (index !== -1) {
                    this.cookbooks[index] = response.data.cookbook;
                }
                if (this.currentCookbook && this.currentCookbook._id === cookbookId) {
                    this.currentCookbook = response.data.cookbook;
                }
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to remove recipe from cookbook' 
                };
            }
        },

        async reorderRecipes(cookbookId, recipeOrder) {
            try {
                const response = await axios.put(`/cookbooks/${cookbookId}/recipes/order`, {
                    recipe_order: recipeOrder
                });
                
                const index = this.cookbooks.findIndex(cookbook => cookbook._id === cookbookId);
                if (index !== -1) {
                    this.cookbooks[index] = response.data.cookbook;
                }
                if (this.currentCookbook && this.currentCookbook._id === cookbookId) {
                    this.currentCookbook = response.data.cookbook;
                }
                return { success: true, cookbook: response.data.cookbook };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to reorder recipes' 
                };
            }
        },

        clearCurrentCookbook() {
            this.currentCookbook = null;
        }
    }
});
