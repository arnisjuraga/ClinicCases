<?php
/*
  Development functions, created by arnis.juraga@gmail.com @ INDEED, Ltd / Copona.org
  works from specific IP addresses.

  To work, must be included in /system/startup.php after
  require_once(DIR_SYSTEM . 'helper/utf8.php');
  by
  require_once(DIR_SYSTEM . 'helper/indeed-development.php');
  pr($data, $args)
 * if args('raw') - will not ouput HTML tags.
 *
 *
 */

/* * ************************ CONFIG ***************** */
// $ip = $_SERVER['REMOTE_ADDR'];
// $ip_cloudflare = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;


$client_ip = '';
$debug_mode = false;

$ips = [
    '80.232.223.246',
    '172.28.112.1', // jaunās Linux WSL lokālās ip!,
    '109.110.25.253',
    '212.3.192.174',
    '213.226.141.71',
    '127.0.0.1',
    '90.133.15.134',
    '80.89.76.253', // Lauki, LMT
    '77.219.11.198', // Lauki, TELE2
    '172.18.208.1', // Ofiss Dators Local IP
    //'172.21.128.1', // Ofiss Dators Local IP
    // '195.122.4.174',
    // '2a03:ec00:b18a:36ca:ec96:1e14:305e:6efb', //kurš Jēkaba dators, DPD CSV testēšanā bija
];


if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    $client_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
    if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
        $client_ip = $_SERVER["REMOTE_ADDR"];
    } else {
        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $client_ip = $_SERVER["HTTP_CLIENT_IP"];
        }
    }
}


// var_dump ($client_ip) ;

// 172.* ir LOCAL jaunā WSL IP adrese!
if (strpos($client_ip, '172.') !== false) {
    $debug_mode = true;
} elseif (is_array($ips) && count($ips) && array_search($client_ip, $ips) === false) {
    $debug_mode = false;
} elseif (is_array($ips) && array_search($client_ip, $ips) !== false) {
    $debug_mode = true;
}

// if PHP is accessed from http://php.net/manual/en/reserved.variables.server.php#92121
if (isset($_SERVER['SHELL'])) {
    $debug_mode = true;
    $GLOBALS['debug_mode'] = true;
}

if (defined('DEBUG')) {
    $start_time = microtime();
    $start_mem = memory_get_usage();
}
// debug hack endd

// $debug_mode = true;
$GLOBALS['debug_mode'] = $debug_mode;

if (!function_exists('pr')) {

    function pr($data = 'w/o variable', $vardump = false, $prd = false, $plaintext = false)
    {
        if (@$GLOBALS['debug_mode']) {
            echo "\n\n";
            $html = "<div style='border: 1px solid grey; padding: 5px;'>";
            $html .= "<span style='color: black; background-color: white; font-size: 12px;'>\n" . ($prd ? 'PRD' : 'PR') . " data: <strong>" . gettype($data) . "</strong></span>\n";
            $html .= "<pre style='white-space: pre-wrap; background-color: " . ($prd ? 'grey' : '#EACCCC') . "; padding: 10px;  font-size: 14px; color: black; margin: 0; line-height: 14px;'>\n";

            ob_start();
            if ($data === '') {
                echo "empty STRING\n";
            } elseif ($data === ' ') {
                echo "empty SPACE\n";
            } elseif ($data === 0) {
                echo " 0 \n";
            } elseif ($data === false) {
                echo "FALSE \n";
            } elseif ($data === null) {
                echo "UNDEFINED\n";
            } elseif (gettype($data) == 'string') {
                echo !$vardump ? htmlentities($data) : $data;
            } else {
                $vardump ? array_walk_recursive($data, function (&$v) {
                    $v = htmlspecialchars($v);
                }) : false;
                $vardump ? var_dump($data) : print_r($data);
            }

            $result = ob_get_contents();
            ob_end_clean();

            $html .= $result . "\n</pre>\n";

            $debug = debug_backtrace();

            // if called FROM PRD, then line index will be +1
            if ($prd) {
                $fileindex = 1;
            } else {
                $fileindex = 0;
            }
            $file_from = file($debug[$fileindex]['file']);

            foreach ($debug as $file) {
                $html .= "<span style='font-size: 12px;'>\n<strong>" . trim($file_from[$debug[$fileindex]['line'] - 1]) . "</strong>\n</span><br />\n";
                $html .= "<span style='font-size: 12px;'>" . $debug[$fileindex]['file'] . "</span>:\n";
                $html .= "<span style='font-size: 12px; color: red; font-weight: bold;'>" . $debug[$fileindex]['line'] . "</span> <br />\n";
                break;
            }
            $html .= "</div>";

            if (!isset($_SERVER['SHELL']) && !$plaintext) {
                echo $html;
            } else {
                echo "/************ start ****************/\n\n";
                echo $result . "\n";
                echo "\n/************* end *****************/\n";
                echo trim($file_from[$debug[$fileindex]['line'] - 1]) . "\n";
                echo $debug[$fileindex]['file'] . ":" . $debug[$fileindex]['line'] . "\n";
            }


        }
    }

}


if (!function_exists('prd')) {

    function prd($data = 'w/o variable', $vardump = false, $bulk = false, $palintext = false)
    {
        if (@$GLOBALS['debug_mode']) {
            pr($data, $vardump, true, $palintext);
            die();
        }
    }

}

if (!function_exists('dt')) {

    function dt($int = false)
    {
        return loadTime::diff($int);
    }
}
if (!function_exists('ddd')) {
    function ddd()
    {
        $i = 0;
        $output = '';
        while (!empty(debug_backtrace()[$i])) {
            if (!empty(debug_backtrace()[$i]['file'])) {
                $output .= debug_backtrace()[$i]['file'] . ":" . debug_backtrace()[$i]['line'] . " \n";
            }
            $i++;
        }

        return $output;
    }
}

