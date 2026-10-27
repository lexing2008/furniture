<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';
import TextInput from '@/account/Components/TextInput.vue';
import type { ScheduleDay, ScheduleException, ScheduleFormData } from '@/account/Components/Company/scheduleForm';
import { dayNames } from '@/account/Components/Company/scheduleForm';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    schedule: ScheduleDay[];
    exceptions: ScheduleException[];
    timezone: string | null;
}>();

const form = useForm<ScheduleFormData>({
    schedule: props.schedule.map((day) => ({ ...day })),
    exceptions: props.exceptions.map((exception) => ({ ...exception })),
});

// Nested error keys like "schedule.0.open_at" are not covered by the form typings
const errors = computed(() => form.errors as Record<string, string | undefined>);

const scheduleError = (index: number, field: string) => errors.value[`schedule.${index}.${field}`];
const exceptionError = (index: number, field: string) => errors.value[`exceptions.${index}.${field}`];

const addException = () => {
    form.exceptions.push({ date: '', name: '' });
};

const removeException = (index: number) => {
    form.exceptions.splice(index, 1);
};

const submit = () => {
    form.put(route('account.schedule.update'), { preserveScroll: true });
};

const checkbox = 'rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first';
const smallButton =
    'inline-flex cursor-pointer items-center gap-1 rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-green-first hover:text-green-800';
const addButton =
    'inline-flex cursor-pointer items-center gap-1 rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-800 transition hover:bg-green-100';
const hint = 'text-sm leading-6 text-gray-500';
</script>

