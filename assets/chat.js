jQuery(function ($) {

    function scrollBottom() {

        let messages =
            $('#recipe-ai-messages');

        messages.scrollTop(
            messages[0].scrollHeight
        );

    }

    $('.recipe-ai-suggestion').on(
        'click',
        function () {

            $('#recipe-ai-input').val(
                $(this).text()
            );

        }
    );

    $('#recipe-ai-send').on(
        'click',
        function () {

            let message =
                $('#recipe-ai-input')
                    .val()
                    .trim();

            if (!message) {
                return;
            }

            $('#recipe-ai-messages').append(
                `
                <div class="recipe-ai-message user">
                    ${message}
                </div>
                `
            );

            $('#recipe-ai-input').val('');

            $('#recipe-ai-messages').append(
                `
                <div class="recipe-ai-message ai typing">
                    Thinking...
                </div>
                `
            );

            scrollBottom();

            $.ajax({

                url: RecipeAI.endpoint,

                method: 'POST',

                beforeSend: function (xhr) {

                    xhr.setRequestHeader(
                        'X-WP-Nonce',
                        RecipeAI.nonce
                    );

                },

                data: {
                    message: message
                },

                success: function (response) {

                    $('.typing').remove();

                    $('#recipe-ai-messages').append(
                        `
                        <div class="recipe-ai-message ai">
                            ${response.reply}
                        </div>
                        `
                    );

                    scrollBottom();

                },

                error: function () {

                    $('.typing').remove();

                    $('#recipe-ai-messages').append(
                        `
                        <div class="recipe-ai-message ai">
                            Something went wrong.
                        </div>
                        `
                    );

                }

            });

        }
    );

});