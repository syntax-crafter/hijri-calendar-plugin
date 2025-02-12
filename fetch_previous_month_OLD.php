<?php
// Include WordPress functionality and database access
require_once('../../../wp-load.php');

// Get the current month and year from the AJAX request
$current_month_year = isset($_POST['current_month_year']) ? sanitize_text_field($_POST['current_month_year']) : '';
$current_start_date_for_month = isset($_POST['current_start_date']) ? sanitize_text_field($_POST['current_start_date']) : '';

// Connect to the database
global $wpdb;

// Get the previous month and year
$previous_month_year = date('Y-m', strtotime('-1 month', strtotime($current_month_year . '-01')));

// Query the database for the previous month's start date
$table_name = $wpdb->prefix . 'hijri_start_dates';
$previous_month_data = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM $table_name WHERE gregorian_month_year = %s", 
    $previous_month_year
));

// Check if this is the first month by finding the earliest entry in the database
$first_month_data = $wpdb->get_var("
    SELECT MIN(gregorian_month_year) FROM $table_name
");

// Compare the current month with the first available month in the database
$is_first_month = ($current_month_year == $first_month_data);

// Check if the previous month exists in the database
if ($previous_month_data) {
    // Return the previous month's data along with the first month check
    echo json_encode([
        'success' => true,
        'gregorian_month_year' => $previous_month_data->gregorian_month_year,
        'start_date' => $previous_month_data->start_date,
        'end_date' => $previous_month_data->end_date,
        'is_first_month' => $is_first_month // Add the is_first_month flag
    ]);
} else {
    // No previous month found
    echo json_encode([
        'success' => false,
        'message' => 'Calendar history ends here.',
        'is_first_month' => $is_first_month // Add the is_first_month flag here as well
    ]);
}

exit;
