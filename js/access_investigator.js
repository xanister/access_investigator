google.load('visualization', '1', {packages: ['gauge']});
google.load('visualization', '1', {packages: ['table']});
google.load('visualization', '1', {packages: ['corechart']});

var root_path = '/srv/www/ads.thestudio.condenast.com/public_html';

$(document).ready(function() {

    // Totals
    $.ajax({
        url: "../data/totals.json",
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
    // Stats
    $.ajax({
        url: "../data/days.json",
        dataType: "json",
        success: function(response) {
            drawDayRequests(response);
        }
    });

    // Files
    $.ajax({
        url: "../data/files.json",
        dataType: "json",
        success: function(response) {
            drawFileRequests(response);
        }
    });

    // User agents
    $.ajax({
        url: "../data/user_agents.json",
        dataType: "json",
        success: function(response) {
            drawUserAgents(response);
        }
    });

    // Ips
    $.ajax({
        url: "../data/ips.json",
        dataType: "json",
        success: function(response) {
            drawIps(response);
        }
    });

    // New files
    $.ajax({
        url: "../data/new_files.json",
        dataType: "json",
        success: function(response) {
            drawNewFiles(response);
        }
    });

    // Perms
    $.ajax({
        url: "../data/perms.json",
        dataType: "json",
        success: function(response) {
            drawPerms(response);
        }
    });

    // Response codes
    $.ajax({
        url: "../data/response_codes.json",
        dataType: "json",
        success: function(response) {
            drawResponseCodes(response);
        }
    });

    // Errors
    $.ajax({
        url: "../data/errors.json",
        dataType: "json",
        success: function(response) {
            drawErrors(response);
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

function drawResponseCodes(access_data) {
    var graph_data = [['code', 'count']];
    $.each(access_data, function(code, files) {
        var total_count = 0;
        $.each(files, function(filename, count) {
            total_count += count;
        });
        graph_data.push([code, total_count]);
    });

    // Create and populate the data table.
    var data = google.visualization.arrayToDataTable(graph_data);

    // Create and draw the visualization.
    var chart = new google.visualization.PieChart(document.getElementById('response-codes'));
    chart.draw(data, {title: 'Requests by response code', width: 490, height: 384});
}

function drawErrors(access_data) {
    var graph_data = [['count', 'error']];
    $.each(access_data, function(error_string, error_count) {
        graph_data.push([error_count, error_string]);
        if (graph_data.length > 10)
            return false;
    });

    var data = google.visualization.arrayToDataTable(graph_data);
    var table = new google.visualization.Table(document.getElementById('errors'));
    table.draw(data, {width: '1024px', allowHtml: true});
}