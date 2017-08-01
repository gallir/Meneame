;

/*!
 * JavaScript Cookie v2.1.3
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function (factory) {
    var registeredInModuleLoader = false;

    if (typeof define === 'function' && define.amd) {
        define(factory);
        registeredInModuleLoader = true;
    }

    if (typeof exports === 'object') {
        module.exports = factory();
        registeredInModuleLoader = true;
    }

    if (!registeredInModuleLoader) {
        var OldCookies = window.Cookies;
        var api = window.Cookies = factory();
        api.noConflict = function () {
            window.Cookies = OldCookies;
            return api;
        };
    }
}(function () {
    function extend () {
        var i = 0;
        var result = {};

        for (; i < arguments.length; i++) {
            var attributes = arguments[i];

            for (var key in attributes) {
                result[key] = attributes[key];
            }
        }

        return result;
    }

    function init (converter) {
        function api (key, value, attributes) {
            var result;

            if (typeof document === 'undefined') {
                return;
            }

            if (arguments.length > 1) {
                attributes = extend({
                    path: '/'
                }, api.defaults, attributes);

                if (typeof attributes.expires === 'number') {
                    var expires = new Date();
                    expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
                    attributes.expires = expires;
                }

                attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';

                try {
                    result = JSON.stringify(value);

                    if (/^[\{\[]/.test(result)) {
                        value = result;
                    }
                } catch (e) {}

                if (!converter.write) {
                    value = encodeURIComponent(String(value))
                        .replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
                } else {
                    value = converter.write(value, key);
                }

                key = encodeURIComponent(String(key));
                key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
                key = key.replace(/[\(\)]/g, escape);

                var stringifiedAttributes = '';

                for (var attributeName in attributes) {
                    if (!attributes[attributeName]) {
                        continue;
                    }

                    stringifiedAttributes += '; ' + attributeName;

                    if (attributes[attributeName] === true) {
                        continue;
                    }

                    stringifiedAttributes += '=' + attributes[attributeName];
                }

                return (document.cookie = key + '=' + value + stringifiedAttributes);
            }

            if (!key) {
                result = {};
            }

            var cookies = document.cookie ? document.cookie.split('; ') : [];
            var rdecode = /(%[0-9A-Z]{2})+/g;
            var i = 0;

            for (; i < cookies.length; i++) {
                var parts = cookies[i].split('=');
                var cookie = parts.slice(1).join('=');

                if (cookie.charAt(0) === '"') {
                    cookie = cookie.slice(1, -1);
                }

                try {
                    var name = parts[0].replace(rdecode, decodeURIComponent);
                    cookie = converter.read ?
                        converter.read(cookie, name) : converter(cookie, name) ||
                        cookie.replace(rdecode, decodeURIComponent);

                    if (this.json) {
                        try {
                            cookie = JSON.parse(cookie);
                        } catch (e) {}
                    }

                    if (key === name) {
                        result = cookie;
                        break;
                    }

                    if (!key) {
                        result[name] = cookie;
                    }
                } catch (e) {}
            }

            return result;
        }

        api.set = api;

        api.get = function (key) {
            return api.call(api, key);
        };

        api.getJSON = function () {
            return api.apply({
                json: true
            }, [].slice.call(arguments));
        };

        api.defaults = {};

        api.remove = function (key, attributes) {
            api(key, '', extend(attributes, {
                expires: -1
            }));
        };

        api.withConverter = init;

        return api;
    }

    return init(function () {});
}));

function initFormPostEdit($form) {
    if (!$form.length) {
        return;
    }

    var $textarea = $form.find('textarea'),
        textareaSize = $textarea.outerHeight(),
        $textCounter = $form.find('.input-counter');

    $textarea.on('focus', function() {
        $form.find('.hidden.show-on-focus').hide().removeClass('hidden').slideDown();
    });

    $textarea.on('keyup', function() {
        $textCounter.val($textarea.attr('maxlength') - $textarea.val().length);
    });

    showPoll();

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
            $form.find('input[name="key"]').val(Math.floor(Math.random() * (100000000 - 1000000)) + 1000000);

            initPollVote($container.find('.poll-vote form').first());
        }
    });

    $form.droparea({
        maxsize: $form.find('input[name="MAX_FILE_SIZE"]').val()
    });

    $form.find('.uploadFile').nicefileinput();

    $textarea.autosize();
}

function initPollVote($form) {
    if (!$form || !$form.length) {
        return;
    }

    $form.ajaxForm({
        async: false,
        success: function(response) {
            if (/^ERROR:/.test(response)) {
                return mDialog.notify(response, 5);
            }

            $form.closest('.poll-vote').replaceWith(response);
        }
    });
}

function showPoll() {
    $('[data-show-poll="true"]').off('click').on('click', function(e) {
        e.preventDefault();

        var $element = $(this).closest('form').find('.poll-edit');

        if ($element.is(':visible')) {
            $element.slideUp();
        } else {
            $element.hide().removeClass('hidden').slideDown();
        }
    });
}

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

            $.get(base_url + 'backend/checkfield', {name: $input.attr('name'), value: value}, function(response) {
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
        $('.post-edit form').each(function() {
            var $form = $(this);

            addPostCode(function() {
                initFormPostEdit($form);
            });
        });
    };

    INIT.commentCollapse = function() {
        var $expandables = $('.comment-header .comment-expand');
        var cookieName = 'comments-collapsed';

        if (!$expandables.length) {
            return;
        }

        function getUnique(values) {
            return values.filter(function (value, index, values) {
                return values.indexOf(value) === index;
            });
        }

        function cookieGet() {
            return Cookies.getJSON(cookieName) || [];
        }

        function cookieSet(values) {
            Cookies.set(cookieName, getUnique(values));
        }

        function addCookie(id) {
            var current = cookieGet();

            current.push(id);

            cookieSet(current);
        }

        function removeCookie(id) {
            var current = cookieGet();

            while ((index = current.indexOf(id)) !== -1) {
                current.splice(index, 1);
            }

            cookieSet(current);
        }

        function hide($button, $parent, $childs, id) {
            var $header = $button.closest('.comment-header');

            $button.closest('.comment').find('.comment-text, .comment-footer').slideUp('fast', function() {
                if ($childs.length === 0) {
                    $parent.addClass('collapsed');
                }

                $button.html('<i class="fa fa-chevron-down"></i>');
            });

            if ($childs.length === 0) {
                return;
            }

            var count = $parent.find('.comment').length - 1;

            $childs.slideUp('fast', function() {
                $parent.addClass('collapsed');

                if (!count || $header.find('.comments-closed-counter').length) {
                    return;
                }

                $header.append(
                    '<a href="javascript:void(0);" class="comments-closed-counter">'
                    + count + ' <i class="fa fa-comments"></i>'
                    + '</a>'
                );
            });
        }

        function show($button, $parent, $childs, id) {
            $parent.removeClass('collapsed');

            $button.html('<i class="fa fa-chevron-up"></i>');

            $button.closest('.comment').find('.comment-text, .comment-footer').slideDown('fast');
            $childs.slideDown('fast');
        }

        $.each(cookieGet(), function(key, id) {
            var $this = $('.comment-expand[data-id="' + id + '"]');

            if (!$this.length) {
                return;
            }

            var $parent = $this.closest('.threader');

            hide($this, $parent, $parent.find('> .threader-childs'), $this.data('id'));
        });

        $expandables.on('mouseover', function(e) {
            $(this).closest('.threader').addClass('expandable');
        });

        $expandables.on('mouseout', function(e) {
            $(this).closest('.threader').removeClass('expandable');
        });

        $expandables.on('click', function(e) {
            e.preventDefault();

            var $this = $(this),
                $parent = $this.closest('.threader'),
                $childs = $parent.find('> .threader-childs'),
                id = $this.data('id');

            if ($parent.hasClass('collapsed')) {
                show($this, $parent, $childs, id);
                removeCookie(id);
            } else {
                hide($this, $parent, $childs, id);
                addCookie(id);
            }
        });

        $(document).on('click', '.comments-closed-counter', function(e) {
            $(this).closest('.comment-header').find('.comment-expand').trigger('click');
        });
    };

    INIT.formPollVote = function() {
        $('.poll-vote form').each(function() {
            var $form = $(this);

            addPostCode(function() {
                initPollVote($form);
            });
        });
    };

    INIT.showPoll = function() {
        showPoll();
    };

    INIT.sticky = function() {
        var $sticky = $('.apply-sticky');

        if (is_mobile || !$sticky.length) {
            return;
        }

        function sticky() {
            var scrollTop = $(window).scrollTop();

            if (!stuck && ((initOffsetTop - scrollTop) < 0)) {
                $sticky.toggleClass('sticky');
                stuck = true;
            } else if (stuck && (scrollTop <= initOffsetTop)) {
                $sticky.toggleClass('sticky');
                stuck = false;
            }
        }

        $sticky.css('width', $sticky.width());

        var stuck = false;
        var initOffsetTop = $sticky.offset().top - $('#header-top').height() - 40;

        window.addEventListener('scroll', sticky);

        sticky();
    };

    INIT.articleEdit = function() {
        if (!$('.section-article-submit').length) {
            return;
        }

        $('.form textarea[name="title"]').autosize();
        $('#sub_id').selectize();

        var $textarea = $('.form textarea[name="bodytext"]');

        function imageHandler() {
            var value = prompt('{% trans _('Enlace de la imagen:') %}');

            if (!value) {
                return;
            }

            if (value.indexOf('https://') !== 0) {
                return alert('{% trans _('Sólo se permiten URLs bajo HTTPS') %}');
            }

            if (!value.match(/\.(png|jpg|jpeg|gif)$/i)) {
                return alert('{% trans _('Sólo se permiten URLs que finalicen en jpg, png y gif') %}');
            }

            this.quill.insertEmbed(this.quill.getSelection().index, 'image', value, Quill.sources.USER);
        }

        var $quill = new Quill('#editor', {
            placeholder: '{% trans _('Empieza a escribir tu artículo...') %}',
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{'header': [2, 3, false]}],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link', 'image', 'video'],
                        [{'list': 'ordered'}, {'list': 'bullet'}, 'blockquote']
                    ],
                    handlers: {
                        image: imageHandler
                    }
                }
            }
        });

        var $quillContents = $('.ql-editor');

        setInterval(function () {
            $textarea.val($quillContents.html());
        }, 1000);

        var $quillToolbar = $('.ql-toolbar');

        var stuck = false;
        var initOffsetTop = $quillToolbar.offset().top - $('#header-top').height();

        window.addEventListener('scroll', function () {
            var scrollTop = $(window).scrollTop();

            if (!stuck && (scrollTop > initOffsetTop)) {
                $quillToolbar.toggleClass('sticky');
                stuck = true;
            } else if (stuck && (scrollTop <= initOffsetTop)) {
                $quillToolbar.toggleClass('sticky');
                stuck = false;
            }
        });

        $('button[name="discard"]').on('click', function(e) {
            e.preventDefault();

            var $this = $(this),
                data = $this.closest('form').serializeArray();

            data.push({name: this.name, value: this.value});

            $.post(null, data, function(data) {
                if (!data) {
                    return;
                }

                var $alert = $('<div class="alert">' + data.message + '</div>');

                if (data.success) {
                    $alert.addClass('alert-success');
                } else {
                    $alert.addClass('alert-danger');
                }

                $this.closest('.story-blog-aside').append($alert);

                setTimeout(function() {
                    $alert.fadeOut();
                }, 3000);
            }, 'json');
        });

        $('button[name="delete"]').on('click', function(e) {
            $(this).closest('form').find('[required]').removeAttr('required');
            return true;
        });
    };

    INIT.formRegister();
    INIT.showSubDescription();
    INIT.formSubsSearch();
    INIT.formPostEdit();
    INIT.showPoll();
    INIT.formPollVote();
    INIT.commentCollapse();
    INIT.sticky();

    addPostCode(function () {
        INIT.articleEdit();
    });
})(jQuery);
