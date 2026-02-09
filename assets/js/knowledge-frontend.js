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

    // Initialize on document ready
    $(document).ready(function() {
        $('.knowledge-archive-wrapper').each(function() {
            new KnowledgePagination(this);
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
    });

})(jQuery);
