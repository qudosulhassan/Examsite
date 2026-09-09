<style>
    /* Fullscreen editor styling */
    .tiptap-container.tiptap-fullscreen-active {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
        background: white !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .tiptap-container.tiptap-fullscreen-active .editor-element,
    .tiptap-container.tiptap-fullscreen-active .source-editor-element {
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        min-height: 0 !important;
        max-height: none !important;
    }
    /* Prose styling inside editor */
    .tiptap-container .ProseMirror {
        outline: none;
        min-height: 480px;
        padding: 1.5rem;
    }
    .tiptap-container .ProseMirror table {
        border-collapse: collapse;
        width: 100%;
        margin: 1.5rem 0;
        table-layout: fixed;
    }
    .tiptap-container .ProseMirror th,
    .tiptap-container .ProseMirror td {
        border: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
        vertical-align: top;
        position: relative;
    }
    .tiptap-container .ProseMirror th {
        background-color: #0A1628;
        color: white;
        font-weight: 700;
    }
    .tiptap-container .ProseMirror img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap {
        margin: 1.5rem 0;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-center {
        display: flex;
        justify-content: center;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-left {
        display: flex;
        justify-content: flex-start;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-right {
        display: flex;
        justify-content: flex-end;
    }
    .tiptap-container .ProseMirror pre {
        background-color: #0A1628;
        color: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.875rem;
        margin: 1.5rem 0;
        overflow-x: auto;
    }
</style>
