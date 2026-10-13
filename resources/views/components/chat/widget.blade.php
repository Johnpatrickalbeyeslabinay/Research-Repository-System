{{--
    AI research assistant chat widget. Authenticated pages only (see
    routes/web.php's chat.* group) — every layout that includes this is
    already behind 'auth' middleware, so no extra Auth::check() guard is
    needed here.

    Two modes, both handled by the same markup/JS:
      - Repository-wide (default): no $studFileId, retrieval spans every
        research this user may access.
      - Single-research: $studFileId set (research/show.blade.php's "Ask
        about this research") — retrieval is pinned to that one document.

    Replaces the old resources/views/layouts/chatbot_layout.blade.php,
    which was a static "Coming Soon" placeholder wrapped in its own
    <!DOCTYPE html> (nested inside student_layout's <html> — never valid
    markup). This is a real fragment, meant to be @include-quality but used
    as a Blade component (<x-chat.widget />) so it can take props.
--}}
@props(['studFileId' => null, 'studFileTitle' => null])

@php
    // Rendered once on page load so the limit is visible before the user
    // ever sends a message — resources/js/chat.js updates this element
    // after every reply using the live count the API response carries
    // (see App\Support\ChatQuota / ChatController::message()'s 'quota' key).
    $quota = \App\Support\ChatQuota::status(auth()->id());
@endphp

{{-- Turbo keeps a data-turbo-permanent element alive across page visits
     when the next page has one with the same id, so an open conversation
     survives navigating the dashboard. The id is per-mode: a research
     page's pinned widget must not be carried onto (or replaced by) the
     repository-wide one. resources/js/chat.js guards against re-binding. --}}
<div id="ai-chat-shell-{{ $studFileId ?? 'repository' }}" data-turbo-permanent>
<div id="ai-chat-widget"
    data-store-url="{{ route('chat.store') }}"
    data-index-url="{{ route('chat.index') }}"
    data-base-url="{{ url('/chat/conversations') }}"
    data-stud-file-id="{{ $studFileId }}"
    data-stud-file-title="{{ $studFileTitle }}">

    <input type="hidden" id="ai-chat-csrf" value="{{ csrf_token() }}">

    {{-- Launcher --}}
    <button type="button"
        id="ai-chat-launcher"
        popovertarget="ai-chat-panel"
        class="btn p-0 d-flex align-items-center justify-content-center"
        style="position:fixed; right:24px; bottom:24px; width:75px; height:75px; background:transparent; border:none; box-shadow:none; z-index:1050;"
        aria-label="Open AI research assistant">

    <img src="{{ asset('images/logo.png') }}"
         alt="AQUA"
         style="width:70px; height:70px; object-fit:contain; display:block;">

    </button>

    {{-- Positioning/sizing lives in the stylesheet below, not inline —
         the panel's default display MUST be `none` in plain CSS (not only
         via the browser's popover UA stylesheet), or a browser without
         Popover API support renders this permanently visible on load
         instead of failing closed. --}}
    <div id="ai-chat-panel" popover>

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background: linear-gradient(135deg, #072A89 0%, #0757B5 45%, #00B8D9 100%); color:#fff;flex:0 0 auto;">
            <div class="d-flex align-items-center gap-2" style="min-width:0;">
                <img src="{{ asset('images/logo.png') }}"
                    alt="AQUA Logo"
                    style="width:70px;height:70px;object-fit:contain;flex:0 0 auto;">

                <div style="min-width:0;">
                    <div class="fw-bold" style="font-size:0.92rem;line-height:1.2;">
                        Ask AQUA
                    </div>

                    <small class="d-block text-truncate"
                        style="opacity:0.85;max-width:220px;">
                        @if ($studFileId)
                            {{ $studFileTitle }}
                        @else
                            Ask about research in the repository
                        @endif
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" id="ai-chat-history-btn" class="btn btn-sm text-white" title="Past conversations">
                    <i data-lucide="history" style="width:17px;height:17px;"></i>
                </button>
                <button type="button" id="ai-chat-new-btn" class="btn btn-sm text-white" title="New chat">
                    <i data-lucide="square-pen" style="width:17px;height:17px;"></i>
                </button>
                <button type="button" popovertarget="ai-chat-panel" popovertargetaction="hide" class="btn btn-sm text-white" title="Close">
                    <i data-lucide="x" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </div>

        {{-- Past conversations (hidden until the history button is used) --}}
        <div id="ai-chat-history-list" class="border-bottom bg-white" style="display:none;max-height:200px;overflow-y:auto;flex:0 0 auto;"></div>

        {{-- Messages --}}
        <div id="ai-chat-messages" class="p-3" style="flex:1 1 auto;overflow-y:auto;background-color:#f8fafc;">
            <div id="ai-chat-empty-state" class="text-center text-secondary py-3">
                <i data-lucide="{{ $studFileId ? 'file-question' : 'fish' }}" style="width:30px;height:30px;" class="mb-2 d-block mx-auto opacity-50"></i>

                @if ($studFileId)
                    <p class="small mb-3 px-2">Ask a question about this research — methodology, findings, or anything else in the paper.</p>
                @else
                    <p class="small mb-3 px-2">Ask about research in this repository. Answers are grounded in the papers on file, with citations.</p>
                @endif

                <div class="d-flex flex-column gap-2 px-2">
                    @if ($studFileId)
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">What methodology did this study use?</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">Summarize the key findings.</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">What are the recommendations?</button>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">What research is available about artificial intelligence?</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">Which studies support SDG 4?</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-chat-suggestion">What studies were published this year?</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Daily limit — kept visible above the composer at all times
             (not just surfaced as a 429 error after the fact), and kept in
             sync live by resources/js/chat.js from each reply's 'quota'. --}}
        <div id="ai-chat-quota" class="px-3 pt-2 text-secondary" style="font-size:0.72rem;flex:0 0 auto;"
            data-used="{{ $quota['used'] }}" data-limit="{{ $quota['limit'] }}">
            {{ $quota['used'] }} of {{ $quota['limit'] }} questions used today
        </div>

        {{-- Composer --}}
        <div class="border-top bg-white p-2" style="flex:0 0 auto;">
            <form id="ai-chat-form" class="d-flex gap-2 align-items-end" autocomplete="off">
                <textarea id="ai-chat-input" class="form-control" rows="1" placeholder="Ask about research..."
                    maxlength="1000" style="resize:none;max-height:100px;font-size:0.9rem;"></textarea>

                {{-- One button, two states — send while idle, stop while a
                     reply is generating — rather than two separate buttons
                     swapping visibility, matching how other chat UIs do it. --}}
                <button type="button" id="ai-chat-send" class="btn text-white flex-shrink-0 d-flex align-items-center justify-content-center"
                    style="background-color:#072a89;width:38px;height:38px;" title="Send">
                    <i data-lucide="send" id="ai-chat-send-icon" style="width:16px;height:16px;"></i>
                    <i data-lucide="square" id="ai-chat-stop-icon" style="width:13px;height:13px;display:none;"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Templates cloned by resources/js/chat.js — kept here rather than
         built as raw strings in JS so the markup stays in one place and is
         easy to restyle. --}}

    <template id="ai-chat-tpl-user">
        <div class="d-flex justify-content-end mb-3 ai-chat-msg">
            <div class="rounded-3 px-3 py-2 text-white" style="background-color:#072a89;max-width:85%;white-space:pre-wrap;word-break:break-word;font-size:0.88rem;" data-role="content"></div>
        </div>
    </template>

    <template id="ai-chat-tpl-assistant">
        <div class="d-flex mb-3 ai-chat-msg">
            <div style="max-width:92%;min-width:0;">
                <div class="rounded-3 px-3 py-2" style="background-color:#fff;border:1px solid #e5e7eb;white-space:pre-wrap;word-break:break-word;font-size:0.88rem;" data-role="content"></div>
                <div data-role="sources" class="mt-2 d-flex flex-column gap-2"></div>
                {{-- A separate list, below the repository's own sources and
                     under its own heading. Never merged into [data-role="sources"]. --}}
                <div data-role="external-sources" class="mt-2 d-none flex-column gap-2">
                    <div class="text-secondary fw-semibold" style="font-size:0.7rem;">Beyond this repository</div>
                </div>
                <div class="d-flex gap-3 mt-1 ps-1 ai-chat-msg-actions">
                    <button type="button" class="btn btn-link text-secondary p-0 ai-chat-copy" style="font-size:0.72rem;text-decoration:none;">
                        <i data-lucide="copy" style="width:12px;height:12px;"></i> Copy
                    </button>
                    <button type="button" class="btn btn-link text-secondary p-0 ai-chat-retry" style="font-size:0.72rem;text-decoration:none;display:none;">
                        <i data-lucide="rotate-ccw" style="width:12px;height:12px;"></i> Retry
                    </button>
                    {{-- Shown only on an abstract-tier answer (see
                         App\Services\Retrieval\Tier): that answer came from the
                         studies' abstracts, so this is how the user asks for the
                         body text without having to guess the phrasing that
                         triggers RetrievalTierPlanner's drill-down rule. --}}
                    <button type="button" class="btn btn-link text-secondary p-0 ai-chat-deeper" style="font-size:0.72rem;text-decoration:none;display:none;">
                        <i data-lucide="search" style="width:12px;height:12px;"></i> Go deeper
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template id="ai-chat-tpl-source-card">
        <a href="#" target="_blank" rel="noopener" class="d-block rounded-3 px-2 py-2 text-decoration-none text-dark ai-chat-source-card"
            style="background-color:#EFF6FF;border:1px solid #dbeafe;font-size:0.76rem;line-height:1.3;">
            <div class="fw-semibold text-truncate" data-role="title"></div>
            <div class="text-secondary" data-role="meta"></div>
        </a>
    </template>

    {{-- Visually distinct from ai-chat-tpl-source-card on purpose. An internal
         source card links same-origin to a study this repository holds and the
         user is authorised to read; this one leaves the site entirely for a
         paper nobody here reviewed. Amber rather than blue, an explicit
         "External" badge, and rel="noopener noreferrer" because the
         destination is untrusted.

         The two are rendered into SEPARATE containers (see chat.js) and must
         stay that way — the whole point is that a reader can never wonder
         whether the bot just presented an outside paper as one of ours. --}}
    <template id="ai-chat-tpl-external-card">
        <a href="#" target="_blank" rel="noopener noreferrer" class="d-block rounded-3 px-2 py-2 text-decoration-none text-dark ai-chat-external-card"
            style="background-color:#FFFBEB;border:1px solid #fde68a;font-size:0.76rem;line-height:1.3;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill" style="background-color:#B45309;font-size:0.6rem;font-weight:600;">External</span>
                <span class="text-secondary" style="font-size:0.66rem;" data-role="provider"></span>
            </div>
            <div class="fw-semibold" data-role="title" style="word-break:break-word;"></div>
            <div class="text-secondary" data-role="meta"></div>
        </a>
    </template>

    <template id="ai-chat-tpl-typing">
        <div class="d-flex mb-3 ai-chat-msg ai-chat-typing">
            <div class="rounded-3 px-3 py-2 d-flex gap-1 align-items-center" style="background-color:#fff;border:1px solid #e5e7eb;">
                <span class="ai-chat-dot"></span><span class="ai-chat-dot"></span><span class="ai-chat-dot"></span>
            </div>
        </div>
    </template>

    <template id="ai-chat-tpl-history-item">
        <button type="button" class="btn btn-sm text-start w-100 border-0 rounded-0 ai-chat-history-item px-3 py-2" style="font-size:0.8rem;">
            <div class="text-truncate" data-role="title"></div>
        </button>
    </template>
</div>

<style>
    /* Base positioning/sizing for the popover panel. display defaults to
       none unconditionally (not only via the browser's popover UA
       stylesheet) — a browser without Popover API support would otherwise
       render this permanently visible, since the `popover` attribute then
       does nothing and an unstyled <div> falls back to normal flow.

       transform/margin are declared explicitly (not left to inherit) —
       student_layout.blade.php has its own global `[popover] { top:50%;
       left:50%; transform:translate(-50%,-50%); margin:0; }` rule for its
       Submit-Research wizard modal. CSS cascades per PROPERTY, not per
       rule: this ID selector already outranks that attribute selector for
       top/left/right/bottom (ID beats attribute), but transform/margin
       were never contested here, so the wizard's centering transform was
       leaking straight through — shifting this panel by half its own
       width/height from its intended bottom-right anchor. Same story for
       the backdrop dimming below: a floating chat bubble isn't a modal
       and shouldn't dim the page behind it. */
    #ai-chat-panel {
        position: fixed;
        right: 24px;
        bottom: 95px;
        left: auto;
        top: auto;
        transform: none;
        margin: 0;
        width: 380px;
        max-width: calc(100vw - 30px);
        height: 560px;
        max-height: calc(100vh - 120px);
        padding: 0;
        margin: 0;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.20);
        z-index: 1049;
        display: none;
        flex-direction: column;
    }
    #ai-chat-panel:popover-open {
        display: flex;
    }
    /* Neutralizes student_layout's global `[popover]::backdrop { background:
       rgba(15,23,42,.45); }` (for its modal wizard) — a floating chat
       widget shouldn't dim the whole page behind it the way a modal does. */
    #ai-chat-panel::backdrop {
        background: transparent;
    }

    .ai-chat-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #9ca3af;
        animation: ai-chat-bounce 1.2s infinite ease-in-out;
    }
    .ai-chat-dot:nth-child(2) { animation-delay: 0.15s; }
    .ai-chat-dot:nth-child(3) { animation-delay: 0.3s; }
    @keyframes ai-chat-bounce {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }
    .ai-chat-suggestion:hover,
    .ai-chat-history-item:hover { background-color: #f1f5f9; }
    .ai-chat-source-card:hover { filter: brightness(0.97); }

    @media (max-width: 480px) {
        #ai-chat-panel[popover] {
            right: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            top: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            border-radius: 0 !important;
        }
    }
</style>
</div>
