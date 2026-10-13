// Behavior for the AI research assistant chat widget
// (resources/views/components/chat/widget.blade.php). Vanilla JS + fetch,
// matching resources/js/research-submission.js's conventions: CSRF read
// from a hidden input (not a <meta> tag — none of the layouts this widget
// lives on have one), config via data-* attributes, and a safeInit()
// wrapper so one broken widget can't take the rest of the page down.
//
// Non-streaming by design for now (see the project's RAG plan — the
// deployment target buffers responses, so SSE isn't reliable there yet).
// "Stop" aborts the client-side fetch only; the server-side request still
// completes and is still billed, and the UI says so rather than implying
// a real cancellation.

function safeInit(label, fn) {
    try {
        fn();
    } catch (error) {
        console.error(`[chat] ${label} failed to initialize`, error);
    }
}

// --- Minimal markdown rendering -------------------------------------------
//
// The model emits **bold** and numbered/bulleted lists, especially on
// catalog turns (App\Services\Retrieval\CatalogService), which answer with
// a list of studies. Those were previously set via .textContent and showed
// up as literal "**Title:**" and "1. " runs of text.
//
// Everything below builds DOM NODES — never innerHTML. Model output is
// untrusted (it restates text extracted from user-uploaded PDFs), so the
// original XSS posture of .textContent is preserved exactly: no string of
// model output is ever parsed as HTML. Deliberately tiny: bold, italic,
// inline code, lists, paragraphs. No links, images, tables or raw HTML —
// nothing here needs them, and each one is another way to be wrong.

