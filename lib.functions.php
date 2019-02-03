<?php

/******************************************************************************
*
*   General/Misc Functions Library
*
******************************************************************************/

/**
*
*   Determine if visitor is using IE 5.2 on Mac
*
*   @author     DigitalFeonix
*   @return     bool
*
*/
function is_ie52mac()
{
    $ret = false;

    // Get the HTTP User Agent info. Becomes the $ua variable
    $ua = $_SERVER['HTTP_USER_AGENT'];

    // Mac Internet Explorer 5.2
    if(eregi("Mac", $ua) && eregi("msie", $ua) && eregi("5\.2[0-9]", $ua))
    {
        $ret = true;
    }

    return $ret;
}


/**
*
*   Returns an array of info about user_agent
*
*   @author     DigitalFeonix
*   @return     array
*
*/
function get_user_agent()
{
    $ret            = array();
    $ret['browser'] = 'Unknown';
    $ret['version'] = 'Unknown';
    $ret['user_os'] = 'Unknown';

    // get browser
    preg_match('/(MSIE|Safari|Firefox|Opera)/i', $_SERVER['HTTP_USER_AGENT'], $browser_matches);

    if (count($browser_matches) > 0)
    {
        $ret['browser'] = $browser_matches[0];
    }

    switch($ret['browser'])
    {
        case 'MSIE':
            preg_match('/MSIE ([0-9.]*)/i', $_SERVER['HTTP_USER_AGENT'], $version_matches);
            $ret['version'] = $version_matches[1];
            break;
        case 'Firefox':
            preg_match('/Firefox[ \/]([0-9.]*)/i', $_SERVER['HTTP_USER_AGENT'], $version_matches);
            $ret['version'] = $version_matches[1];
            break;
        case 'Opera':
            preg_match('/Opera[ \/]([0-9.]*)/i', $_SERVER['HTTP_USER_AGENT'], $version_matches);
            $ret['version'] = $version_matches[1];
            break;
        case 'Safari':
            // Safari's versioning is more like build numbers, actual version seems to be added to 3 and higher
            // while it may be Safari 3.0.1 the return string will be 522.12.2
            preg_match('/Safari\/([0-9.]*)/i', $_SERVER['HTTP_USER_AGENT'], $version_matches);
            $ret['version'] = $version_matches[1];
            break;
        ## chrome can be mis-read as safari
    }

    // get OS
    preg_match('/(Windows|Mac)/i', $_SERVER['HTTP_USER_AGENT'], $os_matches);

    if (count($os_matches) > 0)
    {
        $ret['user_os'] = $os_matches[0];
    }

    // return array
    return $ret;
}


/**
*
*   Compares two version numbers.
*
*   @author     DigitalFeonix
*   @param      mixed       version number/string to check
*   @param      mixed       version number/string to compare check against
*   @return     int         -1 = lower, 0 = same, 1 = higher
*
*/
function version_comparison($check_ver, $base_ver)
{
    $ret = 0;

    // determine the parts of the version to compare to
    $base_tmp       = split('\.', $base_ver);
    $base           = array();
    $base['major']  = $base_tmp[0] != '' ? $base_tmp[0]:0;
    $base['minor']  = $base_tmp[1] != '' ? $base_tmp[1]:0;
    $base['patch']  = $base_tmp[2] != '' ? $base_tmp[2]:0;
    $base['build']  = $base_tmp[3] != '' ? $base_tmp[3]:0;

    // determine the parts of the version to check
    $check_tmp      = split('\.', $check_ver);
    $check          = array();
    $check['major'] = $check_tmp[0] != '' ? $check_tmp[0]:0;
    $check['minor'] = $check_tmp[1] != '' ? $check_tmp[1]:0;
    $check['patch'] = $check_tmp[2] != '' ? $check_tmp[2]:0;
    $check['build'] = $check_tmp[3] != '' ? $check_tmp[3]:0;

    // loop through each part of the version number
    foreach ($base as $key => $val)
    {
        if ($check[$key] != $val)
        {
            // determine higher or lower and set return var
            if ($check[$key] > $val)
            {

                $ret = 1;
            }
            else
            {
                $ret = -1;
            }

            // and break out of loop
            break;
        }
    }

    // return the verdict
    return $ret;
}

