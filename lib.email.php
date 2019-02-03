<?php

/******************************************************************************
*
*   Email Functions Library
*
******************************************************************************/

// include the network function library, a couple functions require bridge functions in it
include_once('lib.network.php');

/**
*
*   Simple mailer function
*
*   @author     DigitalFeonix
*   @param      string      $to         email address of recepient
*   @param      string      $from       email address of "sender"
*   @param      string      $subject    subject of email
*   @param      string      $body       message body to be sent
*   @param      string      $bcc        bcc recepient
*
*   @return     bool                    email sent successfully
*
*/
function send_email($to, $from, $subject, $body, $bcc='')
{
    $mail_sent = false;

    $headers    = array();
    $headers[]  = 'From: ' . $from;

    if ($bcc != '')
    {
        $headers[] = 'Bcc: ' . $bcc;
    }

    // simplify the addresses for Windows
    if (preg_match('/^win/i',PHP_OS))
    {
        if (preg_match('/<(.*@.*)>/',$to,$matches))
        {
            $to = $matches[1];
        }
    }

    //send the email
    if (trim($body) != '')
    {
        if (preg_match('/<(.*@.*)>/',$from,$matches))
        {
            $envelope = $matches[1];
        }
        else
        {
            $envelope = $from;
        }

        $mail_sent = mail($to, $subject, $body, implode("\r\n",$headers),'-f'.$envelope);
    }

    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
    return $mail_sent;
}

/**
*
*   Multipart email (html+text) function
*
*   @author     DigitalFeonix
*   @param      string      $to         email address of recepient
*   @param      string      $from       email address of "sender"
*   @param      string      $subject    subject of email
*   @param      string      $text_part  text message body to be sent
*   @param      string      $html_part  optional html message body to be sent
*
*   @return     bool                    email sent successfully
*
*/
function send_multipart_email($to,$from,$subject,$text_part,$html_part='',$bcc='')
{
    $mail_sent = false;

    //define the headers we want passed. Note that they are separated with \r\n
    $headers    = array();
    $headers[]  = 'From: '.$from;

    if ($bcc != '')
    {
        $headers[] = 'Bcc: ' . $bcc;
    }

    //define the body of the message.
    if (trim($html_part) == '')
    {
        $body = $text_part;
    }
    else
    {
        //create a boundary string. It must be unique
        //so we use the MD5 algorithm to generate a random hash
        $random_hash = md5(date('r', time()));

        $headers[]  = 'Content-Type: multipart/alternative; boundary="PHP-alt-'.$random_hash.'"';

        $body  = '--PHP-alt-'.$random_hash."\n";
        $body .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
        $body .= 'Content-Transfer-Encoding: 7bit'."\n\n";

        $body .= $text_part."\n\n";

        $body .= '--PHP-alt-'.$random_hash."\n";
        $body .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
        $body .= 'Content-Transfer-Encoding: 7bit'."\n\n";

        $body .= $html_part."\n\n";

        $body .= '--PHP-alt-'.$random_hash.'--'."\n";
    }

    //send the email
    if (trim($body) != '')
    {
        if (preg_match('/<(.*@.*)>/',$from,$matches))
        {
            $envelope = $matches[1];
        }
        else
        {
            $envelope = $from;
        }

        $mail_sent = mail($to, $subject, $body, implode("\r\n",$headers),'-f'.$envelope);
    }

    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
    return $mail_sent;
}

