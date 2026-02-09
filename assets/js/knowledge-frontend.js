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

    // Initialize on document ready
    $(document).ready(function() {
        new KnowledgeRecheck();

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