/**
*
*   converts mm to inches
*
*   @param      mixed       size in mm to convert (can also be in the for 1x1 or 1-1)
*   @return     string
*   @required   decimal_to_fraction()
*
*/
function convert_mm_to_inches($mmsize)
{
    if (strpos($mmsize, "x")!==false)
    {
        // separated by x
        $sizes  = explode("x", $mmsize);
        $sep    = 'x';
    }
    elseif (strpos($mmsize, "&")!==false)
    {
        // separated by &
        $sizes  = explode("&", $mmsize);
        $sep    = '&';
    }
    elseif (strpos($mmsize, "-")!==false)
    {
        // separated by -
        $sizes  = explode("-", $mmsize);
        $sep    = '-';
    }
    elseif (strpos($mmsize, "or")!==false)
    {
        // separated by or
        $sizes  = explode("or", $mmsize);
        $sep    = 'or';
    }
    else
    {
        $sizes[]    = $mmsize;
        $sep        = "";
    }


    foreach ($sizes as $size)
    {
        if (intval($size)<4)
        {
            return $mmsize . "mm";
        }

        $decimal = (($size/10)/2.54);
        $decimal = round($decimal / 0.125) * 0.125;
        $newsize[] = " " . decimal_to_fraction($decimal) . " inch ";
        $newmm[] = " ".trim($size)."mm ";
    }

    return implode($sep, $newsize) . " (" . trim(implode($sep, $newmm)) . ") ";
}

/**
*
*   converts a decimal number into a fraction
*
*   @param      float       number to convert
*   @return     string
*   @required   GCD()
*
*/
function decimal_to_fraction($number)
{
    list($whole, $numerator) = explode ('.', $number);

    if ($numerator==0)
    {
        return $whole;
    }
    $denominator = 1 . str_repeat(0, strlen($numerator));
    $GCD = GCD($numerator, $denominator);

    $numerator /= $GCD;
    $denominator /= $GCD;

    if ($denominator>8)
    {
        //return $whole;
    }
    if(!$whole)
    {
        $whole = "";
    }

    return sprintf ('%s %d/%d', $whole, $numerator, $denominator);
}

/**
*
*   finds the greatest common denominator
*
*   @param      integer       numerator
*   @param      integer       denominator
*
*   @return     integer
*
*/
function GCD($a, $b)
{
    while ($b != 0)
    {
        $remainder = $a % $b;
        $a = $b;
        $b = $remainder;
    }
    return abs($a);
}

/**
*
*   finds the simplest representation of a decimal number
*
*   ex: 4.30000 will return 4.3
*
*   @param      float       decimal to simplify
*   @return     float
*
*/
function simplest_number($num)
{
    // NOTE: round() will also do this. Using the example above
    // round(4.30000,5) will also return 4.3

    $ret = $num;

    if (!strpos($num,'.')) { return $ret; }

    list($int,$float) = explode('.',$num);

    for ($i=strlen($float);$i>=0;$i--)
    {
        $new_num = $int + number_format('0.'.$float,$i);

        if ($new_num != $num)
        {
            break;
        }

        $ret = $new_num;
    }

    return $ret;
}

/*******************************************************************************
    This function is used to round a number to the closest nickel.

    @author     unknown
    @source     http://php.xivix.net/php_6_22_Random_Functions_Round_to_Closest_Nickel
                updated streamline and to deal with issues dividing by a float

    @param      int,float   price to round
    @return     float
*******************************************************************************/
function round_to_nickel($incoming)
{
    $number     = (int) round($incoming * 100);
    $remainder  = $number % 5;
    $base       = $number - $remainder;

    if ($remainder > 2)
    {
        $base += 5;
    }

    return number_format(($base/100),2,'.','');
}

/**
*
*   This function is used to round a number to the closest quarter.
*
*   @param      int,float   price to round
*   @return     float
*
*/
function round_to_quarter($incoming)
{
    $number     = (int) round($incoming * 100);
    $remainder  = $number % 25;
    $base       = $number - $remainder;

    if ($remainder > 12)
    {
        $base += 25;
    }

    return number_format(($base/100),2,'.','');
}

