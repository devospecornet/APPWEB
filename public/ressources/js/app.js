document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('[data-count]');

    counters.forEach((element) => {
        const target = Number(element.getAttribute('data-count') || 0);
        const duration = 900;
        const start = performance.now();

        const updateCounter = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const value = Math.round(target * progress);
            element.textContent = value.toLocaleString('fr-FR');

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        };

        requestAnimationFrame(updateCounter);
    });

    const refusalForms = document.querySelectorAll('form');
    refusalForms.forEach((form) => {
        const refuseButton = form.querySelector('button[name="refuser_fiche"]');
        const commentField = form.querySelector('textarea[name="commentaire_comptable"]');

        if (!refuseButton || !commentField) {
            return;
        }

        refuseButton.addEventListener('click', (event) => {
            if (commentField.value.trim() === '') {
                event.preventDefault();
                alert('Un commentaire comptable est obligatoire pour refuser une fiche.');
                commentField.focus();
            }
        });
    });
});
