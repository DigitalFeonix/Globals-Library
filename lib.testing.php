<?php

/******************************************************************************
*
*   DEBUG and Test Function Library
*
******************************************************************************/

// used to get start/stop times to determine length of operations
function get_microtime()
{
    list($usec, $sec) = explode(' ', microtime());
    return ($usec + $sec);
}

function assert_true($condition,$message='',$level=3)
{
    // if not in DEBUG mode, stop
    if (!defined('DEBUG') || (DEBUG !== true))
    {
        ## check, then log?
        return;
    }

    // get error level
    $err = error_level($level);

    // if TEST not true, generate error
    if (!$condition)
    {
        user_error($message,$err);
    }
}

function assert_equals($condition1,$condition2,$message='',$level=3)
{
    // if not in DEBUG mode, stop
    if (!defined('DEBUG') || (DEBUG !== true))
    {
        return;
    }

    // get error level
    $err = error_level($level);

    // if TEST not true, generate error
    if ($condition1 != $condition2)
    {
        user_error($message,$err);
    }
}

// return the error constant for that level
function error_level($level)
{
    $ret = '';

    switch($level)
    {
        case 1:
            $ret = E_USER_NOTICE;
            break;
        case 2:
            $ret = E_USER_WARNING;
            break;
        case 3:
            $ret = E_USER_ERROR;
            break;
        default:
            $ret = E_USER_ERROR;
            break;
    }

    return $ret;
}

// error handler function from PHP online documentation
function my_error_handler($errno, $errstr, $errfile, $errline)
{
    switch ($errno)
    {
        case E_USER_ERROR:
            echo "<b>ERROR</b> [$errno] $errstr<br />\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...<br />\n";
            exit(1);
            break;
        case E_USER_WARNING:
            echo "<b>WARNING</b> [$errno] $errstr<br />\n";
            break;
        case E_USER_NOTICE:
            echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
            break;
        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

/**
*
*   rewrite of memory_get_usage for Windows OS
*
*/
if (!function_exists('memory_get_usage') && preg_match('/^windows/i',getenv('OS')))
{
    function memory_get_usage()
    {
        $output = array();
        exec('tasklist /FI "PID eq '.getmypid().'" /FO LIST', $output );
        return preg_replace( '/[^0-9]/', '', $output[5] ) * 1024;
    }
}

function echo_test()
{
    return dirname(__FILE__);
}

