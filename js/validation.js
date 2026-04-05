
document.addEventListener("DOMContentLoaded", function () {


    function showError(input, message) {
        clearError(input);
        input.classList.add("is-invalid");
        const err = document.createElement("div");
        err.className = "invalid-feedback";
        err.textContent = message;
        input.parentNode.appendChild(err);
    }

    function clearError(input) {
        input.classList.remove("is-invalid");
        input.classList.remove("is-valid");
        const existing = input.parentNode.querySelector(".invalid-feedback");
        if (existing) existing.remove();
    }

    function markValid(input) {
        clearError(input);
        input.classList.add("is-valid");
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }



    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            let valid = true;

            const email    = document.getElementById("loginEmail");
            const password = document.getElementById("loginPassword");

            if (!email.value.trim()) {
                showError(email, "Email is required."); valid = false;
            } else if (!validateEmail(email.value.trim())) {
                showError(email, "Enter a valid email address."); valid = false;
            } else {
                markValid(email);
            }

            if (!password.value.trim()) {
                showError(password, "Password is required."); valid = false;
            } else if (password.value.length < 8) {
                showError(password, "Password must be at least 8 characters."); valid = false;
            } else {
                markValid(password);
            }

            if (!valid) e.preventDefault();
        });
    }



    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            let valid = true;

            const name     = document.getElementById("registerName");
            const email    = document.getElementById("registerEmail");
            const password = document.getElementById("registerPassword");
            const confirm  = document.getElementById("registerConfirm");

            if (!name.value.trim()) {
                showError(name, "Full name is required."); valid = false;
            } else if (name.value.trim().length > 100) {
                showError(name, "Name is too long (max 100 characters)."); valid = false;
            } else {
                markValid(name);
            }

            if (!email.value.trim()) {
                showError(email, "Email is required."); valid = false;
            } else if (!validateEmail(email.value.trim())) {
                showError(email, "Enter a valid email address."); valid = false;
            } else {
                markValid(email);
            }

            if (!password.value) {
                showError(password, "Password is required."); valid = false;
            } else if (password.value.length < 8) {
                showError(password, "Password must be at least 8 characters."); valid = false;
            } else {
                markValid(password);
            }

            if (!confirm.value) {
                showError(confirm, "Please confirm your password."); valid = false;
            } else if (confirm.value !== password.value) {
                showError(confirm, "Passwords do not match."); valid = false;
            } else {
                markValid(confirm);
            }

            if (!valid) e.preventDefault();
        });
    }



    const listingForm = document.getElementById("listingForm");
    if (listingForm) {
        listingForm.addEventListener("submit", function (e) {
            let valid = true;

            const name        = document.getElementById("listingName");
            const description = document.getElementById("listingDescription");
            const price       = document.getElementById("listingPrice");
            const category    = document.getElementById("listingCategory");
            const condition   = document.getElementById("listingCondition");
            const image       = document.getElementById("imageInput");

            if (!name.value.trim()) {
                showError(name, "Product name is required."); valid = false;
            } else if (name.value.trim().length > 100) {
                showError(name, "Name is too long (max 100 characters)."); valid = false;
            } else {
                markValid(name);
            }

            if (!description.value.trim()) {
                showError(description, "Description is required."); valid = false;
            } else if (description.value.trim().length < 10) {
                showError(description, "Description must be at least 10 characters."); valid = false;
            } else if (description.value.trim().length > 1000) {
                showError(description, "Description is too long (max 1000 characters)."); valid = false;
            } else {
                markValid(description);
            }

            if (!price.value || isNaN(price.value)) {
                showError(price, "Enter a valid price."); valid = false;
            } else if (parseFloat(price.value) <= 0) {
                showError(price, "Price must be greater than $0."); valid = false;
            } else if (parseFloat(price.value) > 99999) {
                showError(price, "Price seems too high (max $99,999)."); valid = false;
            } else {
                markValid(price);
            }

            if (!category.value) {
                showError(category, "Please select a category."); valid = false;
            } else {
                markValid(category);
            }

            if (!condition.value) {
                showError(condition, "Please select a condition."); valid = false;
            } else {
                markValid(condition);
            }

            // Image validation - only if a file was selected
            if (image && image.files.length > 0) {
                const file      = image.files[0];
                const allowed   = ["image/jpeg", "image/png", "image/webp"];
                const maxSize   = 5 * 1024 * 1024; // 5MB

                if (!allowed.includes(file.type)) {
                    showError(image, "Only JPG, PNG, and WEBP images are allowed."); valid = false;
                } else if (file.size > maxSize) {
                    showError(image, "Image must be under 5MB."); valid = false;
                }
            }

            if (!valid) e.preventDefault();
        });

        // Live price formatting - strip non-numeric chars except decimal
        const priceInput = document.getElementById("listingPrice");
        if (priceInput) {
            priceInput.addEventListener("blur", function () {
                const val = parseFloat(this.value);
                if (!isNaN(val)) this.value = val.toFixed(2);
            });
        }
    }



    const checkoutForm = document.getElementById("checkoutForm");
    if (checkoutForm) {
        checkoutForm.addEventListener("submit", function (e) {
            const confirmed = confirm("Are you sure you want to place this order?");
            if (!confirmed) e.preventDefault();
        });
    }



    // Validate fields as user leaves them for instant feedback
    document.querySelectorAll("input[required], select[required], textarea[required]").forEach(function (input) {
        input.addEventListener("blur", function () {
            if (!this.value.trim()) {
                showError(this, "This field is required.");
            } else {
                clearError(this);
            }
        });

        // Clear error on input
        input.addEventListener("input", function () {
            if (this.value.trim()) clearError(this);
        });
    });

});