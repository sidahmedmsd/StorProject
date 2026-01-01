document.addEventListener('DOMContentLoaded', function () {
    // Helper function to show error
    function showError(input, message) {
        let parent = input.parentElement;
        let existingError = parent.querySelector('.error-message');
        if (!existingError) {
            let errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.color = '#e74c3c';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.innerText = message;
            parent.appendChild(errorDiv);
        } else {
            existingError.innerText = message;
        }
        input.style.borderColor = '#e74c3c';
    }

    // Helper to clear error
    function clearError(input) {
        let parent = input.parentElement;
        let errorDiv = parent.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.style.borderColor = '#ddd';
    }

    // Generic form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input, textarea');

            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    showError(input, 'This field is required');
                    isValid = false;
                } else {
                    clearError(input);
                }

                // Email validation
                if (input.type === 'email' && input.value.trim()) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(input.value)) {
                        showError(input, 'Invalid email address');
                        isValid = false;
                    }
                }

                // Price validation
                if (input.name === 'price' && input.value.trim()) {
                    if (isNaN(input.value) || Number(input.value) <= 0) {
                        showError(input, 'Please enter a valid price');
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Find first invalid input and scroll/focus
                const firstInvalid = form.querySelector('.error-message').previousElementSibling;
                firstInvalid.focus();

                // Add shake animation
                form.classList.add('shake');
                setTimeout(() => form.classList.remove('shake'), 500);
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                if (input.value.trim()) {
                    clearError(input);
                }
            });
        });
    });

    // Add animation to products
    const products = document.querySelectorAll('.product');
    products.forEach((product, index) => {
        product.style.opacity = '0';
        product.style.transform = 'translateY(20px)';
        product.style.transition = 'all 0.5s ease';

        setTimeout(() => {
            product.style.opacity = '1';
            product.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