/**
 * Generates a Universally Unique IDentifier, version 4.
 *
 * RFC 4122 (http://www.ietf.org/rfc/rfc4122.txt) defines a special type of Globally
 * Unique IDentifiers (GUID), as well as several methods for producing them. One
 * such method, described in section 4.4, is based on truly random or pseudo-random
 * number generators, and is therefore implementable in a language like PHP.
 *
 * We choose to produce pseudo-random numbers with the Mersenne Twister, and to always
 * limit single generated numbers to 16 bits (ie. the decimal value 65535). That is
 * because, even on 32-bit systems, PHP's RAND_MAX will often be the maximum *signed*
 * value, with only the equivalent of 31 significant bits. Producing two 16-bit random
 * numbers to make up a 32-bit one is less efficient, but guarantees that all 32 bits
 * are random.
 *
 * The algorithm for version 4 UUIDs (ie. those based on random number generators)
 * states that all 128 bits separated into the various fields (32 bits, 16 bits, 16 bits,
 * 8 bits and 8 bits, 48 bits) should be random, except : (a) the version number should
 * be the last 4 bits in the 3rd field, and (b) bits 6 and 7 of the 4th field should
 * be 01. We try to conform to that definition as efficiently as possible, generating
 * smaller values where possible, and minimizing the number of base conversions.
 *
 * @copyright   Copyright (c) CFD Labs, 2006. This function may be used freely for
 *              any purpose ; it is distributed without any form of warranty whatsoever.
 * @author      David Holmes <dholmes@cfdsoftware.net>
 *
 * @return  string  A UUID, made up of 32 hex digits and 4 hyphens.
 */
function uuid()
{
    // The field names refer to RFC 4122 section 4.1.2
    return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
        mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
        mt_rand(0, 65535), // 16 bits for "time_mid"
        mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
        bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
        mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
        );
}

/**
*
*   Calculate check digit and return full UPC value
*
*   @param      string      complete UPC minus the final check digit
*   @return     string      complete UPC code
*
*/
function upc_checkdigit($string)
{
    if (!ctype_digit($string))
    {
        return false;
    }
    else
    {
        if (strlen($string) == 12)
        {
            $string = substr($string,0,11);
        }
        elseif (strlen($string) != 11)
        {
            return false;
        }

        $x = (($string{0} + $string{2} + $string{4} + $string{6} + $string{8} + $string{10})*3) + ($string{1} + $string{3} + $string{5} + $string{7} + $string{9});
        $r = $x % 10;
        $d = ($r > 0) ? 10 - $r : $r;

        return $string . $d;
    }
}

/**
*
*   Using random number generator, determine if a probility succeeds
*   Basically this is a dice roller, determine if a roll pass/fails
*
*   @param      int     percent chance (numerator)
*   @param      int     optional denominator
*   @return     bool
*
*/
function probability($chance, $out_of = 100)
{
    if ($out_of > mt_getrandmax())
    {
        // out of range of the random function
        return -1;
    }

    $random = mt_rand(1, $out_of);
    return $random <= $chance;
}


/**
*
*   returns html encoded value of string (hiding email address from bots is one use)
*
*   @param      string      string to obfusicate
*   @return     string      html/hex encoded string
*
*/
if (!function_exists('special_char'))
{
    function special_char($string)
    {
        $ret = '';

        for ($i=0;$i<strlen($string);$i++)
        {
            $c = ord(substr($string,$i,1));
            $ret .= '&#'.$c.';';
        }

        return $ret;

        //echo $ret;
    }
}

/**
*
*    @author     Miguel Perez
*    http://us3.php.net/manual/en/function.chr.php
*
*/
function unichr($c)
{
    if ($c <= 0x7F)
    {
        return chr($c);
    }
    else if ($c <= 0x7FF)
    {
        return chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
    }
    else if ($c <= 0xFFFF)
    {
        return chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
    }
    else if ($c <= 0x10FFFF)
    {
        return chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F) . chr(0x80 | $c >> 6 & 0x3F) . chr(0x80 | $c & 0x3F);
    }
    else
    {
        return false;
    }
}

/**
*
*   returns a safe string to use for mysql queries, depending on what's installed
*
*   @param      string      string to cleanse
*   @return     string      safe string
*
*/
function mysql_safe_string($string)
{
    if (get_magic_quotes_gpc() == 1)
    {
        $string = stripslashes($string);
    }

    if (function_exists(mysql_real_escape_string))
    {
        $string = mysql_real_escape_string($string);
    }
    else
    {
        $string = mysql_escape_string($string);
    }

    return trim($string);
}

