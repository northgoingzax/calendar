<?php
namespace Northgoingzax\Calendar;
/**
 * github.com/northgoingzax/calendar
 * @package bs-calendar
 * @author Alex Gordon
 * @version 1.0.1
 * See the Readme for examples
  */
class Calendar
{
    /**
     * When providing dates to highlight you can force the weekends on or off<br>
     * This might be used if you are using it for a holiday system but you<br> 
     * input a date range that includes weekends.<br>
     * Defaults to true (include weekends)
     * @var bool 
     */
    public $isWeekends = true;
    
    /**
     * Do you want to display muted trailing days before & after the start of the month<br>
     * E.g. if the month starts on Wednesday, should Monday and Tuesday contain the previous month's days?
     * @var bool 
     */
    public $isTrailingDays = true;
    
    /**
     * Which Bootstrap 3 class should be applied to the background for highlighted cells
     * @var string bootstrap 3 compatible colours e.g. primary, warning, danger, info
     */
    public $highlightClass = 'primary';
    
    /**
     * Which Bootstrap 3 class should be applied to the background for bank holiday cells
     * @var string bootstrap 3 compatible colours e.g. primary, warning, danger, info
     */
    public $bankHolidayClass = 'bankholidays';
    
    /**
     * Which Bootstrap 3 class should be applied to the background for bank holiday cells
     * @var string bootstrap 3 compatible colours e.g. primary, warning, danger, info
     */
    public $otherDateClass = 'danger';
    
    /**
     * Which day of the week should the calendar start on
     * @var string e.g. Sunday, Monday
     */
    public $weekStartsOn = 'Monday';

    
    /**
     * Defaults to current year if not defined
     * @var int e.g. 2017
     */
    public $year;
    
    /**
     * Defaults to current month during construct
     * @var string e.g. January, February
     */
    public $month;
    
    /**
     * Days of the month to highlight within the month
     * Mapped on directly to the $days array, NOT Y-m-d format
     * @var array [0] => 01, [1] => 7, [2] => 13 etc.
     */
    public $highlight = array();
    
    /**
     * Label to show on selected date.
     * An array with the date being the key
     * @var array e.g. [2017-01-01] => "Barbados!"
     */
    public $label = array();
    
    /**
     * Months of the year, used to structure arrays
     * @var array
     */
    private $_months = array('January','February','March','April','May','June','July','August','September','October','November','December');
    
    /**
     * Array used to create entries for every day of the year<br>
     * Used to build the calendars. Do not modify.
     * @var array ['Month'] => array() 
     */
    private $_calendars = array();
    
    /**
     * Flag to check if calendars have been built
     * @var bool 
     */
    private $_calendarsBuilt = false;
    
    /**
     * Bank holidays to highlight in a different colour if required
     * @var array Format is [0] => Y-m-d, [1] => Y-m-d
     */
    public $bankHolidays = array();
    
    /**
     * Other dates to be highlighted
     * @var array format is [0] => Y-m-d, [1] => Y-m-d
     */
    public $otherDates = array();
    
    
    /**
     * Instantiate class and set year, defaults to current year.
     * @param int $year full year e.g. 2017 must be 4 chars long
     * @param array $options pass any settings in one go. Use function name followed by value, e.g. ['setWeekends']=>true
     */
    public function __construct($year = null,$options = array()) {
        // Default to current year
        $this->year = date('Y');
        
        // Default to current month
        $this->month = date('F');
        
        // Check for user defined year
        if(!is_null($year) && strlen($year) === 4){
            $this->year = $year;
        }        
        
        // Apply any set options
        foreach($options as $key => $val) {
            $this->$key = $val;
        }
    }
    
    /**
     * Add a single day to be highlighted
     * @param string|date $day Any date that will be validated by strtotime()
     * @param string $label any text that you wish to be displayed when hovering over this day
     */
    public function addDay($day = null, $label = null) {
        $date = date('Y-m-d',strtotime($day));
        $this->highlight[] = $date;
        $this->label[$date] = $label;
    }
    
    /**
     * Add multiple dates, these can be for any month within the year
     * @param array $days Any dates that will be validated by strtotime()
     * @param string $label any text that you wish to be displayed when hovering over ALL of these days
     */
    public function addDays($days = array(), $label = null) {
        foreach($days as $k => $day) {
            $this->addDay($day, $label);
        }
    }
    
    /**
     * Add a single day to be highlighted as a bank holiday
     * @param string|date $day Any date that will be validated by strtotime()
     */
    public function addBankHoliday($day = null) {
        $this->bankHolidays[] = date('Y-m-d',strtotime($day));
    }
    
    /**
     * Add multiple bank holiday dates, these can be for any month within the year
     * @param array $days Any dates that will be converted using date & strtotime()
     */
    public function addBankHolidays($days = array()) {
        foreach($days as $k => $day) {
            $this->addBankHoliday($day);
        }        
    }
    
