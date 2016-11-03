(function ($){
    'use strict';

    var $formRegister = $('#form-register');

    if ($formRegister.length) {
        var $password = $('#password', $formRegister),
            $name = $('#name', $formRegister),
            $email = $('#email', $formRegister);

        function setStatus($input, response, hideError) {
            var $parent = $input.parent(),
                $status = $('.input-status', $parent);

            $parent.removeClass('input-error input-success');
            $status.removeClass('fa-check fa-times');

            $('.input-error-message', $parent).remove();

            if (response === 'OK') {
                $parent.addClass('input-success');
                $status.addClass('fa-check');

                return;
            }

            if (hideError !== true) {
                $parent.addClass('input-error');
                $status.addClass('fa-times');
            }

            if (response !== 'KO') {
                $parent.append('<span class="input-error-message">' + response + '</span>');
            }
        }

        function checkAjaxField($input, callback) {
            var value = $input.val();

            if ($input.data('previous') === value) {
                return;
            }

            if (typeof callback !== 'function') {
                callback = setStatus;
            }

            $.get(base_url + 'backend/checkfield', {type: $input.attr('name'), name: value}, function(response) {
                callback($input, response);
            });

            $input.data('previous', value);
        }

        function securePasswordCheck(value) {
            return (value.length >= 8) && value.match('^(?=.{8,})(?=(.*[a-z].*))(?=(.*[A-Z].*))(?=(.*[0-9].*)).*$', 'g');
        }

        $name.on('change', function() {
            checkAjaxField($name);
        });

        $email.on('change', function() {
            checkAjaxField($email);
        });

        $password.on('keyup', function() {
            setStatus($password, securePasswordCheck($password.val()) ? 'OK' : 'KO', true);
        });

        $password.on('change', function() {
            setStatus($password, securePasswordCheck($password.val()) ? 'OK' : 'KO');
        });

        $('.input-password-show').on('click', function(e) {
            e.preventDefault();

            var $icon = $('.fa', $(this));

            if ($password.attr('type') === 'text') {
                $password.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                $password.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        $formRegister.on('submit', function(e) {
            $name.trigger('change');
            $email.trigger('change');
            $password.trigger('change');

            if ($('.input-validate', $formRegister).length !== $('.input-validate.input-success', $formRegister).length) {
                e.preventDefault();
                return;
            }

            $formRegister.append('<input type="hidden" name="base_key" value="' + base_key + '" />');
        });

        if ($name.val()) {
            $name.trigger('change');
        }

        if ($email.val()) {
            $email.trigger('change');
        }
    }
})(jQuery);
