//Contact form functionality
(function () {
    var $form = $('#form');
    var $response = $form.find('#response');
    var $file = $form.find('#file');
    var $body = $('body');
    var data;

    $form.submit(function () {

        var formData = new FormData($(this)[0]);

        $.ajax({
            url: './form.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                try {
                    response = JSON.parse(response);

                    if (!response.error) {
                        $form.find('input:not([type=submit]), textarea').val('');
                        $response.removeClass('error').empty();
                        $file.replaceWith($file.val('').clone(true));
                    }
                } catch (e) {
                    response = {
                        message: 'Bad response',
                        error: true
                    }
                }

                $response.html(response.message).toggleClass('error', response.error);
                $form.removeClass('sending');
            },
            cache: false,
            contentType: false,
            processData: false
        });

        return false;
    });
}());
