import './bootstrap';
import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
window.TomSelect = TomSelect;

// Register countdownTimer component
Alpine.data('countdownTimer', () => {
    return {
        show: true,
        hours: '00',
        minutes: '00',
        seconds: '00',
        init() {
            let endTime = localStorage.getItem('ninja_sale_end');
            if (!endTime) {
                endTime = new Date().getTime() + (12 * 60 * 60 * 1000);
                localStorage.setItem('ninja_sale_end', endTime);
            }
            
            const update = () => {
                let now = new Date().getTime();
                let distance = endTime - now;
                
                if (distance < 0) {
                    endTime = new Date().getTime() + (12 * 60 * 60 * 1000);
                    localStorage.setItem('ninja_sale_end', endTime);
                    distance = endTime - now;
                }
                
                this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
            };
            
            update();
            setInterval(update, 1000);
        }
    };
});

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tiptap-container').forEach(container => {
        const editorEl = container.querySelector('.editor-element');
        const inputEl = container.querySelector('.content-input');
        
        let initialHtml = '';
        try {
            initialHtml = atob(container.getAttribute('data-content') || '');
        } catch (e) {
            console.error('Failed to decode initial content:', e);
        }

        const editor = new Editor({
            element: editorEl,
            extensions: [
                StarterKit,
                Image,
                Link.configure({ openOnClick: false }),
                Placeholder.configure({ placeholder: 'Write something amazing...' }),
            ],
            content: initialHtml,
            editorProps: {
                attributes: {
                    class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-2xl max-w-none w-full focus:outline-none min-h-[300px] p-4',
                },
            },
            onUpdate: ({ editor }) => {
                inputEl.value = editor.getHTML();
                updateActiveStates();
            },
            onSelectionUpdate: () => {
                updateActiveStates();
            }
        });

        // Setup Toolbar Buttons
        const btnBold = container.querySelector('.btn-bold');
        const btnItalic = container.querySelector('.btn-italic');
        const btnP = container.querySelector('.btn-p');
        const btnH1 = container.querySelector('.btn-h1');
        const btnH2 = container.querySelector('.btn-h2');
        const btnH3 = container.querySelector('.btn-h3');
        const btnBullet = container.querySelector('.btn-bullet');
        const btnOrdered = container.querySelector('.btn-ordered');
        const btnQuote = container.querySelector('.btn-quote');
        const btnLink = container.querySelector('.btn-link');
        const btnImage = container.querySelector('.btn-image');

        if (btnBold) btnBold.addEventListener('click', () => editor.chain().focus().toggleBold().run());
        if (btnItalic) btnItalic.addEventListener('click', () => editor.chain().focus().toggleItalic().run());
        if (btnP) btnP.addEventListener('click', () => editor.chain().focus().setParagraph().run());
        if (btnH1) btnH1.addEventListener('click', () => editor.chain().focus().toggleHeading({ level: 1 }).run());
        if (btnH2) btnH2.addEventListener('click', () => editor.chain().focus().toggleHeading({ level: 2 }).run());
        if (btnH3) btnH3.addEventListener('click', () => editor.chain().focus().toggleHeading({ level: 3 }).run());
        if (btnBullet) btnBullet.addEventListener('click', () => editor.chain().focus().toggleBulletList().run());
        if (btnOrdered) btnOrdered.addEventListener('click', () => editor.chain().focus().toggleOrderedList().run());
        if (btnQuote) btnQuote.addEventListener('click', () => editor.chain().focus().toggleBlockquote().run());
        
        if (btnLink) btnLink.addEventListener('click', () => {
            const previousUrl = editor.getAttributes('link').href || '';
            const url = window.prompt('URL', previousUrl);
            if (url === null) return;
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        });

        if (btnImage) btnImage.addEventListener('click', () => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (re) => {
                        editor.chain().focus().setImage({ src: re.target.result }).run();
                    };
                    reader.readAsDataURL(file);
                }
            };
            input.click();
        });

        function updateActiveStates() {
            const toggleClass = (el, isActive) => {
                if (!el) return;
                if (isActive) {
                    el.classList.add('bg-gray-200', 'text-gray-900');
                    el.classList.remove('text-gray-600');
                } else {
                    el.classList.remove('bg-gray-200', 'text-gray-900');
                    el.classList.add('text-gray-600');
                }
            };

            toggleClass(btnBold, editor.isActive('bold'));
            toggleClass(btnItalic, editor.isActive('italic'));
            toggleClass(btnP, editor.isActive('paragraph'));
            toggleClass(btnH1, editor.isActive('heading', { level: 1 }));
            toggleClass(btnH2, editor.isActive('heading', { level: 2 }));
            toggleClass(btnH3, editor.isActive('heading', { level: 3 }));
            toggleClass(btnBullet, editor.isActive('bulletList'));
            toggleClass(btnOrdered, editor.isActive('orderedList'));
            toggleClass(btnQuote, editor.isActive('blockquote'));
            toggleClass(btnLink, editor.isActive('link'));
        }
        
        // Initial state
        updateActiveStates();
        
        // Expose to window for debugging if needed
        window.tiptapEditorInstance = editor;
    });
});
