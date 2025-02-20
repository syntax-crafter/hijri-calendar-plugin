jQuery(document).ready(function($) {
    var mediaUploader;
    // When the "Select Media" button is clicked
    $('#select-media-button').click(function(e) {
        e.preventDefault();
        // If the media uploader is already open, return
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        // Otherwise, create a new media uploader
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false // Allow only one file to be selected
        });
        // When a file is selected, set the URL and ID in the appropriate fields
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#media_id').val(attachment.id); // Set the attachment ID in the hidden input
            $('#media_name').val(attachment.filename); // Display the selected file name in the input box
        });
        // Open the media uploader
        mediaUploader.open();
    });

    // Check if in edit mode by looking for the edit parameter in URL
    var urlParams = new URLSearchParams(window.location.search);
    var isEditMode = urlParams.has('edit_hijri_calendar_entry');

    // If in edit mode, handle form fields
    if (isEditMode) {
        // Disable non-editable fields
        $('#start_date').prop('disabled', true);

        // Add cancel button functionality
        $('.button-cancel').on('click', function(e) {
            e.preventDefault();
            window.location.href = 'admin.php?page=hijri-calendar';
        });
    }

    // Check if the URL contains 'delete_hijri_calendar_entry' and remove it after 5 seconds
    if (window.location.href.indexOf("&delete_hijri_calendar_entry") > -1) {
        // Wait for 5 seconds before removing the query parameter
        setTimeout(function() {
            // Get the current URL
            var currentUrl = window.location.href;
            // Remove the 'delete_hijri_calendar_entry' query parameter
            var newUrl = currentUrl.replace(/([&?])delete_hijri_calendar_entry=\d+/, '');
            // Ensure we have a clean URL
            if (newUrl.indexOf('?') === -1) {
                newUrl = newUrl.replace('&', '?'); // Make sure we keep '?' if it's the first query parameter
            }
            // Replace the state in the history
            history.replaceState(null, null, newUrl);
        }, 5000); // 5000 milliseconds = 5 seconds
    }

    // Similar cleanup for edit mode success message
    if (window.location.href.indexOf("&updated=true") > -1) {
        setTimeout(function() {
            var currentUrl = window.location.href;
            var newUrl = currentUrl.replace(/([&?])updated=true/, '');
            if (newUrl.indexOf('?') === -1) {
                newUrl = newUrl.replace('&', '?');
            }
            history.replaceState(null, null, newUrl);
        }, 5000);
    }
});