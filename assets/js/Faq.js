document.addEventListener("DOMContentLoaded", function() {
    const faqCards = document.querySelectorAll(".faq-card");

    function initializeFAQ() {
        if (window.innerWidth > 768) {
            faqCards.forEach(card => {
                const question = card.querySelector(".faq-question");
                question.addEventListener("click", function() {
                    // Close all other FAQ answers
                    faqCards.forEach(otherCard => {
                        if (otherCard !== card) {
                            otherCard.classList.remove("active");
                            otherCard.querySelector(".faq-text").style.maxHeight = 0;
                        }
                    });

                    // Toggle the clicked FAQ answer
                    card.classList.toggle("active");
                    const answer = card.querySelector(".faq-text");
                    if (card.classList.contains("active")) {
                        answer.style.maxHeight = answer.scrollHeight + "px";
                    } else {
                        answer.style.maxHeight = 0;
                    }
                });
            });
        } else {
            faqCards.forEach(card => {
                const question = card.querySelector(".faq-question");
                question.removeEventListener("click", function() {
                    // Prevent click functionality on smaller screens
                });
            });
        }
    }

    initializeFAQ();
    window.addEventListener('resize', initializeFAQ);
});