    /**
     * Add a single day to be highlighted as an additional category
     * @param string|date $day Any date that will be validated by strtotime()
     */
    public function addOtherDate($day = null) {
        $this->otherDates[] = date('Y-m-d',strtotime($day));
    }
    
    /**
     * Add multiple additional dates to be highlighted, these can be for any month within the year
     * @param array $days Any dates that will be validated by strtotime()
     */
    public function addOtherDates($days = array()) {
        foreach($days as $k => $day) {
            $this->addOtherDate($day);
        }
    }
    
    /**
     * Use for adding multiple days between two dates
     * @param date $start Y-m-d e.g. 2017-05-01
     * @param date $end Y-m-d e.g. 2017-05-08
     * @param string $label Add any hover over text, i.e. reason for leave
     */
    public function addDateRange($start_date,$end_date,$label = null) {
        // force formatting of start & end date
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));
        
        // Get dates
        $dates = $this->_makeDateRange($start_date, $end_date);
        
        // Add to array
        $this->addDays($dates, $label);        
    }
    
    
    /**
     * Generate an array of dates between two dates
     * @param date $start_date the start date, any format but default to Y-m-d
     * @param date $end_date the end date, any format but default to Y-m-d
     * @return array [0] = Y-m-d, [1] = Y-m-d, etc.
     */
    private function _makeDateRange($start_date, $end_date) {
        $start_date = \DateTime::createFromFormat('Y-m-d', $start_date);
        $end_date = \DateTime::createFromFormat('Y-m-d', $end_date);

        $date_range = new \DatePeriod($start_date, new \DateInterval('P1D'), $end_date->modify('+1 day')    );
        
        $return = array();
        foreach($date_range as $date) {
            $return[] = $date->format('Y-m-d');
        }
        return $return;
    }
    
    
    
    /**
     * Creates an array with all the dates used to draw the calendar
     */
    private function _buildCalendars() {
        // Draw all the months for the year 
        foreach($this->_months as $month) {
            $this->_calendars[$month] = $this->_makeMonthArray($month);
        }        
        $this->_calendarsBuilt = true;
    }
    
    /**
     * Build an array of all the days for all the months of the year<br>
     * Used to generate the table.
     * @var string $month F version of month, e.g. January, February, March
     */
    public function _makeMonthArray($month) {
        // array of day of week and if muted or not
        // some months may be 7x6 = 42, so all tables to be 7x6
        $cells = 42;
		
        // key, starting at 1, represents each cell, so the first Monday is 1 and the last Sunday is 35
        $days = array();
        
        // Define numeric value for month (e.g. 01, 02, 03)
        $m = date('m', strtotime($month));
        
        // Define last month for working out muted pre-days
        $last_month = date('F Y', strtotime($this->year ."-". $m . "-01 -1 month"));
              
        // returns Monday,Tuesday,etc.
        $first_day_of_month = date('l', strtotime($this->year . "-" . $month . "-01"));
        
        // Get value of last day of month so we know when to stop iterating
        $last_day_of_month = (int) date('t', strtotime($month));
        
        // Start the initial cycle in pre-month cycle
        $cycle_stage = 'pre';
        
        // Go to day before 1st day of week, prep for for loop
        $day = date('l', strtotime($this->weekStartsOn . ' -1 day'));
        
        for($i = 1; $i <= $cells; $i++) {
            // Move to 1st day of week
            $day = date('l', strtotime($day . ' +1 day'));
            
            // Create new row
            $days[$i] = array();
            
            // Assign day of week to them
            $days[$i]['text'] = $day;
            
            // Set default value for ['date']
            $days[$i]['date'] = null;
            
            switch($cycle_stage) {
                // The days in the grid before the 1st day of the month
                case 'pre':
                    // Have made it to 1st day?
                    if($first_day_of_month === $day) {
                        // Not a muted day
                        $days[$i]['muted'] = false;
                        
                        // Assign the number of the day
                        $days[$i]['day'] = 1;
                        
                        // Assign the full date format so we can use it for checking highlights in _drawRow()
                        $days[$i]['date'] = date('Y-m-d', strtotime($this->year . "-" . $m . "-01"));
                        
                        // Start the day of the month integer
                        $day_int = 2;
                        
                        // Set the stage to active cycle
                        $cycle_stage = 'active';
                        
                    // Not at first day so mute the days
                    } else {                    
                        // Mark these days as muted
                        $days[$i]['muted'] = true;
                        
                        // Define the days from the previous month
                        $days[$i]['day'] = date('j', strtotime("last " . $day . " of $last_month"));
                    }
                    
                    break;
                    
                // All of  these days are active days of the month
                case 'active':
                    // Not a muted day
                    $days[$i]['muted'] = false;
                    
                    // Assign the number of the day
                    $days[$i]['day'] = $day_int;
                    
                    // Assign the full date format so we can use it for checking highlights in _drawRow()
                    $days[$i]['date'] = date('Y-m-d', strtotime($this->year . "-" . $m . "-" . $day_int));
                    
                    // Increase to next day as long as we're not end of the month
                    if($day_int !== $last_day_of_month) {
                        $day_int++;
                    } else {                       
                        // reset day back to 1 so we can start the next months muted
                        $day_int = 1;

                        // Set the stage to post month cycle
                        $cycle_stage = 'post';
                    }
                    
                    break;
                    
                // These are days that belong in next month
                case 'post':
                    // Mark these days as muted
                    $days[$i]['muted'] = true;
                    
                    // Assign the number of the day
                    if($this->isTrailingDays) {
                        $days[$i]['day'] = $day_int; 
                    } else {
                        $days[$i]['day'] = null;
                    }
                    
                    // Progress to next day
                    $day_int++;                    
                    
                    break;
            }
        }
        return $days;
    }
    
    /**
     * 
     * @param int $start the array key to start at
     * @param int $month the month of the year 1-12
     * @return string
     */
    private function _drawRow($start,$month) {
        // Get the array for this month
        $days = $this->_calendars[$month];
        
        // start the row html
        $str = '<tr>';
        
        // Set the end cell
        $end = $start + 6;
        
        // Cycle through for this row
        for($i = $start; $i<=$end; $i++) {
            // Check for highlights
            // default
            $td = '<td>';
            
            // Bank holidays first
            if(in_array($days[$i]['date'],$this->bankHolidays)) {
                $popover = 'data-toggle="tooltip" data-placement="top" title="Bank holiday"';
                $td = '<td class="pointer ' . $this->bankHolidayClass . '" ' . $popover . '><strong>';
            }       
            
            // Other dates next
            if(in_array($days[$i]['date'],$this->otherDates)) {
                $td = '<td class="' . $this->otherDateClass . '"><strong>';
            }       
            
            // Highlighted days (override bank holiday highlight)
            if(in_array($days[$i]['date'],$this->highlight)) {
                
                // See if any labels came through
                $popover_label = $label_class = '';
                if(array_key_exists($days[$i]['date'], $this->label)) {
                    $label = addslashes($this->label[$days[$i]['date']]);
                    $popover_label = 'data-toggle="tooltip" data-placement="top" title="' . $label . '"';
                    $label_class = "pointer";
                }
                
                $td = '<td class="' . $this->highlightClass . ' ' . $label_class . '" ' . $popover_label . '><strong>';
            } 
            
            // Look muted/active (override everything)
            if($days[$i]['muted']) {
                $td = '<td class="text-muted">';
            } 
            
            // If weekends set to false, override any highlighting
            if($this->isWeekends === false) {
                if(in_array(date('w', strtotime($days[$i]['date'])), [0,6])) {
                    $td = '<td>';
                }
            }
            
            // Draw the correct td
            $str .= $td;
            
            // Write the day of the month 
            if(!is_null($days[$i]['day'])) {
                $str .= $days[$i]['day'];
            } else {
                // keep row heights consistant
                $str .= '&nbsp;';
            }
            
            // Close highlight & bolds
            if(in_array($days[$i]['date'],$this->highlight) || in_array($days[$i]['date'],$this->bankHolidays)) {
                $str .= '</strong>';
            }
            
            // Close cell
            $str .= '</td>';
        }
        // Close row
        $str .= '</tr>';
        
        return $str;
    }
    
    
    /**
     * Draw the calendar for a specified month, or the current month
     * @param int $month Month of the year as a number, e.g. January = 1, June = 6, October = 10
     * @return string The HTML for the calendar
     */
    public function drawMonth($month = 0) {
        // Check we have calendars ready
        if(!$this->_calendarsBuilt) { $this->_buildCalendars(); }
        
        // default to current month
        if($month === 0) { $month = $this->month; }
        // Or generate correct month from input
        else { $month = date('F', strtotime($this->year . "-" . $month . "-1")); }
        
        $str = '<div class="col-lg-4 col-md-6 col-sm-6" style="margin-bottom:15px;">
    		<table class="table table-bordered table-striped table-responsive">
                <thead>
                    <tr class="active">
                      <th colspan="7" class="text-center">
                        ' . $month . ' ' . $this->year . '                        
                      </th>
                    </tr>
                    <tr>';
        // Go to yesterday
        $day = date('D', strtotime($this->weekStartsOn . ' -1 day'));
        for($i = 1; $i<=7; $i++) {
            $day = date('D', strtotime($day . ' +1 day'));
            $str .= '<th>' . $day . '</th>';
        }
        
        $str .= '
                    </tr>
                </thead>
                <tbody>';
        $str .= $this->_drawRow(1,$month);
        $str .= $this->_drawRow(8,$month);
        $str .= $this->_drawRow(15,$month);
        $str .= $this->_drawRow(22,$month);
        $str .= $this->_drawRow(29,$month);
        $str .= $this->_drawRow(36,$month);
        $str .='</tbody>
            </table>
        </div>';
        return $str;
    }
    
    /**
     * Draws calendars for the entire year
     * @return string The HTML for the entire year
     */
    public function drawYear() {
        $str = "";
        foreach($this->_months as $month) {
            $str .= $this->drawMonth($month);
        }
        return $str;        
    }    
}
