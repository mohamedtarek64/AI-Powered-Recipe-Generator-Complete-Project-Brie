import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';

// Direct imports for main pages
import Login from '@/pages/auth/Login.vue';
import Dashboard from '@/pages/Dashboard.vue';

// Lazy load other pages for better performance
const RecipesIndex = () => import('@/pages/Recipes/Index.vue');
const RecipesCreate = () => import('@/pages/Recipes/Create.vue');
const RecipesShow = () => import('@/pages/Recipes/Show.vue');
const PantryIndex = () => import('@/pages/Pantry/Index.vue');
const ShoppingListsIndex = () => import('@/pages/ShoppingLists/Index.vue');
const ShoppingListsShow = () => import('@/pages/ShoppingLists/Show.vue');
const CollectionsIndex = () => import('@/pages/Collections/Index.vue');
const CollectionsShow = () => import('@/pages/Collections/Show.vue');
const MealPlannerIndex = () => import('@/pages/MealPlanner/Index.vue');

// Define routes
export const routes: RouteRecordRaw[] = [
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/auth/Register.vue')
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard
    },
    // Recipe Routes
    {
        path: '/recipes',
        name: 'recipes.index',
        component: RecipesIndex
    },
    {
        path: '/recipes/create',
        name: 'recipes.create',
        component: RecipesCreate
    },
    {
        path: '/recipes/:slug',
        name: 'recipes.show',
        component: RecipesShow,
        props: true
    },
    // Pantry Routes
    {
        path: '/pantry',
        name: 'pantry.index',
        component: PantryIndex
    },
    // Shopping Lists Routes
    {
        path: '/shopping-lists',
        name: 'shopping-lists.index',
        component: ShoppingListsIndex
    },
    {
        path: '/shopping-lists/:id',
        name: 'shopping-lists.show',
        component: ShoppingListsShow,
        props: true
    },
    // Collections Routes
    {
        path: '/collections',
        name: 'collections.index',
        component: CollectionsIndex
    },
    {
        path: '/collections/:id',
        name: 'collections.show',
        component: CollectionsShow,
        props: true
    },
    // Meal Planner Routes
    {
        path: '/meal-planner',
        name: 'meal-planner.index',
        component: MealPlannerIndex
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Comprehensive route helper function
export const route = (name: string, params?: any): string => {
    const routeMap: Record<string, string | ((params: any) => string)> = {
        'login': '/login',
        'register': '/register',
        'dashboard': '/dashboard',
        'recipes.index': '/recipes',
        'recipes.create': '/recipes/create',
        'recipes.show': (p) => `/recipes/${p}`,
        'pantry.index': '/pantry',
        'shopping-lists.index': '/shopping-lists',
        'shopping-lists.show': (p) => `/shopping-lists/${p}`,
        'collections.index': '/collections',
        'collections.show': (p) => `/collections/${p}`,
        'meal-planner.index': '/meal-planner',
        'password.request': '/forgot-password',
    };

    const routeValue = routeMap[name];
    if (typeof routeValue === 'function') {
        return routeValue(params);
    }
    return routeValue || '/';
};