################################################################################
#    ALTERNATIVE TIME FUNCTIONS
################################################################################

/**
*
*   Similar to gmdate(), returns formated date for UTC timezone
*
*   @param      string      date format (see date())
*   @param      int         unix timestamp
*   @return     string      formatted date string
*
*/
function utcdate($format,$timestamp=NULL)
{
    $ts = ($timestamp == NULL) ? time() : intval($timestamp);

    if (version_compare('5.1.0',phpversion(),'ge'))
    {
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $ret = date($format,$ts);

        date_default_timezone_set($tz);
    }
    else
    {
        $ret = gmdate($format,$ts);
        $ret = preg_replace('/GMT/','UTC',$ret);
    }

    return $ret;
}


/**
*
*   Returns current New Earth Time
*   http://newearthtime.net/
*
*   @return     string
*   @required   unichr()
*
*/
function net_time($timestamp=NULL)
{
    $ts = ($timestamp == NULL) ? time() : intval($timestamp);

    list($h,$m,$s)  = split(':',gmdate('H:i:s',$ts));
    list($u,$ts)    = ($timestamp == NULL) ? split(' ',microtime()) : array(0,0);

    $deg = $h * 15; // 0-345 (increments of 15)
    $deg = $deg + floor($m / 4); // 0-14

    $min = ($m % 4) * 15; // 0,15,30,45
    $min = $min + floor($s / 4); // 0-14

    $sec = ($s % 4) * 15; // 0,15,30,45
    $sec = $sec + ((60 * $u) / 4); // 0-14

    return sprintf('%d%s %02d\' %02d" NET',$deg,unichr(176),$min,$sec);
}

/**
*
*   Returns current World Time Format
*   http://www.worldtimeformat.com/
*
*   @return     string
*   @required   num2alpha()
*
*/
function wtf_time()
{
    $ds = 86400; // total seconds in day
    $jd = (microtime(true) / $ds) + 2440587.5; // Julian Date

    $js = floor(($jd - floor($jd)) * $ds); // Julian Seconds (for today)
    $jd = floor($jd); // Julian Day

    $wd = num2alpha($jd);
    $wt = num2alpha(($js/$ds) * pow(26,3)); // same as * 10 * 10 * 10 if it were base 10!

    return sprintf('%s:%s',$wd,$wt);
}

/**
*
*   Converts an base 10 integer to base 26 alpha
*
*   @param      int
*   @return     string
*
*/
function num2alpha($n)
{
    $r = '';

    while($n > 0)
    {
        $r = chr(($n % 26) + 0x41) . $r;
        $n = intval($n / 26);
    }

    return $r;
}

/**
*
*   Returns current local time as Decimal Time
*   http://en.wikipedia.org/wiki/Decimal_time
*
*   @return     string
*
*/
function dec_time()
{
    $ds = 86400; // total seconds in day
    $dt = microtime(true) - mktime(0,0,0);

    $hs = $ds / pow(10,1);
    $ms = $ds / pow(10,3);
    $ss = $ds / pow(10,5);

    $hour = floor($dt/$hs);
    $min  = floor($dt/$ms) - ($hour * 100);
    $sec  = floor($dt/$ss) - ($hour * 10000) - ($min * 100);
    //$usec = floor($dt/$us) - ($hour * 10000) - ($min * 100);

    return sprintf('%01d',$hour).'h '.sprintf('%02d',$min).'m '.sprintf('%02d',$sec).'s';
}

/**
*
*   Returns number of days since a given date
*
*   @param      string      parsable date string
*   @return     int
*
*/
function days_since($date)
{
    $ret = 0;
    $ts1 = strtotime($date);
    $ts2 = time();

    if ($ts1 > $ts2)
    {
        list($ts1,$ts2) = array($ts2,$ts1);

        /*
        $tmp = $ts1;
        $ts1 = $ts2;
        $ts2 = $tmp;
        */
    }

    $Yd = date('Y',$ts2) - date('Y',$ts1);

    if ($Yd == 0)
    {
        // dates are both in the same year
        $ret = date('z', $ts2) - date('z', $ts1);
    }
    else
    {
        $ret  = date('z',strtotime(sprintf('%d-12-31',date('Y',$ts1)))) - date('z', $ts1);

        for ($y = date('Y',$ts1) + 1; $y < date('Y',$ts2); $y++)
        {
            $ret += date('z',strtotime(sprintf('%d-12-31',$y))) + 1;
        }

        $ret += date('z', $ts2) + 1;
    }

    return $ret;
}

