// Show or hide dress code details based on selection
function toggleDressCodeDetails() {
  const dressCode = document.getElementById("dress_code").value;
  const details = document.getElementById("dress_code_details");
  details.style.display = dressCode === "Yes" ? "block" : "none";
}

// Validate input for number-only fields
function validateNumber(input) {
  if (!/^\d+$/.test(input.value)) {
    alert("Please enter a valid number");
    input.value = "";
  }
}

// Function to fetch location using Maps API (replace with actual implementation)
function fetchLocation() {
  // This should trigger a map selection or fetch user's location via geolocation
  alert("Fetching location...");
}
