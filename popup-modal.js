document.addEventListener("DOMContentLoaded", function () {
    // Add event listeners to all "View" buttons
    document.querySelectorAll('.upload_section_view_btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const imageUrl = this.getAttribute('data-url'); // Get the URL from the button
            const popupImage = document.getElementById('upload_section_popup_image');
            const popupOverlay = document.getElementById('upload_section_image_popup');

            // Set the image source and show the popup
            popupImage.src = imageUrl;
            popupOverlay.style.display = 'flex'; // Ensure it's set to 'flex'
        });
    });

    // Close popup when the close button is clicked
    document.getElementById('upload_section_close_popup').addEventListener('click', function () {
        document.getElementById('upload_section_image_popup').style.display = 'none';
    });

    // Close popup when clicking outside the image
    document.getElementById('upload_section_image_popup').addEventListener('click', function (event) {
        if (event.target === this) {
            this.style.display = 'none';
        }
    });
});