function mayan_date()
{
    $mld = array();

    $ds = 86400; // total seconds in day
    $jd = floor((microtime(true) / $ds) + 2440587.5); // Julian Day

    $md = $jd - 584283; // use the julian/gmt correlation
    $mld[] = $x = floor($md / 144000);
    $md = $md - ($x * 144000);
    $mld[] = $x = floor($md / 7200);
    $md = $md - ($x * 7200);
    $mld[] = $x = floor($md / 360);
    $md = $md - ($x * 360);
    $mld[] = $x = floor($md / 20);
    $mld[] = $md - ($x * 20);

    return implode('.',$mld);
}

################################################################################
#    BRIDGE FUNCTIONS
#    PHP 5+ functions rewritten to be used in PHP 4
#    seperate library?
################################################################################

/**
*
*    creates a query string from an array
*
*    @author     <mqchen@gmail.com>
*    @return     string
*
*/
if(!function_exists('http_build_query'))
{
    function http_build_query($data,$prefix=null,$sep='',$key='')
    {
        $ret = array();

        foreach((array)$data as $k => $v)
        {
            $k = urlencode($k);
            if(is_int($k) && $prefix != null)
            {
                $k = $prefix.$k;
            }

            if(!empty($key))
            {
                $k  = $key."[".$k."]";
            }

            if(is_array($v) || is_object($v))
            {
                array_push($ret,http_build_query($v,"",$sep,$k));
            }
            else
            {
                array_push($ret,$k."=".urlencode($v));
            }
        }

        if(empty($sep))
        {
            $sep = ini_get("arg_separator.output");
        }

        return implode($sep, $ret);
    }
}

/**
*
*    determine directory that systems temp files are created in
*
*    @return     varies
*
*/
if ( !function_exists('sys_get_temp_dir') )
{
    // Based on http://www.phpit.net/
    // article/creating-zip-tar-archives-dynamically-php/2/
    function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) )
        {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) )
        {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) )
        {
            return realpath( $_ENV['TEMP'] );
        }

        // Detect by creating a temporary file
        else
        {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            }
            else
            {
                return FALSE;
            }
        }
    }
}

/**
*
*    create a Comma Seperated Values line
*
*    @param     pointer     pointer to the open file
*    @param     array       array of data
*    @param     string      single character deliminator between fields
*    @param     string      single character field enclosure
*
*    @return    int         bytes written to file
*
*/
if (!function_exists('fputcsv'))
{
    function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"')
    {

        // Sanity Check
        if (!is_resource($handle))
        {
            trigger_error('fputcsv() expects parameter 1 to be resource, ' .
                gettype($handle) . ' given', E_USER_WARNING);
            return false;
        }

        if ($delimiter!=NULL)
        {
            if( strlen($delimiter) < 1 )
            {
                trigger_error('delimiter must be a character', E_USER_WARNING);
                return false;
            }
            elseif( strlen($delimiter) > 1 )
            {
                trigger_error('delimiter must be a single character', E_USER_NOTICE);
            }

            /* use first character from string */
            $delimiter = $delimiter[0];
        }

        if( $enclosure!=NULL )
        {
            if( strlen($enclosure) < 1 )
            {
                trigger_error('enclosure must be a character', E_USER_WARNING);
                return false;
            }
            elseif( strlen($enclosure) > 1 )
            {
                trigger_error('enclosure must be a single character', E_USER_NOTICE);
            }

            /* use first character from string */
            $enclosure = $enclosure[0];
        }

        $i = 0;
        $csvline = '';
        $escape_char = '\\';
        $field_cnt = count($fields);
        $enc_is_quote = in_array($enclosure, array('"',"'"));
        reset($fields);

        foreach( $fields AS $field )
        {

            /* enclose a field that contains a delimiter, an enclosure character, or a newline */
            if( is_string($field) && (
                strpos($field, $delimiter)!==false ||
                strpos($field, $enclosure)!==false ||
                strpos($field, $escape_char)!==false ||
                strpos($field, "\n")!==false ||
                strpos($field, "\r")!==false ||
                strpos($field, "\t")!==false ||
                strpos($field, ' ')!==false ) )
            {

                $field_len = strlen($field);
                $escaped = 0;

                $csvline .= $enclosure;
                for( $ch = 0; $ch < $field_len; $ch++ )
                {
                    if( $field[$ch] == $escape_char && $field[$ch+1] == $enclosure && $enc_is_quote )
                    {
                        continue;
                    }
                    elseif( $field[$ch] == $escape_char )
                    {
                        $escaped = 1;
                    }
                    elseif( !$escaped && $field[$ch] == $enclosure )
                    {
                        $csvline .= $enclosure;
                    }
                    else
                    {
                        $escaped = 0;
                    }
                    $csvline .= $field[$ch];
                }
                $csvline .= $enclosure;
            }
            else
            {
                $csvline .= $field;
            }

            $i++;

            if( $i != $field_cnt )
            {
                $csvline .= $delimiter;
            }
        }

        $csvline .= "\n";

        return fwrite($handle, $csvline);
    }
}



function fputmscsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"')
{

    // Sanity Check
    if (!is_resource($handle))
    {
        trigger_error('fputcsv() expects parameter 1 to be resource, ' .
            gettype($handle) . ' given', E_USER_WARNING);
        return false;
    }

    if ($delimiter!=NULL)
    {
        if( strlen($delimiter) < 1 )
        {
            trigger_error('delimiter must be a character', E_USER_WARNING);
            return false;
        }
        elseif( strlen($delimiter) > 1 )
        {
            trigger_error('delimiter must be a single character', E_USER_NOTICE);
        }

        /* use first character from string */
        $delimiter = $delimiter[0];
    }

    if( $enclosure!=NULL )
    {
        if( strlen($enclosure) < 1 )
        {
            trigger_error('enclosure must be a character', E_USER_WARNING);
            return false;
        }
        elseif( strlen($enclosure) > 1 )
        {
            trigger_error('enclosure must be a single character', E_USER_NOTICE);
        }

        /* use first character from string */
        $enclosure = $enclosure[0];
    }

    $i = 0;
    $csvline = '';
    $escape_char = '\\';
    $field_cnt = count($fields);
    $enc_is_quote = in_array($enclosure, array('"',"'"));
    reset($fields);

    foreach( $fields AS $field )
    {
        /* enclose EVERY field */
        $field_len = strlen($field);
        $escaped = 0;

        $csvline .= $enclosure;

        for( $ch = 0; $ch < $field_len; $ch++ )
        {
            if( $field[$ch] == $escape_char && $field[$ch+1] == $enclosure && $enc_is_quote )
            {
                continue;
            }
            elseif( $field[$ch] == $escape_char )
            {
                $escaped = 1;
            }
            elseif( !$escaped && $field[$ch] == $enclosure )
            {
                $csvline .= $enclosure;
            }
            else
            {
                $escaped = 0;
            }
            $csvline .= $field[$ch];
        }

        $csvline .= $enclosure;

        $i++;

        if( $i != $field_cnt )
        {
            $csvline .= $delimiter;
        }
    }

    $csvline .= "\n";

    return fwrite($handle, $csvline);
}


/******************************************************************************************************************
RSS PARSING FUNCTION
******************************************************************************************************************/

//FUNCTION TO PARSE RSS IN PHP 4 OR PHP 4
function parseRSS($string)
{
    //PARSE RSS FEED
    $parser = xml_parser_create();
    xml_parse_into_struct($parser, $string, $valueals, $index);
    xml_parser_free($parser);

    #return $valueals;

    //CONSTRUCT ARRAY
    foreach($valueals as $keyey => $valueal)
    {
        if($valueal['type'] != 'cdata')
        {
            $item[$keyey] = $valueal;
        }
    }

    $i = 0;

    foreach($item as $key => $value)
    {
        if($value['type'] == 'open')
        {
            $i++;
            $itemame[$i] = strtolower($value['tag']);

        }
        elseif($value['type'] == 'close')
        {
            $feed = $values[$i];
            $item = $itemame[$i];
            $i--;

            if(count($values[$i])>1)
            {
                $values[$i][$item][] = $feed;
            }
            else
            {
                $values[$i][$item] = $feed;
            }

        }
        else
        {
            $values[$i][strtolower($value['tag'])] = $value['value'];
        }
    }

    //RETURN ARRAY VALUES
    return $values[0];
}