<template>
    <Head :title="$t('График работы')" />

    <AccountLayout>
        <AccountPageHeader
            icon="schedule"
            :title="$t('График работы')"
            :description="$t('Укажите часы работы и нерабочие дни.')"
        >
            <template v-if="timezone" #actions>
                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ timezone }}
                </span>
            </template>
        </AccountPageHeader>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <div>
                    <h2 :class="[formTitle, errors.schedule ? 'text-red-600' : 'text-gray-900']">{{ $t('Рабочие часы') }}</h2>
                    <p :class="hint">
                        {{ $t('Время работы компании по дням недели.') }}
                        <template v-if="timezone">{{ $t('Часовой пояс: :timezone.', { timezone }) }}</template>
                    </p>
                </div>

                <InputError :message="errors.schedule" />

                <div class="space-y-3">
                    <div
                        v-for="(day, index) in form.schedule"
                        :key="day.day_of_week"
                        class="rounded-xl border border-gray-200/80 bg-gray-50/50 p-4"
                    >
                        <div class="flex flex-wrap items-end gap-4">
                            <div class="inline-flex w-fit rounded-full border border-gray-200 bg-white px-3 py-1 pb-1 mb-1 text-xs font-semibold text-gray-700 shadow-xs">
                                {{ $t(dayNames[day.day_of_week]) }}
                            </div>

                            <label class="flex items-center gap-2 pb-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="day.is_closed" :class="checkbox" />
                                {{ $t('Выходной') }}
                            </label>

                            <template v-if="!day.is_closed">
                                <div class="w-36">
                                    <InputLabel :for="`open_at_${index}`" :value="$t('Открытие')" required :invalid="!!scheduleError(index, 'open_at')" />
                                    <TextInput
                                        :id="`open_at_${index}`"
                                        :invalid="!!scheduleError(index, 'open_at')"
                                        type="time"
                                        class="mt-1 block w-full"
                                        v-model="day.open_at"
                                    />
                                    <InputError class="mt-1" :message="scheduleError(index, 'open_at')" />
                                </div>

                                <div class="w-36">
                                    <InputLabel :for="`close_at_${index}`" :value="$t('Закрытие')" required :invalid="!!scheduleError(index, 'close_at')" />
                                    <TextInput
                                        :id="`close_at_${index}`"
                                        :invalid="!!scheduleError(index, 'close_at')"
                                        type="time"
                                        class="mt-1 block w-full"
                                        v-model="day.close_at"
                                    />
                                    <InputError class="mt-1" :message="scheduleError(index, 'close_at')" />
                                </div>

                                <label class="flex items-center gap-2 pb-2 text-sm text-gray-700">
                                    <input type="checkbox" v-model="day.has_break" :class="checkbox" />
                                    {{ $t('Перерыв') }}
                                </label>
                            </template>
                        </div>

                        <div
                            v-if="!day.is_closed && day.has_break"
                            class="mt-4 flex flex-wrap items-end gap-4 border-t border-gray-100 pt-4"
                        >
                            <div class="w-36">
                                <InputLabel :for="`break_start_at_${index}`" :value="$t('Начало перерыва')" required :invalid="!!scheduleError(index, 'break_start_at')" />
                                <TextInput
                                    :id="`break_start_at_${index}`"
                                        :invalid="!!scheduleError(index, 'break_start_at')"
                                    type="time"
                                    class="mt-1 block w-full"
                                    v-model="day.break_start_at"
                                />
                                <InputError class="mt-1" :message="scheduleError(index, 'break_start_at')" />
                            </div>

                            <div class="w-36">
                                <InputLabel :for="`break_end_at_${index}`" :value="$t('Конец перерыва')" required :invalid="!!scheduleError(index, 'break_end_at')" />
                                <TextInput
                                    :id="`break_end_at_${index}`"
                                        :invalid="!!scheduleError(index, 'break_end_at')"
                                    type="time"
                                    class="mt-1 block w-full"
                                    v-model="day.break_end_at"
                                />
                                <InputError class="mt-1" :message="scheduleError(index, 'break_end_at')" />
                            </div>
                        </div>

                        <p v-if="day.is_closed" :class="[hint, 'mt-2']">{{ $t('В этот день компания не работает.') }}</p>
                    </div>
                </div>
            </section>

            <section id="exceptions" :class="formCard">
                <div>
                    <h2 :class="[formTitle, errors.exceptions ? 'text-red-600' : 'text-gray-900']">{{ $t('Нерабочие и праздничные дни') }}</h2>
                    <p :class="hint">{{ $t('В эти даты компания закрыта, даже если день недели рабочий.') }}</p>
                </div>

                <InputError :message="errors.exceptions" />

                <p v-if="form.exceptions.length === 0" class="text-sm text-gray-500">
                    {{ $t('Исключений пока нет.') }}
                </p>

                <div class="space-y-3">
                    <div
                        v-for="(exception, index) in form.exceptions"
                        :key="index"
                        class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200/80 bg-gray-50/50 p-4"
                    >
                        <div class="w-48">
                            <InputLabel :for="`exception_date_${index}`" :value="$t('Дата')" required :invalid="!!exceptionError(index, 'date')" />
                            <TextInput
                                :id="`exception_date_${index}`"
                                        :invalid="!!exceptionError(index, 'date')"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="exception.date"
                            />
                            <InputError class="mt-1" :message="exceptionError(index, 'date')" />
                        </div>

                        <div class="min-w-48 flex-1">
                            <InputLabel :for="`exception_name_${index}`" :value="$t('Название (необязательно)')" :invalid="!!exceptionError(index, 'name')" />
                            <TextInput
                                :id="`exception_name_${index}`"
                                        :invalid="!!exceptionError(index, 'name')"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="exception.name"
                                :placeholder="$t('Например, Новый год')"
                            />
                            <InputError class="mt-1" :message="exceptionError(index, 'name')" />
                        </div>

                        <button type="button" :class="[smallButton, 'text-red-600']" @click="removeException(index)">
                            {{ $t('Удалить') }}
                        </button>
                    </div>
                </div>

                <button type="button" :class="addButton" @click="addException">{{ $t('+ Добавить день') }}</button>
            </section>

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" />
        </form>
    </AccountLayout>
</template>
