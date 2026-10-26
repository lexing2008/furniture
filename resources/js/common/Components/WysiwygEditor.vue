<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { editorHeights } from './wysiwygHeight';

/**
 * WYSIWYG editor (TipTap, MIT). It only produces the tags the server keeps (see config/purify.php):
 * paragraphs, h3-h5, bold, italic, lists, tables; the "seo" profile also has line breaks.
 * The server cleans the HTML anyway, so the editor is not a security boundary.
 */
const props = withDefaults(
    defineProps<{
        modelValue: string;
        profile?: 'seo' | 'company';
        invalid?: boolean;
        // The editor gets a bottom edge that can be dragged to change its height
        resizable?: boolean;
        // The height is remembered between visits under this localStorage key
        heightKey?: string;
    }>(),
    { profile: 'seo', invalid: false, resizable: false, heightKey: 'wysiwyg-height' },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// An empty editor is "<p></p>", the form wants ""
const htmlOf = (editor: { isEmpty: boolean; getHTML: () => string }) => (editor.isEmpty ? '' : editor.getHTML());

// Bumped on every transaction: the toolbar reads it to refresh the active buttons
const tick = ref(0);

// Height of a resizable editor: 10rem by default, kept in localStorage.
// Editors with the same heightKey share it, so all SEO blocks on a page resize together.
const MIN_HEIGHT = 160;
const MAX_HEIGHT = 900;
const resizing = ref(false);

let resizeStartY = 0;
let resizeStartHeight = 0;

const clampHeight = (value: number) => Math.min(MAX_HEIGHT, Math.max(MIN_HEIGHT, Math.round(value)));

const height = computed({
    get: () => editorHeights[props.heightKey] ?? MIN_HEIGHT,
    set: (value: number) => {
        editorHeights[props.heightKey] = clampHeight(value);
    },
});

const saveHeight = () => {
    if (props.resizable) {
        localStorage.setItem(props.heightKey, String(height.value));
    }
};

onMounted(() => {
    if (!props.resizable) {
        return;
    }

    const stored = Number(localStorage.getItem(props.heightKey));

    if (Number.isFinite(stored) && stored > 0) {
        height.value = clampHeight(stored);
    }
});

const resizeMove = (event: PointerEvent) => {
    if (resizing.value) {
        height.value = clampHeight(resizeStartHeight + event.clientY - resizeStartY);
    }
};

const stopResize = () => {
    if (!resizing.value) {
        return;
    }

    resizing.value = false;
    window.removeEventListener('pointermove', resizeMove);
    window.removeEventListener('pointerup', stopResize);
    window.removeEventListener('pointercancel', stopResize);
    document.body.classList.remove('select-none');
    saveHeight();
};

const startResize = (event: PointerEvent) => {
    if (!props.resizable || event.button !== 0) {
        return;
    }

    resizing.value = true;
    resizeStartY = event.clientY;
    resizeStartHeight = height.value;
    window.addEventListener('pointermove', resizeMove);
    window.addEventListener('pointerup', stopResize);
    window.addEventListener('pointercancel', stopResize);
    document.body.classList.add('select-none');
};

const nudgeHeight = (delta: number) => {
    height.value = clampHeight(height.value + delta);
    saveHeight();
};

const resetHeight = () => {
    height.value = MIN_HEIGHT;
    saveHeight();
};

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', resizeMove);
    window.removeEventListener('pointerup', stopResize);
    window.removeEventListener('pointercancel', stopResize);
    document.body.classList.remove('select-none');
});

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [3, 4, 5] },
            blockquote: false,
            code: false,
            codeBlock: false,
            horizontalRule: false,
            strike: false,
            underline: false,
            link: false,
            ...(props.profile === 'seo' ? {} : { hardBreak: false }),
        }),
        Table.configure({ resizable: false }),
        TableRow,
        TableHeader,
        TableCell,
    ],
    editorProps: {
        attributes: { class: 'rich-text min-h-40 px-3 py-2 focus:outline-hidden' },
    },
    onUpdate: ({ editor }) => emit('update:modelValue', htmlOf(editor)),
    onTransaction: () => {
        tick.value++;
    },
});

// The form can change the value from outside (reset, another language tab)
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== htmlOf(editor.value)) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
);

const active = (name: string, attributes?: Record<string, unknown>) => {
    // eslint-disable-next-line @typescript-eslint/no-unused-expressions
    tick.value;

    return editor.value?.isActive(name, attributes) ?? false;
};

const inTable = () => active('table');

const button =
    'rounded border px-2 py-1 text-xs hover:bg-gray-100 disabled:opacity-30';
