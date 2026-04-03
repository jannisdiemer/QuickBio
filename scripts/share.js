function shareProfile() {
    const url = window.location.href;

    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: "Schau dir dieses Profil an:",
            url: url
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert("Link kopiert!");
        }).catch(() => {
            prompt("Kopiere diesen Link:", url);
        });
    }
}