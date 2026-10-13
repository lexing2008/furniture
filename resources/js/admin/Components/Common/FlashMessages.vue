<template>
    <div
        v-if="$page.props.flash?.success || $page.props.flash?.error"
        class="mx-auto mt-6 max-w-7xl space-y-2 sm:px-6 lg:px-8"
    >
        <div
            v-if="$page.props.flash?.success"
            class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"
        >
            {{ $page.props.flash.success }}
        </div>

        <div
            v-if="$page.props.flash?.error"
            class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ $page.props.flash.error }}
        </div>
    </div>
</template>
