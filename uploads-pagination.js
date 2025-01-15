jQuery(document).ready(function($) {
    var $mediaContainer = $('#upload_section_media_tiles_container');
    var $paginationContainer = $('#pagination-controls');

    // Function to load a given page of media
    function loadMediaPage(page) {
        $.ajax({
            url: hijriCalendarUploadsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'hijri_calendar_get_uploads_paged',
                page: page,
                // security: hijriCalendarUploadsData.security, // if using nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the tiles
                    $mediaContainer.html(response.data.html);
                    // Update pagination controls
                    $paginationContainer.html(response.data.pagination);
                } else {
                    $mediaContainer.html('<p>Error loading media.</p>');
                    $paginationContainer.empty();
                }
            },
            error: function() {
                $mediaContainer.html('<p>Error loading media.</p>');
                $paginationContainer.empty();
            }
        });
    }

    // When the page loads, load the first page
    loadMediaPage(1);

    // Delegate click event for pagination buttons
    $paginationContainer.on('click', '.page-button', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadMediaPage(page);
    });

    // -------------------------------------------------------
    // If you still want the "View" button to open the popup:
    // -------------------------------------------------------
    $mediaContainer.on('click', '.upload_section_view_btn', function() {
        var imageUrl = $(this).data('url');
        $('#upload_section_popup_image').attr('src', imageUrl);
        $('#upload_section_image_popup').show();
    });

    // Close popup
    $('#upload_section_close_popup').click(function() {
        $('#upload_section_image_popup').hide();
    });
});
