<?php
/*
Plugin Name: Hijri Calendar Plugin
Description: A comprehensive plugin to generate a Hijri calendar, allowing users to display uploaded media seamlessly. Perfect for Islamic organizations and individuals looking to integrate the Hijri calendar into their WordPress sites.
Version: 1.9
Author: Ushan Ikshana - HDR_LABS
Email: ikushan23261uni@gmail.com
License: GPL2

Changelog:
1.9 - Added Role Based Access
1.8 - removed the pdf generation and added client requested mobile view improvements.
1.7 - Added support for media downloads.
1.6 - Fixed bugs related to date calculations.
1.5 - ....
*/


// Ensure the Composer autoload file exists before including it
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    wp_die('Composer autoload file missing. Run `composer install` to generate it.');
}

// Create an admin settings page
require_once plugin_dir_path(__FILE__) . 'admin-settings-page.php';

// Activation Hook to create/update the table
register_activation_hook(__FILE__, 'hijri_calendar_create_table');

// Function to create the Hijri Calendar table if it doesn't already exist
function hijri_calendar_create_table()
{
    global $wpdb;

    // Define the table name with WordPress prefix
    $table_name = $wpdb->prefix . 'hijri_start_dates';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL for creating the table if it does not already exist
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        gregorian_month_year varchar(7) NOT NULL,
        start_date date NOT NULL,
        end_date date NULL,
        custom_url varchar(255) DEFAULT NULL,
        file_name varchar(255) DEFAULT NULL,
        file_size bigint(20) DEFAULT NULL,
        description text DEFAULT NULL,
        upload_date datetime DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    // Include the WordPress upgrade file for dbDelta function
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Checks if the current user has access to the Hijri Calendar plugin.
 *
 * @return bool True if the user is logged in and is an Administrator, Editor, or Author.
 */
function hijri_calendar_has_access() {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        // Allowed roles: administrator, editor, author.
        $allowed_roles = array('administrator', 'editor', 'author');

        // Check if the current user has any of the allowed roles.
        if ( array_intersect( $allowed_roles, $user->roles ) ) {
            return true;
        }
    }
    return false;
}

