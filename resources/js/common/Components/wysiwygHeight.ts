import { reactive } from 'vue';

/**
 * Height of the resizable WYSIWYG editors, shared by all editors with the same key.
 * A module-level store (not a ref inside the component) keeps every language tab in sync.
 */
export const editorHeights = reactive<Record<string, number>>({});
