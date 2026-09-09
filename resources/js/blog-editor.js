import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import { TextStyle } from '@tiptap/extension-text-style';
import Color from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import TextAlign from '@tiptap/extension-text-align';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import CharacterCount from '@tiptap/extension-character-count';
import Placeholder from '@tiptap/extension-placeholder';
import { createLowlight, common } from 'lowlight';

// Initialize lowlight with common programming languages
const lowlight = createLowlight(common);

// Custom Image extension supporting alt, title, width, and alignment
const CustomImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            alt: {
                default: '',
            },
            title: {
                default: '',
            },
            width: {
                default: '100%',
                renderHTML: attributes => {
                    if (!attributes.width) return {};
                    return {
                        style: `width: ${attributes.width}; max-width: 100%; height: auto;`,
                    };
                },
            },
            alignment: {
                default: 'center',
                renderHTML: attributes => {
                    const align = attributes.alignment || 'center';
                    return {
                        'data-align': align,
                        class: `tiptap-image-wrap align-${align}`,
                    };
                },
            },
        };
    },
});

// Simple HTML Pretty-Printer for Source Mode
function formatHtml(html) {
    let tab = '  ';
    let result = '';
    let indent = '';

    html.split(/>\s*</).forEach(element => {
        if (element.match(/^\/\w/)) {
            indent = indent.substring(tab.length);
        }

        result += indent + '<' + element + '>\r\n';

        if (element.match(/^<?\w[^>]*[^\/]$/) && !element.startsWith("input") && !element.startsWith("img") && !element.startsWith("br") && !element.startsWith("hr")) {
            indent += tab;
        }
    });

    return result.substring(1, result.length - 3);
}

