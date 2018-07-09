# Calendar
This library generates a simple Bootsrap 3 styled table layout for a calendar year. 
You can draw an individual month, or an entire year.
This project originated from a holiday/leave system to display which days in a calendar had been booked off.

## Features
- Draw an entire year or an individual month
- Highlight days
- Highlight public holidays
- Add Bootstrap 3 style tooltip to any highlighted day

## Installation
### Composer
Install
````bash
composer require northgoingzax/calendar
````
Wherever you need to use it
````php
use northgoingzax\calendar;
````
### Non-composer
Download the files [github.com/northgoingzax/calendar]()
````php
require_once 'src/Calendar.php';
use northgoingzax\calendar;
````

## CSS & JS
To make this work you will need Bootstrap 3, this has been tested with the last release of 3.3.7
If you want to use the tooltip/label elements you will need to include **jquery.js** and **bootstrap.js** using your preferred installation process.

For bower, use:
````bash
bower install bootstrap@3.3.7
bower install jquery
````

**Tooltip**

To use tooltips you need to call it from your script, as per Bootstrap documentation.
Because the calendar is a bunch of tables, you need to tell the tooltip to render in the body, not the current div.
````html
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
        })
      });
</script>
````

**calendar.css**
You will also need to include the **css/calendar.css** style sheet.

**Complete example**
````html
<html>
<head>
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.css">
    <script type="text/javascript" src="bower_components/jquery/dist/jquery.js"></script>
    <script type="text/javascript" src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/calendar.css">
</head>
<body>
	<div class="container-fluid">
    	<?= $cal->drawYear() ?>
    </div>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body'
            })
          });
    </script>
</body>
</html>
    
````

## Usage
**Example 1: Simple array of dates**
To create a default calendar for the current year, and highlight 3 days in June.
````php
$cal = new Calendar();
$cal->addDays([
    '2018-06-01',
    '2018-06-02',
    '2018-06-03',
]);
echo $cal->drawYear();
````
This will highlight the first 3 days in June.
To add a label to these 3 days, use the label parameter.
````php
$cal->addDays([
    '2018-06-01',
    '2018-06-02',
    '2018-06-03'
], 'Message text here');
````
This will apply the same message to all 3 dates.
To add a different label for each date, you need to use the **addDay()** method instead
````php
$cal->addDay('2018-06-01','Reason for 1st June');
$cal->addDay('2018-06-02','Reason for 2nd June');
$cal->addDay('2018-06-03','Reason for 3rd June');
````

The **addDays()** method just wraps the **addDay()** method in a loop anyway.

**Example 2: Draw an individual month**
At any time you can draw a single month from the year. Use the **drawMonth(int $month)** method where 1 = January, 2 = February, 12 = December

````php
// Only render month of June
echo $cal->drawMonth(6);
````

**Example 3: Adding mulitple dates after database query**
Assume you have a data source from your database

|id | date | reason |
|---|------|--------|
|1| 2018-02-03 | Holiday in South of France |
|2| 2018-03-04 | BBQ |


After a simple SQL query you have your data in an array
````php
$result = array(
	0 => array(
    	'date' => '2018-02-03',
        'reason' => 'Holiday in South of France',
    ),
    1 => array(
    	'date' => '2018-03-04',
        'reason' => 'BBQ',
    )
)
````
You would then cycle through your result set, adding the dates to the calendar:
````php
foreach ($result as $r) {
	$cal->addDay($r['date'], $r['reason']);
}
````


**Example 4: Date Ranges **
Depending on how your data is structured, you database might be stored in ranges rather than individual dates.

| id | start_date | end_date | reason |
|----|------------|----------|--------|
| 1  | 2018-02-04 | 2018-02-14| Week in Portugal | 

To add this date range, use the **addDateRange($start,$end,$label)** method
````php
$cal->addDateRange('2018-02-04', '2018-02-14', 'Week in Portugal')
````

**Example 5: Public Holidays **
This follows the same principal as normal date ranges, but with different method names:

**addBankHoliday(string $day, string $label)**
**addBankHolidays(array $days, string $label)**
````php
$cal->addBankHoliday('2018-05-01','May Day');

$cal->addBankHolidays(['2018-12-25','2018-12-26'], 'Festive');
````

**Example 6: Other dates **
If you have any other category you want to highlight, you can use **addOtherDates**

````php
$cal->addOtherDate('2018-07-03','Work summer party');

$cal->addOtherDates(['2018-07-04','2018-07-05','2018-07-06'], 'Sick');
````

## Customisation
You can override most default functions after creating the object by modifying the public params.

**Year**
You can create calendars for any year by passing the year into the constructor.
````php
$cal = new Calendar(2015);
````
This is really important. even though you are passing dates in with each method, because this is used to work out the days of the week for the calendar layouts. If you don't provide a specific year it will default to the current year.

**Weekends**
When providing dates to highlight you can force the weekends on or off. 
This might be used if you are using it for a holiday system but you input a date range that includes weekends.

````php
$cal = new Calendar(); 
$cal->isWeekends = true; // default 
$cal->isWeekends = false; // turn off weekend highlighting
````
PICTURE HERE

**Trailing Days**
When a month starts on a Wednesday for example, you can turn the previous dates on or off. The default is to show them, the font colour is muted.
````php
$cal->isTrailingDays = true; // default
$cal->isTrailingDays = false; // trailing days are empty cells
````
**Week Starting Day**
If you are of an unusual disposition you can override the sensible option of having the week start on a Monday, and you can specify the week to start on a Sunday.
````php
$cal->weekStartsOn = 'Monday'; // default
$cal->weekStartsOn = 'Sunday'; // for the unhinged
````

**Change highlighting colours**
These take class names, you can use anything that exists in your style sheet. Aisde from the yellow for public holidays that are in the calendar.css file, most are Bootstrap defaults.
````php
$cal->highlightClass = 'primary'; // Default (bootstrap)
$cal->publicHolidayClass = 'public-holiday'; // Default (custom)
$cal->otherDateClass = 'danger'; // Default (bootstrap)
````

**Term for public holidays**
Default is "Public Holiday" but you can override for your country specific term. E.g. in the UK we call them Bank Holidays
````php
$cal->publicHolidayTerm = 'Public Holiday'; // default
$cal->publicHolidayTerm = 'Bank Holiday'; // custom
````

## Customisation alternative
If you have a lot of customisation, or you just prefer to code a different way, you can also pass all the customisation options into the constructor.

````php
// Declare any options in an associative array
$options = [
    'isWeekends' => true,
    'isTrailingDays' => true,
    'weekStartsOn' => 'Monday',
    'highlightClass' => 'primary',
    'publicHolidayClass' => 'public-holiday',
    'otherDateClass' => 'danger',
    'publicHolidayTerm' => 'Public Holiday',    
];

$cal = new Calendar(2018, $options);
````