const INLINE_PATTERN = /(\*\*[^*]+\*\*|__[^_]+__|\*[^*\n]+\*|`[^`\n]+`)/g;

function appendInline(parent, text) {
    for (const part of text.split(INLINE_PATTERN)) {
        if (!part) continue;

        let el = null;

        if (part.length > 4 && part.startsWith('**') && part.endsWith('**')) {
            el = document.createElement('strong');
            el.textContent = part.slice(2, -2);
        } else if (part.length > 4 && part.startsWith('__') && part.endsWith('__')) {
            el = document.createElement('strong');
            el.textContent = part.slice(2, -2);
        } else if (part.length > 2 && part.startsWith('*') && part.endsWith('*')) {
            el = document.createElement('em');
            el.textContent = part.slice(1, -1);
        } else if (part.length > 2 && part.startsWith('`') && part.endsWith('`')) {
            el = document.createElement('code');
            el.textContent = part.slice(1, -1);
        }

        parent.appendChild(el || document.createTextNode(part));
    }
}

// Groups consecutive list lines into one <ul>/<ol> and everything else into
// <p>, so a list doesn't render as a stack of one-item lists.
function renderMarkdownInto(container, text) {
    container.textContent = '';

    const lines = String(text ?? '').replace(/\r\n/g, '\n').split('\n');
    let list = null;
    let paragraph = null;

    const closeList = () => { list = null; };
    const closeParagraph = () => { paragraph = null; };

    for (const line of lines) {
        const bullet = line.match(/^\s*[-*+]\s+(.*)$/);
        const numbered = line.match(/^\s*\d+[.)]\s+(.*)$/);

        if (bullet || numbered) {
            closeParagraph();
            const wanted = bullet ? 'UL' : 'OL';

            if (!list || list.tagName !== wanted) {
                list = document.createElement(bullet ? 'ul' : 'ol');
                list.className = 'mb-2 ps-4';
                container.appendChild(list);
            }

            const li = document.createElement('li');
            appendInline(li, (bullet ? bullet[1] : numbered[1]).trim());
            list.appendChild(li);
            continue;
        }

        closeList();

        if (line.trim() === '') {
            closeParagraph();
            continue;
        }

        if (!paragraph) {
            paragraph = document.createElement('p');
            paragraph.className = 'mb-2';
            container.appendChild(paragraph);
        } else {
            // A soft line break inside the same paragraph.
            paragraph.appendChild(document.createElement('br'));
        }

        appendInline(paragraph, line.trim());
    }

    // Drop the trailing margin so the sources list sits tight under the text.
    const last = container.lastElementChild;
    if (last) last.classList.remove('mb-2');
}

function initChatWidget(root) {
    const csrfInput = document.getElementById('ai-chat-csrf');
    const storeUrl = root.dataset.storeUrl;
    const indexUrl = root.dataset.indexUrl;
    const baseUrl = root.dataset.baseUrl;
    const pinnedStudFileId = root.dataset.studFileId || null;

    const messagesEl = document.getElementById('ai-chat-messages');
    const emptyStateEl = document.getElementById('ai-chat-empty-state');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const sendIcon = document.getElementById('ai-chat-send-icon');
    const stopIcon = document.getElementById('ai-chat-stop-icon');
    const newChatBtn = document.getElementById('ai-chat-new-btn');
    const historyBtn = document.getElementById('ai-chat-history-btn');
    const historyList = document.getElementById('ai-chat-history-list');
    const quotaEl = document.getElementById('ai-chat-quota');

    const tplUser = document.getElementById('ai-chat-tpl-user');
    const tplAssistant = document.getElementById('ai-chat-tpl-assistant');
    const tplSourceCard = document.getElementById('ai-chat-tpl-source-card');
    const tplExternalCard = document.getElementById('ai-chat-tpl-external-card');
    const tplTyping = document.getElementById('ai-chat-tpl-typing');
    const tplHistoryItem = document.getElementById('ai-chat-tpl-history-item');

    if (!csrfInput || !storeUrl || !baseUrl || !messagesEl || !form || !input || !sendBtn) {
        console.error('[chat] widget is missing required markup, skipping init', root);
        return;
    }

    const csrfToken = csrfInput.value;

    const state = {
        conversationId: null,
        sending: false,
        abortController: null,
        lastQuestion: null,
        historyLoaded: false,
    };

    function jsonHeaders() {
        return {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
            'Content-Type': 'application/json',
        };
    }

    function hideEmptyState() {
        if (emptyStateEl) emptyStateEl.style.display = 'none';
    }

    function showEmptyState() {
        if (emptyStateEl) emptyStateEl.style.display = '';
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function renderUserMessage(text) {
        const node = tplUser.content.firstElementChild.cloneNode(true);
        node.querySelector('[data-role="content"]').textContent = text;
        messagesEl.appendChild(node);
        scrollToBottom();
    }

    function formatCitationMeta(citation) {
        const bits = [];
        if (citation.authors && citation.authors.length) {
            bits.push(citation.authors.slice(0, 2).join(', ') + (citation.authors.length > 2 ? ' et al.' : ''));
        }
        if (citation.year) bits.push(String(citation.year));
        if (citation.section) {
            const section = citation.section.charAt(0).toUpperCase() + citation.section.slice(1);
            bits.push(citation.page_number ? `${section}, p.${citation.page_number}` : section);
        }
        return bits.join(' · ');
    }

    function formatExternalMeta(source) {
        const bits = [];
        if (source.authors && source.authors.length) {
            bits.push(source.authors[0] + (source.authors.length > 1 ? ' et al.' : ''));
        }
        if (source.year) bits.push(String(source.year));
        if (source.venue) bits.push(source.venue);
        // The DOI is what makes an external citation independently verifiable,
        // so it is shown rather than hidden behind the link.
        if (source.doi) bits.push('doi:' + source.doi);
        return bits.join(' · ');
    }

    function renderAssistantMessage(content, citations, question, suggestedResearch, tier, externalSources) {
        const node = tplAssistant.content.firstElementChild.cloneNode(true);
        // Markdown for assistant turns only — the model is the only source
        // that emits it. User text and error text stay literal.
        renderMarkdownInto(node.querySelector('[data-role="content"]'), content);

        const sourcesEl = node.querySelector('[data-role="sources"]');
        (citations || []).forEach((citation) => {
            const card = tplSourceCard.content.firstElementChild.cloneNode(true);
            card.href = citation.url || '#';
            card.querySelector('[data-role="title"]').textContent = `[${citation.n}] ${citation.title}`;
            card.querySelector('[data-role="meta"]').textContent = formatCitationMeta(citation);
            sourcesEl.appendChild(card);
        });

        // "Couldn't find enough evidence" turns carry no citations, but may
        // suggest the closest-topic research instead (see
        // ChatController::suggestedResearchCards()).
        (suggestedResearch || []).forEach((item) => {
            const card = tplSourceCard.content.firstElementChild.cloneNode(true);
            card.href = item.url || '#';
            card.querySelector('[data-role="title"]').textContent = item.title;
            card.querySelector('[data-role="meta"]').textContent = 'You might mean this — view research';
            sourcesEl.appendChild(card);
        });

        // External works go in their OWN container, below the repository's own
        // sources and under their own heading. Deliberately never merged into
        // sourcesEl: an internal card links same-origin to a study this
        // repository holds and the user is cleared to read, while this one
        // leaves the site for a paper nobody here reviewed. A reader must never
        // have to wonder which they are looking at.
        const externalEl = node.querySelector('[data-role="external-sources"]');
        if (externalEl && tplExternalCard && (externalSources || []).length) {
            externalEl.classList.remove('d-none');
            externalEl.classList.add('d-flex');

            externalSources.forEach((source) => {
                const card = tplExternalCard.content.firstElementChild.cloneNode(true);
                card.href = source.url || '#';
                card.querySelector('[data-role="provider"]').textContent = source.provider || '';
                card.querySelector('[data-role="title"]').textContent = '[' + source.n + '] ' + source.title;
                card.querySelector('[data-role="meta"]').textContent = formatExternalMeta(source);
                externalEl.appendChild(card);
            });
        }

        const copyBtn = node.querySelector('.ai-chat-copy');
        copyBtn.addEventListener('click', () => {
            navigator.clipboard?.writeText(content).then(() => {
                const original = copyBtn.innerHTML;
                copyBtn.textContent = 'Copied';
                setTimeout(() => {
                    copyBtn.innerHTML = original;
                    if (window.lucide) window.lucide.createIcons();
                }, 1500);
            });
        });

        const retryBtn = node.querySelector('.ai-chat-retry');
        if (question) {
            retryBtn.style.display = '';
            retryBtn.addEventListener('click', () => sendMessage(question));
        }

        // Offered only when the answer came from abstract-level evidence. The
        // server decides that (retrieval_meta.tier), so the button appears
        // exactly when there is actually more text to read — rather than
        // inviting the user to pay for a second, identical full-text turn.
        const deeperBtn = node.querySelector('.ai-chat-deeper');
        if (deeperBtn && question && tier === 'abstract') {
            deeperBtn.style.display = '';
            // Phrased to match RetrievalTierPlanner's explicit drill-down
            // pattern, so the follow-up is planned at full text rather than
            // re-planned as another abstract turn.
            deeperBtn.addEventListener('click', () => sendMessage(`${question} — in more detail`));
        }

        messagesEl.appendChild(node);
        if (window.lucide) window.lucide.createIcons();
        scrollToBottom();

        return node;
    }

    function renderErrorAsAssistantTurn(message, question) {
        const node = renderAssistantMessage(message, [], question);
        const contentEl = node.querySelector('[data-role="content"]');
        // Error copy is ours, not the model's — render it literally rather
        // than letting a stray asterisk in an upstream message be styled.
        contentEl.textContent = message;
        contentEl.classList.add('text-danger');
        return node;
    }

    function showTyping() {
        const node = tplTyping.content.firstElementChild.cloneNode(true);
        node.id = 'ai-chat-typing-indicator';
        messagesEl.appendChild(node);
        scrollToBottom();
    }

    function hideTyping() {
        document.getElementById('ai-chat-typing-indicator')?.remove();
    }

    function setSending(isSending) {
        state.sending = isSending;
        sendIcon.style.display = isSending ? 'none' : '';
        stopIcon.style.display = isSending ? '' : 'none';
        sendBtn.title = isSending ? 'Stop' : 'Send';
        input.disabled = isSending;
    }

    // Renders whatever quota the server last reported (initial page load
    // supplies it server-side in the widget's data-* attributes below;
    // every message()/429 response refreshes it after that) — the limit is
    // visible before it's ever hit, not just surfaced as a surprise error.
    function updateQuota(quota) {
        if (!quota || !quotaEl) return;

        quotaEl.textContent = `${quota.used} of ${quota.limit} questions used today`;

        if (quota.remaining <= 0) {
            quotaEl.classList.add('text-danger');
            input.disabled = true;
            sendBtn.disabled = true;
        } else {
            quotaEl.classList.remove('text-danger');
            input.disabled = state.sending;
            sendBtn.disabled = false;
        }
    }

    async function ensureConversation(signal) {
        if (state.conversationId) return state.conversationId;

        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: jsonHeaders(),
            // Without this, clicking Stop while the FIRST message of a
            // conversation is still creating the conversation record did
            // nothing — this fetch had no signal, so it always ran to
            // completion regardless of the abort, and Stop only ever
            // affected the second fetch below.
            signal,
            body: JSON.stringify({ stud_file_id: pinnedStudFileId }),
        });

        const body = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(body.message || 'Could not start a new conversation.');
        }

        state.conversationId = body.id;
        return body.id;
    }

    async function sendMessage(rawText) {
        const text = (rawText ?? input.value).trim();
        if (!text || state.sending) return;

        hideEmptyState();
        renderUserMessage(text);
        input.value = '';
        input.style.height = 'auto';
        state.lastQuestion = text;
        setSending(true);
        showTyping();

        // Captured locally, not just read back off `state` later — two
        // overlapping sendMessage() calls (this turn's abort racing a
        // freshly-started next turn) must never let one call's cleanup
        // null out a DIFFERENT call's controller. See the finally block.
        const controller = new AbortController();
        state.abortController = controller;

        try {
            const conversationId = await ensureConversation(controller.signal);

            const response = await fetch(`${baseUrl}/${conversationId}/messages`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ message: text }),
                signal: controller.signal,
            });

            const body = await response.json().catch(() => ({}));
            hideTyping();
            updateQuota(body.quota);

            if (!response.ok) {
                // Shown once, as a turn with Retry. It used to also fill a
                // banner above the composer, so every error appeared twice.
                const message = body.message || 'Something went wrong. Please try again.';
                renderErrorAsAssistantTurn(message, text);
                return;
            }

            renderAssistantMessage(
                body.assistant_message.content,
                body.assistant_message.citations,
                // The question, so the "Go deeper" button can resend it.
                text,
                body.assistant_message.suggested_research,
                body.assistant_message.tier,
                body.assistant_message.external_sources
            );
        } catch (error) {
            hideTyping();

            if (error.name === 'AbortError') {
                renderErrorAsAssistantTurn(
                    'Stopped. Note: the request may still finish processing on the server.',
                    null
                );
            } else {
                const message = 'Could not reach the AI research assistant. Check your connection and try again.';
                renderErrorAsAssistantTurn(message, text);
            }
        } finally {
            setSending(false);

            // Only clear the SHARED abortController if it's still this
            // call's own — a click-to-stop already force-resets state.sending
            // synchronously (see the sendBtn click handler below) and can let
            // a brand new sendMessage() start, with its own controller,
            // before this (the aborted call's) finally gets here. Clearing
            // unconditionally would null out the NEW call's live controller,
            // silently breaking that message's own Stop button.
            if (state.abortController === controller) {
                state.abortController = null;
            }
        }
    }

    function newChat() {
        state.conversationId = null;
        state.lastQuestion = null;
        messagesEl.querySelectorAll('.ai-chat-msg').forEach((el) => el.remove());
        showEmptyState();
        historyList.style.display = 'none';
    }

    async function loadConversation(id) {
        const response = await fetch(`${baseUrl}/${id}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) return;

        const body = await response.json();

        state.conversationId = body.id;
        messagesEl.querySelectorAll('.ai-chat-msg').forEach((el) => el.remove());
        hideEmptyState();
        historyList.style.display = 'none';

        (body.messages || []).forEach((message) => {
            if (message.role === 'user') {
                renderUserMessage(message.content);
            } else {
                // External sources replay too — a past turn that cited outside
                // literature must not silently lose those cards, or the
                // transcript stops matching what the user was shown.
                renderAssistantMessage(
                    message.content, message.citations, null, null, null, message.external_sources
                );
            }
        });
    }

    async function toggleHistory() {
        const isOpen = historyList.style.display !== 'none';

        if (isOpen) {
            historyList.style.display = 'none';
            return;
        }

        historyList.style.display = '';

        if (state.historyLoaded) return;

        // Placeholder rows while the list loads, so the panel doesn't sit
        // empty on a slow connection looking like there's no history.
        historyList.innerHTML = '<div class="placeholder-glow px-3 py-2">'
            + '<span class="placeholder col-10 mb-2"></span>'
            + '<span class="placeholder col-8 mb-2"></span>'
            + '<span class="placeholder col-9"></span></div>';

        let body;
        try {
            const response = await fetch(indexUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            body = await response.json();
        } catch (error) {
            historyList.innerHTML = '<div class="text-secondary small px-3 py-2">Couldn&#39;t load past conversations. Try again.</div>';
            return;
        }

        historyList.innerHTML = '';
        state.historyLoaded = true;

        if (!body.conversations || body.conversations.length === 0) {
            historyList.innerHTML = '<div class="text-secondary small px-3 py-2">No past conversations yet.</div>';
            return;
        }

        body.conversations.forEach((conversation) => {
            const node = tplHistoryItem.content.firstElementChild.cloneNode(true);
            node.querySelector('[data-role="title"]').textContent = conversation.title || 'New conversation';
            node.addEventListener('click', () => loadConversation(conversation.id));
            historyList.appendChild(node);
        });
    }

    // Apply the server-rendered initial quota immediately — if the user
    // already hit today's limit in an earlier session and reloads the
    // page, the composer should start disabled, not wait for a failed
    // send attempt to discover that.
    if (quotaEl) {
        const used = Number(quotaEl.dataset.used || 0);
        const limit = Number(quotaEl.dataset.limit || 0);
        updateQuota({ used, limit, remaining: limit - used });
    }

    // Enter sends; Shift+Enter inserts a newline. Textarea grows with
    // content up to the CSS max-height already set on it.
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = `${input.scrollHeight}px`;
    });

    // Safety net only — sendBtn is type="button" (not "submit"), and Enter
    // is already handled directly above; this just catches any other path
    // that might fire a native form submission.
    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });

    // One button, two jobs depending on state: send while idle, abort the
    // in-flight fetch while a reply is generating.
    sendBtn.addEventListener('click', () => {
        if (state.sending) {
            state.abortController?.abort();

            // Reset the UI right here, synchronously — don't just fire
            // abort() and wait for sendMessage()'s own catch/finally to
            // eventually restore it. abort() only *requests* cancellation;
            // if that promise rejection is ever delayed or doesn't land for
            // any reason, waiting on it alone leaves the button stuck
            // showing "stop" forever with no way for the user to recover
            // (reported live: no active request, no response, composer
            // permanently in "stop" mode). This makes recovery guaranteed
            // and instant instead of dependent on that async chain.
            // sendMessage()'s own finally still runs afterward and is a
            // harmless no-op against this already-reset state.
            setSending(false);
            hideTyping();
        } else {
            sendMessage();
        }
    });

    newChatBtn?.addEventListener('click', newChat);
    historyBtn?.addEventListener('click', toggleHistory);

    root.querySelectorAll('.ai-chat-suggestion').forEach((button) => {
        button.addEventListener('click', () => sendMessage(button.textContent.trim()));
    });
}

// Called by resources/js/app.js on every 'turbo:load'. The repository-wide
// widget is data-turbo-permanent, so Turbo carries the SAME element across
// pages (an open conversation survives navigation) — it must not be bound
// twice. A WeakSet rather than a data-* flag: Turbo's back/forward cache
// restores CLONES of the page, which have the attribute but not the
// listeners, and those clones do need initializing.
const initialized = new WeakSet();

export function init() {
    document.querySelectorAll('#ai-chat-widget').forEach((root) => {
        if (initialized.has(root)) return;
        initialized.add(root);
        safeInit('chat widget', () => initChatWidget(root));
    });
}