// Initialize Blog Tiptap Editor
export function initBlogEditor(container) {
    if (!container || container.dataset.tiptapInitialized) return;
    // Only initialize full rich editors (avoid capturing simple textareas or unequipped containers)
    if (!container.classList.contains('tiptap-full-editor') && !container.querySelector('.source-editor-element') && container.dataset.editorType !== 'full') {
        return;
    }
    container.dataset.tiptapInitialized = 'true';

    const editorEl = container.querySelector('.editor-element');
    const inputEl = container.querySelector('.content-input');
    const sourceEl = container.querySelector('.source-editor-element');
    const isSourceModeActive = { value: false };

    let initialHtml = '';
    try {
        initialHtml = atob(container.getAttribute('data-content') || '');
    } catch (e) {
        initialHtml = container.getAttribute('data-content') || '';
    }
    if (!initialHtml && inputEl) {
        initialHtml = inputEl.value || '';
    }

    const editor = new Editor({
        element: editorEl,
        extensions: [
            StarterKit.configure({
                heading: { levels: [1, 2, 3, 4, 5, 6] },
                codeBlock: false, // Replaced by CodeBlockLowlight
            }),
            Underline,
            TextStyle,
            Color,
            Highlight.configure({ multicolor: true }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    class: 'text-cyan underline hover:text-blue-600 transition-colors',
                },
            }),
            CustomImage,
            Table.configure({
                resizable: true,
                HTMLAttributes: {
                    class: 'tiptap-table border-collapse my-6 w-full text-sm text-left border border-gray-300 rounded-lg overflow-hidden shadow-sm',
                },
            }),
            TableRow,
            TableHeader.configure({
                HTMLAttributes: {
                    class: 'bg-navy text-white font-bold p-3 border border-gray-300',
                },
            }),
            TableCell.configure({
                HTMLAttributes: {
                    class: 'p-3 border border-gray-200 align-top',
                },
            }),
            CodeBlockLowlight.configure({
                lowlight,
                HTMLAttributes: {
                    class: 'tiptap-code-block bg-[#0A1628] text-gray-100 p-4 rounded-xl font-mono text-sm my-6 overflow-x-auto border border-gray-800 shadow-inner',
                },
            }),
            Subscript,
            Superscript,
            CharacterCount,
            Placeholder.configure({
                placeholder: container.dataset.placeholder || 'Draft your comprehensive, high-ranking IT certification guide or article...',
            }),
        ],
        content: initialHtml,
        editorProps: {
            attributes: {
                class: 'prose prose-slate max-w-none w-full focus:outline-none min-h-[450px] p-6 text-gray-800 leading-relaxed',
            },
            transformPastedHTML(html) {
                // Strip Microsoft Office XML/MSO artifacts while preserving semantic tags
                let clean = html.replace(/<!--\[if[\s\S]*?<!\[endif\]-->/gi, '');
                clean = clean.replace(/<o:p[\s\S]*?<\/o:p>/gi, '');
                clean = clean.replace(/class="Mso[\s\S]*?"/gi, '');
                clean = clean.replace(/style="mso-[\s\S]*?"/gi, '');
                return clean;
            },
        },
        onUpdate: ({ editor }) => {
            const html = editor.getHTML();
            if (inputEl) inputEl.value = html;
            updateStats();
            updateActiveStates();
            validateHeadingHierarchy();
        },
        onSelectionUpdate: () => {
            updateActiveStates();
        },
    });

    // Sync initial state to input
    if (inputEl) {
        inputEl.value = editor.getHTML();
    }

    /* =========================================================================
       TOOLBAR CONTROLS BINDING
       ========================================================================= */

    // History
    bindClick('.btn-undo', () => editor.chain().focus().undo().run());
    bindClick('.btn-redo', () => editor.chain().focus().redo().run());

    // Headings Dropdown / Buttons
    const blockSelect = container.querySelector('.select-block-format');
    if (blockSelect) {
        blockSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val === 'p') editor.chain().focus().setParagraph().run();
            else if (val.startsWith('h')) {
                const level = parseInt(val.replace('h', ''));
                editor.chain().focus().toggleHeading({ level }).run();
            }
        });
    }

    // Inline Formatting
    bindClick('.btn-bold', () => editor.chain().focus().toggleBold().run());
    bindClick('.btn-italic', () => editor.chain().focus().toggleItalic().run());
    bindClick('.btn-underline', () => editor.chain().focus().toggleUnderline().run());
    bindClick('.btn-strike', () => editor.chain().focus().toggleStrike().run());
    bindClick('.btn-code', () => editor.chain().focus().toggleCode().run());
    bindClick('.btn-superscript', () => editor.chain().focus().toggleSuperscript().run());
    bindClick('.btn-subscript', () => editor.chain().focus().toggleSubscript().run());
    bindClick('.btn-clear-format', () => editor.chain().focus().clearNodes().unsetAllMarks().run());

    // Color & Highlight
    const colorPicker = container.querySelector('.input-text-color');
    if (colorPicker) {
        colorPicker.addEventListener('input', (e) => {
            editor.chain().focus().setColor(e.target.value).run();
        });
    }
    container.querySelectorAll('.btn-color-preset').forEach(btn => {
        btn.addEventListener('click', () => {
            const col = btn.getAttribute('data-color');
            if (col) editor.chain().focus().setColor(col).run();
            else editor.chain().focus().unsetColor().run();
        });
    });

    container.querySelectorAll('.btn-highlight-preset').forEach(btn => {
        btn.addEventListener('click', () => {
            const col = btn.getAttribute('data-color');
            if (col) editor.chain().focus().toggleHighlight({ color: col }).run();
            else editor.chain().focus().unsetHighlight().run();
        });
    });

    // Alignment
    bindClick('.btn-align-left', () => editor.chain().focus().setTextAlign('left').run());
    bindClick('.btn-align-center', () => editor.chain().focus().setTextAlign('center').run());
    bindClick('.btn-align-right', () => editor.chain().focus().setTextAlign('right').run());
    bindClick('.btn-align-justify', () => editor.chain().focus().setTextAlign('justify').run());

    // Lists & Blocks
    bindClick('.btn-bullet-list', () => editor.chain().focus().toggleBulletList().run());
    bindClick('.btn-ordered-list', () => editor.chain().focus().toggleOrderedList().run());
    bindClick('.btn-blockquote', () => editor.chain().focus().toggleBlockquote().run());
    bindClick('.btn-hr', () => editor.chain().focus().setHorizontalRule().run());

    // Code Block with Language
    const codeLangSelect = container.querySelector('.select-code-lang');
    bindClick('.btn-code-block', () => {
        const lang = codeLangSelect ? codeLangSelect.value : 'javascript';
        editor.chain().focus().toggleCodeBlock({ language: lang }).run();
    });
    if (codeLangSelect) {
        codeLangSelect.addEventListener('change', (e) => {
            if (editor.isActive('codeBlock')) {
                editor.chain().focus().updateAttributes('codeBlock', { language: e.target.value }).run();
            }
        });
    }

    // Link Modal
    const linkModal = container.querySelector('.tiptap-link-modal');
    const linkUrlInput = container.querySelector('.link-modal-url');
    const linkTextInput = container.querySelector('.link-modal-text');
    const linkTargetCheck = container.querySelector('.link-modal-target');

    bindClick('.btn-link', () => {
        const { href, target } = editor.getAttributes('link');
        const { from, to } = editor.state.selection;
        const selectedText = editor.state.doc.textBetween(from, to, ' ');

        if (linkUrlInput) linkUrlInput.value = href || 'https://';
        if (linkTextInput) linkTextInput.value = selectedText || '';
        if (linkTargetCheck) linkTargetCheck.checked = target === '_blank' || !target;

        if (linkModal) linkModal.classList.remove('hidden');
    });

    bindClick('.btn-link-save', () => {
        const url = linkUrlInput ? linkUrlInput.value.trim() : '';
        const text = linkTextInput ? linkTextInput.value.trim() : '';
        const target = (linkTargetCheck && linkTargetCheck.checked) ? '_blank' : null;

        if (!url || url === 'https://') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
        } else {
            if (text && editor.state.selection.empty) {
                editor.chain().focus().insertContent(`<a href="${url}" target="${target || ''}">${text}</a>`).run();
            } else {
                editor.chain().focus().extendMarkRange('link').setLink({ href: url, target }).run();
            }
        }
        if (linkModal) linkModal.classList.add('hidden');
    });

    bindClick('.btn-link-unlink', () => {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();
        if (linkModal) linkModal.classList.add('hidden');
    });

    bindClick('.btn-link-cancel', () => {
        if (linkModal) linkModal.classList.add('hidden');
    });

    // Image Modal
    const imageModal = container.querySelector('.tiptap-image-modal');
    const imageFileInput = container.querySelector('.image-modal-file');
    const imageUrlInput = container.querySelector('.image-modal-url');
    const imageAltInput = container.querySelector('.image-modal-alt');
    const imageTitleInput = container.querySelector('.image-modal-title');
    const imageAlignSelect = container.querySelector('.image-modal-align');
    const imageWidthSelect = container.querySelector('.image-modal-width');
    const imageUploadProgress = container.querySelector('.image-modal-progress');

    bindClick('.btn-image', () => {
        if (imageModal) imageModal.classList.remove('hidden');
    });

    bindClick('.btn-image-save', async () => {
        let src = imageUrlInput ? imageUrlInput.value.trim() : '';
        const file = imageFileInput && imageFileInput.files ? imageFileInput.files[0] : null;

        if (file) {
            // Upload to server
            if (imageUploadProgress) imageUploadProgress.classList.remove('hidden');
            const formData = new FormData();
            formData.append('image', file);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch('/admin/blog/upload-image', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await res.json();
                if (data.success && data.url) {
                    src = data.url;
                } else {
                    alert(data.message || 'Image upload failed.');
                    if (imageUploadProgress) imageUploadProgress.classList.add('hidden');
                    return;
                }
            } catch (err) {
                alert('Upload error: ' + err.message);
                if (imageUploadProgress) imageUploadProgress.classList.add('hidden');
                return;
            }
            if (imageUploadProgress) imageUploadProgress.classList.add('hidden');
        }

        if (src) {
            const alt = imageAltInput ? imageAltInput.value.trim() : '';
            const title = imageTitleInput ? imageTitleInput.value.trim() : '';
            const alignment = imageAlignSelect ? imageAlignSelect.value : 'center';
            const width = imageWidthSelect ? imageWidthSelect.value : '100%';

            editor.chain().focus().setImage({ src, alt, title, width, alignment }).run();
            if (imageFileInput) imageFileInput.value = '';
            if (imageUrlInput) imageUrlInput.value = '';
            if (imageAltInput) imageAltInput.value = '';
            if (imageTitleInput) imageTitleInput.value = '';
            if (imageModal) imageModal.classList.add('hidden');
        }
    });

    bindClick('.btn-image-cancel', () => {
        if (imageModal) imageModal.classList.add('hidden');
    });

    // Table Controls
    bindClick('.btn-table-insert', () => {
        const rows = parseInt(prompt('Number of rows:', '3')) || 3;
        const cols = parseInt(prompt('Number of columns:', '3')) || 3;
        editor.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
    });
    bindClick('.btn-table-add-row-before', () => editor.chain().focus().addRowBefore().run());
    bindClick('.btn-table-add-row-after', () => editor.chain().focus().addRowAfter().run());
    bindClick('.btn-table-del-row', () => editor.chain().focus().deleteRow().run());
    bindClick('.btn-table-add-col-before', () => editor.chain().focus().addColumnBefore().run());
    bindClick('.btn-table-add-col-after', () => editor.chain().focus().addColumnAfter().run());
    bindClick('.btn-table-del-col', () => editor.chain().focus().deleteColumn().run());
    bindClick('.btn-table-merge-cells', () => editor.chain().focus().mergeCells().run());
    bindClick('.btn-table-split-cell', () => editor.chain().focus().splitCell().run());
    bindClick('.btn-table-toggle-header', () => editor.chain().focus().toggleHeaderRow().run());
    bindClick('.btn-table-delete', () => editor.chain().focus().deleteTable().run());

    // Special Characters Modal
    const specialCharsModal = container.querySelector('.tiptap-special-chars-modal');
    bindClick('.btn-special-chars', () => {
        if (specialCharsModal) specialCharsModal.classList.remove('hidden');
    });
    container.querySelectorAll('.btn-insert-char').forEach(btn => {
        btn.addEventListener('click', () => {
            const char = btn.getAttribute('data-char');
            if (char) editor.chain().focus().insertContent(char).run();
            if (specialCharsModal) specialCharsModal.classList.add('hidden');
        });
    });
    bindClick('.btn-special-chars-cancel', () => {
        if (specialCharsModal) specialCharsModal.classList.add('hidden');
    });

    // Find & Replace Panel
    const findReplaceBar = container.querySelector('.tiptap-find-replace-bar');
    const findInput = container.querySelector('.find-input');
    const replaceInput = container.querySelector('.replace-input');

    bindClick('.btn-find-replace', () => {
        if (findReplaceBar) {
            findReplaceBar.classList.toggle('hidden');
            if (!findReplaceBar.classList.contains('hidden') && findInput) {
                findInput.focus();
            }
        }
    });

    bindClick('.btn-do-replace-all', () => {
        const findText = findInput ? findInput.value : '';
        const replaceText = replaceInput ? replaceInput.value : '';
        if (!findText) return;

        const html = editor.getHTML();
        const escaped = findText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(escaped, 'g');
        const newHtml = html.replace(regex, replaceText);
        editor.commands.setContent(newHtml, false);
        if (inputEl) inputEl.value = newHtml;
        alert(`Replaced all occurrences of "${findText}".`);
    });

    // Fullscreen Mode
    bindClick('.btn-fullscreen', () => {
        container.classList.toggle('tiptap-fullscreen-active');
        const isFull = container.classList.contains('tiptap-fullscreen-active');
        const icon = container.querySelector('.btn-fullscreen svg');
        document.body.classList.toggle('overflow-hidden', isFull);
    });

    // Keyboard Shortcuts Dialog
    const shortcutsModal = container.querySelector('.tiptap-shortcuts-modal');
    bindClick('.btn-shortcuts', () => {
        if (shortcutsModal) shortcutsModal.classList.remove('hidden');
    });
    bindClick('.btn-shortcuts-close', () => {
        if (shortcutsModal) shortcutsModal.classList.add('hidden');
    });

    // HTML / Source Code Mode Toggle
    const btnSourceMode = container.querySelector('.btn-source-mode');
    if (btnSourceMode && sourceEl) {
        btnSourceMode.addEventListener('click', () => {
            isSourceModeActive.value = !isSourceModeActive.value;

            if (isSourceModeActive.value) {
                // Switch to HTML Source mode
                const rawHtml = editor.getHTML();
                sourceEl.value = formatHtml(rawHtml);
                editorEl.classList.add('hidden');
                sourceEl.classList.remove('hidden');
                btnSourceMode.classList.add('bg-navy', 'text-white', 'border-navy');
                btnSourceMode.innerHTML = `<svg class="w-3.5 h-3.5 mr-1 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Visual Editor`;
            } else {
                // Switch back to Visual mode
                const updatedHtml = sourceEl.value;
                editor.commands.setContent(updatedHtml, false);
                if (inputEl) inputEl.value = updatedHtml;
                sourceEl.classList.add('hidden');
                editorEl.classList.remove('hidden');
                btnSourceMode.classList.remove('bg-navy', 'text-white', 'border-navy');
                btnSourceMode.innerHTML = `<svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg> HTML Source`;
                updateActiveStates();
                updateStats();
                validateHeadingHierarchy();
            }
        });

        // If in source mode and user types, keep hidden input in sync
        sourceEl.addEventListener('input', () => {
            if (inputEl) inputEl.value = sourceEl.value;
        });
    }

    // Live Content Preview Drawer / Modal
    const previewModal = container.querySelector('.tiptap-preview-modal');
    const previewContainer = container.querySelector('.tiptap-preview-content');
    bindClick('.btn-live-preview', () => {
        if (previewModal && previewContainer) {
            previewContainer.innerHTML = editor.getHTML();
            previewModal.classList.remove('hidden');
        }
    });
    bindClick('.btn-preview-close', () => {
        if (previewModal) previewModal.classList.add('hidden');
    });

    /* =========================================================================
       HELPER FUNCTIONS & STATUS MONITORS
       ========================================================================= */

    function bindClick(selector, handler) {
        const btn = container.querySelector(selector);
        if (btn) btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isSourceModeActive.value) {
                alert('Please switch back to Visual mode to use toolbar buttons.');
                return;
            }
            handler(e);
        });
    }

    function updateActiveStates() {
        const toggleClass = (el, isActive) => {
            if (!el) return;
            if (isActive) {
                el.classList.add('bg-navy', 'text-cyan', 'border-navy');
                el.classList.remove('text-gray-700', 'hover:bg-gray-100');
            } else {
                el.classList.remove('bg-navy', 'text-cyan', 'border-navy');
                el.classList.add('text-gray-700', 'hover:bg-gray-100');
            }
        };

        toggleClass(container.querySelector('.btn-bold'), editor.isActive('bold'));
        toggleClass(container.querySelector('.btn-italic'), editor.isActive('italic'));
        toggleClass(container.querySelector('.btn-underline'), editor.isActive('underline'));
        toggleClass(container.querySelector('.btn-strike'), editor.isActive('strike'));
        toggleClass(container.querySelector('.btn-code'), editor.isActive('code'));
        toggleClass(container.querySelector('.btn-superscript'), editor.isActive('superscript'));
        toggleClass(container.querySelector('.btn-subscript'), editor.isActive('subscript'));
        toggleClass(container.querySelector('.btn-blockquote'), editor.isActive('blockquote'));
        toggleClass(container.querySelector('.btn-bullet-list'), editor.isActive('bulletList'));
        toggleClass(container.querySelector('.btn-ordered-list'), editor.isActive('orderedList'));
        toggleClass(container.querySelector('.btn-code-block'), editor.isActive('codeBlock'));
        toggleClass(container.querySelector('.btn-link'), editor.isActive('link'));

        // Sync block selector dropdown
        if (blockSelect) {
            if (editor.isActive('heading', { level: 1 })) blockSelect.value = 'h1';
            else if (editor.isActive('heading', { level: 2 })) blockSelect.value = 'h2';
            else if (editor.isActive('heading', { level: 3 })) blockSelect.value = 'h3';
            else if (editor.isActive('heading', { level: 4 })) blockSelect.value = 'h4';
            else if (editor.isActive('heading', { level: 5 })) blockSelect.value = 'h5';
            else if (editor.isActive('heading', { level: 6 })) blockSelect.value = 'h6';
            else blockSelect.value = 'p';
        }
    }

    function updateStats() {
        const wordCount = editor.storage.characterCount.words();
        const charCount = editor.storage.characterCount.characters();
        const readingTime = Math.max(1, Math.ceil(wordCount / 200));

        const wordsPill = container.querySelector('.stat-words-count');
        const charsPill = container.querySelector('.stat-chars-count');
        const readTimePill = container.querySelector('.stat-reading-time');

        if (wordsPill) wordsPill.textContent = `${wordCount.toLocaleString()} words`;
        if (charsPill) charsPill.textContent = `${charCount.toLocaleString()} chars`;
        if (readTimePill) readTimePill.textContent = `${readingTime} min read`;
    }

    function validateHeadingHierarchy() {
        const warningEl = container.querySelector('.heading-hierarchy-warning');
        if (!warningEl) return;

        const doc = editor.state.doc;
        const headings = [];
        doc.descendants((node) => {
            if (node.type.name === 'heading') {
                headings.push(node.attrs.level);
            }
        });

        let hasSkip = false;
        let multipleH1 = headings.filter(l => l === 1).length > 1;

        for (let i = 0; i < headings.length - 1; i++) {
            if (headings[i + 1] > headings[i] + 1) {
                hasSkip = true;
                break;
            }
        }

        if (hasSkip || multipleH1) {
            warningEl.classList.remove('hidden');
            let msg = '';
            if (multipleH1) msg = 'Multiple H1s found. Best SEO practice uses one main H1.';
            else if (hasSkip) msg = 'Heading level skipped (e.g. H2 to H4). Check hierarchy.';
            warningEl.textContent = msg;
        } else {
            warningEl.classList.add('hidden');
        }
    }

    // Initial runs
    updateActiveStates();
    updateStats();
    validateHeadingHierarchy();

    window.tiptapEditorInstance = editor;
    window.initBlogEditor = initBlogEditor;
    return editor;
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tiptap-container').forEach(container => {
        initBlogEditor(container);
    });
});
