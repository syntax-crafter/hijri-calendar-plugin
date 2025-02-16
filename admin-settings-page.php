<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the media uploader script
function hijri_calendar_enqueue_media_uploader($hook)
{
    if ($hook !== 'toplevel_page_hijri-calendar') {
        return;
    }
    wp_enqueue_media();
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
    // Check if the current user has permission
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'hijri-calendar'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'hijri_start_dates';

    // Get entry for editing if in edit mode
    $edit_entry = null;
    if (isset($_GET['edit_hijri_calendar_entry'])) {
        $edit_id = intval($_GET['edit_hijri_calendar_entry']);
        $edit_entry = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $edit_id
        ));
    }

    // Handle form submission for updating Hijri start date
    if (isset($_POST['update_hijri_calendar'])) {
        $entry_id = intval($_POST['entry_id']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $media_id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;

        // Get the media URL
        $media_url = $media_id ? wp_get_attachment_url($media_id) : '';

        // Update the entry
        $wpdb->update(
            $table_name,
            [
                'end_date' => $end_date,
                'custom_url' => $media_url
            ],
            ['id' => $entry_id]
        );

        echo '<div class="updated"><p>Hijri Calendar entry updated successfully!</p></div>';
    }

    // Handle form submission for adding Hijri start date
    if (isset($_POST['save_hijri_calendar'])) {
        // Get form data
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $description = sanitize_textarea_field($_POST['description']);
        $media_id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;

        // Get the month and year from the start_date
        $start_month_year = date('Y-m', strtotime($start_date));

        // Check if there is already an entry for the same month
        $existing_entry = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE DATE_FORMAT(start_date, '%Y-%m') = %s",
                $start_month_year
            )
        );

        if ($existing_entry > 1) {
            echo '<div class="error"><p>Maximum of 2 already exists for the month ' . esc_html($start_month_year) . '.</p></div>';
        } else {
            // Get the media URL
            $media_url = $media_id ? wp_get_attachment_url($media_id) : '';

            // Insert the Hijri start date
            $wpdb->insert(
                $table_name,
                [
                    'gregorian_month_year' => $start_month_year,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'description' => $description,
                    'custom_url' => $media_url
                ]
            );
            // Get the current date in Colombo timezone
            $timezone = new DateTimeZone('Asia/Colombo');
            $current_date = new DateTime('now', $timezone);
            $current_date_str = $current_date->format('Y-m-d');

            // First, get the start_date for the current period
            $current_period = $wpdb->get_row($wpdb->prepare("
                SELECT start_date, end_date 
                FROM $table_name 
                WHERE start_date <= '%s' AND end_date >= '%s'
                ORDER BY start_date DESC 
                LIMIT 1
            ", $current_date_str));

            // First, get the start_date for the current period withot end date
            $current_period_winthout_end = $wpdb->get_row($wpdb->prepare("
                SELECT start_date, end_date 
                FROM $table_name 
                WHERE start_date <= '%s'
                ORDER BY start_date DESC 
                LIMIT 1
            ", $current_date_str));

            if ($current_period) {
                // Update the WordPress options with the current period's dates
                update_option('hijri_calendar_start_date', $current_period->start_date);
                update_option('hijri_calendar_end_date', $current_period->end_date);
            } else {
                if ($current_period_winthout_end) {
                    update_option('hijri_calendar_start_date', $current_period_winthout_end->start_date);
                    update_option('hijri_calendar_end_date', $current_period_winthout_end->end_date);
                } else {
                    // Get the latest start_date from the database after deletion
                    $latest_start_date = $wpdb->get_var(
                        "SELECT start_date FROM $table_name ORDER BY start_date DESC LIMIT 1"
                    );

                    $latest_end_date = $wpdb->get_var(
                        "SELECT end_date FROM $table_name WHERE start_date = $latest_start_date LIMIT 1"
                    );

                    update_option('hijri_calendar_start_date', $latest_start_date);
                    update_option('hijri_calendar_end_date', $latest_end_date);
                }
            }

            echo '<div class="updated"><p>Hijri Calendar entry saved successfully!</p></div>';
        }
    }

    // Handle deletion of Hijri calendar entry
    if (isset($_GET['delete_hijri_calendar_entry'])) {
        $delete_id = absint($_GET['delete_hijri_calendar_entry']);
        $wpdb->delete($table_name, ['id' => $delete_id]);

        // Get the latest start_date from the database after deletion
        $latest_start_date = $wpdb->get_var(
            "SELECT start_date FROM $table_name ORDER BY start_date DESC LIMIT 1"
        );

        $latest_end_date = $wpdb->get_var(
            "SELECT end_date FROM $table_name WHERE start_date = $latest_start_date LIMIT 1"
        );

        // Get the current date in Colombo timezone
        $timezone = new DateTimeZone('Asia/Colombo');
        $current_date = new DateTime('now', $timezone);
        $current_date_str = $current_date->format('Y-m-d');

        // First, get the start_date for the current period
        $current_period = $wpdb->get_row($wpdb->prepare("
            SELECT start_date, end_date 
            FROM $table_name 
            WHERE start_date <= '%s' AND end_date >= '%s'
            ORDER BY start_date DESC 
            LIMIT 1
        ", $current_date_str));

        // First, get the start_date for the current period withot end date
        $current_period_winthout_end = $wpdb->get_row($wpdb->prepare("
            SELECT start_date, end_date 
            FROM $table_name 
            WHERE start_date <= '%s'
            ORDER BY start_date DESC 
            LIMIT 1
        ", $current_date_str));

        if ($current_period) {
            // Update the WordPress options with the current period's dates
            update_option('hijri_calendar_start_date', $current_period->start_date);
            update_option('hijri_calendar_end_date', $current_period->end_date);
        } else {
            if ($current_period_winthout_end) {
                update_option('hijri_calendar_start_date', $current_period_winthout_end->start_date);
                update_option('hijri_calendar_end_date', $current_period_winthout_end->end_date);
            } else {
                // Get the latest start_date from the database after deletion
                $latest_start_date = $wpdb->get_var(
                    "SELECT start_date FROM $table_name ORDER BY start_date DESC LIMIT 1"
                );

                $latest_end_date = $wpdb->get_var(
                    "SELECT end_date FROM $table_name WHERE start_date = $latest_start_date LIMIT 1"
                );

                update_option('hijri_calendar_start_date', $latest_start_date);
                update_option('hijri_calendar_end_date', $latest_end_date);
            }
        }

        echo '<div class="updated"><p>Hijri Calendar entry deleted successfully!</p></div>';
    }

    // Fetch existing entries
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY start_date DESC");
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Hijri Calendar Settings</h1>

        <h2><?php echo $edit_entry ? 'Edit' : 'Add New'; ?> Hijri Start Date</h2>
        <form method="post" enctype="multipart/form-data" id="hijri-calendar-form">
            <?php if ($edit_entry): ?>
                <input type="hidden" name="entry_id" value="<?php echo esc_attr($edit_entry->id); ?>">
            <?php endif; ?>

            <table class="form-table">
                <tr>
                    <th><label for="start_date">Start Date (Gregorian)</label></th>
                    <td>
                        <input type="date" name="start_date" id="start_date" class="regular-text"
                            value="<?php echo $edit_entry ? esc_attr($edit_entry->start_date) : ''; ?>"
                            <?php echo $edit_entry ? 'disabled' : 'required'; ?>>
                    </td>
                </tr>
                <tr>
                    <th><label for="end_date">End Date (Gregorian)</label></th>
                    <td>
                        <input type="date" name="end_date" id="end_date" class="regular-text"
                            value="<?php echo $edit_entry ? esc_attr($edit_entry->end_date) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="description">Description</label></th>
                    <td>
                        <textarea name="description" id="description" class="regular-text"
                            <?php echo $edit_entry ? 'disabled' : 'required'; ?>><?php
                                                                                    echo $edit_entry ? esc_textarea($edit_entry->description) : '';
                                                                                    ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="media_name">Select Media (Image)</label></th>
                    <td>
                        <input type="text" name="media_name" id="media_name" class="regular-text" readonly>
                        <input type="hidden" name="media_id" id="media_id" class="regular-text" readonly>
                        <button type="button" class="button" id="select-media-button">Select Media</button>
                        <?php if ($edit_entry && $edit_entry->custom_url): ?>
                            <div class="current-image">
                                <img src="<?php echo esc_url($edit_entry->custom_url); ?>" width="100" alt="Current Image">
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <?php if ($edit_entry): ?>
                    <input type="submit" name="update_hijri_calendar" class="button-primary" value="Update Entry">
                    <a href="<?php echo admin_url('admin.php?page=hijri-calendar'); ?>" class="button button-cancel">Cancel</a>
                <?php else: ?>
                    <input type="submit" name="save_hijri_calendar" class="button-primary" value="Save Settings">
                <?php endif; ?>
            </p>
        </form>

        <h2>Current Hijri Calendar Entries</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry->start_date); ?></td>
                        <td><?php echo esc_html($entry->end_date); ?></td>
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

                            <a href="<?php echo esc_url(admin_url('admin.php?page=hijri-calendar&edit_hijri_calendar_entry=' . $entry->id)); ?>"
                                class="dashicons dashicons-edit" style="color: blue; font-size: 20px; text-decoration: none; margin-right: 10px;"></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
