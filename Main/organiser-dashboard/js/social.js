function validateForm() {
  // Get form fields
  var twitter = document.forms["socialForm"]["twitter_username"].value;
  var facebook = document.forms["socialForm"]["facebook_username"].value;
  var linkedin = document.forms["socialForm"]["linkedin_username"].value;
  var youtube = document.forms["socialForm"]["youtube_username"].value;

  // Check if all fields are empty
  if (twitter === "" && facebook === "" && linkedin === "" && youtube === "") {
    alert("Please fill in at least one social media field before submitting.");
    return false; // Prevent form submission
  }

  // Allow form submission if at least one field is filled
  return true;
}
