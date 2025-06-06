import { defineStore } from 'pinia';
import axios from 'axios';

export const useRecipeStore = defineStore('recipes', {
    state: () => ({
        recipes: [],
        publicRecipes: [],
        currentRecipe: null,
        searchResults: [],
        loading: false,
        searchLoading: false,
        metadata: {
            classifications: [],
            sources: [],
            meals: [],
            courses: [],
            preparations: []
        }
    }),

    getters: {
        getRecipeById: (state) => (id) => {
            return state.recipes.find(recipe => recipe._id === id) ||
                   state.publicRecipes.find(recipe => recipe._id === id);
        }
    },

    actions: {
        async fetchRecipes() {
            this.loading = true;
            try {
                const response = await axios.get('/recipes');
                this.recipes = response.data.recipes;
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch recipes' 
                };
            } finally {
                this.loading = false;
            }
        },

        async fetchPublicRecipes() {
            this.loading = true;
            try {
                const response = await axios.get('/recipes/public');
                this.publicRecipes = response.data.recipes;
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch public recipes' 
                };
            } finally {
                this.loading = false;
            }
        },

        async fetchRecipe(id) {
            this.loading = true;
            try {
                const response = await axios.get(`/recipes/${id}`);
                this.currentRecipe = response.data.recipe;
                return { success: true, recipe: response.data.recipe };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to fetch recipe' 
                };
            } finally {
                this.loading = false;
            }
        },

        async createRecipe(recipeData) {
            try {
                const response = await axios.post('/recipes', recipeData);
                this.recipes.unshift(response.data.recipe);
                return { success: true, recipe: response.data.recipe };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to create recipe',
                    errors: error.response?.data?.errors || {}
                };
            }
        },

        async updateRecipe(id, recipeData) {
            try {
                const response = await axios.put(`/recipes/${id}`, recipeData);
                const index = this.recipes.findIndex(recipe => recipe._id === id);
                if (index !== -1) {
                    this.recipes[index] = response.data.recipe;
                }
                if (this.currentRecipe && this.currentRecipe._id === id) {
                    this.currentRecipe = response.data.recipe;
                }
                return { success: true, recipe: response.data.recipe };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to update recipe',
                    errors: error.response?.data?.errors || {}
                };
            }
        },

        async deleteRecipe(id) {
            try {
                await axios.delete(`/recipes/${id}`);
                this.recipes = this.recipes.filter(recipe => recipe._id !== id);
                if (this.currentRecipe && this.currentRecipe._id === id) {
                    this.currentRecipe = null;
                }
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to delete recipe' 
                };
            }
        },

        async toggleRecipePrivacy(id) {
            try {
                const response = await axios.put(`/recipes/${id}/privacy`);
                const index = this.recipes.findIndex(recipe => recipe._id === id);
                if (index !== -1) {
                    this.recipes[index] = response.data.recipe;
                }
                if (this.currentRecipe && this.currentRecipe._id === id) {
                    this.currentRecipe = response.data.recipe;
                }
                return { success: true, recipe: response.data.recipe };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Failed to toggle recipe privacy' 
                };
            }
        },

        async searchRecipes(query, filters = {}) {
            this.searchLoading = true;
            try {
                const params = { q: query, ...filters };
                const response = await axios.get('/recipes/search', { params });
                this.searchResults = response.data.recipes;
                return { success: true, recipes: response.data.recipes };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'Search failed' 
                };
            } finally {
                this.searchLoading = false;
            }
        },

        async loadMetadata() {
            try {
                const [classifications, sources, meals, courses, preparations] = await Promise.all([
                    axios.get('/classifications'),
                    axios.get('/sources'),
                    axios.get('/meals'),
                    axios.get('/courses'),
                    axios.get('/preparations')
                ]);

                this.metadata = {
                    classifications: classifications.data.classifications,
                    sources: sources.data.sources,
                    meals: meals.data.meals,
                    courses: courses.data.courses,
                    preparations: preparations.data.preparations
                };

                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: 'Failed to load metadata' 
                };
            }
        },

        clearCurrentRecipe() {
            this.currentRecipe = null;
        },

        clearSearchResults() {
            this.searchResults = [];
        }
    }
});
