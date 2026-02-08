jQuery(document).ready(function($) {
    // Initialize Sortable
    $('#knowledge-providers-list').sortable({
        handle: '.knowledge-provider-handle',
        placeholder: 'knowledge-provider-placeholder',
        update: function(event, ui) {
            updateProviderOrder();
        }
    });

    // Initial Connection Check
    $('#knowledge-providers-list .knowledge-provider-row').each(function() {
        checkProviderConnection($(this));
    });

    function checkProviderConnection($row) {
        var type = $row.find('input[name*="[type]"]').val();
        var url = $row.find('input[name*="[config][url]"]').val();
        var model = $row.find('input[name*="[config][model]"]').val();
        var apiKey = $row.find('input[name*="[config][api_key]"]').val();

        if (!type) return;

        $row.addClass('status-checking');
        
        $.ajax({
            url: knowledgeSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'knowledge_check_connection',
                nonce: knowledgeSettings.nonce,
                type: type,
                config: {
                    url: url,
                    model: model,
                    api_key: apiKey
                }
            },
            success: function(response) {
                $row.removeClass('status-checking');
                if (response.success) {
                    $row.addClass('status-connected').removeClass('status-disconnected');
                } else {
                    $row.addClass('status-disconnected').removeClass('status-connected');
                }
            },
            error: function() {
                $row.removeClass('status-checking');
                $row.addClass('status-disconnected').removeClass('status-connected');
            }
        });
    }

    // Add Provider Toggle
    $('#knowledge-add-provider-btn').on('click', function(e) {
        e.preventDefault();
        resetForm();
        $('#knowledge-add-provider-form').slideToggle();
    });

    // Edit Provider
    $(document).on('click', '.knowledge-edit-provider', function(e) {
        e.preventDefault();
        var $row = $(this).closest('.knowledge-provider-row');
        var id = $row.data('id');
        
        // Populate Form
        $('#editing_provider_id').val(id);
        $('#new_provider_type').val($row.find('input[name*="[type]"]').val()).trigger('change');
        $('#new_provider_name').val($row.find('input[name*="[name]"]').val());
        $('#new_provider_url').val($row.find('input[name*="[config][url]"]').val());
        $('#new_provider_model').val($row.find('input[name*="[config][model]"]').val());
        $('#new_provider_key').val($row.find('input[name*="[config][api_key]"]').val());

        // Update UI
        $('#knowledge-provider-form-title').text('Edit Provider');
        $('#knowledge-save-new-provider').text('Update Provider');
        $('#knowledge-add-provider-form').slideDown();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $("#knowledge-add-provider-form").offset().top - 50
        }, 500);
    });

    function resetForm() {
        $('#editing_provider_id').val('');
        $('#new_provider_name').val('');
        $('#new_provider_url').val('');
        $('#new_provider_model').val('');
        $('#new_provider_key').val('');
        $('#knowledge-provider-form-title').text('Add New Provider');
        $('#knowledge-save-new-provider').text('Add to List');
        
        // Reset Status
        $('#knowledge-add-provider-form').removeClass('status-connected status-disconnected status-checking');
        $('.knowledge-check-indicator').hide();
        $('#knowledge_provider_models').empty();
    }

    // Check Connection in Form
    function checkFormConnection() {
        var type = $('#new_provider_type').val();
        var url = $('#new_provider_url').val();
        var apiKey = $('#new_provider_key').val();
        var model = $('#new_provider_model').val();

        if (type === 'ollama' && !url) return;
        if (type === 'openai' && !apiKey) return;

        var $form = $('#knowledge-add-provider-form');
        var $indicators = $('.knowledge-check-indicator');
        
        $form.removeClass('status-connected status-disconnected').addClass('status-checking');
        $indicators.fadeIn();

        $.ajax({
            url: knowledgeSettings.ajaxurl,
            type: 'POST',
            data: {
                action: 'knowledge_check_connection',
                nonce: knowledgeSettings.nonce,
                type: type,
                config: {
                    url: url,
                    api_key: apiKey,
                    model: model
                }
            },
            success: function(response) {
                $indicators.fadeOut();
                $form.removeClass('status-checking');
                
                if (response.success) {
                    $form.addClass('status-connected');
                    
                    // Populate Models
                    var $datalist = $('#knowledge_provider_models');
                    $datalist.empty();
                    
                    if (response.data.models && response.data.models.length > 0) {
                        response.data.models.forEach(function(m) {
                            $datalist.append('<option value="' + m + '">');
                        });
                    }
                } else {
                    $form.addClass('status-disconnected');
                }
            },
            error: function() {
                $indicators.fadeOut();
                $form.removeClass('status-checking').addClass('status-disconnected');
            }
        });
    }

    // Trigger checks on blur
    $('#new_provider_url, #new_provider_key').on('blur', function() {
        checkFormConnection();
    });

    // Remove Provider
    $(document).on('click', '.knowledge-remove-provider', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to remove this provider?')) {
            $(this).closest('.knowledge-provider-row').remove();
            updateProviderOrder();
        }
    });

    // Save/Update Provider
    $('#knowledge-save-new-provider').on('click', function(e) {
        e.preventDefault();
        
        var type = $('#new_provider_type').val();
        var name = $('#new_provider_name').val();
        var url = $('#new_provider_url').val();
        var model = $('#new_provider_model').val();
        var apiKey = $('#new_provider_key').val();
        var editId = $('#editing_provider_id').val();

        if (!name || !model) {
            alert('Name and Model are required.');
            return;
        }

        if (editId) {
            // Update Existing
            var $row = $('.knowledge-provider-row[data-id="' + editId + '"]');
            
            // Update Header
            $row.find('strong').first().text(name);
            $row.find('.badge').text(type);
            
            // Update Details
            var details = '';
            if (type === 'ollama') {
                details = 'URL: ' + url + ' | Model: ' + model;
            } else {
                details = 'Model: ' + model;
            }
            $row.find('.knowledge-provider-details').text(details);
            
            // Update Inputs
            $row.find('input[name*="[type]"]').val(type);
            $row.find('input[name*="[name]"]').val(name);
            $row.find('input[name*="[config][url]"]').val(url);
            $row.find('input[name*="[config][model]"]').val(model);
            $row.find('input[name*="[config][api_key]"]').val(apiKey);

            // Re-check connection
            $row.removeClass('status-connected status-disconnected');
            checkProviderConnection($row);

        } else {
            // Add New
            var index = $('#knowledge-providers-list .knowledge-provider-row').length;
            var id = 'new_' + Date.now();

            var html = `
                <div class="knowledge-provider-row card" data-id="${id}">
                    <div class="knowledge-provider-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="dashicons dashicons-move knowledge-provider-handle" style="cursor: move; color: #aaa;"></span>
                            <strong>${name}</strong> <span class="badge" style="background: #f0f0f1; padding: 2px 6px; border-radius: 4px; font-size: 11px;">${type}</span>
                        </div>
                        <div>
                            <button class="button button-small knowledge-edit-provider" style="margin-right: 5px;">Edit</button>
                            <button class="button button-small knowledge-remove-provider" style="color: #b32d2e; border-color: #b32d2e;">Remove</button>
                        </div>
                    </div>
                    <div class="knowledge-provider-details" style="margin-top: 10px; padding-left: 30px; font-size: 13px; color: #666;">
                        URL: ${url} | Model: ${model}
                    </div>
                    <input type="hidden" name="providers[${index}][id]" value="${id}">
                    <input type="hidden" name="providers[${index}][type]" value="${type}">
                    <input type="hidden" name="providers[${index}][name]" value="${name}">
                    <input type="hidden" name="providers[${index}][config][url]" value="${url}">
                    <input type="hidden" name="providers[${index}][config][model]" value="${model}">
                    <input type="hidden" name="providers[${index}][config][api_key]" value="${apiKey}">
                    <input type="hidden" name="providers[${index}][enabled]" value="1">
                </div>
            `;

            var $newRow = $(html);
            $('#knowledge-providers-list').append($newRow);
            checkProviderConnection($newRow);
        }

        $('#knowledge-add-provider-form').slideUp();
        resetForm();
        updateProviderOrder();
    });

    function updateProviderOrder() {
        $('#knowledge-providers-list .knowledge-provider-row').each(function(index) {
            // Update input names to reflect new order
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/providers\[\d+\]/, 'providers[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Provider Type Change (Toggle fields)
    $('#new_provider_type').on('change', function() {
        var type = $(this).val();
        if (type === 'ollama') {
            $('#field-row-url').show();
            $('#field-row-key').hide();
        } else {
            $('#field-row-url').hide();
            $('#field-row-key').show();
        }
    }).trigger('change');
});
