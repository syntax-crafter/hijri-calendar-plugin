<?php
/*
Plugin Name: Hijri Calendar Plugin
Description: A plugin to generate Hijri calendar with downloadable PDF.
Version: 1.5
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

// Register shortcode to display the calendar with navigation buttons
function hijri_calendar_shortcode() {
    // Get the start date from the plugin options
    $start_date = get_option('hijri_calendar_start_date', '2024-08-07'); // Default to a specific date

    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Enqueue necessary scripts and styles
    wp_enqueue_script('jquery'); // Load jQuery
    wp_enqueue_script('hijri-calendar-js', plugins_url('hijri-calendar.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style('hijri-calendar-css', plugins_url('styles.css', __FILE__));

    // Pass the start date, PDF generation URL, and Previous/Next Month URLs to JavaScript
    wp_localize_script('hijri-calendar-js', 'hijriCalendarData', array(
        'startDate' => $start_date,
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

