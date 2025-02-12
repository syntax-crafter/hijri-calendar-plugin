<?php
// Include WordPress functionality and database access
require_once('../../../wp-load.php');

// Get the current month and year from the AJAX request
$current_month_year = isset($_POST['current_month_year']) ? sanitize_text_field($_POST['current_month_year']) : '';
$current_start_date_for_month = isset($_POST['current_start_date']) ? sanitize_text_field($_POST['current_start_date']) : '';

// Connect to the database
global $wpdb;

// Table name
$table_name = $wpdb->prefix . 'hijri_start_dates';

// Check if there are multiple start dates for the current month
$start_dates_for_current_month = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table_name 
    WHERE gregorian_month_year = %s 
    ORDER BY start_date ASC", 
    $current_month_year
));

// Prepare variables to track navigation
$is_lowest_start_date = false;
$previous_month_data = null;

// If multiple start dates exist for the current month
if (count($start_dates_for_current_month) > 1) {
    // Find the current start date's position
    $current_start_date_index = -1;
    foreach ($start_dates_for_current_month as $index => $date_entry) {
        if ($date_entry->start_date === $current_start_date_for_month) {
            $current_start_date_index = $index;
            break;
        }
    }
    
    // If current start date is the first (lowest) in the month
    if ($current_start_date_index === 0) {
        // Move to previous month's latest start date
        $is_lowest_start_date = true;
    } elseif ($current_start_date_index > 0) {
        // Use the previous start date in the same month
        $previous_start_date_entry = $start_dates_for_current_month[$current_start_date_index - 1];
        
        echo json_encode([
            'success' => true,
            'gregorian_month_year' => $current_month_year,
            'start_date' => $previous_start_date_entry->start_date,
            'end_date' => $previous_start_date_entry->end_date,
            'is_first_start_date' => false
        ]);
        exit;
    }
} else {
    // Only one start date exists, so move to previous month
    $is_lowest_start_date = true;
}

// If we need to move to the previous month
if ($is_lowest_start_date) {
    // Get the previous month and year
    $previous_month_year = date('Y-m', strtotime('-1 month', strtotime($current_month_year . '-01')));

    // Check if this is the first month by finding the earliest entry in the database
    $first_month_data = $wpdb->get_var("
        SELECT MIN(gregorian_month_year) FROM $table_name
    ");

    // Check if this is the first month
    $is_first_month = ($current_month_year == $first_month_data);

    // Query for the previous month's latest start date
    $previous_month_data = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table_name 
        WHERE gregorian_month_year = %s 
        ORDER BY start_date DESC 
        LIMIT 1", 
        $previous_month_year
    ));

    // Check if previous month data exists
    if ($previous_month_data) {
        echo json_encode([
            'success' => true,
            'gregorian_month_year' => $previous_month_data->gregorian_month_year,
            'start_date' => $previous_month_data->start_date,
            'end_date' => $previous_month_data->end_date,
            'is_first_month' => $is_first_month
        ]);
    } else {
        // No previous month found
        echo json_encode([
            'success' => false,
            'message' => 'Calendar history ends here.',
            'is_first_month' => $is_first_month
        ]);
    }
}

exit;
