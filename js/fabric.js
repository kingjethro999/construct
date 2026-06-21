function saveDesign() {
    const designData = JSON.stringify(canvas.toJSON());

    fetch('save_design.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'design=' + encodeURIComponent(designData)
    })
    .then(res => res.text())
    .then(data => alert(data));
}