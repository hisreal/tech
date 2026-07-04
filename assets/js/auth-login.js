/* Standalone authentication screen behavior: validation, password toggle, loading state. */
(function(){
    document.querySelectorAll('.password-toggle').forEach(function(button){
        button.addEventListener('click', function(){
            var input = document.getElementById(button.getAttribute('aria-controls'));
            var icon = button.querySelector('i');
            if(!input){ return; }
            var visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            if(icon){ icon.className = visible ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'; }
        });
    });

    document.querySelectorAll('[data-auth-form]').forEach(function(form){
        var alertBox = form.querySelector('.auth-alert');
        var submit = form.querySelector('.auth-submit');
        form.addEventListener('submit', function(event){
            var valid = true;
            form.querySelectorAll('[data-error-for]').forEach(function(error){ error.textContent = ''; });
            var username = form.querySelector('[name="username"]');
            var password = form.querySelector('[name="password"]');
            if(!username.value.trim()){
                valid = false;
                form.querySelector('[data-error-for="username"]').textContent = 'Username or email is required.';
            }
            if(!password.value.trim()){
                valid = false;
                form.querySelector('[data-error-for="password"]').textContent = 'Password is required.';
            } else if(password.value.trim().length < 6){
                valid = false;
                form.querySelector('[data-error-for="password"]').textContent = 'Password must be at least 6 characters.';
            }
            if(!valid){
                event.preventDefault();
                alertBox.className = 'auth-alert error';
                alertBox.textContent = 'Please correct the highlighted fields and try again.';
                return;
            }
            alertBox.className = 'auth-alert success';
            alertBox.textContent = 'Signing in...';
            submit.disabled = true;
            submit.dataset.originalText = submit.innerHTML;
            submit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Signing in...';
        });
    });
})();