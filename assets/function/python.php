<?php
function init_python($command, $log_file)
{
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $python_path = "python";
        $final_command = "start /B $python_path $command > " . escapeshellarg($log_file) . " 2>&1";
    } else {
        // Linux/Unix
        $final_command = "python3 $command > " . escapeshellarg($log_file) . " 2>&1 &";
    }
    // echo $final_command . "\n";
    // exit;
    pclose(popen($final_command, 'r'));
}
