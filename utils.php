<?php

function get_server_memory_usage() {
    $free = shell_exec('free');
    $free = (string) trim($free);
    $free_arr = explode("\n", $free);
    if(!isset($free_arr[1]))
	return 0;
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2] / $mem[1] * 100;

    return $memory_usage;
}

function get_server_cpu_usage() {
    $sys_load = sys_getloadavg();
    return $sys_load[0];
}

?>
