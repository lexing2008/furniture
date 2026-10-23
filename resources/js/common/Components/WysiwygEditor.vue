<script setup lang="ts">
import StarterKit from '@tiptap/starter-kit';
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { ref, watch } from 'vue';

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
    }>(),
    { profile: 'seo', invalid: false },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// An empty editor is "<p></p>", the form wants ""
const htmlOf = (editor: { isEmpty: boolean; getHTML: () => string }) => (editor.isEmpty ? '' : editor.getHTML());

// Bumped on every transaction: the toolbar reads it to refresh the active buttons
const tick = ref(0);

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
            <button type="button" :class="buttonClass(active('bold'))" title="Жирный" @click="editor.chain().focus().toggleBold().run()">
                <b>B</b>
            </button>
            <button type="button" :class="buttonClass(active('italic'))" title="Курсив" @click="editor.chain().focus().toggleItalic().run()">
                <i>I</i>
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(active('paragraph'))" title="Обычный текст" @click="editor.chain().focus().setParagraph().run()">
                ¶
            </button>
            <button
                v-for="level in [3, 4, 5] as const"
                :key="level"
                type="button"
                :class="buttonClass(active('heading', { level }))"
                :title="`Заголовок ${level}`"
                @click="editor.chain().focus().toggleHeading({ level }).run()"
            >
                H{{ level }}
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(active('bulletList'))" title="Маркированный список" @click="editor.chain().focus().toggleBulletList().run()">
                • Список
            </button>
            <button type="button" :class="buttonClass(active('orderedList'))" title="Нумерованный список" @click="editor.chain().focus().toggleOrderedList().run()">
                1. Список
            </button>

            <span class="mx-1 w-px bg-gray-300" />

            <button
                type="button"
                :class="buttonClass(inTable())"
                title="Вставить таблицу"
                :disabled="inTable()"
                @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()"
            >
                Таблица
            </button>
            <template v-if="inTable()">
                <button type="button" :class="buttonClass(false)" title="Добавить столбец справа" @click="editor.chain().focus().addColumnAfter().run()">+ столбец</button>
                <button type="button" :class="buttonClass(false)" title="Удалить столбец" @click="editor.chain().focus().deleteColumn().run()">− столбец</button>
                <button type="button" :class="buttonClass(false)" title="Добавить строку снизу" @click="editor.chain().focus().addRowAfter().run()">+ строка</button>
                <button type="button" :class="buttonClass(false)" title="Удалить строку" @click="editor.chain().focus().deleteRow().run()">− строка</button>
                <button type="button" :class="[buttonClass(false), 'text-red-600']" title="Удалить таблицу" @click="editor.chain().focus().deleteTable().run()">× таблица</button>
            </template>

            <span class="mx-1 w-px bg-gray-300" />

            <button type="button" :class="buttonClass(false)" title="Отменить" :disabled="!editor.can().undo()" @click="editor.chain().focus().undo().run()">↶</button>
            <button type="button" :class="buttonClass(false)" title="Повторить" :disabled="!editor.can().redo()" @click="editor.chain().focus().redo().run()">↷</button>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>
