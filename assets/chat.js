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

            scrollBottom();

            $('#recipe-ai-messages').append(
                `
                <div class="recipe-ai-message ai typing">
                    Thinking...
                </div>
                `
            );

            scrollBottom();

            /*
             * OpenAI call comes later
             */

            setTimeout(function () {

                $('.typing').remove();

                $('#recipe-ai-messages').append(
                    `
                    <div class="recipe-ai-message ai">
                        This is a sample AI response.
                    </div>
                    `
                );

                scrollBottom();

            }, 1000);

        }
    );

});