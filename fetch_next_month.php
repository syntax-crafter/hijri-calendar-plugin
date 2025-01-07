<?php
// Include WordPress functionality and database access
require_once('../../../wp-load.php');

// Get the current month and year from the AJAX request
$current_month_year = isset($_POST['current_month_year']) ? sanitize_text_field($_POST['current_month_year']) : '';

if (!$current_month_year) {
    echo json_encode(['success' => false, 'message' => 'Invalid month-year data.']);
    exit;
}

// Connect to the database
global $wpdb;

// Get the next month and year
$next_month_year = date('Y-m', strtotime('+1 month', strtotime($current_month_year . '-01')));

// Query the database for the next month's start date
$table_name = $wpdb->prefix . 'hijri_start_dates';
$next_month_data = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM $table_name WHERE gregorian_month_year = %s", 
    $next_month_year
));

// Check if the next month exists in the database
if ($next_month_data) {
    // Return the next month's data as JSON
    echo json_encode([
        'success' => true,
        'gregorian_month_year' => $next_month_data->gregorian_month_year,
        'start_date' => $next_month_data->start_date,
    ]);
} else {
    // No next month found
    echo json_encode([
        'success' => false,
        'message' => 'No further calendar available.'
    ]);
}

exit;
?>
