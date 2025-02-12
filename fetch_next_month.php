<?php
// Include WordPress functionality and database access
require_once('../../../wp-load.php');

// Get the current month and year from the AJAX request
$current_month_year = isset($_POST['current_month_year']) ? sanitize_text_field($_POST['current_month_year']) : '';
$current_start_date_for_month = isset($_POST['current_start_date']) ? sanitize_text_field($_POST['current_start_date']) : '';

if (!$current_month_year) {
    echo json_encode(['success' => false, 'message' => 'Invalid month-year data.']);
    exit;
}

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
$is_highest_start_date = false;
$next_month_data = null;

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
    
    // If current start date is the last (highest) in the month
    if ($current_start_date_index === count($start_dates_for_current_month) - 1) {
        // Move to next month's earliest start date
        $is_highest_start_date = true;
    } elseif ($current_start_date_index >= 0 && $current_start_date_index < count($start_dates_for_current_month) - 1) {
        // Use the next start date in the same month
        $next_start_date_entry = $start_dates_for_current_month[$current_start_date_index + 1];
        
        echo json_encode([
            'success' => true,
            'gregorian_month_year' => $current_month_year,
            'start_date' => $next_start_date_entry->start_date,
            'end_date' => $next_start_date_entry->end_date,
            'is_last_start_date' => false
        ]);
        exit;
    }
} else {
    // Only one start date exists, so move to next month
    $is_highest_start_date = true;
}

// If we need to move to the next month
if ($is_highest_start_date) {
    // Get the next month and year
    $next_month_year = date('Y-m', strtotime('+1 month', strtotime($current_month_year . '-01')));

    // Query for the next month's earliest start date
    $next_month_data = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table_name 
        WHERE gregorian_month_year = %s 
        ORDER BY start_date ASC 
        LIMIT 1", 
        $next_month_year
    ));

    // Check if next month data exists
    if ($next_month_data) {
        echo json_encode([
            'success' => true,
            'gregorian_month_year' => $next_month_data->gregorian_month_year,
            'start_date' => $next_month_data->start_date,
            'end_date' => $next_month_data->end_date,
            'is_last_start_date' => true
        ]);
    } else {
        // No next month found
        echo json_encode([
            'success' => false,
            'message' => 'No further calendar available.'
        ]);
    }
}

exit;
?>
