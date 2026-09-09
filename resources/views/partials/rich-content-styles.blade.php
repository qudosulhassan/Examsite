<style>
    /* Rich TipTap Content Styles */
    .blog-content-body table {
        border-collapse: collapse;
        width: 100%;
        margin: 2rem 0;
        table-layout: fixed;
        display: block;
        overflow-x: auto;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .blog-content-body th {
        background-color: #0A1628;
        color: #ffffff;
        font-weight: 700;
        text-align: left;
        padding: 0.75rem 1rem;
        border: 1px solid #1E293B;
    }
    .blog-content-body td {
        padding: 0.75rem 1rem;
        border: 1px solid #E2E8F0;
        vertical-align: top;
    }
    .blog-content-body tr:nth-child(even) td {
        background-color: #F8FAFC;
    }
    .blog-content-body tr:hover td {
        background-color: #F1F5F9;
    }
    /* Code Blocks */
    .blog-content-body pre {
        background-color: #0A1628;
        color: #F8FAFC;
        border-radius: 0.75rem;
        padding: 1.25rem;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.875rem;
        line-height: 1.6;
        margin: 1.75rem 0;
        overflow-x: auto;
        position: relative;
        border: 1px solid #1E293B;
    }
    .blog-content-body code {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85em;
    }
    .blog-content-body :not(pre) > code {
        background-color: #F1F5F9;
        color: #0A1628;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        border: 1px solid #E2E8F0;
    }
    /* Syntax Highlighting Colors */
    .blog-content-body pre .hljs-keyword,
    .blog-content-body pre .hljs-selector-tag { color: #FF6B35; font-weight: 600; }
    .blog-content-body pre .hljs-string,
    .blog-content-body pre .hljs-attribute { color: #00D4AA; }
    .blog-content-body pre .hljs-title,
    .blog-content-body pre .hljs-section { color: #60A5FA; font-weight: 600; }
    .blog-content-body pre .hljs-comment,
    .blog-content-body pre .hljs-quote { color: #64748B; font-style: italic; }
    .blog-content-body pre .hljs-number,
    .blog-content-body pre .hljs-literal { color: #F59E0B; }
    .blog-content-body pre .hljs-variable,
    .blog-content-body pre .hljs-template-variable { color: #EC4899; }
    /* Images */
    .blog-content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .blog-content-body .tiptap-image-wrap {
        margin: 1.75rem 0;
    }
    .blog-content-body .tiptap-image-wrap.align-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .blog-content-body .tiptap-image-wrap.align-left {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .blog-content-body .tiptap-image-wrap.align-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    /* Links */
    .blog-content-body a {
        color: #0284C7;
        text-decoration: underline;
        font-weight: 500;
        transition: color 0.15s;
    }
    .blog-content-body a:hover {
        color: #0369A1;
    }
    /* Marks */
    .blog-content-body mark {
        padding: 0.1rem 0.3rem;
        border-radius: 0.2rem;
    }
    /* Blockquotes */
    .blog-content-body blockquote {
        border-left: 4px solid #00D4AA;
        padding-left: 1.25rem;
        font-style: italic;
        color: #475569;
        margin: 1.5rem 0;
    }
</style>