// Register shortcode to display the calendar with navigation buttons
function hijri_calendar_shortcode()
{
    // Fetch the start date from the plugin options
    $start_date = get_option('hijri_calendar_start_date', '2024-08-07'); // Default to a specific date if not set

    // Enqueue Font Awesome for icons
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Enqueue necessary scripts and styles
    wp_enqueue_script('jquery'); // Load jQuery
    wp_enqueue_script('hijri-calendar-js', plugins_url('hijri-calendar.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style('hijri-calendar-css', plugins_url('styles.css', __FILE__));

    // Pass the start date, PDF generation URL, and Previous/Next Month URLs to JavaScript
    wp_localize_script('hijri-calendar-js', 'hijriCalendarData', array(
        'startDate' => $start_date,  // Pass the start date from plugin options
        'pdfGenerationUrl' => plugins_url('generate-pdf.php', __FILE__), // URL to generate the PDF
        'previousMonthUrl' => plugins_url('fetch_previous_month.php', __FILE__), // URL to fetch previous month
        'nextMonthUrl' => plugins_url('fetch_next_month.php', __FILE__), // URL to fetch next month
    ));

    // Return the HTML container for the calendar and buttons
    return '
    <div id="calendar-container">
        <div id="calendar-header">
            <h1 id="hijri-month-name"></h1>
            <h2 id="gregorian-month-name"></h2>
        </div>
        <div id="calendar">
            <div id="weekdays" class="calendar-row"></div>
            <div id="days" class="calendar-row"></div>
        </div>
        <div class="button-container">
            <button class="previous-btn" id="previous-btn"><i class="fas fa-arrow-left"></i> Previous</button>
            <button class="next-btn" id="next-btn">Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>';
}
add_shortcode('hijri_calendar', 'hijri_calendar_shortcode');

// ----------------------------------------------------------
// SHORTCODE: Display uploaded media with Pagination
// ----------------------------------------------------------
function hijri_calendar_uploads_shortcode()
{
    // This will output a container for the media tiles
    // and a container for pagination controls.
    // The media tiles will be loaded via AJAX.

    // Basic container markup for tiles + pagination
    $html = '
    <div id="upload_section_media_tiles_container"></div> <!-- Filled by AJAX -->

    <!-- Pagination Controls -->
    <div id="pagination-controls" class="pagination-controls">
        <!-- Filled by AJAX: e.g., [Prev] 1 2 3 [Next] -->
    </div>

    <!-- Popup for viewing images -->
    <div id="upload_section_image_popup" class="upload_section_image_popup_overlay" style="display: none;">
        <div class="upload_section_popup_content">
            <span id="upload_section_close_popup" class="upload_section_close_popup">&times;</span>
            <img id="upload_section_popup_image" src="" alt="Image" class="upload_section_popup_image"/>
        </div>
    </div>
    ';

    return $html;
}
add_shortcode('hijri_calendar_uploads', 'hijri_calendar_uploads_shortcode');

// ----------------------------------------------------------
// AJAX HANDLER: Fetch paginated media
// ----------------------------------------------------------
function hijri_calendar_ajax_get_uploads_paged()
{
    // Security check: optional, but recommended if you use a nonce
    // check_ajax_referer('hijri_uploads_nonce', 'security');

    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';

    // Determine which page is requested
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    // Set how many items per page
    $items_per_page = 12;
    // Calculate offset
    $offset = ($page - 1) * $items_per_page;

    // Fetch total number of rows that have custom_url
    $total_items = $wpdb->get_var("
        SELECT COUNT(*) 
        FROM $table_name 
        WHERE custom_url IS NOT NULL
    ");

    // Calculate total pages
    $total_pages = ceil($total_items / $items_per_page);

    // Fetch the actual records for this page
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT * 
        FROM $table_name 
        WHERE custom_url IS NOT NULL 
        ORDER BY start_date DESC
        LIMIT %d OFFSET %d
    ", $items_per_page, $offset));

    // Build the HTML for the media tiles
    $media_tiles = '';
    if (!empty($results)) {
        foreach ($results as $row) {
            $media_tiles .= "
            <div class='upload_section_media_tile'>
                <div class='upload_section_tile_left'>
                    <h3 class='upload_section_media_title'>{$row->gregorian_month_year}</h3>
                    <p class='upload_section_media_description'>{$row->description}</p>
                </div>
                <div class='upload_section_tile_right'>
                    <button class='upload_section_view_btn' data-url='{$row->custom_url}'>View</button>
                    <a class='upload_section_download_btn' href='{$row->custom_url}' download='{$row->file_name}'>Download</a>
                </div>
            </div>";
        }
    } else {
        $media_tiles = '<p>No media found.</p>';
    }

    // Build pagination controls HTML
    // For simplicity, let's just do "Prev" / "Next" plus page numbers.
    $pagination_html = '';
    if ($total_pages > 1) {
        // Prev button
        if ($page > 1) {
            $pagination_html .= '<button class="page-button" data-page="' . ($page - 1) . '">Prev</button>';
        }

        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i === $page) ? 'active-page' : '';
            $pagination_html .= '<button class="page-button ' . $active_class . '" data-page="' . $i . '">' . $i . '</button>';
        }

        // Next button
        if ($page < $total_pages) {
            $pagination_html .= '<button class="page-button" data-page="' . ($page + 1) . '">Next</button>';
        }
    }

    // Return JSON response
    wp_send_json_success([
        'html'         => $media_tiles,
        'pagination'   => $pagination_html,
        'currentPage'  => $page,
        'totalPages'   => $total_pages,
    ]);
}
add_action('wp_ajax_hijri_calendar_get_uploads_paged', 'hijri_calendar_ajax_get_uploads_paged');
add_action('wp_ajax_nopriv_hijri_calendar_get_uploads_paged', 'hijri_calendar_ajax_get_uploads_paged');


// ----------------------------------------------------------
// ENQUEUE STYLES & SCRIPTS
// ----------------------------------------------------------

// Enqueue styles for uploads
function hijri_calendar_enqueue_uploads_styles()
{
    wp_enqueue_style('uploads-styles', plugins_url('uploads-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'hijri_calendar_enqueue_uploads_styles');

// Enqueue external JavaScript file for the popup modal (unchanged)
function hijri_calendar_enqueue_popup_modal_script()
{
    wp_enqueue_script('popup-modal', plugins_url('popup-modal.js', __FILE__), array(), null, true);
}
add_action('wp_enqueue_scripts', 'hijri_calendar_enqueue_popup_modal_script');

// Enqueue new JS file for handling pagination
function hijri_calendar_enqueue_uploads_pagination_script()
{
    wp_enqueue_script('uploads-pagination', plugins_url('uploads-pagination.js', __FILE__), array('jquery'), null, true);

    // Localize script to pass the AJAX URL
    wp_localize_script('uploads-pagination', 'hijriCalendarUploadsData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        // Optional: 'security' => wp_create_nonce('hijri_uploads_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'hijri_calendar_enqueue_uploads_pagination_script');