/**
*
*   Multipart related email (html+images+text) function
*
*   @author     DigitalFeonix
*   @param      string      $to             email address of recepient
*   @param      string      $from           email address of "sender"
*   @param      string      $subject        subject of email
*   @param      string      $text_part      text message body to be sent
*   @param      string      $html_part      optional html message body to be sent
*   @param      array       $attachments    array of files to attach (mime,name[fullpath])
*
*   @return     bool                        email sent successfully
*
*/
function send_multipart_related_email($to,$from,$subject,$text_part,$html_part='',$attachments=array(),$bcc='')
{
    $mail_sent = false;

    //define the headers we want passed. Note that they are separated with \r\n
    $headers    = array();
    $headers[]  = 'From: '.$from;
    if ($bcc != '')
    {
        $headers[] = 'Bcc: ' . $bcc;
    }

    //define the body of the message.
    if (trim($html_part) == '')
    {
        $body = $text_part;
    }
    else
    {
        //create a boundary string. It must be unique
        //so we use the MD5 algorithm to generate a random hash
        $random_hash    = md5(date('r', time()));
        $random_hash2   = md5(uniqid('', true));
        $cid            = substr(md5(uniqid('', true)),0,8).'.'.substr(md5(uniqid('', true)),-8);

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/related; type="multipart/alternative"; boundary="PHP-alt-'.$random_hash.'"';

        $embed = '';

        foreach($attachments as $file)
        {
            $html_part = preg_replace('/'.addslashes(basename($file['name'])).'/i','cid:'.basename($file['name']).'@'.$cid,$html_part);

            $embed .= '--PHP-alt-'.$random_hash."\n";

            $embed .= 'Content-Type: '.$file['mime'].'; name="'.basename($file['name']).'"'."\n";
            $embed .= 'Content-Transfer-Encoding: base64'."\n";
            $embed .= 'Content-ID: <'.basename($file['name']).'@'.$cid.'>'."\n";
            $embed .= 'Content-Description: '.basename($file['name']).''."\n";
            $embed .= 'Content-Location: '.basename($file['name']).''."\n\n";

            $embed .= chunk_split(base64_encode(file_get_contents($file['name'])), 76, "\n");
            $embed .= "\n";
        }

        $body  = 'This is a multi-part message in MIME format.'."\n\n";

        $body .= '--PHP-alt-'.$random_hash."\n";

        $body .= 'Content-Type: multipart/alternative; boundary="PHP-alt-'.$random_hash2.'"'."\n\n";

        $body .= '--PHP-alt-'.$random_hash2."\n";
        $body .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n";
        $body .= 'Content-Transfer-Encoding: 7bit'."\n\n";

        $body .= $text_part."\n\n";

        $body .= '--PHP-alt-'.$random_hash2."\n";
        $body .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
        $body .= 'Content-Transfer-Encoding: 7bit'."\n\n";

        $body .= $html_part."\n\n";

        $body .= '--PHP-alt-'.$random_hash2.'--'."\n\n";

        $body .= $embed;

        $body .= '--PHP-alt-'.$random_hash.'--'."\n";
    }

    //send the email
    if (trim($body) != '')
    {
        if (preg_match('/<(.*@.*)>/',$from,$matches))
        {
            $envelope = $matches[1];
        }
        else
        {
            $envelope = $from;
        }

        $mail_sent = mail($to, $subject, $body, implode("\r\n",$headers),'-f'.$envelope);
    }

    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
    return $mail_sent;
}

/**
*
*   Text email w/ attachments (text+attachments) function
*
*   @author     DigitalFeonix
*   @param      string      $to             email address of recepient
*   @param      string      $from           email address of "sender"
*   @param      string      $subject        subject of email
*   @param      string      $text_part      text message body to be sent
*   @param      array       $attachments    array of files to attach (mime,name,path[fullpath])
*
*   @return     bool                        email sent successfully
*
*/
function send_email_text_attachment($to,$from,$subject,$text_part,$attachments=array(),$bcc='')
{
    /*******************************************************************************
        Simple text email with attachment
    *******************************************************************************/
    $random_hash    = md5(date('r', time()));

    $headers   = array();
    $headers[] = 'From: '.$from;
    if ($bcc != '')
    {
        $headers[] = 'Bcc: ' . $bcc;
    }
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/mixed; boundary="DigiMail-'.$random_hash.'"';

    $body  = 'This is a multi-part message in MIME format.'."\n";

    $body .= '--DigiMail-'.$random_hash."\n";
    $body .= 'Content-Type: text/plain'."\n";
    $body .= 'Content-Transfer-Encoding: 7bit'."\n\n";

    $body .= $text_part."\n\n";

    foreach($attachments as $file)
    {
        $body .= '--DigiMail-'.$random_hash."\n";

        $body .= 'Content-Type: '.$file['mime'].'; name="'.basename($file['name']).'"'."\n";
        $body .= 'Content-Transfer-Encoding: base64'."\n";
        $body .= 'Content-Disposition: inline; filename="'.basename($file['name']).'"'."\n\n";

        $body .= chunk_split(base64_encode(file_get_contents($file['path'])), 76, "\n");
        $body .= "\n";
    }

    $body .= '--DigiMail-'.$random_hash.'--'."\n";

    /******************************************************************************/

    if (preg_match('/<(.*@.*)>/',$from,$matches))
    {
        $envelope = $matches[1];
    }
    else
    {
        $envelope = $from;
    }

    $mail_sent = mail($to, $subject, $body, implode("\r\n",$headers),'-f'.$envelope);

    return $mail_sent;
}

/**
*
*   Merges a form letter text file with data and returns the result
*
*   @author     DigitalFeonix
*   @param      string      fullpath of text file to merge with
*   @param      array       array of data to merge
*
*   @return     string      merged text
*
*/
function form_letter_merge($file, $data)
{
    $form_letter    = file_get_contents($file);

    foreach ($data as $key => $value)
    {
        if (is_array($value))
        {
            foreach ($value as $k => $v)
            {
                $form_letter = str_ireplace('{{'.$key.'::'.$k.'}}',html_entity_decode($v),$form_letter);
            }
        }
        else
        {
            $form_letter = str_ireplace('{{'.$key.'}}',html_entity_decode($value),$form_letter);
        }
    }

    return $form_letter;
}

