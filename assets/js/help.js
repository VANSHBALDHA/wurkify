// Toggle FAQ answers

document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', () => {
        const answer = item.nextElementSibling;


        // Close all other answers
        document.querySelectorAll('.faq-answer').forEach(ans => {
            if (ans !== answer) ans.style.display = 'none';

        });

        // Toggle the clicked answer
        if (answer.style.display === 'block') {
            answer.style.display = 'none';


        } else {
            answer.style.display = 'block';
        }
    });
});

// Form submission handling
document.getElementById('support-form').addEventListener('submit', function(event) {
    event.preventDefault();

    // Basic validation (can be enhanced as needed)
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const issue = document.getElementById('issue').value.trim();

    if (name === '' || email === '' || issue === '') {
        alert('Please fill in all required fields.');
        return;
    }

    // Show confirmation message
    document.getElementById('confirmation-message').style.display = 'block';

    // Optionally, clear the form
    this.reset();
});