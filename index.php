<?php
date_default_timezone_set('UTC');
include('utils.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <title>Access Investigator</title>
        <link rel="stylesheet" type="text/css" href="access_investigator.css" />
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

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript" src="//www.google.com/jsapi"></script>
        <script type="text/javascript">
            google.load('visualization', '1', {packages: ['gauge']});
            google.load('visualization', '1', {packages: ['table']});
            google.load('visualization', '1', {packages: ['corechart']});
            var root_path = '/srv/www/ads.thestudio.condenast.com/public_html';
            $(document).ready(function() {

                // Totals
                $.ajax({
                    url: "data/totals.json",
                    dataType: "json",
                    success: function(response) {
                        window.totals = response;
                        $('#last-update').html('Last updated: ' + window.totals.last_update + " UTC");
                        drawCharts();
                        $(document).on('click', '.file-list', toggleShowing);
                        $('.google-visualization-table-th').css('min-width', '60px');
                    }
                });

            });

            function toggleShowing(element) {
                if ($(this).hasClass('showing')) {
                    $(this).removeClass('showing');
                    $(this).scrollTop(0);
                } else
                    $(this).addClass('showing');
            }

            function drawCharts() {
                // Server load
                drawServerLoad();

                // Stats
                $.ajax({
                    url: "data/days.json",
                    dataType: "json",
                    success: function(response) {
                        drawDayRequests(response);
                    }
                });

                // Files
                $.ajax({
                    url: "data/files.json",
                    dataType: "json",
                    success: function(response) {
                        drawFileRequests(response);
                    }
                });

                // User agents
                $.ajax({
                    url: "data/user_agents.json",
                    dataType: "json",
                    success: function(response) {
                        drawUserAgents(response);
                    }
                });

                // Ips
                $.ajax({
                    url: "data/ips.json",
                    dataType: "json",
                    success: function(response) {
                        drawIps(response);
                    }
                });

                // New files
                $.ajax({
                    url: "data/new_files.json",
                    dataType: "json",
                    success: function(response) {
                        drawNewFiles(response);
                    }
                });

                // Perms
                $.ajax({
                    url: "data/perms.json",
                    dataType: "json",
                    success: function(response) {
                        drawPerms(response);
                    }
                });
            }

            function drawFileRequests(access_data) {
                var graph_data = [['target', 'requests']];
                $.each(access_data, function(filename, requests) {
                    graph_data.push([filename, requests]);
                    if (graph_data.length > 10)
                        return false;
                });

                // Create and populate the data table.
                var data = google.visualization.arrayToDataTable(graph_data);

                // Create and draw the visualization.
                new google.visualization.BarChart(document.getElementById('file-requests')).
                        draw(data,
                                {title: "Requests by file",
                                    width: 490, height: 384,
                                    vAxis: {title: "Filename"},
                                    hAxis: {title: "Requests"}}
                        );
            }

            function drawDayRequests(access_data) {
                var graph_data = [['date', 'requests']];
                $.each(access_data, function(rdate, requests) {
                    graph_data.push([rdate, requests]);
                });

                // Create and populate the data table.
                var data = google.visualization.arrayToDataTable(graph_data);

                // Create and draw the visualization.
                new google.visualization.ComboChart(document.getElementById('day-requests')).
                        draw(data,
                                {title: "Requests by day",
                                    width: 490, height: 384,
                                    vAxis: {title: "Requests"},
                                    hAxis: {title: "Date"}}
                        );
            }

            function drawUserAgents(access_data) {
                var graph_data = [['user agents', 'requests']];
                $.each(access_data, function(ua, requests) {
                    graph_data.push([ua, requests]);
                });

                // Create and populate the data table.
                var data = google.visualization.arrayToDataTable(graph_data);

                // Create and draw the visualization.
                var chart = new google.visualization.PieChart(document.getElementById('user-agents'));
                chart.draw(data, {title: 'Requests by user agent', width: 490, height: 384});
            }

            function drawIps(access_data) {
                var graph_data = [['ip', 'requests']];
                $.each(access_data, function(ip, requests) {
                    graph_data.push([ip, requests]);
                    if (graph_data.length > 10)
                        return false;
                });

                // Create and populate the data table.
                var data = google.visualization.arrayToDataTable(graph_data);

                // Create and draw the visualization.
                new google.visualization.ComboChart(document.getElementById('ip-requests')).
                        draw(data,
                                {title: "Requests by ip",
                                    width: 490, height: 384,
                                    vAxis: {title: "Requests"},
                                    hAxis: {title: "IP"},
                                    seriesType: "bars",
                                    series: {5: {type: "line"}}}
                        );
            }

            function drawNewFiles(access_data) {
                var graph_data = [['date', 'amount', '']];
                $.each(access_data, function(created_date, filenames) {
                    graph_data.push([created_date, filenames.length, "<div class='file-list'><p>" + filenames[0].replace(new RegExp(root_path, "g"), '').substring(0, 45) + "...</p>" + filenames.join('<br />').replace(new RegExp(root_path, "g"), '') + "</div>"]);
                });

                var data = google.visualization.arrayToDataTable(graph_data);
                var table = new google.visualization.Table(document.getElementById('new-files'));
                table.draw(data, {width: '490px', allowHtml: true});
            }

            function drawPerms(access_data) {
                var graph_data = [['date', 'amount', '']];
                $.each(access_data, function(created_date, filenames) {
                    graph_data.push([created_date, filenames.length, "<div class='file-list'><p>" + filenames[0].replace(new RegExp(root_path, "g"), '').substring(0, 45) + "...</p>" + filenames.join('<br />').replace(new RegExp(root_path, "g"), '') + "</div>"]);
                });

                var data = google.visualization.arrayToDataTable(graph_data);
                var table = new google.visualization.Table(document.getElementById('perms'));
                table.draw(data, {width: '490px', allowHtml: true});
            }

            function drawServerLoad() {
                var data = google.visualization.arrayToDataTable([
                    ['Label', 'Value'],
                    ['Memory', <?php echo floor(get_server_memory_usage()); ?>],
                    ['CPU', <?php echo get_server_cpu_usage(); ?>]]);

                var options = {
                    width: 200, height: 120,
                    redFrom: 90, redTo: 100,
                    yellowFrom: 75, yellowTo: 90,
                    minorTicks: 5};

                var chart = new google.visualization.Gauge(document.getElementById('server-load'));
                chart.draw(data, options);
            }

        </script>        
    </body>
</html>
