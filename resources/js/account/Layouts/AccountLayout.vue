<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps } from '@/account/types';

const page = usePage<PageProps>();

const user = computed(() => page.props.auth.user);

const logout = () => router.post(route('account.logout'));
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3">
                <Link :href="route('account.index')" class="text-lg font-bold text-gray-900">
                    {{ page.props.app_name }}
                    <span class="ml-1 text-sm font-normal text-gray-500">Кабинет компании</span>
                </Link>

                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <span>{{ user.name }}</span>
                    <button type="button" class="text-indigo-600 hover:text-indigo-900" @click="logout">Выйти</button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div v-if="page.props.flash?.success" class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ page.props.flash.error }}
            </div>

            <slot />
        </main>
    </div>
</template>
