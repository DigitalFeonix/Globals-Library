<?php

/******************************************************************************
*
*   Network related Functions Library
*
******************************************************************************/

/**
*
*   Determine if an IP address resides in a CIDR netblock or netblocks.
*
*   @param          string      IP of the visitor
*   @param          mixed       CIDR or array of CIDRs to check against
*   @return         bool
*
*/
function in_cidr($addr, $cidr)
{
    $output = false;

    if (is_array($cidr))
    {
        foreach ($cidr as $cidrlet)
        {
            if (match_cidr($addr, $cidrlet))
            {
                $output = true;
            }
        }
    }
    else
    {
        @list($ip, $mask) = explode('/', $cidr);
        if (!$mask) { $mask = 32; }
        $mask = pow(2,32) - pow(2, (32 - $mask));
        $output = ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
    }

    return $output;
}

/**
*
*   Returns info about the vistor based on IP
*
*   @author     DigitalFeonix
*   @param      string      IP of the visitor
*   @return     array       array of information found (or not)
*
*/
function get_host_info($ip)
{
    $ret = array();

    $ret['net'] = '';   // tld (.net .com .mil .gov .edu)
    $ret['dom'] = '';   // full hostname
    $ret['sub'] = array();  // array of the sub-domains starting with the tld as 0

    $hostname = gethostbyaddr($ip);

    if ($hostname != $ip)
    {
        $domain = array();
        // break the domain into it's parts
        $domain = explode('.',$hostname);
        // reverse the array so tld is first, then domain, then sub-domain, etc.
        $domain = array_reverse($domain);

        // passed on for more specific parsing on the page level, leaving generic here
        $ret['sub'] = $domain;
        $ret['net'] = $domain[0];
        $ret['dom'] = $hostname;
    }
    else
    {
        // no hostname found, returns the blank initialization array
    }

    return $ret;
}


/**
*
*   rewrite of gtmxrr for Windows OS
*
*/
if (!function_exists('getmxrr') && preg_match('/^win/i',PHP_OS))
{
    function getmxrr($hostname, &$mxrecords, &$mxweights)
    {
        $mxrecords = array();
        $mxweights = array();

        //@exec ("nslookup.exe -type=MX $hostname.", $output);
        $cmd = 'nslookup -type=MX '.escapeshellarg($hostname);

        ob_implicit_flush(false);
        ob_start();
        @exec($cmd, $result_arr); // echos out a line when run via command line
        ob_end_clean();

        foreach($result_arr as $line)
        {
            if (preg_match("/MX preference = ([0-9]+), mail exchanger = (.*)/", $line, $matches))
            {
                $mxrecords[] = $matches[2];
                $mxweights[] = $matches[1];
            }
        }

        return( count($mxrecords) > 0 );
    }
}

/**
*
*   check if string matches IPv4 address format
*
*/
function is_IPv4_addr($ip)
{
    $ret = FALSE;

    if (function_exists('filter_var'))
    {
        $ret = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
    else
    {
        $ret = preg_match('/^(?:(?:25[0-5]|2[0-4]\d|(?:(?:1\d)?|[1-9]?)\d)\.){3}(?:25[0-5]|2[0-4]\d|(?:(?:1\d)?|[1-9]?)\d)$/',$ip);
    }

    return $ret;
}

/**
*
*   Quick and dirty check for an IPv6 address
*
*/
function is_IPv6_addr($ip)
{
    return (strpos($ip, ":")) ? TRUE : FALSE;
}

/**
*
*   Returns breakdown of a URL ( HTTP/port 80 is assumed is can not be determined )
*
*   @author     DigitalFeonix
*   @param      string      URL
*   @return     array       array of information derived from URL
*
*/
function url_split($url)
{
    $input      = $url;
    $protocol   = '';
    $port       = '';

    if (strpos($url,'://'))
    {
        list($protocol,$url) = split('://',$url);
    }

    $uri        = explode('/', $url);
    $fullhost   = array_shift($uri);

    switch($protocol)
    {
        case 'telnet':
            $port = 20;
            break;
        case 'ftp':
            $port = 21;
            break;
        case 'ssh':
            $port = 22;
            break;
        case 'http':
            $port = 80;
            break;
        case 'https':
            $port = 443;
            break;
    }

    if (strpos($fullhost,':'))
    {
        list($host,$port) = split(':',$fullhost);
    }
    else
    {
        $host = $fullhost;
    }

    if ($port == '')
    {
        $port = 80;
    }

    if ($protocol == '')
    {
        switch($port)
        {
            case 443:
                $protocol = 'https';
                break;
            case 80:
            default:
                $protocol = 'http';
        }
    }

    $path   = '/' . (implode('/', $uri));

    return array('input' => $input, 'protocol' => $protocol, 'fullhost' => $fullhost, 'host' => $host, 'port' => $port, 'path' => $path);
}

function check_blackholes($ip)
{
    // Can't use IPv6 addresses yet
    if (is_IPv6_addr($ip))
    {
        return;
    }

    // Only conservative lists
    $blackhole_lists = array(
        "sbl-xbl.spamhaus.org", // All around nasties
//      "dnsbl.sorbs.net",  // Old useless data.
//      "list.dsbl.org",    // Old useless data.
//      "dnsbl.ioerror.us", // Bad Behavior Blackhole
    );

    // Things that shouldn't be blocked, from aggregate lists
    $blackhole_exceptions = array(
        "sbl-xbl.spamhaus.org" => array("127.0.0.4"),   // CBL is problematic
        "dnsbl.sorbs.net" => array("127.0.0.10",),  // Dynamic IPs only
        "list.dsbl.org" => array(),
        "dnsbl.ioerror.us" => array(),
    );

    // Check the blackhole lists
    $find = implode('.', array_reverse(explode('.', $ip)));

    foreach ($blackhole_lists as $dnsbl)
    {
        $result = gethostbynamel($find . "." . $dnsbl . ".");
        if (!empty($result))
        {
            // Got a match and it isn't on the exception list
            $result = @array_diff($result, $blackhole_exceptions[$dnsbl]);
            if (!empty($result))
            {
                return true;
            }
        }
    }

    return false;
}

/**
*
*   Send commands and return result for socket connections like direct SMTP
*
*   @param      reference   filepointer from fsockopen
*   @param      string      command
*   @return     string      result
*
*/
function socket_send_command($fp, $out)
{
    $ret = '';

    fwrite($fp, $out . "\r\n");
    stream_set_timeout($fp, 1);

    while ((($dat = fgets($fp)) != '') || ($ret == ''))
    {
        $ret .= $dat;
    }

    return $ret;
}

/**
*
*   Send commands and return result for socket connections like direct SMTP
*
*   @param      reference   filepointer from fsockopen
*   @param      string      command
*   @return     string      result
*
*/
function socket_receive($fp)
{
    $ret = '';

    stream_set_timeout($fp, 1);

    while ((($dat = fgets($fp)) != '') || ($ret == ''))
    {
        $ret .= $dat;
    }

    return $ret;
}

