/**
 * Knowledge Plugin Frontend Scripts
 * Handles Pagination (Load More / Infinite Scroll)
 */

(function($) {
    'use strict';

    class KnowledgePagination {
        constructor(container) {
            this.$container = $(container);
            this.$grid = this.$container.find('.knowledge-archive-grid');
            this.$pagination = this.$container.find('.knowledge-pagination');
            this.$button = this.$pagination.find('.knowledge-load-more-btn');
            this.$message = this.$pagination.find('.knowledge-end-message');
            this.$loader = this.$pagination.find('.knowledge-loading-spinner');
            
            this.type = this.$container.data('pagination-type');
            this.page = parseInt(this.$container.data('page')) || 1;
            this.maxPages = parseInt(this.$container.data('max-pages')) || 1;
            this.queryArgs = this.$container.data('query-args') || {};
            this.options = this.$container.data('options') || {};
            
            console.log('KnowledgePagination initialized:', {
                type: this.type,
                page: this.page,
                maxPages: this.maxPages,
                queryArgs: this.queryArgs,
                options: this.options
            });

            this.isLoading = false;

            if (this.type && this.maxPages > 1) {
                this.init();
            }
        }

        init() {
            if (this.type === 'load_more') {
                this.$button.on('click', (e) => {
                    e.preventDefault();
                    console.log('Load More clicked');
                    this.loadNextPage();
                });
            } else if (this.type === 'infinite_scroll') {
                console.log('Setting up infinite scroll');
                this.setupInfiniteScroll();
            }
        }


        setupInfiniteScroll() {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px 200px 0px', // Trigger 200px before end
                threshold: 0.1
            };

            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.isLoading && this.page < this.maxPages) {
                        this.loadNextPage();
                    }
                });
            }, observerOptions);

            // Observe the pagination container (or a dedicated sentinel)
            if (this.$pagination.length) {
                this.observer.observe(this.$pagination[0]);
            }
        }

        loadNextPage() {
            if (this.isLoading || this.page >= this.maxPages) return;

            this.isLoading = true;
            this.$container.addClass('knowledge-loading');
            
            if (this.type === 'load_more') {
                this.$button.prop('disabled', true).addClass('loading');
                // Optional: Show spinner inside button if designed
            } else {
                this.$loader.show();
            }

            const data = {
                action: 'knowledge_load_more',
                nonce: knowledge_vars.nonce,
                page: this.page + 1,
                posts_per_page: this.queryArgs.posts_per_page || 12,
                orderby: this.queryArgs.orderby,
                order: this.queryArgs.order,
                category: this.queryArgs.category,
                tag: this.queryArgs.tag,
                ids: this.queryArgs.ids,
                options: this.options
            };

            $.ajax({
                url: knowledge_vars.ajax_url,
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.page++;
                        this.appendPosts(response.data.html);
                        
                        // Update state
                        if (this.page >= this.maxPages) {
                            this.handleEnd();
                        }
                    } else {
                        console.error('Knowledge Load More Error:', response);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Knowledge Pagination Error:', status, error, xhr.responseText);
                    this.isLoading = false;
                    this.$container.removeClass('knowledge-loading');
                    if (this.type === 'load_more') {
                        this.$button.prop('disabled', false).removeClass('loading');
                    } else {
                        this.$loader.hide();
                    }
                },
                complete: () => {
                    this.isLoading = false;
                    this.$container.removeClass('knowledge-loading');
                    
                    if (this.type === 'load_more') {
                        this.$button.prop('disabled', false).removeClass('loading');
                    } else {
                        this.$loader.hide();
                    }
                }
            });
        }

        appendPosts(html) {
            const $newPosts = $(html);
            $newPosts.hide();
            this.$grid.append($newPosts);
            $newPosts.fadeIn(400);
        }

        handleEnd() {
            if (this.type === 'load_more') {
                this.$button.hide();
            }
            this.$message.show(); // "No more posts"
            
            if (this.observer) {
                this.observer.disconnect();
            }
        }
    }

    class KnowledgeSearch {
        constructor(container) {
            this.$container = $(container);
            this.$form = this.$container.find('form');
            this.$input = this.$form.find('input[type="search"]');
            this.$button = this.$form.find('button[type="submit"]');
            this.$response = this.$container.find('.knowledge-ai-response');
            this.$content = this.$response.find('.knowledge-ai-content');
            this.$provenance = this.$response.find('.knowledge-ai-provenance');
            this.$results = this.$response.find('.knowledge-ai-results');
            
            this.mode = this.$container.data('knowledge-search-mode');
            this.aiMode = this.$container.data('ai-mode') || 'combined';
            this.categories = this.$container.data('categories') || [];
            this.displayOptions = this.$container.data('display-options') || {};
            
            this.showOtherContent = this.$container.data('show-other-content') === 'yes';
            this.otherContentSelector = this.$container.data('other-content-selector') || '';

            if (this.mode === 'ai') {
                this.initAI();
            } else if (this.mode === 'standard-ajax') {
                this.initStandard();
            }

            // Handle clearing search
            this.$input.on('input search', () => {
                if (this.$input.val().trim() === '') {
                    this.resetSearch();
                }
            });
        }

        resetSearch() {
            this.$results.empty();
            if (this.$content.length) this.$content.empty();
            if (this.$provenance.length) this.$provenance.empty();
            this.$response.slideUp();
            this.toggleOtherContent(true);
        }

        toggleOtherContent(show) {
            if (this.showOtherContent) return; // If setting is "Show", we don't hide anything
            
            if (!this.otherContentSelector) return;

            if (show) {
                $(this.otherContentSelector).show();
            } else {
                $(this.otherContentSelector).hide();
            }
        }

        initAI() {
            this.$form.on('submit', (e) => {
                e.preventDefault();
                this.performSearch();
            });
        }

        initStandard() {
            this.$form.on('submit', (e) => {
                e.preventDefault();
                this.performStandardSearch();
            });
        }

        performSearch() {
            const query = this.$input.val().trim();
            if (!query) return;

            this.setLoading(true);
            this.$response.slideDown();
            this.$content.html('<p>Thinking...</p>');
            this.$provenance.empty();
            this.$results.empty();
            this.$response.find('.knowledge-results-divider').hide();

            $.ajax({
                url: knowledge_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'knowledge_chat',
                    nonce: knowledge_vars.chat_nonce,
                    question: query,
                    mode: this.aiMode,
                    options: JSON.stringify(this.displayOptions)
                },
                success: (response) => {
                    if (response.success) {
                        this.$content.html(this.formatAnswer(response.data.answer));
                        if (response.data.provenance) {
                            this.$provenance.html(this.formatProvenance(response.data.provenance));
                        }
                        if (response.data.cards_html) {
                            this.$results.html(response.data.cards_html);
                            this.$response.find('.knowledge-results-divider').show();
                        }
                        this.toggleOtherContent(false);
                    } else {
                        this.$content.html('<span class="error">Error: ' + response.data + '</span>');
                    }
                },
                error: (xhr, status, error) => {
                     this.$content.html('<span class="error">Connection Error: ' + error + '</span>');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        performStandardSearch() {
            const query = this.$input.val().trim();
            if (!query) return;

            this.setLoading(true);
            this.$response.slideDown();
            this.$results.empty();
            // In standard mode, we might not have content/provenance containers, but clearing them is safe if they don't exist
            if (this.$content.length) this.$content.empty();
            if (this.$provenance.length) this.$provenance.empty();
            this.$response.find('.knowledge-results-divider').hide();

            $.ajax({
                url: knowledge_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'knowledge_search_results',
                    nonce: knowledge_vars.nonce, // Use the standard nonce
                    search: query,
                    options: this.displayOptions
                },
                success: (response) => {
                    if (response.success) {
                        if (response.data.cards_html) {
                            this.$results.html(response.data.cards_html);
                            this.$response.find('.knowledge-results-divider').show();
                        } else {
                            this.$results.html('<p>No results found.</p>');
                        }
                        this.toggleOtherContent(false);
                    } else {
                        this.$results.html('<span class="error">Error: ' + (response.data || 'Unknown error') + '</span>');
                    }
                },
                error: (xhr, status, error) => {
                     this.$results.html('<span class="error">Connection Error: ' + error + '</span>');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        setLoading(loading) {
            this.$button.prop('disabled', loading);
            if (loading) {
                this.$button.addClass('loading');
            } else {
                this.$button.removeClass('loading');
            }
        }
        
        formatAnswer(text) {
             if (!text) return '';
             // Simple markdown-like parsing for bold and newlines
             let html = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
             return html;
        }

        formatProvenance(provenance) {
            if (!provenance) return '';
            let html = '<div class="knowledge-ai-meta">';
            if (provenance.provider_name) {
                html += `<span class="ai-model-info">Generated by ${provenance.provider_name} (${provenance.model})</span>`;
            }
            html += '</div>';
            return html;
        }
    }

    class KnowledgeRecheck {
        constructor() {
            this.init();
        }

        init() {
            // Use delegation since cards can be loaded dynamically
            $(document).on('click', '.knowledge-recheck-btn', (e) => {
                this.handleClick(e);
            });
        }

        handleClick(e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(e.currentTarget);
            const url = $btn.data('url');

            if (!url || $btn.hasClass('loading') || $btn.hasClass('success')) {
                return;
            }

            // Save original text
            if (!$btn.data('original-text')) {
                $btn.data('original-text', $btn.text());
            }

            $btn.addClass('loading').text('Checking...');

            $.ajax({
                url: knowledge_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'knowledge_recheck_article',
                    nonce: knowledge_vars.recheck_nonce,
                    url: url
                },
                success: (response) => {
                    if (response.success) {
                        $btn.removeClass('loading')
                            .addClass('success')
                            .text('Queued');
                    } else {
                        $btn.removeClass('loading').text($btn.data('original-text'));
                        alert(response.data || 'Error rechecking article');
                    }
                },
                error: (xhr, status, error) => {
                    $btn.removeClass('loading').text($btn.data('original-text'));
                    console.error('Recheck Error:', error);
                    alert('Network error. Please try again.');
                }
            });
        }
    }

    class KnowledgeAnnotator {
        constructor() {
            this.$container = $('.kb-article-content');
            if (!this.$container.length) return;
            
            this.versionUuid = this.$container.data('version-uuid');
            this.tagCache = {};
            this.init();
            this.createUI();
        }

        extractTags(text) {
            if (!text) return [];
            const matches = text.match(/#([\w-]+)/g);
            if (!matches) return [];
            return [...new Set(matches.map(t => t.substring(1)))];
        }

        async resolveTags(tagNames) {
            const ids = [];
            for (const name of tagNames) {
                let id = this.tagCache[name];
                if (!id) {
                    try {
                        // Search for existing tag
                        const searchRes = await $.ajax({
                            url: knowledge_vars.rest_url + 'wp/v2/kb_tag',
                            data: { search: name, _fields: 'id,name,slug' },
                            beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce)
                        });
                        
                        const existing = searchRes.find(t => t.name.toLowerCase() === name.toLowerCase());
                        
                        if (existing) {
                            id = existing.id;
                        } else {
                            // Create new
                            const createRes = await $.ajax({
                                url: knowledge_vars.rest_url + 'wp/v2/kb_tag',
                                method: 'POST',
                                data: { name: name },
                                beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce)
                            });
                            id = createRes.id;
                        }
                        
                        this.tagCache[name] = id;
                    } catch (e) {
                        console.error('Error resolving tag', name, e);
                    }
                }
                if (id) ids.push(id);
            }
            return ids;
        }

        init() {
            document.addEventListener('mouseup', (e) => this.handleSelection(e));
            this.$container.on('click', '.kb-highlight', (e) => {
                 const noteId = $(e.currentTarget).data('noteId');
                 if (noteId) {
                     this.openSidebarAndFocus(noteId);
                 }
            });
        }

        createUI() {
            // Create Add Note Button
            this.$btn = $('<button class="kb-add-note-btn" title="Add Note"><span class="dashicons dashicons-edit"></span></button>')
                .hide()
                .appendTo('body');
            
            // Prevent focus loss on mousedown, show form on click
            this.$btn.on('mousedown', (e) => {
                e.preventDefault();
            });
            
            this.$btn.on('click', (e) => {
                e.stopPropagation();
                this.showForm();
            });

            // Create Popover Form
            this.$popover = $(`
                <div class="kb-annotation-popover">
                    <textarea placeholder="Add a note..."></textarea>
                    <div class="kb-popover-actions">
                        <select class="kb-note-mode">
                            <option value="highlight">Just Highlight</option>
                            <option value="excerpt">Excerpt</option>
                            <option value="copy">Copy</option>
                        </select>
                        <button class="kb-cancel">Cancel</button>
                        <button class="kb-save">Save</button>
                    </div>
                </div>
            `)
                .hide()
                .appendTo('body');
                
            this.$popover.find('.kb-cancel').on('click', () => this.hidePopover());
            this.$popover.find('.kb-save').on('click', () => this.saveNote());
            this.$popover.find('.kb-note-mode').on('change', (e) => this.handleModeChange(e));

            // Sidebar
            this.$sidebar = $('<div class="kb-notes-sidebar"><div class="kb-sidebar-header"><h3>Notes</h3><button class="kb-close-sidebar">&times;</button></div><div class="kb-notes-list"></div></div>')
                .appendTo('body');

            this.$toggle = $('<button class="kb-notes-toggle" title="Show Notes"><span class="dashicons dashicons-sticky"></span></button>')
                .appendTo('body')
                .on('click', () => this.toggleSidebar());

            this.$sidebar.find('.kb-close-sidebar').on('click', () => this.toggleSidebar());
            
            // Handle clicking a note to find its highlight
            this.$sidebar.on('click', '.kb-note-item', (e) => {
                // Ignore if clicking actions or edit area
                if ($(e.target).closest('.kb-note-actions, .kb-edit-wrapper').length) return;
                
                const noteId = $(e.currentTarget).data('noteId');
                if (noteId) {
                    this.scrollToHighlight(noteId);
                }
            });

            // Handle Delete
            this.$sidebar.on('click', '.kb-note-action-btn.delete', (e) => {
                e.stopPropagation();
                if (confirm('Delete this note?')) {
                    const id = $(e.currentTarget).closest('.kb-note-item').data('noteId');
                    this.deleteNote(id);
                }
            });

            // Handle Edit
            this.$sidebar.on('click', '.kb-note-action-btn.edit', (e) => {
                e.stopPropagation();
                const $item = $(e.currentTarget).closest('.kb-note-item');
                this.startEditNote($item);
            });

            this.fetchNotes();
        }

        deleteNote(id) {
            $.ajax({
                url: knowledge_vars.rest_url + 'wp/v2/kb_note/' + id,
                method: 'DELETE',
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce);
                },
                success: () => {
                    // Remove sidebar item
                    this.$sidebar.find('.kb-note-item[data-note-id="' + id + '"]').slideUp(200, function() {
                        $(this).remove();
                    });
                    // Remove highlight
                    const $highlight = this.$container.find('.kb-highlight[data-note-id="' + id + '"]');
                    $highlight.contents().unwrap();
                },
                error: (xhr) => {
                    alert('Error deleting note: ' + xhr.responseText);
                }
            });
        }

        startEditNote($item) {
            if ($item.find('.kb-note-edit-area').length) return; // Already editing

            const $contentDiv = $item.find('.kb-note-content');
            const note = $item.data('noteData'); 
            
            // Try to get raw text. If not available, parse HTML
            let rawText = '';
            if (note && note.content && note.content.raw) {
                rawText = note.content.raw;
            } else {
                // Fallback parsing from displayed HTML
                const $clone = $contentDiv.clone();
                const quotes = [];
                $clone.find('.kb-note-quote').each(function() {
                    let html = $(this).html();
                    html = html.replace(/<br\s*\/?>/gi, '\n');
                    let text = $('<div>').html(html).text(); // Decode entities
                    // Prefix each line with >
                    const lines = text.split('\n');
                    lines.forEach(line => quotes.push('> ' + line));
                    $(this).remove();
                });
                
                // Remaining text is user text
                let userText = $clone.html().replace(/<br\s*\/?>/gi, '\n').replace(/<\/p>/gi, '\n').replace(/<p>/gi, '');
                userText = $('<div>').html(userText).text().trim(); // Decode entities
                
                rawText = quotes.join('\n') + (quotes.length ? '\n\n' : '') + userText;
            }

            // Separate quotes and user text
            const lines = rawText.split('\n');
            const quoteLines = lines.filter(l => l.trim().startsWith('>'));
            const userLines = lines.filter(l => !l.trim().startsWith('>'));
            const userText = userLines.join('\n').trim();

            // Initialize Manual Tags (Tags present in Note but NOT in the text)
            // We treat tags in text as "dynamic" and tags stored separately as "manual"
            const initialTextTags = this.extractTags(userText);
            const allExistingTags = this.getTagsFromNote(note).map(t => ({id: t.id, name: t.name}));
            
            // Filter out tags that are already in the text to avoid duplication in logic
            let manualTags = allExistingTags.filter(et => 
                !initialTextTags.some(tt => tt.toLowerCase() === et.name.toLowerCase())
            );

            const renderTags = () => {
                const $container = $editArea.find('.kb-current-tags').empty();
                
                // Get current text tags
                const currentTextVal = $editArea.find('textarea').val() || '';
                const currentTextTags = this.extractTags(currentTextVal);
                
                // Merge manual and text tags for display (Unique by name)
                const displayTags = [];
                const seenNames = new Set();
                
                // Add manual tags first
                manualTags.forEach(t => {
                    if (!seenNames.has(t.name.toLowerCase())) {
                        displayTags.push({ ...t, source: 'manual' });
                        seenNames.add(t.name.toLowerCase());
                    }
                });
                
                // Add text tags
                currentTextTags.forEach(name => {
                    if (!seenNames.has(name.toLowerCase())) {
                        displayTags.push({ name: name, id: null, source: 'text' });
                        seenNames.add(name.toLowerCase());
                    }
                });

                displayTags.forEach((tag) => {
                    $container.append(`
                        <span class="kb-tag-pill ${tag.source === 'text' ? 'kb-tag-text' : ''}">
                            #${tag.name}
                            <span class="kb-remove-tag" data-name="${tag.name}" data-source="${tag.source}">&times;</span>
                        </span>
                    `);
                });
            };

            const $editArea = $(`
                <div class="kb-edit-wrapper">
                    <textarea class="kb-note-edit-area">${userText}</textarea>
                    <div class="kb-tag-edit-area">
                        <div class="kb-current-tags"></div>
                        <div class="kb-tag-input-wrapper">
                            <input type="text" class="kb-tag-input" placeholder="Add tag...">
                            <button class="kb-add-tag-btn">+</button>
                            <ul class="kb-tag-autocomplete" style="display:none"></ul>
                        </div>
                    </div>
                    <div class="kb-popover-actions" style="margin-top:5px;">
                        <button class="kb-cancel-edit">Cancel</button>
                        <button class="kb-save-edit">Save</button>
                    </div>
                </div>
            `);

            $contentDiv.hide();
            // Hide existing tags display if present
            $item.find('.kb-note-tags').hide();
            $contentDiv.after($editArea);
            $item.find('.kb-note-actions').hide(); 
            
            renderTags();

            // Live extraction from textarea
            $editArea.find('textarea').on('input', () => {
                renderTags();
            });

            // Tag Events
            $editArea.on('click', '.kb-remove-tag', (e) => {
                const tagName = $(e.currentTarget).data('name');
                const source = $(e.currentTarget).data('source');
                
                if (source === 'manual') {
                    // Remove from manual tags
                    const idx = manualTags.findIndex(t => t.name.toLowerCase() === tagName.toLowerCase());
                    if (idx !== -1) manualTags.splice(idx, 1);
                } else if (source === 'text') {
                    // Remove from text (replace #tag with tag)
                    let val = $editArea.find('textarea').val();
                    // Regex to replace #tagName with tagName, preserving boundaries
                    const regex = new RegExp(`#${tagName}\\b`, 'g');
                    val = val.replace(regex, tagName);
                    $editArea.find('textarea').val(val);
                }
                
                renderTags();
            });

            const addTag = (name) => {
                name = name.trim().replace(/^#/, '');
                if (!name) return;
                
                // Check if already exists in manual or text
                const currentTextVal = $editArea.find('textarea').val() || '';
                const currentTextTags = this.extractTags(currentTextVal);
                
                const existsInManual = manualTags.some(t => t.name.toLowerCase() === name.toLowerCase());
                const existsInText = currentTextTags.some(t => t.toLowerCase() === name.toLowerCase());
                
                if (!existsInManual && !existsInText) {
                    manualTags.push({ name: name, id: null }); // ID resolved on save
                    renderTags();
                }
                $editArea.find('.kb-tag-input').val('');
                $editArea.find('.kb-tag-autocomplete').hide();
            };

            $editArea.find('.kb-add-tag-btn').on('click', () => {
                addTag($editArea.find('.kb-tag-input').val());
            });

            $editArea.find('.kb-tag-input').on('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addTag($(e.target).val());
                }
            });

            let searchTimeout;
            $editArea.find('.kb-tag-input').on('input', (e) => {
                // if (e.key === 'Enter') return; // Not needed for input event
                const val = $(e.target).val().trim();
                if (val.length < 2) {
                    $editArea.find('.kb-tag-autocomplete').hide();
                    return;
                }
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: knowledge_vars.rest_url + 'wp/v2/kb_tag',
                        data: { search: val, _fields: 'id,name', per_page: 5 },
                        beforeSend: (xhr) => xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce),
                        success: (tags) => {
                            const $list = $editArea.find('.kb-tag-autocomplete').empty();
                            if (tags.length) {
                                tags.forEach(tag => {
                                    $(`<li>${tag.name}</li>`).on('click', () => {
                                        // Add tag with ID
                                        // Check duplicates again
                                        const currentTextVal = $editArea.find('textarea').val() || '';
                                        const currentTextTags = this.extractTags(currentTextVal);
                                        const existsInManual = manualTags.some(t => t.name.toLowerCase() === tag.name.toLowerCase());
                                        const existsInText = currentTextTags.some(t => t.toLowerCase() === tag.name.toLowerCase());

                                        if (!existsInManual && !existsInText) {
                                            manualTags.push({ name: tag.name, id: tag.id });
                                            renderTags();
                                        }
                                        $editArea.find('.kb-tag-input').val('');
                                        $list.hide();
                                    }).appendTo($list);
                                });
                                $list.show();
                            } else {
                                $list.hide();
                            }
                        }
                    });
                }, 300);
            });

            // Bind events
            $editArea.find('.kb-cancel-edit').on('click', (e) => {
                e.stopPropagation();
                $editArea.remove();
                $contentDiv.show();
                $item.find('.kb-note-tags').show();
                $item.find('.kb-note-actions').show();
            });

            $editArea.find('.kb-save-edit').on('click', (e) => {
                e.stopPropagation();
                const newUserText = $editArea.find('textarea').val();
                const newFullContent = quoteLines.join('\n') + (quoteLines.length ? '\n\n' : '') + newUserText;
                
                // Final merge of tags: Manual + Extracted
                // We don't need to push extracted to manualTags because saveEditedNote handles the merge
                // But we do need to pass the FULL list of tags to be saved
                
                const textTags = this.extractTags(newUserText);
                const finalTags = [...manualTags];
                
                textTags.forEach(name => {
                    if (!finalTags.find(t => t.name.toLowerCase() === name.toLowerCase())) {
                        finalTags.push({ name: name, id: null });
                    }
                });

                this.saveEditedNote($item, $item.data('noteId'), newFullContent, finalTags);
            });
            
            // Prevent click propagation to sidebar item
            $editArea.on('click', (e) => e.stopPropagation());
        }

        async saveEditedNote($item, id, content, tags) {
            $item.find('.kb-save-edit').text('Saving...').prop('disabled', true);
            
            // Resolve tags
            const tagNames = tags.map(t => t.name);
            const tagIds = await this.resolveTags(tagNames);
            
            // Map back to update the tags array with IDs (for local display update if needed)
            // Although we rely on response
            
            $.ajax({
                url: knowledge_vars.rest_url + 'wp/v2/kb_note/' + id,
                method: 'POST',
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce);
                },
                data: JSON.stringify({ 
                    content: content,
                    kb_tag: tagIds 
                }),
                contentType: 'application/json',
                success: (response) => {
                    // Inject tags details manually if missing (WP REST update might not return embedded terms unless requested)
                    // But we can just use our local tags since we resolved them
                    if (!response.tags_details) {
                        response.tags_details = tags.map((t, i) => ({
                            id: tagIds[i],
                            name: t.name
                        })).filter(t => t.id);
                    }

                    // Replace the item with re-rendered version
                    const $newItem = this.renderNoteItem(response);
                    $item.replaceWith($newItem);
                },
                error: (xhr) => {
                    $item.find('.kb-save-edit').text('Save').prop('disabled', false);
                    alert('Error updating note: ' + xhr.responseText);
                }
            });
        }

        scrollToHighlight(noteId) {
            const $highlights = this.$container.find('.kb-highlight[data-note-id="' + noteId + '"]');
            
            if ($highlights.length) {
                // Scroll to the first highlight
                const $first = $highlights.first();
                
                // Account for fixed header if present (adjust offset as needed)
                const offset = $first.offset().top - 100;
                
                $('html, body').animate({
                    scrollTop: offset
                }, 400);
                
                // Visual feedback
                $highlights.addClass('kb-flash');
                setTimeout(() => $highlights.removeClass('kb-flash'), 1500);
            }
        }

        toggleSidebar() {
             this.$sidebar.toggleClass('open');
        }

        openSidebarAndFocus(noteId) {
             if (!this.$sidebar.hasClass('open')) {
                 this.toggleSidebar();
             }
             
             const $item = this.$sidebar.find('.kb-note-item[data-note-id="' + noteId + '"]');
             if ($item.length) {
                 const $list = this.$sidebar.find('.kb-notes-list');
                 $list.animate({
                     scrollTop: $item.offset().top - $list.offset().top + $list.scrollTop()
                 }, 200);
                 
                 $item.addClass('kb-flash');
                 setTimeout(() => $item.removeClass('kb-flash'), 1000);
             }
        }


        fetchNotes() {
            $.ajax({
                url: knowledge_vars.rest_url + 'wp/v2/kb_note',
                data: {
                    source: this.versionUuid,
                    per_page: 100,
                    context: 'edit',
                    _fields: 'id,content,date,target,author_details,author,kb_tag,_links,_embedded',
                    _embed: true
                },
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce);
                },
                success: (notes) => {
                    this.renderNotes(notes);
                    this.restoreHighlights(notes);
                }
            });
        }
        
        getAllTextNodes(root) {
            const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
            const nodes = [];
            let node;
            while(node = walker.nextNode()) nodes.push(node);
            return nodes;
        }

        restoreHighlights(notes) {
            if (!notes || !notes.length) return;

            // Process notes sequentially to handle DOM updates
            notes.forEach(note => {
                if (!note.target || !note.target.selector || !note.target.selector.exact) return;
                
                const searchText = note.target.selector.exact;
                // Robust matching: strip all whitespace to match across block boundaries and formatting changes
                const searchStripped = searchText.replace(/\s/g, '');
                if (!searchStripped) return;

                // Re-build map for each iteration as DOM changes
                const textNodes = this.getAllTextNodes(this.$container[0]);
                let fullStrippedText = '';
                const charMap = [];
                
                textNodes.forEach(node => {
                    const nodeText = node.textContent;
                    for (let i = 0; i < nodeText.length; i++) {
                        const char = nodeText[i];
                        // Only map non-whitespace characters
                        if (!/\s/.test(char)) {
                            charMap.push({ node: node, offset: i });
                            fullStrippedText += char;
                        }
                    }
                });

                // Find first occurrence in stripped text
                const startIndex = fullStrippedText.indexOf(searchStripped);
                
                if (startIndex !== -1) {
                    const startInfo = charMap[startIndex];
                    // End index in stripped string is startIndex + length - 1
                    const endIndex = startIndex + searchStripped.length - 1;
                    
                    if (endIndex < charMap.length) {
                        const endInfo = charMap[endIndex];
                        
                        const range = document.createRange();
                        try {
                            range.setStart(startInfo.node, startInfo.offset);
                            // setEnd is exclusive, so we use offset + 1 to include the last character
                            range.setEnd(endInfo.node, endInfo.offset + 1);
                            this.renderHighlight(range, note.id);
                        } catch (e) {
                            console.warn('Error restoring highlight for note ' + note.id, e);
                        }
                    }
                }
            });
        }
        
        formatContent(content) {
            if (!content) return '';
            
            // Normalize HTML to text-like structure for regex processing
            content = content.replace(/&nbsp;/g, ' ');
            // Convert <br> and </p> to newlines
            content = content.replace(/<br\s*\/?>/gi, '\n');
            content = content.replace(/<\/p>/g, '\n');
            content = content.replace(/<p>/g, '');
            
            // Regex to match blocks of quotes (lines starting with > or &gt;)
            // Allow optional space/newlines after > to handle blank lines or compact formatting
            // AND swallow intermediate blank lines between quote lines to form contiguous blocks
            const quoteBlockRegex = /((?:^(?:>|&gt;).*(?:\n|$)(?:[\r\n\s]*?(?=^(?:>|&gt;)))?)+)/gm;
            
            content = content.replace(quoteBlockRegex, (match) => {
                // Strip the > or &gt; markers
                let inner = match.replace(/^(?:>|&gt;) ?/gm, '');
                inner = inner.trim(); 
                // Convert newlines inside quote to <br>
                inner = inner.replace(/\n/g, '<br>');
                return `<blockquote class="kb-note-quote">${inner}</blockquote>`;
            });
            
            // Convert remaining newlines to <br> for display
            content = content.replace(/\n/g, '<br>');
            
            return content;
        }
        
        formatDate(isoString) {
            const date = new Date(isoString);
            const time = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const day = date.toLocaleDateString();
            return `${time} ${day}`;
        }

        renderNotes(notes) {
            const list = this.$sidebar.find('.kb-notes-list').empty();
            if (!notes.length) {
                list.append('<p class="kb-no-notes">No notes yet.</p>');
                return;
            }
            
            notes.forEach(note => {
                const $item = this.renderNoteItem(note);
                list.append($item);
            });
        }

        renderNoteItem(note) {
            let content = note.content ? (note.content.rendered || note.content) : '';
            content = this.formatContent(content);

            // Default fallback if author_details missing (e.g. older cached responses)
            const authorName = (note.author_details && note.author_details.name) ? note.author_details.name : 'Unknown';
            const authorAvatar = (note.author_details && note.author_details.avatar) ? note.author_details.avatar : 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';

            // Extract tags if available (from embedded terms or directly if we just saved)
            let tagsHtml = '';
            const tags = this.getTagsFromNote(note);
            if (tags.length) {
                tagsHtml = '<div class="kb-note-tags">';
                tags.forEach(tag => {
                    tagsHtml += `<span class="kb-note-tag">#${tag.name}</span>`;
                });
                tagsHtml += '</div>';
            }

            const $item = $('<div class="kb-note-item" data-note-id="' + note.id + '"></div>')
                .append($('<div class="kb-note-content"></div>').html(content))
                .append(tagsHtml)
                .append(`
                    <div class="kb-note-info">
                        <div class="kb-note-author">
                            <img src="${authorAvatar}" class="kb-note-avatar" alt="${authorName}">
                            <span class="kb-note-author-name">${authorName}</span>
                        </div>
                        <div class="kb-note-meta">${this.formatDate(note.date)}</div>
                    </div>
                `)
                .append(`
                    <div class="kb-note-actions">
                        <button class="kb-note-action-btn edit" title="Edit"><span class="dashicons dashicons-edit"></span></button>
                        <button class="kb-note-action-btn delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                `);
            
            $item.data('noteData', note);
            return $item;
        }

        getTagsFromNote(note) {
            // Check embedded terms
            if (note._embedded && note._embedded['wp:term']) {
                // kb_tag is usually the first taxonomy if requested, but we should be careful.
                // wp:term returns an array of arrays (one per taxonomy)
                // We need to flatten and find kb_tag
                const allTerms = note._embedded['wp:term'].flat();
                return allTerms.filter(term => term.taxonomy === 'kb_tag');
            }
            // If we just saved, we might have passed tags directly or attached them
            if (note.tags_details) {
                return note.tags_details;
            }
            return [];
        }

        handleSelection(e) {
            const selection = window.getSelection();
            if (selection.isCollapsed) {
                // Only hide if we are not clicking inside the popover
                if (!$(e.target).closest('.kb-annotation-popover').length && !$(e.target).closest('.kb-add-note-btn').length) {
                    this.$btn.hide();
                    this.hidePopover();
                }
                return;
            }

            const range = selection.getRangeAt(0);
            if (!this.$container[0].contains(range.commonAncestorContainer)) {
                this.$btn.hide();
                return;
            }

            const rect = range.getBoundingClientRect();
            this.$btn.css({
                top: (rect.top + window.scrollY - 40) + 'px',
                left: (rect.left + window.scrollX + (rect.width / 2) - 15) + 'px'
            }).show();
            
            this.currentRange = range;
        }

        showForm() {
            const rect = this.currentRange.getBoundingClientRect();
            this.$btn.hide();
            this.$popover.css({
                top: (rect.bottom + window.scrollY + 10) + 'px',
                left: (rect.left + window.scrollX) + 'px'
            }).show();
            
            // Add pending highlight
            if (this.currentRange) {
                this.pendingSpans = this.highlightSafe(this.currentRange, 'kb-highlight-pending');
            }
            
            // Restore preference
            const mode = localStorage.getItem('kb_note_mode') || 'highlight';
            this.$popover.find('.kb-note-mode').val(mode);
            
            this.$popover.find('textarea').focus();
        }
        
        hidePopover() {
            this.$popover.hide();
            this.$popover.find('textarea').val('');

            // Remove pending highlight if it exists (cancelled)
            if (this.pendingSpans && this.pendingSpans.length) {
                this.pendingSpans.forEach(span => {
                    $(span).contents().unwrap();
                });
                this.pendingSpans = null;
            }
        }
        
        handleModeChange(e) {
            const mode = $(e.target).val();
            localStorage.setItem('kb_note_mode', mode);
        }

        serializeRange(range) {
             const text = range.toString();
             return {
                 type: 'TextQuoteSelector',
                 exact: text
             };
        }

        pasteSelection() {
            // Deprecated in favor of applyMode
        }

        async saveNote() {
            let text = this.$popover.find('textarea').val() || '';
            const mode = this.$popover.find('.kb-note-mode').val();
            
            // Prepend quoted text based on mode
            if (this.currentRange && (mode === 'excerpt' || mode === 'copy')) {
                const selectedText = this.currentRange.toString();
                let quote = '';
                
                if (mode === 'excerpt') {
                    const truncated = selectedText.substring(0, 100) + (selectedText.length > 100 ? '...' : '');
                    quote = truncated.split('\n').map(line => '> ' + line).join('\n');
                } else if (mode === 'copy') {
                    quote = selectedText.split('\n').map(line => '> ' + line).join('\n');
                }
                
                if (quote) {
                    text = text ? quote + '\n\n' + text : quote;
                }
            }

            if (!text) return;

            // Extract and resolve tags
            const tagNames = this.extractTags(text);
            let tagIds = [];
            let tagsDetails = [];
            
            if (tagNames.length > 0) {
                // Show loading state if needed
                this.$popover.find('.kb-save').text('Saving...').prop('disabled', true);
                tagIds = await this.resolveTags(tagNames);
                // Map back to details for immediate display
                tagsDetails = tagNames.map((name, index) => ({
                    id: tagIds[index], // Might be undefined if resolution failed
                    name: name
                })).filter(t => t.id);
            }

            const target = {
                source: this.versionUuid,
                selector: this.serializeRange(this.currentRange)
            };

            const data = {
                title: 'Note',
                content: text,
                target: target,
                status: 'publish',
                kb_tag: tagIds
            };

            $.ajax({
                url: knowledge_vars.rest_url + 'wp/v2/kb_note',
                method: 'POST',
                beforeSend: (xhr) => {
                    xhr.setRequestHeader('X-WP-Nonce', knowledge_vars.rest_nonce);
                },
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: (response) => {
                    this.$popover.find('.kb-save').text('Save').prop('disabled', false);
                    
                    // Attach tags details for display
                    response.tags_details = tagsDetails;

                    // Handle pending highlight commit
                    if (this.pendingSpans && this.pendingSpans.length) {
                        this.pendingSpans.forEach(span => {
                            span.className = 'kb-highlight';
                            span.dataset.noteId = response.id;
                        });
                        this.pendingSpans = null; // Prevent hidePopover from removing it
                    } else {
                        this.renderHighlight(this.currentRange, response.id);
                    }

                    this.hidePopover();
                    window.getSelection().removeAllRanges();
                    this.addNoteToSidebar(response);
                    if (!this.$sidebar.hasClass('open')) {
                        this.toggleSidebar();
                    }
                },
                error: (xhr) => {
                    this.$popover.find('.kb-save').text('Save').prop('disabled', false);
                    alert('Error saving note: ' + xhr.responseText);
                }
            });
        }
        
        addNoteToSidebar(note) {
             const list = this.$sidebar.find('.kb-notes-list');
             list.find('.kb-no-notes').remove();
             
             const $item = this.renderNoteItem(note);
             list.prepend($item);
        }
        
        renderHighlight(range, noteId) {
             this.highlightSafe(range, 'kb-highlight', noteId);
        }

        highlightSafe(range, className, noteId) {
            const spans = [];
            const commonAncestor = range.commonAncestorContainer;
            
            // If commonAncestor is text node, it's simple
            if (commonAncestor.nodeType === Node.TEXT_NODE) {
                try {
                    const span = document.createElement('span');
                    span.className = className;
                    if (noteId) span.dataset.noteId = noteId;
                    range.surroundContents(span);
                    spans.push(span);
                    return spans;
                } catch (e) {
                    console.warn('Simple surround failed, falling back to walker', e);
                }
            }

            // Complex range: Walk all text nodes
            const walker = document.createTreeWalker(
                commonAncestor, 
                NodeFilter.SHOW_TEXT, 
                {
                    acceptNode: (node) => {
                        if (range.intersectsNode(node)) return NodeFilter.FILTER_ACCEPT;
                        return NodeFilter.FILTER_REJECT;
                    }
                },
                false
            );

            const nodesToWrap = [];
            let currentNode;
            while (currentNode = walker.nextNode()) {
                nodesToWrap.push(currentNode);
            }

            // Wrap each node
            nodesToWrap.forEach(node => {
                const rangeToWrap = document.createRange();
                
                // Determine start offset
                const startOffset = (node === range.startContainer) ? range.startOffset : 0;
                
                // Determine end offset
                const endOffset = (node === range.endContainer) ? range.endOffset : node.length;
                
                // Skip empty wraps
                if (startOffset >= endOffset) return;

                try {
                    rangeToWrap.setStart(node, startOffset);
                    rangeToWrap.setEnd(node, endOffset);
                    
                    const span = document.createElement('span');
                    span.className = className;
                    if (noteId) span.dataset.noteId = noteId;
                    
                    rangeToWrap.surroundContents(span);
                    spans.push(span);
                } catch (e) {
                    console.warn('Failed to wrap node segment', e);
                }
            });
            
            return spans;
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        if (knowledge_vars.is_logged_in) {
            new KnowledgeAnnotator();
            new KnowledgeRecheck();
        }

        $('.knowledge-archive-wrapper').each(function() {
            new KnowledgePagination(this);
        });

        $('.knowledge-search-wrapper').each(function() {
            new KnowledgeSearch(this);
        });
    });

    // Re-initialize for Elementor Editor (if needed)
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/knowledge_archive.default', function($scope) {
            const container = $scope.find('.knowledge-archive-wrapper')[0];
            if (container) {
                new KnowledgePagination(container);
            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/knowledge_search.default', function($scope) {
            const container = $scope.find('.knowledge-search-wrapper')[0];
            if (container) {
                new KnowledgeSearch(container);
            }
        });
    });

})(jQuery);
