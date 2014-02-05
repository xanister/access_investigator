<?php
date_default_timezone_set('UTC');
include('utils.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <title>Access Investigator</title>
        <link rel="stylesheet" type="text/css" href="css/access_investigator.css" />
    </head>
    <body>

        <div class='header'>
            <span>
                <h4>Access Investigator stats for <?php echo date('l \t\h\e jS'); ?></h4>
                <h4 id="last-update"></h4>
            </span>
            <span>
                <div class="chart" id="server-load"></div>
            </span>
        </div>

        <hr />
        <div class="chart" id="day-requests"></div>
        <div class="chart" id="file-requests"></div>
        <hr />
        <div class="chart" id="user-agents"></div>
        <div class="chart" id="ip-requests"></div>
        <hr />
        <div class='data-tables'>
            <span class='data-table' style='margin-right: 20px;'>
                <h4 class='chart-heading'>Modified/Created files</h4>
                <div class="chart" id="new-files"></div>
            </span>
            <span class='data-table right'>
                <h4 class='chart-heading'>Files with permission changes</h4>
                <div class="chart" id="perms"></div>
            </span>
        </div>
        <hr />
        <div class="chart" id="response-codes"></div>
        <hr />
        <h4 class='chart-heading'>Top errors</h4>
        <div class="chart" id="errors"></div>


        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
        <script type="text/javascript" src="js/access_investigator.js"></script>        
        <script type="text/javascript">
            function drawServerLoad() {
                var data = google.visualization.arrayToDataTable([
                        ['Label', 'Value'],
                        ['Memory', < ?php echo floor(get_server_memory_usage()); ? > ],
                        ['CPU', < ?php echo get_server_cpu_usage(); ? > ]]);
                        var options = {
                            width: 200, height: 120,
                            redFrom: 90, redTo: 100,
                            yellowFrom: 75, yellowTo: 90,
                            minorTicks: 5};

                var chart = new google.visualization.Gauge(document.getElementById('server-load'));
                chart.draw(data, options);
            }

            $(document).ready(function() {
                // Server load
                drawServerLoad();
            });
        </script>
    </body>
</html>
