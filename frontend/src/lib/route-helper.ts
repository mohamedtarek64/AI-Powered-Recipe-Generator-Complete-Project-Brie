export const routeDefinitions = [
    { name: 'dashboard', path: '/dashboard' },
    { name: 'login', path: '/login' },
    { name: 'register', path: '/register' },
    { name: 'recipes.index', path: '/recipes' },
    { name: 'recipes.create', path: '/generate' },
    { name: 'recipes.show', path: '/recipes/:slug' },
    { name: 'pantry.index', path: '/pantry' },
    { name: 'shopping-lists.index', path: '/shopping-lists' },
    { name: 'shopping-lists.show', path: '/shopping-lists/:id' },
    { name: 'collections.index', path: '/collections' },
    { name: 'collections.show', path: '/collections/:id' },
    { name: 'meal-planner.index', path: '/meal-planner' },
];

export const route = (name: string, params?: any) => {
    let path = routeDefinitions.find(r => r.name === name)?.path || '/';
    if (params) {
        if (typeof params === 'object') {
            Object.keys(params).forEach(key => {
                path = path.replace(`:${key}`, params[key]);
            });
        } else {
            path = path.replace(/:[a-z]+/, params);
        }
    }
    return path;
};
