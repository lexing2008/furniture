<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-[70vh] items-center overflow-hidden bg-gray-50 px-4 py-10 sm:py-16">
        <div class="pointer-events-none absolute -left-32 top-0 h-72 w-72 rounded-full bg-green-first/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-green-second/10 blur-3xl"></div>

        <div class="relative mx-auto w-full max-w-md">
            <div class="mb-6 text-center">
                <Link href="/" class="inline-flex items-center gap-2 text-lg font-bold tracking-wide text-green-950 hover:text-green-800">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-first text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" />
                        </svg>
                    </span>
                    {{ $page.props.app_name }}
                </Link>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                    {{ $t('Кабинет компании') }}
                </p>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xl shadow-gray-200/50 sm:p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
