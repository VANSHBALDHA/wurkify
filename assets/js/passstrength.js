document.getElementById("password").addEventListener("input", function () {
  const strengthMeter = document.getElementById("password-strength");
  const password = this.value;
  let strength = "Weak";

  if (password.length > 8) {
    strength = "Medium";
    if (/[A-Z]/.test(password) && /[0-9]/.test(password)) {
      strength = "Strong";
    }
  }

  strengthMeter.textContent = `Password Strength: ${strength}`;
  if (strength === "Weak") {
    strengthMeter.style.color = "#ff4d4d";
  } else if (strength === "Medium") {
    strengthMeter.style.color = "#ffcc00";
  } else {
    strengthMeter.style.color = "#4dff4d";
  }
});