/**
*
*   Get the mxhost for a given email address
*
*   @author     DigitalFeonix
*   @param      string      email address to check
*
*   @return     string      mxhost for email
*
*/
function get_mxhost($email)
{
    list($user,$host) = split('@',$email);

    $records = getmxrr($host,$mxrecords,$mxweights);

    if (!is_array($mxrecords))
    {
        return false;
    }
    else
    {
        return $mxrecords[0];
    }
}

/**
*
*   Checks the return code from direct SMTP commands
*
*   @author     DigitalFeonix
*   @param      array       array of success code for this command
*   @param      pointer     SMTP connection (socket) pointer
*
*   @return     string      code returned from server
*
*/
function check_return_code($accept,&$smtp_conn)
{
    $smtp_ret   = @fgets($smtp_conn,515);
    $code       = substr($smtp_ret,0,3);

    if (in_array($code,$accept))
    {
        return true;
    }

    return array($code => substr($smtp_ret,4));
}

/**
*
*   Send email directly to end users mail server
*
*   @author     DigitalFeonix
*   @param      string      recepient email address
*   @param      string      sender email address
*   @param      array       array of additional header/values (including Subject)
*   @param      string      body of the email to send
*
*   @return     varies      false on failure
*
*   @required   get_mxhost()
*   @required   check_return_code()
*
*/
function send_direct($to,$from,$headers,$body)
{
    $host   = get_mxhost($to);
    $host   = '192.168.2.3';
    $port   = 25;
    $errno  = '';
    $errstr = '';
    $tval   = 5;
    $clrf   = "\r\n";
    $htmlf  = "<br />\n";

    if ($host === false)
    {
        return false;
    }

    echo 'connecting to ',$host,"\n";

    // open connection, but suppress error printing
    $smtp = @fsockopen($host,$port,$errno,$errstr,$tval);

    if ($smtp === false)
    {
        echo 'failed connection: '.$errno.' '.$errstr;
        return false;
    }
    $rply = check_return_code(array('220'),$smtp);

    list($user,$host) = split('@',$from);

    // helo [mail.example.com]
    $smtp_helo = 'helo mailserver.example.com';
    fputs($smtp,$smtp_helo.$clrf);
    $rply_helo = check_return_code(array('250'),$smtp);

    // check accepting helo
    if ($rply_helo !== true)
    {
        echo 'HELO FAILED';
        print_r($rply_helo);
        fclose($smtp);
        return false;
    }

    // mail from:<sender@example.com>
    $smtp_mail = 'mail from:<'.$from.'>';
    fputs($smtp,$smtp_mail.$clrf);
    $rply_mail = check_return_code(array('250'),$smtp);

    // check for sender okay
    if ($rply_mail !== true)
    {
        echo 'MAIL not accepted by server';
        fclose($smtp);
        return false;
    }

    // rcpt to:<recipient@example.com>
    $smtp_rcpt = 'rcpt to:<'.$to.'>';
    fputs($smtp,$smtp_rcpt.$clrf);
    $rply_rcpt = check_return_code(array('250'),$smtp);

    // check for bad recipient here
    if ($rply_rcpt !== true)
    {
        # 452,552 mailbox full/over quota
        # 550 mailbox not found

        echo 'RCPT not accepted by server';
        fclose($smtp);
        return false;
    }

    // data
    fputs($smtp,'data'.$clrf);
    $rply_data = check_return_code(array('354'),$smtp);

    // check for data go ahead
    if ($rply_data !== true)
    {
        echo 'DATA command not accepted by server';
        fclose($smtp);
        return false;
    }

    if (is_array($headers) && !empty($headers))
    {
        foreach($headers as $header => $value)
        {
            // example:
            // To: "Robbie Williams" <robbie@dj.co.uk>
            // From: "Mr DJ" <mister.deejay@dj.co.uk>
            // BCC: "Dick Clark" <dick.clark@timesquare.ny.us>
            // Subject: Put the music on
            // X-Mailer: PHP/4.30
            fputs($smtp,$header.': '.$value.$clrf);
        }

        // blank line between headers and body
        fputs($smtp,$clrf);
    }

    // send the body of the email
    fputs($smtp,$body.$clrf);
    // end the email body
    fputs($smtp,'.'.$clrf);
    $rply_send = check_return_code(array('250'),$smtp);

    // check for acceptance of mail
    if ($rply_send !== true)
    {
        echo 'DATA not accepted by server';
        fclose($smtp);
        return false;
    }

    // finished
    fputs($smtp,'quit'.$clrf);
    // close out
    fclose($smtp);
}