const buttonClass = (isActive: boolean) =>
    [button, isActive ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-700'];
</script>

<template>
    <div
        class="rounded-md border bg-white shadow-sm"
        :class="invalid ? 'border-red-400' : 'border-gray-300'"
    >
        <div v-if="editor" class="flex flex-wrap gap-1 border-b border-gray-200 bg-gray-50 p-2">
            <button type="button" :class="buttonClass(active('bold'))" :title="$t('Жирный')" @click="editor.chain().focus().toggleBold().run()">
                <b>B</b>
            </button>
            <button type="button" :class="buttonClass(active('italic'))" :title="$t('Курсив')" @click="editor.chain().focus().toggleItalic().run()">
                <i>I</i>
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(active('paragraph'))" :title="$t('Обычный текст')" @click="editor.chain().focus().setParagraph().run()">
                ¶
            </button>
            <button
                v-for="level in [3, 4, 5] as const"
                :key="level"
                type="button"
                :class="buttonClass(active('heading', { level }))"
                :title="$t('Заголовок :level', { level })"
                @click="editor.chain().focus().toggleHeading({ level }).run()"
            >
                H{{ level }}
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(active('bulletList'))" :title="$t('Маркированный список')" @click="editor.chain().focus().toggleBulletList().run()">
                {{ $t('• Список') }}
            </button>
            <button type="button" :class="buttonClass(active('orderedList'))" :title="$t('Нумерованный список')" @click="editor.chain().focus().toggleOrderedList().run()">
                {{ $t('1. Список') }}
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button
                type="button"
                :class="buttonClass(inTable())"
                :title="$t('Вставить таблицу')"
                :disabled="inTable()"
                @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()"
            >
                {{ $t('Таблица') }}
            </button>
            <template v-if="inTable()">
                <button type="button" :class="buttonClass(false)" :title="$t('Добавить столбец справа')" @click="editor.chain().focus().addColumnAfter().run()">{{ $t('+ столбец') }}</button>
                <button type="button" :class="buttonClass(false)" :title="$t('Удалить столбец')" @click="editor.chain().focus().deleteColumn().run()">{{ $t('− столбец') }}</button>
                <button type="button" :class="buttonClass(false)" :title="$t('Добавить строку снизу')" @click="editor.chain().focus().addRowAfter().run()">{{ $t('+ строка') }}</button>
                <button type="button" :class="buttonClass(false)" :title="$t('Удалить строку')" @click="editor.chain().focus().deleteRow().run()">{{ $t('− строка') }}</button>
                <button type="button" :class="[buttonClass(false), 'text-red-600']" :title="$t('Удалить таблицу')" @click="editor.chain().focus().deleteTable().run()">{{ $t('× таблица') }}</button>
            </template>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(false)" :title="$t('Отменить')" :disabled="!editor.can().undo()" @click="editor.chain().focus().undo().run()">↶</button>
            <button type="button" :class="buttonClass(false)" :title="$t('Повторить')" :disabled="!editor.can().redo()" @click="editor.chain().focus().redo().run()">↷</button>
        </div>

        <div
            class="wysiwyg-content"
            :class="{ resizable: resizable, 'overflow-y-auto': resizable }"
            :style="resizable ? { height: `${height}px` } : undefined"
        >
            <EditorContent :editor="editor" />
        </div>

        <!-- Нижняя кромка: тянем вверх/вниз, чтобы изменить высоту -->
        <div
            v-if="resizable"
            class="group relative flex h-5 cursor-ns-resize touch-none rounded-b-md select-none items-center justify-center border-t border-gray-200 bg-gray-50"
            role="separator"
            aria-orientation="horizontal"
            :aria-label="$t('Высота редактора')"
            :aria-valuenow="height"
            :aria-valuemin="MIN_HEIGHT"
            :aria-valuemax="MAX_HEIGHT"
            tabindex="0"
            :title="$t('Потяните вверх или вниз, чтобы изменить высоту. Двойной щелчок — сбросить.')"
            @pointerdown.prevent="startResize"
            @dblclick="resetHeight"
            @keydown.up.prevent="nudgeHeight(-20)"
            @keydown.down.prevent="nudgeHeight(20)"
        >
            <span class="h-1 w-10 rounded-full bg-gray-300 transition-colors group-hover:bg-gray-400"></span>
            <span class="absolute right-2 text-[10px] leading-none text-gray-400">Высота: {{ $t('Высота: :height px', { height }) }}</span>
        </div>
    </div>
</template>

<style scoped>
.wysiwyg-content.resizable :deep(.ProseMirror) {
    min-height: 100%;
}
</style>
