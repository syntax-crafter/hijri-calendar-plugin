// Load jsPDF and jsPDF-AutoTable
<<<<<<< HEAD
document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"><\/script>');
document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"><\/script>');
=======
document.write(
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>'
);
document.write(
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>'
);
>>>>>>> 0d26edc (initial commit)

// Define weekdays
const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const arabicWeekdays = [
<<<<<<< HEAD
    "الأحد<br><span>(Al-Ahad)</span>",         // Sunday
    "الإثنين<br><span>(Al-Ithnayn)</span>",    // Monday
    "الثلاثاء<br><span>(Al-Thulatha)</span>",  // Tuesday
    "الأربعاء<br><span>(Al-Arbi‘a)</span>",    // Wednesday
    "الخميس<br><span>(Al-Khamis)</span>",      // Thursday
    "الجمعة<br><span>(Al-Jumu‘ah)</span>",     // Friday
    "السبت<br><span>(Al-Sabt)</span>"          // Saturday
];


function generateHijriCalendar(startGregorianDate) {
    const calendar = document.getElementById('calendar');
    const weekdaysRow = document.getElementById('weekdays');
    const daysRow = document.getElementById('days');

    // Check if #weekdays and #days elements exist, create them if not
    if (!weekdaysRow || !daysRow) {
        console.error('Error: Required calendar elements (#weekdays or #days) are missing.');
        return; // Stop execution if elements are missing
    }

    // Clear previous content
    weekdaysRow.innerHTML = '';
    daysRow.innerHTML = '';

    // Get today's date
    const today = new Date();
    const todayDay = today.getDate();
    const todayMonth = today.getMonth();
    const todayYear = today.getFullYear();

    // Get the initial Gregorian date
    const startDate = new Date(startGregorianDate);
    const hijriFormatter = new Intl.DateTimeFormat('en-US-u-ca-islamic', { year: 'numeric', month: 'long', day: 'numeric' });
    const gregorianFormatter = new Intl.DateTimeFormat('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const gregorianMonthFormatter = new Intl.DateTimeFormat('en-US', { month: 'short' });

    // Hijri and Gregorian month names
    const hijriMonthName = hijriFormatter.format(startDate);
    const gregorianMonthName = gregorianFormatter.format(startDate);

    // Set month names in the header
    document.getElementById('hijri-month-name').innerText = `Hijri Month: ${hijriMonthName}`;
    document.getElementById('gregorian-month-name').innerText = `Gregorian: ${gregorianMonthName}`;

    // Add weekdays headers with Arabic and English names
    weekdays.forEach((day, index) => {
        const weekdayElement = document.createElement('div');
        weekdayElement.classList.add('weekday');
        
        // Add separate blocks for English and Arabic names
        weekdayElement.innerHTML = `
            <div id="english-weekday">${day}</div>
            <div id="arabic-weekday">${arabicWeekdays[index]}</div>
        `;
        weekdaysRow.appendChild(weekdayElement);
    });
    
    // Get the weekday of the first day in the Gregorian month
    let startWeekday = startDate.getDay();

    // Add empty spaces for days before the first of the month
    for (let i = 0; i < startWeekday; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.classList.add('day');
        daysRow.appendChild(emptyDay);
    }

    // Generate 30 days for the Hijri month
    for (let day = 1; day <= 30; day++) {
        const currentDay = new Date(startDate);
        currentDay.setDate(startDate.getDate() + day - 1);

        // Get Hijri date for the current day
        const hijriDate = hijriFormatter.format(currentDay);
        const fullHijriDate = hijriFormatter.format(currentDay);

        // Gregorian date number and month
        const gregorianDateNumber = currentDay.getDate();
        const gregorianMonth = gregorianMonthFormatter.format(currentDay); // Get first 3 letters of the month

        // Create a day element and append Hijri and Gregorian dates
        const dayElement = document.createElement('div');
        dayElement.classList.add('day');

        // Check if the current day is today
        if (currentDay.getDate() === todayDay && currentDay.getMonth() === todayMonth && currentDay.getFullYear() === todayYear) {
            dayElement.setAttribute('id', 'today'); // Add a unique ID for today's date
        }

        // For desktop: Display month abbreviation and day number
        // For mobile: Only display day number (hide the month)
        dayElement.innerHTML = `
=======
  "الأحد<br><span>(Al-Ahad)</span>", // Sunday
  "الإثنين<br><span>(Al-Ithnayn)</span>", // Monday
  "الثلاثاء<br><span>(Al-Thulatha)</span>", // Tuesday
  "الأربعاء<br><span>(Al-Arbi‘a)</span>", // Wednesday
  "الخميس<br><span>(Al-Khamis)</span>", // Thursday
  "الجمعة<br><span>(Al-Jumu‘ah)</span>", // Friday
  "السبت<br><span>(Al-Sabt)</span>", // Saturday
];

function generateHijriCalendar(startGregorianDate) {
  const calendar = document.getElementById("calendar");
  const weekdaysRow = document.getElementById("weekdays");
  const daysRow = document.getElementById("days");

  // Check if #weekdays and #days elements exist, create them if not
  if (!weekdaysRow || !daysRow) {
    console.error(
      "Error: Required calendar elements (#weekdays or #days) are missing."
    );
    return; // Stop execution if elements are missing
  }

  // Clear previous content
  weekdaysRow.innerHTML = "";
  daysRow.innerHTML = "";

  // Get today's date
  const today = new Date();
  const todayDay = today.getDate();
  const todayMonth = today.getMonth();
  const todayYear = today.getFullYear();

  // Get the initial Gregorian date
  const startDate = new Date(startGregorianDate);
  const hijriFormatter = new Intl.DateTimeFormat("en-US-u-ca-islamic", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const gregorianFormatter = new Intl.DateTimeFormat("en-US", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const gregorianMonthFormatter = new Intl.DateTimeFormat("en-US", {
    month: "short",
  });

  // Hijri and Gregorian month names
  const hijriMonthName = hijriFormatter.format(startDate);
  const gregorianMonthName = gregorianFormatter.format(startDate);

  // Set month names in the header
  document.getElementById(
    "hijri-month-name"
  ).innerText = `Hijri Month: ${hijriMonthName}`;
  document.getElementById(
    "gregorian-month-name"
  ).innerText = `Gregorian: ${gregorianMonthName}`;

  // Add weekdays headers with Arabic and English names
  weekdays.forEach((day, index) => {
    const weekdayElement = document.createElement("div");
    weekdayElement.classList.add("weekday");

    // Add separate blocks for English and Arabic names
    weekdayElement.innerHTML = `
            <div id="english-weekday">${day}</div>
            <div id="arabic-weekday">${arabicWeekdays[index]}</div>
        `;
    weekdaysRow.appendChild(weekdayElement);
  });

  // Get the weekday of the first day in the Gregorian month
  let startWeekday = startDate.getDay();

  // Add empty spaces for days before the first of the month
  for (let i = 0; i < startWeekday; i++) {
    const emptyDay = document.createElement("div");
    emptyDay.classList.add("day");
    daysRow.appendChild(emptyDay);
  }

  // Generate 30 days for the Hijri month
  for (let day = 1; day <= 30; day++) {
    const currentDay = new Date(startDate);
    currentDay.setDate(startDate.getDate() + day - 1);

    // Get Hijri date for the current day
    const hijriDate = hijriFormatter.format(currentDay);
    const fullHijriDate = hijriFormatter.format(currentDay);

    // Gregorian date number and month
    const gregorianDateNumber = currentDay.getDate();
    const gregorianMonth = gregorianMonthFormatter.format(currentDay); // Get first 3 letters of the month

    // Create a day element and append Hijri and Gregorian dates
    const dayElement = document.createElement("div");
    dayElement.classList.add("day");

    // Check if the current day is today
    if (
      currentDay.getDate() === todayDay &&
      currentDay.getMonth() === todayMonth &&
      currentDay.getFullYear() === todayYear
    ) {
      dayElement.setAttribute("id", "today"); // Add a unique ID for today's date
    }

    // For desktop: Display month abbreviation and day number
    // For mobile: Only display day number (hide the month)
    dayElement.innerHTML = `
>>>>>>> 0d26edc (initial commit)
            <div class="hijri-date">${day}</div>
            <div class="gregorian-date">
                <span class="gregorian-month">${gregorianMonth}</span> ${gregorianDateNumber}
            </div>
            <div class="tooltip">
                <strong>Hijri:</strong> ${fullHijriDate}<br>
                <strong>Gregorian:</strong> ${gregorianMonth} ${gregorianDateNumber}
            </div>
        `;

<<<<<<< HEAD
        daysRow.appendChild(dayElement);
    }
}


// Function to create PDF with jsPDF AutoTable
function createCalendarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', format: 'a4' });

    // Title and Footer
    doc.setFontSize(22);
    doc.text('Hijri Calendar', 140, 20, { align: 'center' });
    doc.setFontSize(12);
    doc.text('Generated from ALL CEYLON JAMIYYATHUL ULAMA | www.acju.lk [ 2024 ]', 140, 190, { align: 'center' });

    // Table setup
    let data = [];
    let currentDate = new Date(hijriCalendarData.startDate);

    const hijriFormatter = new Intl.DateTimeFormat('en-US-u-ca-islamic', { year: 'numeric', month: 'long', day: 'numeric' });
    const gregorianFormatter = new Intl.DateTimeFormat('en-US', { day: 'numeric' });

    // Generate rows for the calendar (5 rows for weeks)
    for (let week = 0; week < 5; week++) {
        let row = [];
        for (let day = 0; day < 7; day++) {
            const hijriDate = hijriFormatter.format(currentDate);
            const gregorianDate = gregorianFormatter.format(currentDate);

            // Add combined Hijri and Gregorian date in one cell
            row.push(`${hijriDate}\n${gregorianDate}`);

            // Move to next day
            currentDate.setDate(currentDate.getDate() + 1);
        }
        data.push(row);
    }

    // Use autoTable to generate a properly formatted table
    doc.autoTable({
        head: [weekdays],
        body: data,
        startY: 30,
        theme: 'grid',
        styles: { fontSize: 10, cellPadding: 3 },
        columnStyles: { 0: { halign: 'center' }, 1: { halign: 'center' } },
    });

    // Save the generated PDF
    doc.save('hijri_calendar.pdf');
}

// Event Listener for Download Button
jQuery(document).ready(function($) {
    const startDate = hijriCalendarData.startDate || '2024-08-07'; // Fallback to default
    generateHijriCalendar(startDate); // Function to generate the calendar

    // Add event listener for download button
    $('#download-pdf').on('click', function() {
        // Redirect to the PHP file to generate the PDF
        window.location.href = hijriCalendarData.pdfGenerationUrl;
    });
});

// jQuery-based Calendar Interaction Logic
(function($) {
    $(document).ready(function() {
        let currentMonthYear = hijriCalendarData.startDate.substring(0, 7); // Initially showing the current month
        let originalMonthYear = currentMonthYear; // Save the original month (current month)
        let elementsExist = true; // Boolean flag to check if calendar elements exist

        // Function to load the previous month's calendar
        function loadPreviousMonth() {
            console.log("Previous button clicked. Current month:", currentMonthYear);

            $.ajax({
                url: hijriCalendarData.previousMonthUrl,
                type: 'POST',
                data: {
                    current_month_year: currentMonthYear
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Prevent reloading the same month
                        if (currentMonthYear !== response.gregorian_month_year) {
                            currentMonthYear = response.gregorian_month_year;
                            generateHijriCalendar(response.start_date);
                            $('#next-btn').show(); // Show Next button when moving back
                        }

                        // Hide the Previous button if it's the first month
                        if (response.is_first_month) {
                            $('#previous-btn').hide();
                        } else {
                            $('#previous-btn').show();
                        }
                    } else {
                        // No more previous data, hide the button
                        $('#previous-btn').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching previous month:', error);
                }
            });
        }

        // Function to load the next month's calendar
        function loadNextMonth() {
            console.log("Next button clicked. Current month:", currentMonthYear);

            $.ajax({
                url: hijriCalendarData.nextMonthUrl,
                type: 'POST',
                data: {
                    current_month_year: currentMonthYear
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Prevent reloading the same month
                        if (currentMonthYear !== response.gregorian_month_year) {
                            currentMonthYear = response.gregorian_month_year;
                            generateHijriCalendar(response.start_date);
                        }

                        // Hide the Next button if we're at the current month
                        if (currentMonthYear === originalMonthYear) {
                            $('#next-btn').hide();
                        } else {
                            $('#next-btn').show();
                            $('#previous-btn').show(); // Show Previous button when moving forward
                        }
                    } else {
                        console.error('Error loading next month:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching next month:', error);
                }
            });
        }

        // Event listener for Previous button
        $('#previous-btn').on('click', loadPreviousMonth);

        // Event listener for Next button
        $('#next-btn').on('click', loadNextMonth);

        // Event listener for Download button
        $('#download-pdf').on('click', function() {
            // Redirect to the PHP file to generate the PDF for the current month
            window.location.href = hijriCalendarData.pdfGenerationUrl + '?month_year=' + currentMonthYear;
        });

        // Initial calendar generation based on start date
        generateHijriCalendar(hijriCalendarData.startDate);

        // Ensure the Next button is hidden initially and the Previous button is visible
        $('#next-btn').hide(); // Initially hide the Next button (since it's the current month)
        $('#previous-btn').show(); // Show the Previous button at start
    });
=======
    daysRow.appendChild(dayElement);
  }
}

// jQuery-based Calendar Interaction Logic
(function ($) {
  $(document).ready(function () {
    let currentMonthYear = hijriCalendarData.startDate.substring(0, 7); // Initially showing the current month
    let originalMonthYear = currentMonthYear; // Save the original month (current month)
    let elementsExist = true; // Boolean flag to check if calendar elements exist

    // Function to load the previous month's calendar
    function loadPreviousMonth() {
      $.ajax({
        url: hijriCalendarData.previousMonthUrl,
        type: "POST",
        data: {
          current_month_year: currentMonthYear,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            // Prevent reloading the same month
            if (currentMonthYear !== response.gregorian_month_year) {
              currentMonthYear = response.gregorian_month_year;
              generateHijriCalendar(response.start_date);
              $("#next-btn").show(); // Show Next button when moving back
            }

            // Hide the Previous button if it's the first month
            if (response.is_first_month) {
              $("#previous-btn").hide();
            } else {
              $("#previous-btn").show();
            }
          } else {
            // No more previous data, hide the button
            $("#previous-btn").hide();
          }
        },
        error: function (xhr, status, error) {
          console.error("Error fetching previous month:", error);
        },
      });
    }

    // Function to load the next month's calendar
    function loadNextMonth() {
      $.ajax({
        url: hijriCalendarData.nextMonthUrl,
        type: "POST",
        data: {
          current_month_year: currentMonthYear,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            // Prevent reloading the same month
            if (currentMonthYear !== response.gregorian_month_year) {
              currentMonthYear = response.gregorian_month_year;
              generateHijriCalendar(response.start_date);
            }

            // Hide the Next button if we're at the current month
            if (currentMonthYear === originalMonthYear) {
              $("#next-btn").hide();
            } else {
              $("#next-btn").show();
              $("#previous-btn").show(); // Show Previous button when moving forward
            }
          } else {
            console.error("Error loading next month:", response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("Error fetching next month:", error);
        },
      });
    }

    // Event listener for Previous button
    $("#previous-btn").on("click", loadPreviousMonth);

    // Event listener for Next button
    $("#next-btn").on("click", loadNextMonth);

    // Event listener for Download button
    $("#download-pdf").on("click", function () {
      // Redirect to the PHP file to generate the PDF for the current month
      window.location.href =
        hijriCalendarData.pdfGenerationUrl + "?month_year=" + currentMonthYear;
    });

    // Initial calendar generation based on start date
    generateHijriCalendar(hijriCalendarData.startDate);

    // Ensure the Next button is hidden initially and the Previous button is visible
    $("#next-btn").hide(); // Initially hide the Next button (since it's the current month)
    $("#previous-btn").show(); // Show the Previous button at start
  });
>>>>>>> 0d26edc (initial commit)
})(jQuery);
