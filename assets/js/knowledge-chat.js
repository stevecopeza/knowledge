jQuery(document).ready(function($) {
    const $chatHistory = $('#knowledge-chat-history');
    const $input = $('#knowledge-chat-input');
    const $submit = $('#knowledge-chat-submit');
    const $status = $('#knowledge-chat-status');

    function appendMessage(sender, text, type) {
        const $msg = $('<div>').addClass('knowledge-chat-message').addClass('message-' + type);
        
        // Convert newlines to <br> for simple formatting
        const formattedText = text.replace(/\n/g, '<br>');
        
        $msg.html('<strong>' + sender + ':</strong><br>' + formattedText);
        $chatHistory.append($msg);
        $chatHistory.scrollTop($chatHistory[0].scrollHeight);
    }

    function askAI() {
        const question = $input.val().trim();
        if (!question) return;

        // UI Updates
        appendMessage('You', question, 'user');
        $input.val('').prop('disabled', true);
        $submit.prop('disabled', true);
        $status.text('Thinking...');

        // AJAX Request
        $.ajax({
            url: knowledgeChat.ajaxurl,
            type: 'POST',
            data: {
                action: 'knowledge_chat',
                nonce: knowledgeChat.nonce,
                question: question
            },
            success: function(response) {
                if (response.success) {
                    appendMessage('AI', response.data.answer, 'ai');
                } else {
                    appendMessage('System', 'Error: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                appendMessage('System', 'Network Error: ' + error, 'error');
            },
            complete: function() {
                $input.prop('disabled', false).focus();
                $submit.prop('disabled', false);
                $status.text('');
            }
        });
    }

    // Event Listeners
    $submit.on('click', askAI);
    
    $input.on('keypress', function(e) {
        if (e.which === 13) {
            askAI();
        }
    });
});
