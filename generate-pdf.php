<?php
// Load WordPress functions if needed
require_once __DIR__ . '/../../../wp-load.php';
require_once __DIR__ . '/vendor/autoload.php'; // Composer's autoload for TCPDF

// Ensure no output has been sent
ob_start(); // Start output buffering

// Function to convert Gregorian date to Hijri
function gregorianToHijri($year, $month, $day)
{
    $jd = gregoriantojd($month, $day, $year);
    $l = $jd - 1948440 + 10632;
    $n = (int)(($l - 1) / 10631);
    $l = $l - 10631 * $n + 354;
    $j = ((int)((10985 - $l) / 5316)) * ((int)(50 * $l / 17719)) + ((int)($l / 5670)) * ((int)(43 * $l / 15238));
    $l = $l - ((int)((30 - $j) / 15)) * ((int)(17719 * $j / 50)) - ((int)($j / 16)) * ((int)(15238 * $j / 43)) + 29;
    $month = (int)(24 * $l / 709);
    $day = $l - (int)(709 * $month / 24);
    $year = 30 * $n + $j - 30;
    return array($year, $month, $day);
}

// Get the current month and year from the request (passed via JavaScript)
$month_year = isset($_GET['month_year']) ? $_GET['month_year'] : date('Y-m'); // Default to the current month if not provided

// Fetch the Hijri start date from the database for the provided month and year
global $wpdb;
$table_name = $wpdb->prefix . 'hijri_start_dates';
$start_date = $wpdb->get_var($wpdb->prepare("SELECT start_date FROM $table_name WHERE gregorian_month_year = %s", $month_year));

if (!$start_date) {
    // If no date is found, default to a specific date
    $start_date = '2024-08-07';
}
$start_date = new DateTime($start_date);

// Create a new PDF instance using TCPDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // Correct TCPDF class

// Disable header and footer to avoid page numbers
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(10, 20, 10); // left, top, right
$pdf->SetAutoPageBreak(true, 20);

// Add a page to the PDF
$pdf->AddPage();

// Fonts
$primary_font = 'helvetica';
$secondary_font = 'times'; // Example of using Times New Roman

// Title with Hijri and Gregorian month
$pdf->SetFont($primary_font, 'B', 20);
$pdf->SetTextColor(50, 50, 50);
$hijri_month = gregorianToHijri($start_date->format('Y'), $start_date->format('m'), 1)[1]; // Get Hijri month number
$gregorian_month = $start_date->format('F Y');
$pdf->Cell(0, 15, "Hijri Month $hijri_month - Gregorian Month $gregorian_month", 0, 1, 'C');
$pdf->Ln(10);

// Weekday headers
$pdf->SetFont($primary_font, 'B', 14);
$pdf->SetFillColor(140, 138, 93); // Dark background for weekdays
$pdf->SetTextColor(255, 255, 255); // White text
$weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
foreach ($weekdays as $weekday) {
    $pdf->Cell(40, 12, $weekday, 1, 0, 'C', 1); // 1 = fill
}
$pdf->Ln();

// Grid Background Color for Gregorian date text background
$gregorian_background_color = array(236, 228, 204); // #ece4cc

// Calendar Days
$pdf->SetFont($primary_font, '', 12);
$pdf->SetTextColor(0, 0, 0); // Reset text color to black
$pdf->SetDrawColor(200, 200, 200); // Thin light gray border for cells

// Find out the day of the week the month starts on
$start_day_of_week = $start_date->format('w'); // 0 = Sunday, 6 = Saturday

// Track the total number of days in the month
$total_days_in_month = 30; // Hijri months typically have 29 or 30 days

// Generate the calendar days (30 days for the Hijri calendar)
$current_day_of_week = 0; // Start at Sunday (0)

// Output empty cells before the first day
for ($empty_cells = 0; $empty_cells < $start_day_of_week; $empty_cells++) {
    $pdf->Cell(40, 20, '', 1, 0, 'C'); // Empty cell for non-existing dates
    $current_day_of_week++;
}

// Define cell dimensions for both dates (within one cell)
$cellWidth = 40; // The width of each cell
$cellHeight = 20; // The height of each cell (this will be split into two sections)

// Loop through the total number of days and output the Hijri and Gregorian dates in the same cell
for ($day = 1; $day <= $total_days_in_month; $day++) {
    // Get the Gregorian and Hijri dates for this cell
    $gregorian_date = $start_date->format('d');
    list($hijri_year, $hijri_month, $hijri_day) = gregorianToHijri($start_date->format('Y'), $start_date->format('m'), $start_date->format('d'));

    // Capture the current X and Y position for the start of the cell
    $currentX = $pdf->GetX();
    $currentY = $pdf->GetY();

    // First, print the Hijri date in the top part of the cell
    $pdf->SetFont($primary_font, 'B', 16); // Larger font for Hijri date
    $pdf->Cell($cellWidth, $cellHeight / 2, $hijri_day, 0, 0, 'C'); // Print Hijri date in top section

    // Move the cursor down for the Gregorian date within the same cell
    $pdf->SetXY($currentX, $currentY + ($cellHeight / 2)); // Move to bottom half of the same cell

    // Print the Gregorian date in the bottom part of the cell
    $pdf->SetFont($secondary_font, '', 10); // Smaller font for Gregorian date
    $pdf->Cell($cellWidth, $cellHeight / 2, $gregorian_date, 0, 0, 'C'); // Print Gregorian date in bottom section

    // After printing both, draw the border around the entire cell
    $pdf->SetXY($currentX, $currentY); // Reset position to draw the border
    $pdf->Cell($cellWidth, $cellHeight, '', 1, 0, 'C'); // Draw the border around the whole cell

    // Move to the next day by incrementing the Gregorian date
    $start_date->modify('+1 day');

    $current_day_of_week++;

    // Check if we reached the end of the week (Saturday), and move to the next row
    if ($current_day_of_week % 7 == 0) {
        $pdf->Ln(); // Move to the next line for the new week
        $current_day_of_week = 0; // Reset to Sunday
    }
}


// Footer Content (no page numbers, just branding)
$pdf->SetY(-30);
$pdf->SetFont($secondary_font, 'I', 10);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 10, 'Calendar Generated by ALL CEYLON JAMIYYATHUL ULAMA | www.acju.lk', 0, 1, 'C');

// Output the PDF as a download
ob_end_clean(); // Clear any buffer before sending the PDF
$pdf->Output('hijri_calendar.pdf', 'D');

exit;
