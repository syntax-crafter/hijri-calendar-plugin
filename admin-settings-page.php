<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the media uploader script
function hijri_calendar_enqueue_media_uploader($hook)
{
    wp_enqueue_media();
    if ($hook !== 'toplevel_page_hijri-calendar') {
        return;
    }
    wp_enqueue_script('hijri-calendar-admin-js', plugin_dir_url(__FILE__) . 'hijri-calendar-admin-js.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'hijri_calendar_enqueue_media_uploader');

// Create Admin Menu for Hijri Calendar
function hijri_calendar_admin_menu()
{
    add_menu_page(
        'Hijri Calendar',            // Page title
        'Hijri Calendar',            // Menu title
        'edit_posts',            // Capability required to view
        'hijri-calendar',            // Slug for the menu page
        'hijri_calendar_admin_page', // Callback function to render the page
        'dashicons-calendar',        // Icon for the menu
        20                           // Position in the menu
    );
}
add_action('admin_menu', 'hijri_calendar_admin_menu');

// Display the Admin Page for Hijri Calendar
function hijri_calendar_admin_page()
{
    // Check if the current user has one of the allowed roles.
    if (! current_user_can('edit_posts')) { // Or use a custom check if needed.
        wp_die(__('You do not have sufficient permissions to access this page.', 'hijri-calendar'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';

    // Handle form submission for adding Hijri start date
    if (isset($_POST['save_hijri_calendar'])) {
        // Get form data
        $start_date = sanitize_text_field($_POST['start_date']);
        $description = sanitize_textarea_field($_POST['description']);
        $media_id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;

        // Get the month and year from the start_date
        $start_month_year = date('Y-m', strtotime($start_date)); // 'Y-m' to store Year-Month format

        // Check if there is already an entry for the same month
        $existing_entry = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE DATE_FORMAT(start_date, '%Y-%m') = %s",
                $start_month_year
            )
        );

        if ($existing_entry > 0) {
            // Show a message if the entry for the month already exists
            echo '<div class="error"><p>An entry already exists for the month ' . esc_html($start_month_year) . '.</p></div>';
        } else {
            // Get the media URL
            $media_url = $media_id ? wp_get_attachment_url($media_id) : '';

            // Insert the Hijri start date
            $wpdb->insert(
                $table_name,
                [
                    'gregorian_month_year' => $start_month_year,
                    'start_date' => $start_date,
                    'description' => $description,
                    'custom_url' => $media_url
                ]
            );

            // Get the latest start_date from the database after the insert
            $latest_start_date = $wpdb->get_var(
                "SELECT start_date FROM $table_name ORDER BY start_date DESC LIMIT 1"
            );

            // Set the latest start date as the option value
            update_option('hijri_calendar_start_date', $latest_start_date);

            echo '<div class="updated"><p>Hijri Calendar entry saved successfully!</p></div>';
        }
    }

    // Handle deletion of Hijri calendar entry
    if (isset($_GET['delete_hijri_calendar_entry'])) {
        $delete_id = absint($_GET['delete_hijri_calendar_entry']);
        // Delete the entry from the database
        $wpdb->delete($table_name, ['id' => $delete_id]);
        // Get the latest start_date from the database after the insert
        $latest_start_date = $wpdb->get_var(
            "SELECT start_date FROM $table_name ORDER BY start_date DESC LIMIT 1"
        );

        // Set the latest start date as the option value
        update_option('hijri_calendar_start_date', $latest_start_date);
        echo '<div class="updated"><p>Hijri Calendar entry deleted successfully!</p></div>';
    }

    // Fetch existing Hijri calendar entries, order by start date descending
    $entries = $wpdb->get_results(
        "SELECT * FROM $table_name ORDER BY start_date DESC"
    );

?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Hijri Calendar Settings</h1>

        <h2>Add New Hijri Start Date</h2>
        <form method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th><label for="start_date">Start Date (Gregorian)</label></th>
                    <td><input type="date" name="start_date" id="start_date" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td><textarea name="description" id="description" class="regular-text" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="media_name">Select Media (Image)</label></th>
                    <td>
                        <input type="text" name="media_name" id="media_name" class="regular-text" readonly>
                        <input type="hidden" name="media_id" id="media_id" class="regular-text" readonly>
                        <button type="button" class="button" id="select-media-button">Select Media</button>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="save_hijri_calendar" class="button-primary" value="Save Settings">
            </p>
        </form>

        <h2>Current Hijri Calendar Entries</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry->start_date); ?></td>
                        <td><?php echo esc_html($entry->description); ?></td>
                        <td>
                            <?php if ($entry->custom_url): ?>
                                <img src="<?php echo esc_url($entry->custom_url); ?>" width="100" alt="Image">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=hijri-calendar&delete_hijri_calendar_entry=' . $entry->id)); ?>"
                                onclick="return confirm('Are you sure you want to delete this entry?');"
                                class="dashicons dashicons-trash" style="color: red; font-size: 20px; text-decoration: none;"></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
