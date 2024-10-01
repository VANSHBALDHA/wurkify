document.addEventListener('DOMContentLoaded', function() {
    const scroller = document.querySelector('.team-cols');
    const cards = Array.from(document.querySelectorAll('.team-col'));
    let scrollAmount = 0;
    const scrollSpeed = 0.2; // Slower speed for smooth scrolling
    let isScrolling = true;
    let scrollTimeout;

    // Clone the entire sequence to ensure a seamless circular loop
    function createCircularLoop() {
        cards.forEach(card => {
            const clone = card.cloneNode(true);
            scroller.appendChild(clone);
        });
    }

    createCircularLoop();

    function autoScroll() {
        if (isScrolling) {
            scrollAmount += scrollSpeed;
            if (scrollAmount >= scroller.scrollWidth / 2) {
                scrollAmount = 0; // Reset the scroll amount for the loop
            }
            scroller.style.transform = `translateX(-${scrollAmount}px)`;
        }
        requestAnimationFrame(autoScroll);
    }

    autoScroll();

    // Event listeners for pausing on hover and resuming after 2 seconds
    scroller.addEventListener('mousedown', function() {
        isScrolling = false;
        clearTimeout(scrollTimeout);
    });

    scroller.addEventListener('mouseup', function() {
        scrollTimeout = setTimeout(function() {
            isScrolling = true;
            autoScroll();
        }, 2000); // Restart scrolling after 2 seconds
    });

    scroller.addEventListener('mouseleave', function() {
        isScrolling = true;
        autoScroll();
    });

    // Pause scrolling and scale the card on hover
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            isScrolling = false;
            card.style.transform = 'scale(1.05)';
        });

        card.addEventListener('mouseleave', function() {
            card.style.transform = 'scale(1)';
            isScrolling = true;
            autoScroll();
        });
    });
});