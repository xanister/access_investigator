<?php
/**
 * 
 * To Add:
 *  - Updated file permissions
 *  - SSL vs normal 
 *  - User actions
**/

ini_set('memory_limit', '-1');
date_default_timezone_set('UTC');

$root_path = "/srv/www/ads.thestudio.condenast.com/public_html/";
$lookback = 10;

// Grab the current apache log files and load into array
$logs = scandir('/var/log/httpd');
$lines = array();
foreach ($logs as $filename) {
    if (strpos($filename, 'access_log') !== false){    
        echo "[" . date(DATE_RFC2822) . "] - Pulling log file $filename\n";
        $handle = fopen("/var/log/httpd/$filename", 'rb');
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle);
                $lines[] = $buffer;
            }
        }
    }
}

// Grab the most recently created files
$new_files = array();
for($i = 0; $i < $lookback; $i++){
    $this_date = date("Y-m-d", strtotime('-'. $i .' days'));
    $this_date2 = date("Y-m-d", strtotime('-'. $i+1 .' days'));
    $cmd = "find $root_path -type f -newermt $this_date ! -newermt $this_date2";
    $result = preg_split('/[\r\n]+/', shell_exec($cmd), -1, PREG_SPLIT_NO_EMPTY);
    $new_files[$this_date] = $result; 
}

// Grab files that had permissions changes
$perms = array();
for($i = 0; $i < $lookback; $i++){
    $this_date = date("Y-m-d", strtotime('-'. $i .' days'));
    $this_date2 = date("Y-m-d", strtotime('-'. $i+1 .' days'));
    $cmd = "find $root_path -type f -newerct $this_date ! -newerct $this_date2";
    $result = preg_split('/[\r\n]+/', shell_exec($cmd), -1, PREG_SPLIT_NO_EMPTY);
    $perms[$this_date] = $result;
}

// Calculate stats on access_logs
echo "[" . date(DATE_RFC2822) . "] - Parsing\n";
$stats = array();
$days = array();
$files = array();
$ips = array();
$user_agents = array();
$total_requests = 0;
$today_requests = 0;
foreach ($lines as $line) {
    $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';
    preg_match($regex, $line, $this_line);

    if (isset($this_line[4]) && isset($this_line[8])) {
	$timestamp = DateTime::createFromFormat('!d/M/Y', $this_line[4])->getTimestamp();
	$this_date = date('m/d/Y',$timestamp);

        $total_requests++;
        if (date('m/d/Y') == $this_date) {
            $today_requests++;
        }

        $days[$this_date] = isset($days[$this_date]) ? $days[$this_date] + 1 : 1;
        $files[$this_line[8]] = isset($files[$this_line[8]]) ? $files[$this_line[8]] + 1 : 1;
        $ips[$this_line[1]] = isset($ips[$this_line[1]]) ? $ips[$this_line[1]] + 1 : 1;
        //$stats[$this_date][$this_line[8]] = isset($stats[$this_date][$this_line[8]]) ? $stats[$this_date][$this_line[8]] + 1 : 1;

        if (strpos(strtolower($this_line[13]), 'ipad')) {
            $user_agents['ipad'] = isset($user_agents['ipad']) ? $user_agents['ipad'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'iphone')) {
            $user_agents['iphone'] = isset($user_agents['iphone']) ? $user_agents['iphone'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 5')) {
            $user_agents['ie5'] = isset($user_agents['ie5']) ? $user_agents['ie5'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 6')) {
            $user_agents['ie6'] = isset($user_agents['ie6']) ? $user_agents['ie6'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 7')) {
            $user_agents['ie7'] = isset($user_agents['ie7']) ? $user_agents['ie7'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 8')) {
            $user_agents['ie8'] = isset($user_agents['ie8']) ? $user_agents['ie8'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 9')) {
            $user_agents['ie9'] = isset($user_agents['ie9']) ? $user_agents['ie9'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'msie 10')) {
            $user_agents['ie10'] = isset($user_agents['ie10']) ? $user_agents['ie10'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'rv:11')) {
            $user_agents['ie11'] = isset($user_agents['ie11']) ? $user_agents['ie11'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'firefox')) {
            $user_agents['firefox'] = isset($user_agents['firefox']) ? $user_agents['firefox'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'chrome')) {
            $user_agents['chrome'] = isset($user_agents['chrome']) ? $user_agents['chrome'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'safari')) {
            $user_agents['safari'] = isset($user_agents['safari']) ? $user_agents['safari'] + 1 : 1;
        } else if (strpos(strtolower($this_line[13]), 'mozilla/4.0 (compatible;)') >= 0) {
            $user_agents['bots'] = isset($user_agents['bots']) ? $user_agents['bots'] + 1 : 1;
        } else {
            $user_agents['other'] = isset($user_agents['other']) ? $user_agents['other'] + 1 : 1;
        }
    }
}

// Sort data
echo "[" . date(DATE_RFC2822) . "] - Sorting\n";
ksort($days);
arsort($files);
arsort($user_agents);
arsort($ips);

// Write to file
echo "[" . date(DATE_RFC2822) . "] - Writing json files\n";
file_put_contents(__DIR__ . '/totals.json', json_encode(array('today' => $today_requests, 'total' => $total_requests, 'last_update' => date(DATE_RFC2822))));
file_put_contents(__DIR__ . '/days.json', json_encode($days));
file_put_contents(__DIR__ . '/files.json', json_encode($files));
file_put_contents(__DIR__ . '/ips.json', json_encode($ips));
file_put_contents(__DIR__ . '/user_agents.json', json_encode($user_agents));
file_put_contents(__DIR__ . '/new_files.json', json_encode($new_files));
file_put_contents(__DIR__ . '/perms.json', json_encode($perms));

// Email
if (date('H') != '23')
    exit;

echo "[" . date(DATE_RFC2822) . "] - Sending email\n";
$to = "inkvock@gmail.com,jamie_watson@condenast.com,kesal_patel@condenast.com";
$subject = 'ADS Server Access Report for ' . date('j/M/Y');
$body = "<p>Total requests today: $today_requests</p>";
$body .= "<p>Top Files Requested:</p>";
$entry_count = 0;
$body .= "<p>";
foreach ($files as $key => $val) {
    if ($entry_count++ < 10)
        $body .= "$key requested $val times <br />";
}
$body .= "</p>";
$body .= "<p>View full statistics <a href='http://origin.ads.thestudio.condenast.com/partners/access_investigator/'>here</a></p>";

$request_url = "http://origin.ads.thestudio.condenast.com/partners/generic_emailer/email.php?to=$to&is_html=true&subject=" . urlencode($subject) . "&body=" . urlencode($body);

echo "[" . date(DATE_RFC2822) . "] - email response: " . file_get_contents($request_url) . "\n";
?>

