/**
 * EmoEat — Frontend JavaScript
 */
document.addEventListener('DOMContentLoaded', () => {

    // Highlight active nav link
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-links a').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.style.color = '#6366f1';
            link.style.fontWeight = '700';
        }
    });

    // Emotion card selection animation
    document.querySelectorAll('.emotion-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.emotion-card').forEach(card => {
                card.style.transform = '';
            });
            const card = radio.nextElementSibling;
            if (card) {
                card.style.transform = 'scale(1.05)';
            }
        });
    });

    // Quiz option hover feedback
    document.querySelectorAll('.option-item input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const parent = radio.closest('.options-list');
            if (parent) {
                parent.querySelectorAll('.option-item').forEach(item => {
                    item.style.borderColor = '';
                    item.style.background = '';
                });
                const item = radio.closest('.option-item');
                if (item) {
                    item.style.borderColor = '#6366f1';
                    item.style.background = '#eef2ff';
                }
            }
        });
    });

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // XP bar animation on load
    document.querySelectorAll('.xp-bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bar.style.width = width;
            });
        });
    });
});
