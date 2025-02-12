// Load jsPDF and jsPDF-AutoTable
document.write(
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>'
);
document.write(
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>'
);

// Define weekdays
const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const arabicWeekdays = [
  "الأحد<br><span>(Al-Ahad)</span>", // Sunday
  "الإثنين<br><span>(Al-Ithnayn)</span>", // Monday
  "الثلاثاء<br><span>(Al-Thulatha)</span>", // Tuesday
  "الأربعاء<br><span>(Al-Arbi‘a)</span>", // Wednesday
  "الخميس<br><span>(Al-Khamis)</span>", // Thursday
  "الجمعة<br><span>(Al-Jumu‘ah)</span>", // Friday
  "السبت<br><span>(Al-Sabt)</span>", // Saturday
];

function generateHijriCalendar(startGregorianDate, endGregorianDate) {
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
  const endDate = new Date(endGregorianDate);
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

  let timeDifference;
  let daysToGenerate;
  if (!isNaN(endDate)) {
    timeDifference = endDate.getTime() - startDate.getTime();
    daysToGenerate =
      Math.ceil(Math.abs(timeDifference / (1000 * 60 * 60 * 24))) + 1;
  } else {
    timeDifference = 0;
    daysToGenerate = 30;
  }

  if (daysToGenerate == 29 || daysToGenerate == 30) {
    // Generate 30 days for the Hijri month
    for (let day = 1; day <= daysToGenerate; day++) {
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
            <div class="hijri-date">${day}</div>
            <div class="gregorian-date">
                <span class="gregorian-month">${gregorianMonth}</span> ${gregorianDateNumber}
            </div>
            <div class="tooltip">
                <strong>Hijri:</strong> ${fullHijriDate}<br>
                <strong>Gregorian:</strong> ${gregorianMonth} ${gregorianDateNumber}
            </div>
        `;

      daysRow.appendChild(dayElement);
    }
  } else {
    for (let day = 1; day <= 30; day++) {
      // Create a day element and append Hijri and Gregorian dates
      const dayElement = document.createElement("div");
      dayElement.classList.add("day");

      // For desktop: Display month abbreviation and day number
      // For mobile: Only display day number (hide the month)
      dayElement.innerHTML = `
            <div class="hijri-date">Invalid</div>
            <div class="gregorian-date">
                <span class="gregorian-month">  ---  </span> ---
            </div>
            <div class="tooltip">
                <strong>Hijri:</strong> --- <br>
                <strong>Gregorian:</strong> ---- ----
            </div>
        `;

      daysRow.appendChild(dayElement);
    }
  }
}

// jQuery-based Calendar Interaction Logic
(function ($) {
  $(document).ready(function () {
    let currentMonthYear = hijriCalendarData.startDate.substring(0, 7); // Initially showing the current month
    let currentStartDate = hijriCalendarData.startDate;
    const originalMonthYear = currentMonthYear; // Save the original month (current month)
    const latestStartDate = currentStartDate;
    let elementsExist = true; // Boolean flag to check if calendar elements exist

    // Function to load the previous month's calendar
    function loadPreviousMonth() {
      $.ajax({
        url: hijriCalendarData.previousMonthUrl,
        type: "POST",
        data: {
          current_month_year: currentMonthYear,
          current_start_date: currentStartDate,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            // Update current month year and start date
            currentMonthYear = response.gregorian_month_year;
            currentStartDate = response.start_date;

            // Generate calendar with new month's data
            generateHijriCalendar(response.start_date, response.end_date);

            // Show Next button when moving back
            $("#next-btn").show();

            // Handle first month scenario
            if (response.is_first_month) {
              $("#previous-btn").hide();
            } else {
              $("#previous-btn").show();
            }
          } else {
            // No more previous data, hide the previous button
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
          current_start_date: currentStartDate,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            // Update current month year and start date
            currentMonthYear = response.gregorian_month_year;
            currentStartDate = response.start_date;

            // Generate calendar with new month's data
            generateHijriCalendar(response.start_date, response.end_date);

            // Handle navigation button visibility
            if (currentStartDate === latestStartDate) {
              // If back to the original month, hide Next button
              $("#next-btn").hide();
            } else {
              $("#next-btn").show();
            }

            // Always show Previous button when moving forward
            $("#previous-btn").show();
          } else {
            // No more next data
            $("#next-btn").hide();
          }
        },
        error: function (xhr, status, error) {
          console.error("Error fetching next month:", error);
        },
      });
    }

    // Event listener for Previous button
    $("#previous-btn").on("click", function() {
      loadPreviousMonth();
    });

    // Event listener for Next button
    $("#next-btn").on("click", function() {
      loadNextMonth();
    });

    // Initial calendar generation based on start date
    generateHijriCalendar(hijriCalendarData.startDate,hijriCalendarData.endDate);

    // Initial button state management
    $("#next-btn").hide(); // Initially hide the Next button (since it's the current month)
    $("#previous-btn").show(); // Show the Previous button at start
  });
})(jQuery);