<?php

// Class has been written for PHP4, skip PHP5 E_STRICT notices
error_reporting( error_reporting() & ~ E_STRICT );

// Email address to check
$email='anne@mattila.eu';

// Domain name to use in 'HELO' statement during recipient address availability check
$sender_host='joomla.org';

// Validation level:
//    0   No validation.
//    1   Well-formness check.
//    2   Hostname (or DNS record, if Hostname failed) resolution (cross-platform).
//    3   Recipient account availability check (violates RFC, use with care!). However, it works on some environments.
$validation_level=3;

// Check email address
$result=EmailChecker::checkEmail($email, 3, $sender_host);

if ($result) {
  echo "Email address ".htmlspecialchars($email)." is valid";
} else {
  echo "Email address ".htmlspecialchars($email)." is NOT valid";
}


/**
* This static class contains methods for email address validation
* @author  Kanstantin Reznichak
* @static
*/
class EmailChecker {

  /**
   * E-Mail address validator
   * @param   string    $email        E-Mail address
   * @param   int       $level        Validation level
   *                                     Value     Description
   *                                      0         No validation
   *                                      1         Well-formness check
   *                                      2         Hostname (or DNS record, if Hostname failed) resolution
   *                                      3         Recipient account availability check (violates RFC, use with care!)
   * @param   string    $sender_host  Domain name to use in 'HELO' statement during recipient address availability check
   * @return  boolean TRUE if email address is valid or FALSE if not
   */
  function checkEmail($email='', $level=1, $sender_host='joomla.org') {
    $valid=false;
    $email=trim($email);
    if ($email!='') {
      $valid=true;
      if ($level>=1) {
        // Well-formness check
        $valid=(boolean)ereg('^([a-zA-Z0-9]+[\._-]?[a-zA-Z0-9]+)+@([a-zA-Z0-9]+-?[a-zA-Z0-9]+\.)+([a-zA-Z]{2,4})$', $email);
        if ($valid && $level>=2) {
          // Hostname (or DNS record, if Hostname failed) resolution
          $hostname=strtolower(substr($email, strpos($email, '@')+1));
          $host=gethostbyname($hostname);
          if ($host==$hostname) {
            $host='';
          }
          if ($host=='') {
            // Hostname resolutiion failed
            // Check DNS record
            $valid=EmailChecker::checkDNS_record($hostname);
          } else {
            $valid=true;
          }
          if ($valid && $level>=3) {
            // Recipient account availability check
            $valid=false;
            // Get MX records
            $ips=EmailChecker::getMXRecords($hostname);
            if (empty($ips)) {
              // No MX records found. Using Hostname.
              $ips=gethostbynamel($hostname);
            }
            // Trying to open connection
            $conn=false;
            foreach ($ips as $ip) {
              $conn=null;
              $errno=null;
              $errstr=null;
              if (EmailChecker::connectHost($conn, $errno, $errstr, $ip, 10)) {
                // Connection opened
                break;
              }
            }
            if (!empty($conn)) {
              $line='';
              // Gest SMTP server signature
              if (EmailChecker::readLastLineConn($conn, $line)) {
                if (220===EmailChecker::getStatus($line)) {
                  // Send 'HELO' command
                  if (EmailChecker::writeDataConn($conn, "HELO $sender_host\r\n")) {
                    // Get an answer
                    if (EmailChecker::readLastLineConn($conn, $line)) {
                      // Check response status
                      if (250===EmailChecker::getStatus($line)) {
                        // Start email conversation
                        if (EmailChecker::writeDataConn($conn, "MAIL FROM: <test@$sender_host>\r\n")) {
                          // Get an answer
                          if (EmailChecker::readLastLineConn($conn, $line)) {
                            // Check response status
                            if (250===EmailChecker::getStatus($line)) {
                              // Specify recipient mailbox
                              if (EmailChecker::writeDataConn($conn, "RCPT TO: <$email>\r\n")) {
                                // Get an answer
                                if (EmailChecker::readLastLineConn($conn, $line)) {
                                  // Status 250: mailbox exists :)
                                  $valid=250===EmailChecker::getStatus($line);
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    return $valid;
  }


  /**
   * Check if are any DNS records corresponding to a given Internet host name or IP address
   * @param   string  $hostname   Host name or IP address
   * @return  boolean  TRUE if any records are found or FALSE if no records were found or if an error occurred
   */
  function checkDNS_record($hostname='') {
    $result=false;
    $hostname=strtolower(trim($hostname));
    if ($hostname!='') {
      if (function_exists('checkdnsrr')) {
        // Non-Windows platform
        $result=checkdnsrr($hostname, 'ANY');
      } else {
        // Windows platform
        $output=null;
        @exec('nslookup.exe -type=ANY '.$hostname, $output);
        if (!empty($output)) {
          foreach ($output as $line) {
            if (0===strpos(strtolower($line), $hostname)) {
              // DNS record found
              $result=true;
              break;
            }
          }
        }
      }
    }
    return $result;
  }


  /**
   * Get MX records as IP addresses corresponding to a given.
   * This method is OS-Safe (works on Unix and Windows platforms).
   * @param   string  $hostname   Host name
   * @return  array   Array with IP addresses, sorted by weight (RFC-Compliant).
   */
  function getMXRecords($hostname='') {
    $ips=array();
    if ($hostname!='') {
      $records=array();
      if (function_exists('getmxrr')) {
        // Non-Windows platform
        $mxhosts=null;
        $weights=null;
        if (false!==getmxrr($hostname, $mxhosts, $weights)) {
          // Sort MX records by weight
          $key_host=array();
          foreach ($mxhosts as $key=>$host) {
            if (!isset($key_host[$weights[$key]])) {
              $key_host[$weights[$key]]=array();
            }
            $key_host[$weights[$key]][]=$host;
          }
          unset($weights);
          $records=array();
          ksort($key_host);
          foreach ($key_host as $hosts) {
            foreach ($hosts as $host) {
              $records[]=$host;
            }
          }
        }
      } else {
        // Windows platform
        $result=shell_exec('nslookup.exe -type=MX '.$hostname);
        if ($result!='') {
          $matches=null;
          if (preg_match_all("'^.*MX preference = (\d{1,10}), mail exchanger = (.*)$'simU", $result, $matches)) {
            if (!empty($matches[2])) {
              array_shift($matches);
              array_multisort($matches[0], $matches[1]);
              $records=$matches[1];
            }
          }
        }
      }
    }
    // Resolve host names
    if (!empty($records)) {
      foreach ($records as $rec) {
        if ($resolved=gethostbynamel($rec)) {
          foreach ($resolved as $ip) {
            $ips[]=$ip;
          }
        }
      }
    }
    return $ips;
  }


  /**
   * Open socket connection to specified host
   * @param   resource  $conn       A reference to connection handler
   * @param   int       $errno      If an error occured: error number
   * @param   string    $errstr     If an error occured: error description
   * @param   string    $hostname   Host name or IP address
   * @param   int       $timeout    Connection timeout
   * @return  boolean
   */
  function connectHost(&$conn, &$errno, &$errstr, $host='', $timeout=30) {
    if ($host!='') {
      $errno=null;
      $errstr=null;
      $conn=@fsockopen(gethostbyname($host), 25, $errno, $errstr, $timeout);
      if (false===$conn || !is_resource($conn)) {
        $conn=null;
        $result=false;
      } else {
        $result=true;
      }
    }
    return $result;
  }


  /**
   * Reads line from a socket connection. Lines must end with CRLF sequence
   * @param   resource  $conn       A reference to connection handler
   * @param   string    $line       A reference to read line
   * @param   int       $limit      Line length limit
   * @return  boolean   TRUE on success or FALSE on error
   */
  function readLineConn(&$conn, &$line, $limit=65535) {
    $result=false;
    $line='';
    if (!empty($conn) && is_resource($conn)) {
      $char='';
      $last_char='';
      do{
        $last_char=$char;
        if (false===$char=fgetc($conn)) {
          break;
        } else {
          $line.=$char;
        }
      }while (($last_char.$char)!="\r\n" && $char!==false);
      if ($line!='') {
        $result=true;
      }
    }
    return $result;
  }


  /**
   * Reads the last line from a socket connection
   * @param   resource  $conn       A reference to connection handler
   * @param   string    $line       A reference to read line
   * @param   int       $limit      Line length limit
   * @return  boolean   TRUE on success or FALSE on error
   */
  function readLastLineConn(&$conn, &$line, $limit=65535) {
    $result=false;
    $line='';
    if (!empty($conn) && is_resource($conn) && !feof($conn)) {
      while (EmailChecker::readLineConn($conn, $line)) {
        if ($line=='') {
          break;
        } elseif (substr($line, 3, 1)==' ') {
          $result=true;
          break;
        }
      }
    }
    return $result;
  }


  /**
   * Parses status code from response line
   * @param   string    $line       Response line
   * @return  int   Status code
   */
  function getStatus($line='') {
    $status=0;
    if ($line!='') {
      $status=(int)substr($line, 0, strpos($line, ' '));
    }
    return $status;
  }


  /**
   * Send data to a socket connection
   * @param   resource  $conn       A reference to connection handler
   * @param   string    $data       Data to send
   * @return  boolean   TRUE on success or FALSE on error
   */
  function writeDataConn(&$conn, $data='') {
    $result=false;
    if (is_resource($conn)) {
      $result=fwrite($conn, $data);
    }
    return $result;
  }


}

?>