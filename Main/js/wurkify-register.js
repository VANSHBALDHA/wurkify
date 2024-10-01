const inputs = document.querySelectorAll(".input");

function addcl() {
  let parent = this.parentNode.parentNode;
  parent.classList.add("focus");
}

function remcl() {
  let parent = this.parentNode.parentNode;
  if (this.value == "") {
    parent.classList.remove("focus");
  }
}

inputs.forEach((input) => {
  input.addEventListener("focus", addcl);
  input.addEventListener("blur", remcl);
});

// Password authentication
// Password validation function
function validateForm() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const passwordHelp = document.getElementById("passwordHelp");
  const confirmPasswordHelp = document.getElementById("confirmPasswordHelp");

  // Clear previous help texts
  passwordHelp.innerHTML = "";
  confirmPasswordHelp.innerHTML = "";

  // Validate password strength
  const passwordStrengthRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

  if (!passwordStrengthRegex.test(password)) {
    passwordHelp.style.color = "red";
    passwordHelp.innerHTML =
      "Password must be at least 8 characters long, contain one uppercase letter, one number, and one special character.";
    return false;
  }

  // Validate password match
  if (password !== confirmPassword) {
    confirmPasswordHelp.style.color = "red";
    confirmPasswordHelp.innerHTML = "Passwords do not match.";
    return false;
  }

  return true;
}

// Show/Hide Password functionality
function togglePasswordVisibility() {
  const passwordField = document.getElementById("password");
  const confirmPasswordField = document.getElementById("confirmPassword");
  const checkbox = document.querySelector(
    '.show-password input[type="checkbox"]'
  );

  if (checkbox.checked) {
    passwordField.type = "text";
    confirmPasswordField.type = "text";
  } else {
    passwordField.type = "password";
    confirmPasswordField.type = "password";
  }
}

// Add event listener to the show password checkbox
document
  .querySelector('.show-password input[type="checkbox"]')
  .addEventListener("click", togglePasswordVisibility);

//   login show password
function togglePasswordVisibility() {
  const passwordField = document.getElementById("loginPassword");
  const toggleCheckbox = document.getElementById("togglePassword");

  if (toggleCheckbox.checked) {
    passwordField.type = "text"; // Show password
  } else {
    passwordField.type = "password"; // Hide password
  }
}

function validateForm() {
  const email = document.getElementById("email").value;
  const password = document.getElementById("loginPassword").value;
  const errorMessages = document.getElementById("errorMessages");

  errorMessages.innerHTML = ""; // Clear previous error messages

  let isValid = true;

  // Validate email
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    errorMessages.innerHTML += "<p>Please enter a valid email address.</p>";
    isValid = false;
  }

  // Validate password
  if (password.length < 6) {
    errorMessages.innerHTML +=
      "<p>Password must be at least 6 characters long.</p>";
    isValid = false;
  }

  return isValid; // Prevent form submission if not valid
}
