// assets/js/form-validation.js

// Validate admission form
document.getElementById('admission-form').addEventListener('submit', function (e) {
    const inputs = this.querySelectorAll('input, textarea, select');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });

    if (!isValid) {
        e.preventDefault();
        showToast('Please fill out all required fields.', 'error');
    }
});