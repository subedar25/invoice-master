{{-- Same behavior as profile/changepassword: requirements popup + confirm match + show/hide toggles. --}}
{{-- Delegated + namespaced events so it works when the form is injected (Settings tab) and re-binds safely. --}}
<script>
(function ($) {
    var ns = '.invoiceUpdatePwdForm';

    function toggleReqIcon(id, isValid) {
        var $icon = $(id + ' i');
        $icon.toggleClass('fa-times text-danger', !isValid)
             .toggleClass('fa-check text-success', isValid);
    }

    function validatePasswordRequirements() {
        var $password = $('#update_password_password');
        var $requirements = $('#change-password-requirements');
        if (!$password.length || !$requirements.length) {
            return;
        }

        var password = $password.val() || '';
        var requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{}|\\:;"'<>,.?/~`]/.test(password)
        };

        if (password.length > 0) {
            $requirements.show();
        } else {
            $requirements.hide();
        }

        toggleReqIcon('#change-req-length', requirements.length);
        toggleReqIcon('#change-req-uppercase', requirements.uppercase);
        toggleReqIcon('#change-req-lowercase', requirements.lowercase);
        toggleReqIcon('#change-req-number', requirements.number);
        toggleReqIcon('#change-req-special', requirements.special);
    }

    function validatePasswordMatch() {
        var $password = $('#update_password_password');
        var $confirm = $('#update_password_password_confirmation');
        var $matchMessage = $('#changePasswordMatchMessage');
        if (!$password.length || !$confirm.length || !$matchMessage.length) {
            return;
        }

        var password = $password.val() || '';
        var confirm = $confirm.val() || '';

        if (password && confirm) {
            if (password === confirm) {
                $matchMessage.text('Passwords match')
                    .removeClass('text-danger')
                    .addClass('text-success');
            } else {
                $matchMessage.text('Passwords do not match')
                    .removeClass('text-success')
                    .addClass('text-danger');
            }
        } else {
            $matchMessage.text('').removeClass('text-success text-danger');
        }
    }

    $(document)
        .off('input' + ns, '#update_password_password')
        .on('input' + ns, '#update_password_password', function () {
            validatePasswordRequirements();
            validatePasswordMatch();
        })
        .off('focus' + ns, '#update_password_password')
        .on('focus' + ns, '#update_password_password', function () {
            var $password = $('#update_password_password');
            var $requirements = $('#change-password-requirements');
            if (($password.val() || '').length > 0) {
                $requirements.show();
            }
        })
        .off('blur' + ns, '#update_password_password')
        .on('blur' + ns, '#update_password_password', function () {
            setTimeout(function () {
                $('#change-password-requirements').hide();
            }, 120);
        })
        .off('input' + ns, '#update_password_password_confirmation')
        .on('input' + ns, '#update_password_password_confirmation', validatePasswordMatch)
        .off('click' + ns, '#toggleChangePassword')
        .on('click' + ns, '#toggleChangePassword', function () {
            var $password = $('#update_password_password');
            var $icon = $(this).find('i');
            if ($password.attr('type') === 'password') {
                $password.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $password.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        })
        .off('click' + ns, '#toggleChangeConfirmPassword')
        .on('click' + ns, '#toggleChangeConfirmPassword', function () {
            var $confirm = $('#update_password_password_confirmation');
            var $icon = $(this).find('i');
            if ($confirm.attr('type') === 'password') {
                $confirm.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $confirm.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
})(jQuery);
</script>
