<template>
    <div>
        <div class="name-row mb-3">
            <v-text-field
                :model-value="program.name"
                :label="$t('Program name')"
                :error-messages="nameMessage ? [nameMessage] : []"
                variant="outlined"
                density="comfortable"
                maxlength="40"
                hide-details="auto"
                class="name-field"
                @update:model-value="emit('update-name', $event)"
            />
            <v-btn
                variant="tonal"
                color="primary"
                prepend-icon="mdi-book-open-variant"
                class="docs-btn"
                :text="$t('Docs')"
                @click="docsOpen = true"
            />
        </div>
        <jardin-botanique-docs v-model="docsOpen" />
        <div v-if="loadError" class="text-error mb-2">{{ loadError }}</div>
        <div v-else ref="host" class="cm-host"></div>
        <div v-if="tooLong" class="text-error text-body-2 mt-2">{{ $t('Code is limited to 50 000 characters.') }}</div>
        <div class="console mt-3">
            <div class="console-bar">
                <span>{{ $t('Console') }}</span>
                <v-btn size="small" variant="text" :text="$t('Clear console')" @click="emit('clear-console')" />
            </div>
            <div ref="logEl" class="console-log" role="log" aria-live="polite">
                <div v-for="(line, index) in consoleLines" :key="index" :class="line.level">{{ line.text }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { MAX_CODE } from './jardin-botanique-storage.js';

const props = defineProps({
    program: { type: Object, required: true },
    nameMessage: { type: String, default: '' },
    consoleLines: { type: Array, default: () => [] },
    dark: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
});

const emit = defineEmits(['update-name', 'update-code', 'clear-console']);

const host = ref(null);
const logEl = ref(null);
const tooLong = ref(false);
const docsOpen = ref(false);
const loadError = ref('');
let view = null;
let editorModules = null;

function extensionList(modules, dark) {
    const { basicSetup, javascript, oneDark, EditorView, EditorState } = modules;
    return [
        basicSetup,
        javascript(),
        dark ? oneDark : [],
        EditorView.theme({
            '&': { height: '100%' },
            '.cm-scroller': { overflow: 'auto', fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace' },
        }),
        EditorState.transactionFilter.of((transaction) => {
            if (!transaction.docChanged) return transaction;
            if (transaction.newDoc.length > MAX_CODE) {
                tooLong.value = true;
                return [];
            }
            tooLong.value = false;
            return transaction;
        }),
        EditorView.updateListener.of((update) => {
            if (update.docChanged) emit('update-code', update.state.doc.toString());
        }),
    ];
}

function buildState(modules, doc, selection) {
    return modules.EditorState.create({
        doc,
        selection,
        extensions: extensionList(modules, props.dark),
    });
}

let alive = true;

async function mount() {
    try {
        const [cm, lang, darkTheme, state] = await Promise.all([
            import('codemirror'),
            import('@codemirror/lang-javascript'),
            import('@codemirror/theme-one-dark'),
            import('@codemirror/state'),
        ]);
        if (!alive || !host.value) return;
        editorModules = {
            EditorView: cm.EditorView,
            basicSetup: cm.basicSetup,
            javascript: lang.javascript,
            oneDark: darkTheme.oneDark,
            EditorState: state.EditorState,
        };
        view = new editorModules.EditorView({
            state: buildState(editorModules, props.program.code),
            parent: host.value,
        });
        view.requestMeasure();
    } catch (error) {
        if (alive) loadError.value = error?.message || String(error);
    }
}

onMounted(mount);

watch(() => props.dark, () => {
    if (!view || !editorModules) return;
    const doc = view.state.doc.toString();
    const selection = view.state.selection;
    view.setState(buildState(editorModules, doc, selection));
});

watch(() => props.active, (active) => {
    if (active) view?.requestMeasure();
});

watch(() => props.consoleLines.length, async () => {
    if (!props.active) return;
    await nextTick();
    if (logEl.value) logEl.value.scrollTop = logEl.value.scrollHeight;
});

onUnmounted(() => {
    alive = false;
    view?.destroy();
    view = null;
});
</script>

<style scoped>
.name-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.name-field { flex: 1 1 240px; }
.docs-btn { margin-top: 6px; }
.cm-host {
    height: 420px;
    overflow: hidden;
    border: 1px solid #c4b49a;
    border-radius: 8px;
}
.cm-host :deep(.cm-editor) { height: 100%; }
.console {
    border: 1px solid #c4b49a;
    border-radius: 8px;
    overflow: hidden;
    background: #1e1e1e;
    color: #e8e8e8;
}
.console-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 8px 0 12px;
    background: #2a2a2a;
    font-size: 13px;
}
.console-bar :deep(.v-btn) { color: #e8e8e8; }
.console-log {
    max-height: 180px;
    overflow: auto;
    margin: 0;
    padding: 8px 12px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 12px;
    line-height: 1.45;
    white-space: pre-wrap;
    word-break: break-word;
}
.header { color: #9ad1ff; font-weight: 700; }
.warn { color: #ffb74d; }
.error { color: #ff8a80; }
</style>
