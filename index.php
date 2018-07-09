<?php
//echo "write this down for vincent: # setsebool -P samba_export_all_rw 1";
require_once 'src/Calendar.php';

use northgoingzax\Calendar;

$cal = new Calendar();
$cal->addDateRange('2018-02-05', '2018-02-14', 'Week in Portugal');
$cal->isWeekends = true;

?>
<html>
    <head>
        <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.css">
        <script type="text/javascript" src="bower_components/jquery/dist/jquery.js"></script>
        <script type="text/javascript" src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="css/calendar.css">
        <style type="text/css">
        </style>
    </head>
    <body>
        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body'
                })
              });
        </script>
        
        <div class="container-fluid">
            <div class="col-md-10">
                <h4>2018</h4>
                
                <?= $cal->drawYear() ?> 
                
            </div>
        </div>
        
    </body>
</html>
