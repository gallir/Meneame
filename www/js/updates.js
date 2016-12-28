;(function($) {
    var INIT = {};

    INIT.formRegister = function() {
        var $form = $('#form-register');

        if (!$form.length) {
            return;
        }

        var $password = $form.find('#password'),
            $name = $form.find('#name'),
            $email = $form.find('#email');

        function setStatus($input, response, hideError) {
            var $parent = $input.parent(),
                $status = $parent.find('.input-status');

            $parent.removeClass('input-error input-success');
            $status.removeClass('fa-check fa-times');

            $parent.find('.input-error-message').remove();

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

            var $icon = $(this).find('.fa');

            if ($password.attr('type') === 'text') {
                $password.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                $password.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });

        $form.on('submit', function(e) {
            $name.trigger('change');
            $email.trigger('change');
            $password.trigger('change');

            if ($form.find('.input-validate').length !== $form.find('.input-validate.input-success').length) {
                e.preventDefault();
                return;
            }

            $form.append('<input type="hidden" name="base_key" value="' + base_key + '" />');
        });

        if ($name.val()) {
            $name.trigger('change');
        }

        if ($email.val()) {
            $email.trigger('change');
        }
    };

    INIT.showSubDescription = function() {
        $('.show-sub-description').on('click', function(e) {
            e.preventDefault();

            var $description = $('.sub-description');

            if ($description.hasClass('hidden')) {
                $description.hide().removeClass('hidden');
            }

            $description.slideToggle();
        });
    };

    INIT.formSubsSearch = function() {
        var $form = $('#form-subs-search');

        if (!$form.length) {
            return;
        }

        var $inputSearch = $form.find('.input-search');

        $.ajax({
            url: base_url + 'backend/get_subs.php',
            cache: false,
            dataType: 'json',
            success: function(data) {
                $inputSearch.typeahead({
                    source: data,
                    fitToElement: true,
                    displayText: function(item) {
                        return '<div class="name">' + item.name + '</div><div class="description">' + item.name_long + '</div>';
                    },
                    highlighter: function(item) {
                        return item;
                    },
                    afterSelect: function(item) {
                        $inputSearch.val(item.name);

                        window.location = base_url + 'm/' + item.name;
                    }
                });
            }
        });

        $form.find('.input-filter').on('change', function(e) {
            window.location = base_url + 'subs?' + $(this).val();
        });
    };

    INIT.formPostEdit = function() {
        var $form = $('#edit-form form');

        if (!$form.length) {
            return;
        }

        var $textarea = $form.find('textarea'),
            textareaSize = $textarea.outerHeight();

        $textarea.on('focus', function() {
            $form.find('.hidden.show-on-focus').hide().removeClass('hidden').slideDown();
        });

        $textarea.on('keydown', function(e) {
        });

        $form.find('[data-show]').on('click', function(e) {
            e.preventDefault();

            var $element = $($(this).data('show'));

            if (!$element.length) {
                return;
            }

            if ($element.is(':visible')) {
                $element.slideUp();
            } else {
                $element.hide().removeClass('hidden').slideDown();
            }
        });

        addPostCode(function() {
            $form.ajaxForm({
                async: false,
                success: function(response) {
                    if (/^ERROR:/.test(response)) {
                        return mDialog.notify(response, 5);
                    }

                    var id = parseInt($form.find('input[name="post_id"]').val()),
                        $container;

                    if (id > 0) {
                        $container = $('#pcontainer-' + id);
                    } else {
                        $('.comments-list:first').prepend($container = $('<li />'));
                    }

                    $textarea.animate({ height: textareaSize });

                    $container.html(response).trigger('DOMChanged', $container);

                    $form.find('.show-on-focus').slideUp().addClass('hidden');
                    $form.find('textarea, input[type="text"], input[name="post_id"]').val('');
                }
            });

            $form.droparea({
                maxsize: $form.find('input[name="MAX_FILE_SIZE"]').val()
            });

            $form.find('.uploadFile').nicefileinput();

            $textarea.autosize();
        });
    };

    INIT.formRegister();
    INIT.showSubDescription();
    INIT.formSubsSearch();
    INIT.formPostEdit();
})(jQuery);
