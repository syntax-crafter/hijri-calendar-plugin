<?php
/*
Plugin Name: Hijri Calendar Plugin
Description: A plugin to generate Hijri calendar with downloadable PDF and display uploaded media.
Version: 1.7
Author: Ushan Ikshana
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
function hijri_calendar_create_table() {
    global $wpdb;

    // Define the table name with WordPress prefix
    $table_name = $wpdb->prefix . 'hijri_start_dates';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL for creating the table if it does not already exist
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        gregorian_month_year varchar(7) NOT NULL,
        start_date date NOT NULL,
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

// Register shortcode to display the calendar with navigation buttons
function hijri_calendar_shortcode() {
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
            <button id="download-pdf" class="download-btn">Download</button>
        </div>
    </div>';
}
add_shortcode('hijri_calendar', 'hijri_calendar_shortcode');

function hijri_calendar_uploads_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';

    // Fetch uploaded media from the database
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE custom_url IS NOT NULL ORDER BY upload_date DESC");

    // Generate HTML for media tiles
    $media_tiles = '';
    foreach ($results as $row) {
        $media_tiles .= "
        <div class='upload_section_media_tile'>
            <div class='upload_section_tile_left'>
                <button class='upload_section_view_btn' data-url='{$row->custom_url}'>View</button>
                <a class='upload_section_download_btn' href='{$row->custom_url}' download='{$row->file_name}'>Download</a>
            </div>
            <div class='upload_section_tile_right'>
                <h3 class='upload_section_media_title'>{$row->gregorian_month_year}</h3>
                <p class='upload_section_media_description'>{$row->description}</p>
            </div>
        </div>";
    }

    // Return the media tiles with inline popup structure
    return "
    <div id='upload_section_media_tiles_container'>
        $media_tiles
    </div>
    <div id='upload_section_image_popup' class='upload_section_image_popup_overlay' style='display: none;'>
        <div class='upload_section_popup_content'>
            <span id='upload_section_close_popup' class='upload_section_close_popup'>&times;</span>
            <img id='upload_section_popup_image' src='' alt='Image' class='upload_section_popup_image'/>
        </div>
    </div>";
}
add_shortcode('hijri_calendar_uploads', 'hijri_calendar_uploads_shortcode');



// Enqueue styles for uploads
function hijri_calendar_enqueue_uploads_styles() {
    wp_enqueue_style('uploads-styles', plugins_url('uploads-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'hijri_calendar_enqueue_uploads_styles');

// Enqueue external JavaScript file for the popup modal
function hijri_calendar_enqueue_popup_modal_script() {
    // Assuming the JS file is in your plugin's 'js' folder (adjust path as necessary)
    wp_enqueue_script('popup-modal', plugins_url('popup-modal.js', __FILE__), array(), null, true);
}
add_action('wp_enqueue_scripts', 'hijri_calendar_enqueue_popup_modal_script');
