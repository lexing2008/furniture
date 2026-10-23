<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import NavLink from "@/account/Components/NavLink.vue";
import Content from "@/admin/Components/Common/Content.vue";
</script>

<template>
    <Head title="Dashboard 222" />

    <AuthenticatedLayout>
        <template #tabs>
            <!-- Navigation Links -->
            <div
                class="hidden space-x-8 sm:flex"
            >
<!--                <NavLink-->
<!--                    :href="route('dashboard')"-->
<!--                    :active="route().current('dashboard')"-->
<!--                >-->
<!--                    Dashboard-->
<!--                </NavLink>-->

<!--                <NavLink-->
<!--                    :href="route('dashboard')"-->
<!--                    :active="route().current('dashboard')"-->
<!--                >-->
<!--                    Dashboard-->
<!--                </NavLink>-->

<!--                <NavLink-->
<!--                    :href="route('dashboard')"-->
<!--                    :active="route().current('dashboard')"-->
<!--                >-->
<!--                    Dashboard-->
<!--                </NavLink>-->
            </div>
        </template>

        <template #header>
            Dashboard
        </template>

        <Content>
            You're logged in!
        </Content>

    </AuthenticatedLayout>

</template>
