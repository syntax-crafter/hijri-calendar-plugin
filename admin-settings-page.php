<?php
// Add settings page to the admin menu
function hijri_calendar_add_admin_menu() {
    add_menu_page('Hijri Calendar Settings', 'Hijri Calendar', 'manage_options', 'hijri-calendar', 'hijri_calendar_options_page');
}
add_action('admin_menu', 'hijri_calendar_add_admin_menu');

// Register the plugin settings
function hijri_calendar_settings_init() {
    register_setting('hijriCalendarSettings', 'hijri_calendar_start_date');

    add_settings_section(
        'hijri_calendar_settings_section',
        __('Hijri Calendar Settings', 'hijri_calendar'),
        null,
        'hijriCalendarSettings'
    );

    add_settings_field(
        'hijri_calendar_start_date_field',
        __('Start Date of Hijri Month (Gregorian)', 'hijri_calendar'),
        'hijri_calendar_start_date_render',
        'hijriCalendarSettings',
        'hijri_calendar_settings_section'
    );

    add_settings_field(
        'hijri_calendar_image_field',
        __('Upload Image for the Month', 'hijri_calendar'),
        'hijri_calendar_image_render',
        'hijriCalendarSettings',
        'hijri_calendar_settings_section'
    );
}
add_action('admin_init', 'hijri_calendar_settings_init');

// Render the input field for the start date
function hijri_calendar_start_date_render() {
    $start_date = get_option('hijri_calendar_start_date', '2024-08-07');
    ?>
    <input type="date" name="hijri_calendar_start_date" value="<?php echo esc_attr($start_date); ?>">
    <?php
}

// Render the file upload field
function hijri_calendar_image_render() {
    ?>
    <input type="file" name="hijri_calendar_image" accept="image/*">
    <?php
}

// Render the options page
function hijri_calendar_options_page() {
    ?>
    <form action="options.php" method="post" enctype="multipart/form-data">
        <h1>Hijri Calendar Settings</h1>
        <?php
        settings_fields('hijriCalendarSettings');
        do_settings_sections('hijriCalendarSettings');
        submit_button(__('Save Settings', 'hijri_calendar'));
        ?>
    </form>
    <?php
}

// // Database Table Creation (if not exists) for Hijri Start Dates
// function hijri_calendar_create_table() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'hijri_start_dates';
//     $charset_collate = $wpdb->get_charset_collate();

//     $sql = "CREATE TABLE IF NOT EXISTS $table_name (
//         id bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//         gregorian_month_year varchar(7) NOT NULL,
//         start_date date NOT NULL,
//         image_path varchar(255) DEFAULT NULL
//     ) $charset_collate;";

//     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//     dbDelta($sql);

//     // Log any errors
//     if ($wpdb->last_error) {
//         error_log('Table creation error: ' . $wpdb->last_error);
//     } else {
//         error_log('Table creation successful.');
//     }
// }

// register_activation_hook(__FILE__, 'hijri_calendar_create_table');

// Validate and Save Hijri Start Date and Image Path to the Database
function hijri_calendar_validate_and_save_start_date($start_date) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';

    // Extract the Gregorian month and year from the start date
    $month_year = date('Y-m', strtotime($start_date));

    // Check if an entry already exists for this month and year
    $existing_record = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE gregorian_month_year = %s", 
        $month_year
    ));

    // Handle file upload
    if (isset($_FILES['hijri_calendar_image']) && $_FILES['hijri_calendar_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = wp_upload_dir();
        $target_dir = $upload_dir['path'] . '/';
        $file_name = basename($_FILES['hijri_calendar_image']['name']);
        $target_file = $target_dir . $file_name;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['hijri_calendar _image']['tmp_name'], $target_file)) {
            // If an entry exists, update it; otherwise, insert a new one
            if ($existing_record) {
                $wpdb->update(
                    $table_name,
                    array(
                        'start_date' => $start_date, // Update the start date
                        'image_path' => $target_file   // Update the image path
                    ),
                    array('id' => $existing_record), // Condition: matching ID
                    array('%s', '%s'),                // Data types: date and string
                    array('%d')                       // ID data type
                );
            } else {
                $wpdb->insert(
                    $table_name,
                    array(
                        'gregorian_month_year' => $month_year, // Month-Year (e.g., 2024-08)
                        'start_date' => $start_date,           // Start date (Hijri)
                        'image_path' => $target_file            // Image path
                    ),
                    array('%s', '%s', '%s') // Data types: string (for month-year), date, and string (for image path)
                );
            }
        } else {
            // Handle file upload error
            error_log('File upload error: ' . $_FILES['hijri_calendar_image']['error']);
        }
    }
}

// Hook to save the start date and image path after form submission with validation
function hijri_calendar_after_submission() {
    if (isset($_POST['hijri_calendar_start_date'])) {
        $start_date = sanitize_text_field($_POST['hijri_calendar_start_date']);

        // Validate the date (ensure it's a valid Gregorian date)
        if (strtotime($start_date)) {
            // Call the validation method to replace or insert the start date and image path
            hijri_calendar_validate_and_save_start_date($start_date);
        } else {
            // Handle invalid date (if necessary)
            echo "Invalid date format.";
        }
    }
}
add_action('update_option_hijri_calendar_start_date', 'hijri_calendar_after_submission', 10, 2);

// Add a function to fetch the saved Hijri start date and image path for a given month
function get_hijri_start_date_and_image($month_year) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';
    
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT start_date, image_path FROM $table_name WHERE gregorian_month_year = %s", 
        $month_year
    ));

    return $result ? $result : null; // Return the date and image path if found, or null if not
}
?>