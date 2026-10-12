// Behavior for the redesigned "Submit / Update Research" forms:
// PDF dropzone + deterministic extraction preview (/research/pdf/analyze,
// no AI), keyword chip input, the SDG "Done" button, the pre-submit review
// summary, the researcher list, the multi-step wizard (create form only),
// and the "Analyze Research" AI metadata suggestion button
// (/research/analyze), plus "Generate abstract with AI" for PDFs with no
// detectable abstract (/research/abstract/generate). Both are generative-AI
// calls fired only on explicit user action, never automatically, and share
// the student's daily AI quota.
//
// AI/extracted keyword and SDG suggestions are never added directly to the
// student's selections — they land in a suggestion tray first (see
// keywordSuggestionAdd / sdgSuggestionAdd) and only move into the real
// list when the student clicks them.

function csrfTokenFor(root) {
    const form = root.closest("form");
    const input = form && form.querySelector('input[name="_token"]');
    return input ? input.value : "";
}

function formatBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

// One bad dropzone used to throw out of the page-load callback and
// silently disable every other control on the page. Each init is isolated.
function safeInit(label, fn) {
    try {
        fn();
    } catch (error) {
        console.error(
            `[research-submission] ${label} failed to initialize`,
            error,
        );
    }
}

function initDropzone(root) {
    const input = root.querySelector("[data-dropzone-input]");
    const empty = root.querySelector("[data-dropzone-empty]");
    const result = root.querySelector("[data-dropzone-result]");
    const filename = root.querySelector("[data-dropzone-filename]");
    const filesize = root.querySelector("[data-dropzone-filesize]");
    const status = root.querySelector("[data-dropzone-status]");
    const replaceBtn = root.querySelector("[data-dropzone-replace]");
    const errorBox = root
        .closest(".mb-3")
        ?.querySelector("[data-dropzone-error]");
    const prefix = root.dataset.prefix;
    // Config comes from data attributes (rendered from config('research.*')
    // and route()) rather than being hardcoded, so both the size limit and
    // the endpoint stay correct if either changes.
    const maxBytes = Number(root.dataset.maxBytes) || 10 * 1024 * 1024;
    const analyzeUrl = root.dataset.analyzeUrl || "/research/pdf/analyze";

    const form = root.closest("form");
    if (
        !input ||
        !empty ||
        !result ||
        !filename ||
        !filesize ||
        !status ||
        !errorBox ||
        !form
    ) {
        console.error(
            "[research-submission] dropzone is missing required markup, skipping init",
            root,
        );
        return;
    }

    const abstractField = form.querySelector("[data-abstract-field]");
    const abstractBadge = form.querySelector("[data-abstract-badge]");
    const keywordInputEl = form.querySelector(`#keywords-${prefix}`);
    const fallbackExcerptField = form.querySelector(
        "[data-fallback-excerpt-field]",
    );

    // Null-guarded throughout: a form without this markup simply keeps the
    // plain "couldn't detect / enter manually" message instead.
    const previewBox = form.querySelector("[data-abstract-preview]");
    const previewLabel = form.querySelector("[data-abstract-preview-label]");
    const previewText = form.querySelector("[data-abstract-preview-text]");
    const previewUseBtn = form.querySelector("[data-abstract-preview-use]");
    const previewDismissBtn = form.querySelector(
        "[data-abstract-preview-dismiss]",
    );

    function showError(message) {
        errorBox.textContent = message || "";
        root.classList.toggle("border-danger", Boolean(message));
    }

    function showSelected(file) {
        empty.classList.add("d-none");
        result.classList.remove("d-none");
        filename.textContent = file.name;
        filesize.textContent = formatBytes(file.size);
    }

    // Purely a panel reset — callers own whether an error message survives
    // the reset (previously this always cleared it via showError(''),
    // which meant every rejection error vanished the instant it was shown).
    //function showEmpty() {
    //empty.classList.remove("d-none");
    //result.classList.add("d-none");
    //status.innerHTML = "";
    //input.value = "";
    //}

    function showEmpty() {
        empty.classList.remove("d-none");
        result.classList.add("d-none");
        status.innerHTML = "";
        input.value = "";
        root.dataset.duplicate = "false";
    }

    function hidePreview() {
        if (previewBox) previewBox.classList.add("d-none");
    }

    // The preview box is shared by the PDF's own abstract and an AI draft
    // (initAbstractGenerate) — reset its label whenever the PDF is re-read.
    function setPreviewLabel(text) {
        if (previewLabel) previewLabel.textContent = text;
    }

    if (previewUseBtn) {
        previewUseBtn.addEventListener("click", () => {
            if (abstractField && previewText) {
                abstractField.value = previewText.textContent;
                abstractField.dispatchEvent(
                    new Event("input", { bubbles: true }),
                );
            }
            hidePreview();
        });
    }

    if (previewDismissBtn) {
        previewDismissBtn.addEventListener("click", () => {
            hidePreview();
            if (abstractField) abstractField.focus();
        });
    }

    function analyze(file) {
        status.innerHTML =
            '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Validating document…</span>';

        const data = new FormData();
        data.append("file", file);

        fetch(analyzeUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfTokenFor(root),
                Accept: "application/json",
            },
            body: data,
        })
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok || !body.success) {
                    const message =
                        body.message || "This PDF could not be validated.";
                    status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${message}</span>`;
                    showError(message);
                    form.dataset.pdfHash = "";
                    form.dispatchEvent(
                        new CustomEvent("research:pdf-analyzed", {
                            detail: { canGenerateAbstract: false },
                        }),
                    );
                    return;
                }

                // EXACT DUPLICATE CHECK
                if (body.duplicate) {
                    const message =
                        body.duplicate_message ||
                        "This research document has already been uploaded. Please select a different PDF.";

                    status.innerHTML = `
            <span class="text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>
                ${message}
            </span>
        `;

                    showError(message);

                    // Prevent accidental submission of the duplicate file.
                    root.dataset.duplicate = "true";

                    return;
                }

                // File is not a duplicate.
                root.dataset.duplicate = "false";

                let statusHtml =
                    '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Document validated</span>';
                if (fallbackExcerptField) {
                    fallbackExcerptField.value = body.fallback_excerpt || "";
                }

                // Read by initAnalyzeButton (author keywords, so AI
                // suggests complementary ones) and initAbstractGenerate
                // (the hash identifies the server-side paper excerpt).
                form.dataset.pdfHash = body.pdf_hash || "";
                form.dataset.extractedKeywords = JSON.stringify(
                    Array.isArray(body.keywords) ? body.keywords : [],
                );
                form.dispatchEvent(
                    new CustomEvent("research:pdf-analyzed", {
                        detail: {
                            canGenerateAbstract: Boolean(
                                body.can_generate_abstract,
                            ),
                        },
                    }),
                );

                // A mostly-scanned PDF was already rejected server-side;
                // a partly-scanned one is accepted but worth flagging,
                // since its image pages contribute no text to the
                // abstract, keywords or search index.
                if (body.scan && body.scan.verdict === "partial") {
                    statusHtml += `<br><span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>${body.scan.image_pages} of ${body.scan.page_count} pages look like scanned images — their text can't be read. Consider uploading a fully text-based PDF.</span>`;
                }

                const hasAbstractText = Boolean(
                    body.abstract && body.abstract.text,
                );
                const isHighConfidence =
                    hasAbstractText && body.abstract.confidence === "high";

                if (
                    isHighConfidence &&
                    abstractField &&
                    !abstractField.value.trim()
                ) {
                    abstractField.value = body.abstract.text;
                    // Setting .value directly doesn't fire 'input', so
                    // the "Analyze Research" button's enable/disable
                    // check (initAnalyzeButton, listening for 'input'
                    // on this field) and the review summary (listening
                    // at the form level) never re-run — the button was
                    // staying disabled forever after an auto-filled
                    // abstract, with no visible sign why.
                    abstractField.dispatchEvent(
                        new Event("input", { bubbles: true }),
                    );

                    if (abstractBadge) {
                        // A confident word-count match can still be the
                        // wrong block (e.g. a CV/personal-info page that
                        // happens to fall in the expected word range) —
                        // this "not right?" escape hatch stays available
                        // even on a high-confidence auto-fill, rather than
                        // only ever surfacing on low confidence.
                        abstractBadge.innerHTML =
                            "✓ Abstract extracted from document — " +
                            '<button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-abstract-clear>not right? Clear it</button>';
                        abstractBadge.classList.remove("d-none", "text-muted");
                        abstractBadge.classList.add("text-success");

                        const clearBtn = abstractBadge.querySelector(
                            "[data-abstract-clear]",
                        );
                        if (clearBtn) {
                            clearBtn.addEventListener("click", () => {
                                abstractField.value = "";
                                abstractField.dispatchEvent(
                                    new Event("input", { bubbles: true }),
                                );
                                abstractBadge.classList.add("d-none");
                            });
                        }
                    }

                    statusHtml +=
                        '<br><span class="text-success"><i class="bi bi-check-circle me-1"></i>Abstract detected</span>';
                } else if (hasAbstractText && previewBox && previewText) {
                    // Low confidence (or the field already has manually-
                    // typed content): show what was found and let the
                    // student decide, rather than silently overwriting or
                    // silently discarding it.
                    setPreviewLabel(
                        "We found this text in your PDF — is this your abstract?",
                    );
                    previewText.textContent = body.abstract.text;
                    previewBox.classList.remove("d-none");
                    if (abstractBadge) abstractBadge.classList.add("d-none");
                } else if (abstractBadge) {
                    abstractBadge.textContent = body.can_generate_abstract
                        ? "We couldn't reliably detect an abstract."
                        : "We couldn't reliably detect an abstract — please enter it manually.";
                    abstractBadge.classList.remove("d-none", "text-success");
                    abstractBadge.classList.add("text-muted");
                }

                if (
                    keywordInputEl &&
                    Array.isArray(body.keywords) &&
                    body.keywords.length
                ) {
                    body.keywords.forEach((kw) =>
                        keywordSuggestionAdd(keywordInputEl, kw, "extracted"),
                    );
                }

                status.innerHTML = statusHtml;
            })
            .catch(() => {
                const message =
                    "Could not validate this file right now. You can still submit — it will be re-checked on submission.";
                status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${message}</span>`;
            });
    }

    function handleFile(file) {
        if (!file) return;

        root.dataset.duplicate = "false";

        if (
            file.type !== "application/pdf" &&
            !file.name.toLowerCase().endsWith(".pdf")
        ) {
            showEmpty();
            showError("Only PDF files are allowed.");
            return;
        }

        if (file.size > maxBytes) {
            showEmpty();
            showError(`The PDF file must not exceed ${formatBytes(maxBytes)}.`);
            return;
        }

        showError("");
        showSelected(file);
        analyze(file);
    }

    // The whole dropzone is clickable while empty (reference: "Click to
    // upload or drag and drop") — the inner link is still a real <button>
    // for keyboard/focus semantics, but doesn't need its own handler since
    // its click bubbles up to this one.
    root.addEventListener("click", (e) => {
        if (replaceBtn && replaceBtn.contains(e.target)) return;
        if (!empty.classList.contains("d-none")) {
            input.click();
        }
    });

    root.addEventListener("keydown", (e) => {
        if (
            (e.key === "Enter" || e.key === " ") &&
            !empty.classList.contains("d-none")
        ) {
            e.preventDefault();
            input.click();
        }
    });

    if (replaceBtn) {
        replaceBtn.addEventListener("click", () => {
            showError("");
            input.click();
        });
    }

    input.addEventListener("change", () => handleFile(input.files[0]));

    ["dragover", "dragenter"].forEach((evt) =>
        root.addEventListener(evt, (e) => {
            e.preventDefault();
            root.classList.add("border-primary");
        }),
    );

    ["dragleave", "drop"].forEach((evt) =>
        root.addEventListener(evt, (e) => {
            e.preventDefault();
            root.classList.remove("border-primary");
        }),
    );

    root.addEventListener("drop", (e) => {
        const file = e.dataTransfer.files[0];
        if (file) {
            input.files = e.dataTransfer.files;
            handleFile(file);
        }
    });

    // The file input's `required` attribute was removed from the (hidden,
    // display:none) markup because Chrome silently refuses to submit a form
    // containing an invalid, unfocusable required control — no bubble, no
    // console message a user would ever see, just a dead Submit button.
    // Enforce the same rule here instead, where feedback is visible. (The
    // wizard's own per-step validation, initWizard/validateStep, enforces
    // this too before letting the student leave the Document step — this
    // submit-time check is what catches it on the Update Research form,
    // which has no wizard.)
    //if (root.dataset.required === "true") {
    //  form.addEventListener("submit", (e) => {
    //     if (input.files.length === 0) {
    //        e.preventDefault();
    //        showError("Please attach your research PDF.");
    //       root.scrollIntoView({ behavior: "smooth", block: "center" });
    //   }
    // });
    // }

    if (root.dataset.required === "true") {
        form.addEventListener("submit", (e) => {
            if (input.files.length === 0) {
                e.preventDefault();
                showError("Please attach your research PDF.");
                root.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
        });
    }
}

function keywordInputAdd(root, text, source = "manual") {
    text = (text || "").trim();
    if (!text) return;

    const chips = root.querySelector("[data-keyword-chips]");
    const normalized = text.toLowerCase();

    const already = Array.from(
        chips.querySelectorAll("[data-keyword-value]"),
    ).some((el) => el.dataset.keywordValue.toLowerCase() === normalized);
    if (already) return;

    const chip = document.createElement("span");
    chip.className =
        "badge rounded-pill text-dark border d-inline-flex align-items-center gap-2 px-3 py-2";
    chip.style.background = source === "ai_suggested" ? "#f3e8ff" : "#eef2ff";
    chip.dataset.keywordValue = text;
    chip.dataset.keywordSource = source;

    const label = document.createElement("span");
    if (source === "ai_suggested") {
        const icon = document.createElement("i");
        icon.className = "bi bi-stars";
        label.appendChild(icon);
        label.append(` ${text}`);
    } else {
        label.textContent = text;
    }

    const removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.className = "btn-close";
    removeBtn.style.fontSize = ".55rem";
    removeBtn.setAttribute("aria-label", "Remove");
    removeBtn.addEventListener("click", () => {
        chip.remove();
        syncKeywordHiddenInputs(root);
    });

    chip.append(label, removeBtn);
    chips.appendChild(chip);
    syncKeywordHiddenInputs(root);
}

function syncKeywordHiddenInputs(root) {
    const container = root.querySelector("[data-keyword-hidden-inputs]");
    container.innerHTML = "";

    root.querySelectorAll("[data-keyword-value]").forEach((chip) => {
        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "keywords[]";
        hidden.value = chip.dataset.keywordValue;
        container.appendChild(hidden);

        // Aligned by index with keywords[] above (both built in this same
        // loop, over the same NodeList) — lets the server tell an accepted
        // AI suggestion apart from a hand-typed keyword. The server never
        // trusts this for 'extracted': that tag is always re-derived by
        // re-matching against the freshly re-extracted PDF text.
        const sourceHidden = document.createElement("input");
        sourceHidden.type = "hidden";
        sourceHidden.name = "keyword_sources[]";
        sourceHidden.value = chip.dataset.keywordSource || "manual";
        container.appendChild(sourceHidden);
    });
}

/**
 * Adds a suggested keyword to the review tray — never directly to the
 * student's list. Returns true if a new suggestion chip was actually
 * added (false if it's a duplicate, already accepted, or was previously
 * dismissed and shouldn't come back on a re-analyze).
 */
function keywordSuggestionAdd(root, text, source = "extracted") {
    text = (text || "").trim();
    if (!text) return false;

    const normalized = text.toLowerCase();

    const acceptedAlready = Array.from(
        root.querySelectorAll("[data-keyword-value]"),
    ).some((el) => el.dataset.keywordValue.toLowerCase() === normalized);
    if (acceptedAlready) return false;

    if (!root._dismissedKeywords) root._dismissedKeywords = new Set();
    if (root._dismissedKeywords.has(normalized)) return false;

    const tray = root.querySelector("[data-keyword-suggestions]");
    const chips = root.querySelector("[data-keyword-suggestion-chips]");
    if (!tray || !chips) return false;

    const alreadySuggested = Array.from(
        chips.querySelectorAll("[data-suggestion-value]"),
    ).some((el) => el.dataset.suggestionValue.toLowerCase() === normalized);
    if (alreadySuggested) return false;

    const chip = document.createElement("button");
    chip.type = "button";
    chip.className = "keyword-suggestion-chip";
    chip.dataset.suggestionValue = text;

    const icon = document.createElement("i");
    icon.className = "bi bi-plus-lg me-1";
    chip.appendChild(icon);
    chip.append(text);

    chip.addEventListener("click", () => {
        keywordInputAdd(root, text, source);
        chip.remove();
        if (!chips.children.length) tray.classList.add("d-none");
    });

    chips.appendChild(chip);
    tray.classList.remove("d-none");

    return true;
}

function initKeywordInput(root) {
    const textInput = root.querySelector("[data-keyword-text-input]");
    const addBtn = root.querySelector("[data-keyword-add-btn]");
    const addAllBtn = root.querySelector("[data-keyword-suggestions-add-all]");
    const dismissBtn = root.querySelector("[data-keyword-suggestions-dismiss]");

    function addFromInput() {
        keywordInputAdd(root, textInput.value);
        textInput.value = "";
    }

    addBtn.addEventListener("click", addFromInput);
    textInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === ",") {
            e.preventDefault();
            addFromInput();
        }
    });

    if (addAllBtn) {
        addAllBtn.addEventListener("click", () => {
            const chips = root.querySelector("[data-keyword-suggestion-chips]");
            Array.from(
                chips.querySelectorAll("[data-suggestion-value]"),
            ).forEach((chip) => chip.click());
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener("click", () => {
            const chips = root.querySelector("[data-keyword-suggestion-chips]");
            if (!root._dismissedKeywords) root._dismissedKeywords = new Set();

            Array.from(
                chips.querySelectorAll("[data-suggestion-value]"),
            ).forEach((chip) => {
                root._dismissedKeywords.add(
                    chip.dataset.suggestionValue.toLowerCase(),
                );
                chip.remove();
            });

            root.querySelector("[data-keyword-suggestions]")?.classList.add(
                "d-none",
            );
        });
    }

    // Seed keywords (the update form's already-saved list) go straight
    // into the real chip list, tagged 'extracted' — these are the file's
    // existing keywords, not a fresh suggestion to review. The server
    // re-derives the actual source on submit regardless (see
    // StudentController::syncKeywords), so this tag is cosmetic only.
    let seed = [];
    try {
        seed = JSON.parse(root.dataset.seed || "[]");
    } catch {
        seed = [];
    }
    seed.forEach((k) =>
        keywordInputAdd(root, typeof k === "string" ? k : k.text, "extracted"),
    );
}

function updateSdgSummary(form, prefix) {
    const section = form.querySelector(`#sdgSection-${prefix}`);
    if (!section) return;

    let byId = {};
    try {
        byId = JSON.parse(section.dataset.sdgById || "{}");
    } catch {
        byId = {};
    }

    const checked = section.querySelectorAll(".sdg-checkbox:checked");
    const summary = section.querySelector("[data-sdg-summary]");

    summary.innerHTML = "";

    if (checked.length === 0) {
        summary.classList.add("d-none");
    } else {
        summary.classList.remove("d-none");

        checked.forEach((cb) => {
            const info = byId[cb.value];
            const badge = document.createElement("span");
            badge.className = "badge rounded-pill text-dark border px-3 py-2";
            badge.style.background = "#eef2ff";
            badge.textContent = info
                ? `SDG ${info.number} – ${info.name}`
                : `SDG ${cb.value}`;
            summary.appendChild(badge);
        });
    }

    const dropdownToggle = form.querySelector(`#sdgDropdown-${prefix}`);
    dropdownToggle.textContent = checked.length
        ? `${checked.length} SDG(s) selected — Change SDGs`
        : "Select SDG(s)";
}

/**
 * Adds a suggested SDG (by real sdgs.id, already resolved server-side —
 * see ResearchAnalysisController) as a row in the suggestion tray. Returns
 * true if a new row was added.
 */
function sdgSuggestionAdd(form, prefix, sdg) {
    const section = form.querySelector(`#sdgSection-${prefix}`);
    if (!section) return false;

    const checkbox = section.querySelector(`.sdg-checkbox[value="${sdg.id}"]`);
    if (!checkbox || checkbox.checked) return false;

    if (!section._dismissedSdgs) section._dismissedSdgs = new Set();
    if (section._dismissedSdgs.has(sdg.number)) return false;

    const tray = section.querySelector("[data-sdg-suggestions]");
    const rows = section.querySelector("[data-sdg-suggestion-rows]");
    if (!tray || !rows) return false;

    const alreadySuggested = Array.from(
        rows.querySelectorAll("[data-suggestion-number]"),
    ).some((el) => Number(el.dataset.suggestionNumber) === sdg.number);
    if (alreadySuggested) return false;

    let byId = {};
    try {
        byId = JSON.parse(section.dataset.sdgById || "{}");
    } catch {
        byId = {};
    }
    const info = byId[sdg.id] || { number: sdg.number, name: "" };

    const row = document.createElement("div");
    row.className = "sdg-suggestion-row";
    row.dataset.suggestionNumber = String(sdg.number);

    const label = document.createElement("span");
    label.innerHTML = `<strong>SDG ${info.number}</strong> – ${info.name} `;
    const confidence = document.createElement("span");
    confidence.className = "text-muted";
    confidence.textContent = `· ${Math.round(sdg.confidence * 100)}%`;
    label.appendChild(confidence);

    const addRowBtn = document.createElement("button");
    addRowBtn.type = "button";
    addRowBtn.className = "btn btn-sm btn-outline-primary";
    addRowBtn.textContent = "Add";
    addRowBtn.addEventListener("click", () => {
        checkbox.checked = true;
        row.remove();
        updateSdgSummary(form, prefix);
        if (!rows.children.length) tray.classList.add("d-none");
        const errorBox = section.querySelector("[data-sdg-error]");
        if (errorBox) errorBox.classList.add("d-none");
    });

    row.append(label, addRowBtn);
    rows.appendChild(row);
    tray.classList.remove("d-none");

    return true;
}

function initSdgSection(form) {
    form.querySelectorAll("[data-sdg-done]").forEach((btn) => {
        const prefix = btn.dataset.sdgDone;

        btn.addEventListener("click", () => {
            const checked = form.querySelectorAll(
                `#sdgSection-${prefix} .sdg-checkbox:checked`,
            );
            const errorBox = form.querySelector(
                `#sdgSection-${prefix} [data-sdg-error]`,
            );

            if (checked.length === 0) {
                if (errorBox) errorBox.classList.remove("d-none");
                return;
            }

            if (errorBox) errorBox.classList.add("d-none");
            updateSdgSummary(form, prefix);

            const dropdownToggle = form.querySelector(`#sdgDropdown-${prefix}`);
            if (window.bootstrap) {
                window.bootstrap.Dropdown.getOrCreateInstance(
                    dropdownToggle,
                ).hide();
            }
        });
    });

    form.querySelectorAll('[id^="sdgSection-"]').forEach((section) => {
        const prefix = section.id.replace("sdgSection-", "");

        const addAllBtn = section.querySelector(
            "[data-sdg-suggestions-add-all]",
        );
        const dismissBtn = section.querySelector(
            "[data-sdg-suggestions-dismiss]",
        );

        if (addAllBtn) {
            addAllBtn.addEventListener("click", () => {
                const rows = section.querySelector(
                    "[data-sdg-suggestion-rows]",
                );
                Array.from(
                    rows.querySelectorAll("[data-suggestion-number]"),
                ).forEach((row) => {
                    row.querySelector("button")?.click();
                });
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener("click", () => {
                const rows = section.querySelector(
                    "[data-sdg-suggestion-rows]",
                );
                if (!section._dismissedSdgs) section._dismissedSdgs = new Set();

                Array.from(
                    rows.querySelectorAll("[data-suggestion-number]"),
                ).forEach((row) => {
                    section._dismissedSdgs.add(
                        Number(row.dataset.suggestionNumber),
                    );
                    row.remove();
                });

                section
                    .querySelector("[data-sdg-suggestions]")
                    ?.classList.add("d-none");
            });
        }

        section.querySelectorAll(".sdg-checkbox").forEach((cb) => {
            cb.addEventListener("change", () => {
                updateSdgSummary(form, prefix);
                if (cb.checked) {
                    const errorBox = section.querySelector("[data-sdg-error]");
                    if (errorBox) errorBox.classList.add("d-none");
                }
            });
        });

        updateSdgSummary(form, prefix); // reflect any pre-checked (e.g. edit form) state on load
    });
}

// "· 7 of 10 AI uses left today", or a note that the result came from
// cache and cost nothing — appended to either AI action's status line.
function aiQuotaNote(body) {
    if (body.cached) {
        return '<br><span class="text-muted small"><i class="bi bi-lightning-charge me-1"></i>Loaded from cache — no AI use counted.</span>';
    }
    if (body.quota) {
        return `<br><span class="text-muted small">${body.quota.remaining} of ${body.quota.limit} AI uses left today.</span>`;
    }
    return "";
}

// Today's SubmissionAiQuota is spent: disable every AI button in the form.
// Manual entry stays available, and a cached result would still be free,
// but there's no way to tell which clicks would hit the cache client-side.
function markAiQuotaExhausted(form) {
    form.dataset.aiQuotaExhausted = "true";
    form.querySelectorAll(
        "[data-analyze-btn], [data-abstract-generate-btn]",
    ).forEach((btn) => {
        btn.disabled = true;
        btn.title = "Today's AI limit is used up — you can still type it manually.";
    });
}

// "Generate abstract with AI" — only offered when the PDF had no
// confidently-detected abstract (research:pdf-analyzed). Sends just the
// PDF hash + title; the paper excerpt is held server-side. The draft goes
// into the [data-abstract-preview] box for the student to accept or
// dismiss — it never overwrites the abstract field on its own.
function initAbstractGenerate(form) {
    const box = form.querySelector("[data-abstract-generate]");
    const btn = form.querySelector("[data-abstract-generate-btn]");
    const status = form.querySelector("[data-abstract-generate-status]");
    const titleField = form.querySelector('[name="title"]');
    const abstractField = form.querySelector("[data-abstract-field]");
    const previewBox = form.querySelector("[data-abstract-preview]");
    const previewLabel = form.querySelector("[data-abstract-preview-label]");
    const previewText = form.querySelector("[data-abstract-preview-text]");

    if (!box || !btn || !status) return;

    form.addEventListener("research:pdf-analyzed", (e) => {
        box.classList.toggle("d-none", !e.detail.canGenerateAbstract);
        status.innerHTML = "";
    });

    btn.addEventListener("click", () => {
        const title = titleField ? titleField.value.trim() : "";
        const pdfHash = form.dataset.pdfHash || "";

        if (!pdfHash) {
            status.innerHTML =
                '<span class="text-danger">Please select your PDF first.</span>';
            return;
        }
        if (!title) {
            status.innerHTML =
                '<span class="text-danger">Please enter your research title first — it helps the AI write an accurate abstract.</span>';
            if (titleField) titleField.focus();
            return;
        }

        btn.disabled = true;
        status.innerHTML =
            '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Drafting an abstract from your paper…</span>';

        fetch(btn.dataset.generateUrl || "/research/abstract/generate", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfTokenFor(form),
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ pdf_hash: pdfHash, title }),
        })
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok || !body.success) {
                    if (body.quota && body.quota.remaining === 0) {
                        markAiQuotaExhausted(form);
                    }
                    status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${body.message || "AI abstract generation is temporarily unavailable. Please write the abstract manually."}</span>`;
                    return;
                }

                if (previewBox && previewText) {
                    if (previewLabel) {
                        previewLabel.textContent =
                            "AI-generated draft — review it, then edit it in the abstract box before submitting.";
                    }
                    previewText.textContent = body.abstract;
                    previewBox.classList.remove("d-none");
                } else if (abstractField && !abstractField.value.trim()) {
                    abstractField.value = body.abstract;
                    abstractField.dispatchEvent(
                        new Event("input", { bubbles: true }),
                    );
                }

                status.innerHTML =
                    '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Draft ready below.</span>' +
                    aiQuotaNote(body);
                if (body.quota && body.quota.remaining === 0) {
                    markAiQuotaExhausted(form);
                }
            })
            .catch(() => {
                status.innerHTML =
                    '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>AI abstract generation is temporarily unavailable. Please write the abstract manually.</span>';
            })
            .finally(() => {
                if (form.dataset.aiQuotaExhausted !== "true") {
                    btn.disabled = false;
                }
            });
    });
}

function initAnalyzeButton(form, prefix) {
    const titleField = form.querySelector('[name="title"]');
    const abstractField = form.querySelector("[data-abstract-field]");
    const buttons = form.querySelectorAll(`[data-analyze-btn="${prefix}"]`);
    const status = form.querySelector("[data-analyze-status]");
    const staleBanner = form.querySelector("[data-analyze-stale]");
    const fingerprintField = form.querySelector("[data-ai-fingerprint-field]");
    const modelField = form.querySelector("[data-ai-model-field]");
    const fallbackExcerptField = form.querySelector(
        "[data-fallback-excerpt-field]",
    );
    const keywordInputEl = form.querySelector(`#keywords-${prefix}`);
    const analyzeUrl = buttons[0]?.dataset.analyzeUrl || "/research/analyze";

    if (!titleField || !abstractField || buttons.length === 0) return;

    let lastAnalyzed = null; // { title, abstract }

    function currentContent() {
        return {
            title: titleField.value.trim(),
            abstract: abstractField.value.trim(),
        };
    }

    function refreshButtonState() {
        const { title, abstract } = currentContent();
        const ready =
            Boolean(title) &&
            Boolean(abstract) &&
            form.dataset.aiQuotaExhausted !== "true";
        buttons.forEach((btn) => {
            if (btn.hasAttribute("disabled") !== !ready) btn.disabled = !ready;
        });

        if (
            lastAnalyzed &&
            (title !== lastAnalyzed.title || abstract !== lastAnalyzed.abstract)
        ) {
            staleBanner.classList.remove("d-none");
            if (fingerprintField) fingerprintField.value = "";
            if (modelField) modelField.value = "";
        } else {
            staleBanner.classList.add("d-none");
        }
    }

    function runAnalysis() {
        const { title, abstract } = currentContent();
        if (!title || !abstract) return;

        status.innerHTML =
            '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Analyzing title and abstract…</span>';
        buttons.forEach((btn) => (btn.disabled = true));

        const payload = { title, abstract };

        // Always send the intro excerpt when the PDF had one, so keyword
        // suggestions are grounded in the paper itself and not only in
        // its abstract (the server caps the total prompt size).
        if (fallbackExcerptField && fallbackExcerptField.value) {
            payload.fallback_excerpt = fallbackExcerptField.value;
        }

        // Keywords the author already listed in the PDF — the AI is asked
        // to complement these rather than repeat them.
        try {
            const existing = JSON.parse(form.dataset.extractedKeywords || "[]");
            if (Array.isArray(existing) && existing.length) {
                payload.existing_keywords = existing.slice(0, 30);
            }
        } catch {
            // Malformed dataset value — just send without it.
        }

        fetch(analyzeUrl, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfTokenFor(form),
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        })
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok || !body.success) {
                    if (body.quota && body.quota.remaining === 0) {
                        markAiQuotaExhausted(form);
                    }
                    status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${body.message || "AI suggestions are temporarily unavailable. You can continue by entering keywords and SDGs manually."}</span>`;
                    return;
                }

                let addedKeywords = false;
                if (keywordInputEl && Array.isArray(body.keywords)) {
                    body.keywords.forEach((kw) => {
                        if (
                            keywordSuggestionAdd(
                                keywordInputEl,
                                kw,
                                "ai_suggested",
                            )
                        )
                            addedKeywords = true;
                    });
                }

                let addedSdgs = false;
                if (Array.isArray(body.sdgs)) {
                    body.sdgs.forEach((sdg) => {
                        if (sdgSuggestionAdd(form, prefix, sdg))
                            addedSdgs = true;
                    });
                }

                if (fingerprintField)
                    fingerprintField.value = body.fingerprint || "";
                if (modelField) modelField.value = body.model || "";

                lastAnalyzed = { title, abstract };
                staleBanner.classList.add("d-none");

                // The request can succeed with nothing worth adding (e.g.
                // no SDG cleared the confidence threshold) — the old
                // unconditional "suggestions added below" message showed
                // even then, which read as the click having silently done
                // nothing since there was no visible change to point at.
                status.innerHTML =
                    (addedKeywords || addedSdgs
                        ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Suggestions added below — review before adding them.</span>'
                        : '<span class="text-muted"><i class="bi bi-info-circle me-1"></i>AI ran, but found no confident keyword or SDG suggestions for this abstract — add them manually below.</span>') +
                    aiQuotaNote(body);
                if (body.quota && body.quota.remaining === 0) {
                    markAiQuotaExhausted(form);
                }
            })
            .catch(() => {
                status.innerHTML =
                    '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>AI suggestions are temporarily unavailable. You can continue by entering keywords and SDGs manually.</span>';
            })
            .finally(() => refreshButtonState());
    }

    buttons.forEach((btn) => btn.addEventListener("click", runAnalysis));
    [titleField, abstractField].forEach((el) =>
        el.addEventListener("input", refreshButtonState),
    );

    refreshButtonState();
}

/**
 * Free-text researcher list — nobody is added automatically (not even the
 * submitter, already recorded as the file's owner). Each accepted row is
 * either linked to a registered student (name matched one exactly, from
 * the datalist) or a plain "guest" name with no account.
 */
function buildResearcherRow(name, linked) {
    const row = document.createElement("div");
    row.className = "researcher-row";
    row.setAttribute("data-researcher-row", "1");

    const left = document.createElement("div");
    left.className = "d-flex align-items-center gap-2";

    const avatar = document.createElement("span");
    avatar.className = "researcher-avatar";
    avatar.textContent = name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join("");

    const nameEl = document.createElement("span");
    nameEl.className = "fw-semibold";
    nameEl.textContent = name;

    left.append(avatar, nameEl);

    const right = document.createElement("div");
    right.className = "d-flex align-items-center gap-2";

    const badge = document.createElement("span");
    badge.className = "badge rounded-pill text-bg-light border";
    badge.textContent = linked ? "Registered" : "Guest";

    const removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.className = "btn-close";
    removeBtn.style.fontSize = ".65rem";
    removeBtn.setAttribute("aria-label", "Remove");

    right.append(badge, removeBtn);
    row.append(left, right);

    return { row, removeBtn };
}

function initResearcherInput(root) {
    const list = root.querySelector("[data-researcher-list]");
    const empty = root.querySelector("[data-researcher-empty]");
    const textInput = root.querySelector("[data-researcher-text-input]");
    const addBtn = root.querySelector("[data-researcher-add-btn]");
    const hiddenContainer = root.querySelector(
        "[data-researcher-hidden-inputs]",
    );
    const datalist = root.querySelector("datalist");

    if (!list || !empty || !textInput || !addBtn || !hiddenContainer) {
        console.error(
            "[research-submission] researcher-input is missing required markup, skipping init",
            root,
        );
        return;
    }

    let rows = []; // { name, userId }

    function findUserId(name) {
        if (!datalist) return null;
        const normalized = name.trim().toLowerCase();
        const option = Array.from(datalist.querySelectorAll("option")).find(
            (opt) => opt.value.trim().toLowerCase() === normalized,
        );
        return option ? option.dataset.userId : null;
    }

    function render() {
        list.innerHTML = "";
        empty.classList.toggle("d-none", rows.length > 0);

        rows.forEach((entry, index) => {
            const { row, removeBtn } = buildResearcherRow(
                entry.name,
                Boolean(entry.userId),
            );
            removeBtn.addEventListener("click", () => {
                rows.splice(index, 1);
                render();
            });
            list.appendChild(row);
        });

        hiddenContainer.innerHTML = "";
        rows.forEach((entry, index) => {
            const nameInput = document.createElement("input");
            nameInput.type = "hidden";
            nameInput.name = `researchers[${index}][name]`;
            nameInput.value = entry.name;
            hiddenContainer.appendChild(nameInput);

            if (entry.userId) {
                const idInput = document.createElement("input");
                idInput.type = "hidden";
                idInput.name = `researchers[${index}][user_id]`;
                idInput.value = entry.userId;
                hiddenContainer.appendChild(idInput);
            }
        });

        // Lets the review summary (listening at the form level for
        // 'input'/'change') pick up researcher edits, which otherwise only
        // ever happen via button clicks, not native input/change events.
        root.dispatchEvent(new Event("change", { bubbles: true }));
    }

    function addFromInput() {
        const name = textInput.value.trim();
        if (!name) return;

        const normalized = name.toLowerCase();
        if (rows.some((r) => r.name.toLowerCase() === normalized)) {
            textInput.value = "";
            return;
        }

        rows.push({ name, userId: findUserId(name) });
        textInput.value = "";
        render();
    }

    addBtn.addEventListener("click", addFromInput);
    textInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            addFromInput();
        }
    });

    let seed = [];
    try {
        seed = JSON.parse(root.dataset.seed || "[]");
    } catch {
        seed = [];
    }

    rows = seed
        .map((r) => ({
            name: (r && r.name ? String(r.name) : "").trim(),
            userId: r && r.user_id ? String(r.user_id) : null,
        }))
        .filter((r) => r.name !== "");

    render();
}

/**
 * Multi-step "Submit New Research" wizard — Details / Researchers /
 * Document / Review. Only the current step's fields are visible; a step
 * already passed collapses to a one-line summary row (click it, or its
 * Edit link, to jump back). Not used by the Update Research form, which
 * stays a single scrolling panel.
 */
function initWizard(root) {
    const form = root.querySelector(".rw-form");
    if (!form) return;

    const stepSections = Array.from(
        root.querySelectorAll("[data-wizard-step]"),
    );
    if (stepSections.length === 0) return;

    const stepNumbers = stepSections.map((el) => Number(el.dataset.wizardStep));
    const maxStep = Math.max(...stepNumbers);

    const backBtn = root.querySelector("[data-wizard-back]");
    const nextBtn = root.querySelector("[data-wizard-next]");
    const submitBtn = root.querySelector("[data-wizard-submit]");
    const stepIndicators = Array.from(
        root.querySelectorAll("[data-step-indicator]"),
    );

    let currentStep = parseInt(root.dataset.wizardErrorStep, 10) || 1;
    if (currentStep < 1 || currentStep > maxStep) currentStep = 1;

    function summaryTextFor(n) {
        if (n === 1) {
            const title = form.querySelector('[name="title"]');
            const value = title ? title.value.trim() : "";
            return value ? `Title: ${value}` : "Research details";
        }

        if (n === 2) {
            const rows = root.querySelectorAll("[data-researcher-row]").length;
            return rows === 0
                ? "No researchers added"
                : `${rows} researcher${rows === 1 ? "" : "s"} added`;
        }

        if (n === 3) {
            const fileInput = root.querySelector("[data-dropzone-input]");
            const file = fileInput && fileInput.files[0];
            return file ? `Document: ${file.name}` : "Document & keywords";
        }

        return `Step ${n}`;
    }

    function render() {
        stepSections.forEach((section) => {
            const n = Number(section.dataset.wizardStep);
            const body = section.querySelector(`[data-step-body="${n}"]`);
            const summary = section.querySelector(`[data-step-summary="${n}"]`);

            if (n === currentStep) {
                section.hidden = false;
                if (body) body.hidden = false;
                if (summary) summary.hidden = true;
            } else if (n < currentStep) {
                section.hidden = false;
                if (body) body.hidden = true;
                if (summary) {
                    summary.hidden = false;
                    const textEl = summary.querySelector(
                        "[data-step-summary-text]",
                    );
                    if (textEl) textEl.textContent = summaryTextFor(n);
                }
            } else {
                section.hidden = true;
            }
        });

        stepIndicators.forEach((el) => {
            const n = Number(el.dataset.stepIndicator);
            el.classList.remove("is-current", "is-done");

            const circle = el.querySelector(".rw-step-circle");

            if (n === currentStep) {
                el.classList.add("is-current");
                if (circle) circle.textContent = String(n);
            } else if (n < currentStep) {
                el.classList.add("is-done");
                if (circle) circle.innerHTML = '<i class="bi bi-check-lg"></i>';
            } else if (circle) {
                circle.textContent = String(n);
            }
        });

        if (backBtn) backBtn.hidden = currentStep === 1;

        const isLastStep = currentStep === maxStep;
        if (nextBtn) nextBtn.classList.toggle("d-none", isLastStep);
        if (submitBtn) submitBtn.classList.toggle("d-none", !isLastStep);
    }

    function validateStep(n) {
        const section = stepSections.find(
            (s) => Number(s.dataset.wizardStep) === n,
        );
        if (!section) return true;

        const body =
            section.querySelector(`[data-step-body="${n}"]`) || section;

        const invalid = Array.from(
            body.querySelectorAll("input, select, textarea"),
        ).find((el) => !el.disabled && el.willValidate && !el.checkValidity());

        if (invalid) {
            invalid.reportValidity();
            invalid.scrollIntoView({ behavior: "smooth", block: "center" });
            return false;
        }

        if (n === 3) {
            const dropzoneRoot = body.querySelector("[data-dropzone]");

            if (dropzoneRoot) {
                const fileInput = dropzoneRoot.querySelector(
                    "[data-dropzone-input]",
                );
                const dropzoneError = dropzoneRoot
                    .closest(".mb-3")
                    ?.querySelector("[data-dropzone-error]");

                if (
                    dropzoneRoot.dataset.required === "true" &&
                    fileInput &&
                    fileInput.files.length === 0
                ) {
                    if (dropzoneError)
                        dropzoneError.textContent =
                            "Please attach your research PDF.";
                    dropzoneRoot.classList.add("border-danger");
                    dropzoneRoot.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    return false;
                }
            }

            const sdgSection = body.querySelector('[id^="sdgSection-"]');
            if (sdgSection) {
                const checked = sdgSection.querySelectorAll(
                    ".sdg-checkbox:checked",
                );
                const errorBox = sdgSection.querySelector("[data-sdg-error]");

                if (checked.length === 0) {
                    if (errorBox) errorBox.classList.remove("d-none");
                    sdgSection.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                    return false;
                }

                if (errorBox) errorBox.classList.add("d-none");
            }
        }

        return true;
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            if (!validateStep(currentStep)) return;
            currentStep = Math.min(currentStep + 1, maxStep);
            render();
            root.querySelector(".rw-body")?.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        });
    }

    if (backBtn) {
        backBtn.addEventListener("click", () => {
            currentStep = Math.max(currentStep - 1, 1);
            render();
        });
    }

    root.querySelectorAll("[data-step-edit]").forEach((btn) => {
        btn.addEventListener("click", () => {
            currentStep = Number(btn.dataset.stepEdit);
            render();
            root.querySelector(".rw-body")?.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        });
    });

    // Enter inside a step's fields must never submit the form early — only
    // the final step's Submit button should ever POST. Textareas are
    // exempt (Enter there means "new line", and never submits a form
    // natively anyway).
    form.addEventListener("keydown", (e) => {
        if (
            e.key === "Enter" &&
            currentStep !== maxStep &&
            e.target.tagName !== "TEXTAREA"
        ) {
            e.preventDefault();
        }
    });

    render();
}

/**
 * Disables the submit button and swaps its label to "Submitting…" once
 * the form actually starts submitting — a large PDF upload can take a few
 * seconds, and without this a student clicking twice (thinking the first
 * click didn't register) would fire two submissions.
 *
 * Registered last among this form's 'submit' listeners (see the
 * init() block below — the dropzone's required-file check, the
 * one other listener that can call preventDefault() on this form, is
 * attached earlier), so by the time this one runs, e.defaultPrevented
 * correctly reflects whether the submission is actually going through.
 * Skipping it when true is what stops the button from getting stuck
 * disabled on a client-side-rejected submit.
 */
function initSubmitLoadingState(form) {
    const btn = form.querySelector("[data-submit-btn]");
    if (!btn) return;

    form.addEventListener("submit", (e) => {
        if (e.defaultPrevented) return;

        btn.disabled = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting…';
    });
}

function initReviewSummary(form) {
    const summary = form.querySelector("[data-review-summary]");
    if (!summary) return;

    const prefix = summary.dataset.prefix;

    function getField(...names) {
        for (const name of names) {
            const el = form.querySelector(`[name="${name}"]`);
            if (el) return el;
        }

        return null;
    }

    function getValue(...names) {
        const el = getField(...names);
        return el ? String(el.value || "").trim() : "";
    }

    function setReview(name, value) {
        const el = summary.querySelector(`[data-review="${name}"]`);

        if (el) {
            el.textContent = value || "—";
        }
    }

    function update() {
        // =========================
        // TITLE
        // =========================
        setReview("title", getValue("title"));

        // =========================
        // PUBLICATION YEAR
        // =========================
        setReview("publication_year", getValue("publication_year"));

        // =========================
        // ADVISER
        // =========================
        const adviserSelect = getField("adviser_id");

        let adviser = "";

        if (adviserSelect && adviserSelect.selectedIndex >= 0) {
            const option = adviserSelect.options[adviserSelect.selectedIndex];

            if (option && option.value) {
                adviser = option.textContent.trim();
            }
        }

        // fallback kung text field ang adviser
        if (!adviser) {
            adviser = getValue("adviser");
        }

        setReview("adviser", adviser);

        // =========================
        // RESEARCHERS
        // =========================
        const researcherRoot = form.querySelector("[data-researcher-input]");

        let researchers = [];

        if (researcherRoot) {
            researchers = Array.from(
                researcherRoot.querySelectorAll(
                    "[data-researcher-row] .fw-semibold",
                ),
            )
                .map((el) => el.textContent.trim())
                .filter(Boolean);
        }

        setReview(
            "researchers",
            researchers.length
                ? researchers.join(", ")
                : "None (you are the sole researcher)",
        );

        // =========================
        // ABSTRACT
        // =========================
        const abstractField = form.querySelector("[data-abstract-field]");

        const abstract = abstractField
            ? abstractField.value.trim()
            : getValue("desc", "abstract");

        setReview("desc", abstract);

        // =========================
        // KEYWORDS
        // =========================
        const keywordInput = form.querySelector(`#keywords-${prefix}`);

        let keywords = [];

        if (keywordInput) {
            keywords = Array.from(
                keywordInput.querySelectorAll("[data-keyword-value]"),
            )
                .map((el) => el.dataset.keywordValue)
                .filter(Boolean);
        }

        setReview("keywords", keywords.length ? keywords.join(", ") : "—");

        // =========================
        // SDGs
        // =========================
        const sdgSection = form.querySelector(`#sdgSection-${prefix}`);

        let sdgs = [];

        if (sdgSection) {
            let byId = {};

            try {
                byId = JSON.parse(sdgSection.dataset.sdgById || "{}");
            } catch {
                byId = {};
            }

            const checked = sdgSection.querySelectorAll(
                ".sdg-checkbox:checked",
            );

            sdgs = Array.from(checked).map((cb) => {
                const info = byId[cb.value];

                if (info) {
                    return `SDG ${info.number} – ${info.name}`;
                }

                return `SDG ${cb.value}`;
            });
        }

        setReview("sdgs", sdgs.length ? sdgs.join(", ") : "—");

        // =========================
        // DOCUMENT
        // =========================
        const fileInput = form.querySelector(
            `#dropzone-${prefix} [data-dropzone-input]`,
        );

        let filename = "";

        if (fileInput && fileInput.files && fileInput.files.length) {
            filename = fileInput.files[0].name;
        }

        setReview("file", filename);
    }

    // Normal form inputs
    form.addEventListener("input", update);
    form.addEventListener("change", update);

    // Initial display
    update();
}

// Called by resources/js/app.js on every 'turbo:load' — a Turbo visit
// swaps in fresh form markup without a DOMContentLoaded. Forms seen before
// are skipped so nothing is bound twice (a WeakSet, not a data-* flag, so
// the clones Turbo restores from its back/forward cache still get bound).
const initialized = new WeakSet();

function once(el) {
    if (initialized.has(el)) return false;
    initialized.add(el);
    return true;
}

export function init() {
    document
        .querySelectorAll("[data-dropzone]")
        .forEach(
            (root) =>
                once(root) &&
                safeInit(`dropzone#${root.id || root.dataset.prefix}`, () =>
                    initDropzone(root),
                ),
        );

    document
        .querySelectorAll("[data-keyword-input]")
        .forEach(
            (root) =>
                once(root) &&
                safeInit(
                    `keyword-input#${root.id || root.dataset.prefix}`,
                    () => initKeywordInput(root),
                ),
        );

    document
        .querySelectorAll("[data-researcher-input]")
        .forEach(
            (root) =>
                once(root) &&
                safeInit(
                    `researcher-input#${root.id || root.dataset.prefix}`,
                    () => initResearcherInput(root),
                ),
        );

    document
        .querySelectorAll("[data-wizard]")
        .forEach(
            (root) => once(root) && safeInit("wizard", () => initWizard(root)),
        );

    document.querySelectorAll("form").forEach((form) => {
        if (!once(form)) return;

        if (form.querySelector("[data-sdg-done]")) {
            safeInit("sdg-section", () => initSdgSection(form));
        }

        if (form.querySelector("[data-review-summary]")) {
            safeInit("review-summary", () => initReviewSummary(form));
        }

        if (form.querySelector("[data-abstract-generate]")) {
            safeInit("abstract-generate", () => initAbstractGenerate(form));
        }

        const analyzePrefixes = new Set(
            Array.from(form.querySelectorAll("[data-analyze-btn]")).map(
                (btn) => btn.dataset.analyzeBtn,
            ),
        );
        analyzePrefixes.forEach((prefix) =>
            safeInit(`analyze-button#${prefix}`, () =>
                initAnalyzeButton(form, prefix),
            ),
        );

        if (form.querySelector("[data-submit-btn]")) {
            safeInit("submit-loading-state", () =>
                initSubmitLoadingState(form),
            );
        }
    });
}
