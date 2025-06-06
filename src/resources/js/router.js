import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

// Import page components
import Home from './pages/Home.vue';
import Login from './pages/auth/Login.vue';
import Register from './pages/auth/Register.vue';
import ForgotPassword from './pages/auth/ForgotPassword.vue';
import ResetPassword from './pages/auth/ResetPassword.vue';
import Dashboard from './pages/Dashboard.vue';
import Recipes from './pages/recipes/Index.vue';
import RecipeShow from './pages/recipes/Show.vue';
import RecipeCreate from './pages/recipes/Create.vue';
import RecipeEdit from './pages/recipes/Edit.vue';
import Cookbooks from './pages/cookbooks/Index.vue';
import CookbookShow from './pages/cookbooks/Show.vue';
import CookbookCreate from './pages/cookbooks/Create.vue';
import CookbookEdit from './pages/cookbooks/Edit.vue';
import Profile from './pages/user/Profile.vue';
import Subscription from './pages/user/Subscription.vue';
import AdminDashboard from './pages/admin/Dashboard.vue';
import AdminUsers from './pages/admin/Users.vue';
import AdminStatistics from './pages/admin/Statistics.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresAuth: false, guestOnly: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresAuth: false, guestOnly: true }
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
        meta: { requiresAuth: false, guestOnly: true }
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: ResetPassword,
        meta: { requiresAuth: false, guestOnly: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/recipes',
        name: 'recipes',
        component: Recipes,
        meta: { requiresAuth: true }
    },
    {
        path: '/recipes/create',
        name: 'recipe-create',
        component: RecipeCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/recipes/:id',
        name: 'recipe-show',
        component: RecipeShow,
        meta: { requiresAuth: false }
    },
    {
        path: '/recipes/:id/edit',
        name: 'recipe-edit',
        component: RecipeEdit,
        meta: { requiresAuth: true }
    },
    {
        path: '/cookbooks',
        name: 'cookbooks',
        component: Cookbooks,
        meta: { requiresAuth: true }
    },
    {
        path: '/cookbooks/create',
        name: 'cookbook-create',
        component: CookbookCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/cookbooks/:id',
        name: 'cookbook-show',
        component: CookbookShow,
        meta: { requiresAuth: false }
    },
    {
        path: '/cookbooks/:id/edit',
        name: 'cookbook-edit',
        component: CookbookEdit,
        meta: { requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true }
    },
    {
        path: '/subscription',
        name: 'subscription',
        component: Subscription,
        meta: { requiresAuth: true }
    },
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: AdminUsers,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/statistics',
        name: 'admin-statistics',
        component: AdminStatistics,
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Initialize auth store if not already done
    if (!authStore.initialized) {
        await authStore.initialize();
    }
    
    // Check if route requires authentication
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!authStore.isAuthenticated) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }
        
        // Check if route requires admin access
        if (to.matched.some(record => record.meta.requiresAdmin)) {
            if (!authStore.isAdmin) {
                next({ name: 'dashboard' });
                return;
            }
        }
    }
    
    // Redirect authenticated users away from guest-only pages
    if (to.matched.some(record => record.meta.guestOnly)) {
        if (authStore.isAuthenticated) {
            next({ name: 'dashboard' });
            return;
        }
    }
    
    next();
});

export default router;
