<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/account/Components/ApplicationLogo.vue';
import Dropdown from '@/account/Components/Dropdown.vue';
import DropdownLink from '@/account/Components/DropdownLink.vue';
import NavLink from '@/account/Components/NavLink.vue';
import ResponsiveNavLink from '@/account/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import SidebarProfile from "@/admin/Components/Common/Sidebar/SidebarProfile.vue";
import Sidebar from "@/admin/Components/Common/Sidebar/Sidebar.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="flex  bg-gray-100">
        <!-- Боковая панель -->
        <Sidebar/>

        <div class="flex-1 min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">

                        <slot name="tabs" />

                    </div>
                </div>
            </nav>


            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        <slot name="header" />
                    </h2>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
