function previewProfilePicture(event) {
    const file = event.target.files[0];
    const previewImage = document.getElementById('profilepic');

    if (!file || !previewImage) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Nur JPG, PNG und WEBP sind erlaubt.');
        event.target.value = '';
        return;
    }

    const previewUrl = URL.createObjectURL(file);
    previewImage.src = previewUrl;

    previewImage.onload = function () {
        URL.revokeObjectURL(previewUrl);
    };
}

function shareProfile() {
    const url = window.location.origin + '/<?php echo e($id); ?>';

    if (navigator.share) {
        navigator.share({
            title: 'QuickBio Profil',
            text: 'Schau dir mein Profil an:',
            url: url
        }).catch(() => {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Profil-Link kopiert!');
        }).catch(() => {
            prompt('Kopiere diesen Link:', url);
        });
    } else {
        prompt('Kopiere diesen Link:', url);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const inputVorname = document.getElementById('input-vorname');
    const inputNachname = document.getElementById('input-nachname');
    const inputShortIntro = document.getElementById('input-shortintroduction');
    const inputInformations = document.getElementById('input-informations');

    const previewName = document.getElementById('preview-name');
    const previewShortIntro = document.getElementById('preview-shortintro');
    const previewDescription = document.getElementById('preview-description');

    function updateTextPreview() {
        const vorname = inputVorname.value.trim();
        const nachname = inputNachname.value.trim();
        const shortIntro = inputShortIntro.value.trim();
        const informations = inputInformations.value.trim();
        const fullName = (vorname + ' ' + nachname).trim();

        previewName.textContent = fullName !== '' ? fullName : 'Dein Name';
        previewShortIntro.textContent = shortIntro !== '' ? shortIntro : 'Deine Kurzbeschreibung';
        previewDescription.textContent = informations !== '' ? informations : 'Deine Beschreibung';
    }

    function bindSocialToggle(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        function update() {
            preview.style.display = input.value.trim() !== '' ? '' : 'none';
        }

        input.addEventListener('input', update);
        update();
    }

    [
        ['input-instagram', 'preview-instagram'],
        ['input-youtube', 'preview-youtube'],
        ['input-twitter', 'preview-twitter'],
        ['input-linkedin', 'preview-linkedin'],
        ['input-email', 'preview-email'],
        ['input-reddit', 'preview-reddit'],
        ['input-github', 'preview-github'],
        ['input-onlyfans', 'preview-onlyfans'],
        ['input-discord', 'preview-discord'],
        ['input-snapchat', 'preview-snapchat'],
        ['input-facebook', 'preview-facebook'],
        ['input-whatsapp', 'preview-whatsapp'],
        ['input-tiktok', 'preview-tiktok'],
        ['input-pinterest', 'preview-pinterest'],
        ['input-telegram', 'preview-telegram'],
        ['input-other', 'preview-other']
    ].forEach(([inputId, previewId]) => bindSocialToggle(inputId, previewId));

    inputVorname.addEventListener('input', updateTextPreview);
    inputNachname.addEventListener('input', updateTextPreview);
    inputShortIntro.addEventListener('input', updateTextPreview);
    inputInformations.addEventListener('input', updateTextPreview);
    updateTextPreview();
});

document.addEventListener('DOMContentLoaded', function () {
    const themeLink = document.getElementById('theme-stylesheet');
    const designInput = document.getElementById('design-input');
    const designOptions = document.querySelectorAll('.design-option');

    designOptions.forEach(option => {
        option.addEventListener('click', function () {
            const selectedDesign = this.dataset.design;
            if (!selectedDesign || !themeLink || !designInput) return;

            themeLink.href = '/styles/' + selectedDesign;
            designInput.value = selectedDesign;

            designOptions.forEach(box => box.classList.remove('design-selected'));
            this.classList.add('design-selected');
        });
    });
});