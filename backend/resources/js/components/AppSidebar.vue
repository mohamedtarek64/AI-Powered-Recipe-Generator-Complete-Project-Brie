<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChefHat, ShoppingCart, Utensils, Refrigerator, Calendar, BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Generate',
        href: route('recipes.create'),
        icon: ChefHat,
    },
    {
        title: 'Recipes',
        href: route('recipes.index'),
        icon: Utensils,
    },
    {
        title: 'Pantry',
        href: route('pantry.index'),
        icon: Refrigerator,
    },
    {
        title: 'Shopping',
        href: route('shopping-lists.index'),
        icon: ShoppingCart,
    },
    {
        title: 'Meal Plan',
        href: route('meal-planner.index'),
        icon: Calendar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Collections',
        href: route('collections.index'),
        icon: Folder,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
