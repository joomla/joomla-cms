<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
defined('_JEXEC') or die('Restricted access');

# Compares versions of software

function check_version($currentversion, $requiredversion)
{
   list($majorC, $minorC, $editC) = preg_split('/[\/.-]/', $currentversion);
   list($majorR, $minorR, $editR) = preg_split('/[\/.-]/', $requiredversion);

   if ($majorC > $majorR) return true;
   if ($majorC < $majorR) return false;
   if ($minorC > $minorR) return true;
   if ($minorC < $minorR) return false;
   if ($editC  >= $editR)  return true;
   return true;
}


class Qvalent_PayWayAPI
{
    var $url;
    var $logDirectory;
    var $proxyHost;
    var $proxyPort;
    var $proxyUser;
    var $proxyPassword;
    var $certFileName;
    var $initialised;
    var $caFile;

    function Qvalent_PayWayAPI()
    {
        $this->url = NULL;
        $this->logDirectory = NULL;
        $this->initialised = false;
    }

    function isInitialised()
    {
        return $this->initialised;
    }

    function initialise( $parameters )
    {
		if ( $this->initialised == true )
        {
            trigger_error("This client object has already been initialised", E_USER_ERROR);
        }

        $props = $this->parseResponseParameters( $parameters );

        if ( !array_key_exists( 'logDirectory', $props ) )
        {
            $this->handleInitialisationFailure( "Check initialisation parameters " .
                "(logDirectory) - You must specify the log directory" );
        }
        if ( !array_key_exists( 'url', $props ) )
        {
            $props[ 'url' ] = "https://ccapi.client.qvalent.com/payway/ccapi";
        }
        if ( !array_key_exists( 'certificateFile', $props ) )
        {
            $this->handleInitialisationFailure( "Check initialisation parameters " .
                "(certificateFile) - You must specify the certificate file" );
        }
        if ( !array_key_exists( 'caFile', $props ) )
        {
            $this->handleInitialisationFailure( "Check initialisation parameters " .
                "(caFile) - You must specify the Certificate Authority file" );
        }
        if ( !array_key_exists( 'socketTimeout', $props ) )
        {
            $props[ 'socketTimeout' ] = '60000';
        }

        $logDir = $props[ 'logDirectory' ];
        if ( !file_exists( $logDir ) )
        {
            mkdir( $logDir, 0700, true );
        }
        if ( !file_exists( $logDir ) || !is_dir( $logDir ) )
        {
            $this->handleInitialisationFailure( 
                "Cannot use logging directory '" .  $logDir . "'" );
        }
        $this->logDirectory = $logDir;

        $this->_log( "<Init> Initialising PayWay API Client" );
        $this->_log( "<Init> Using PHP version " . phpversion() );
        $extensions = get_loaded_extensions();
        foreach( $extensions as $extension )
        {
            $this->_log( "<Init> Loaded extension " . $extension );
        }

        if ( !is_numeric( $this->_getProperty( $props, "socketTimeout" ) ) )
        {
            $this->handleInitialisationFailure( "Specified socket timeout '" . 
                $this->_getProperty( $props, "socketTimeout" ) . "' is not a number: " );
        }

        $this->url = $this->_getProperty( $props, "url" );
        $this->socketTimeout = (int)$this->_getProperty( $props, "socketTimeout" );

        $this->_log( "<Init> URL = " . $this->url );
        $this->_log( "<Init> socketTimeout = " . $this->socketTimeout . "ms" );

        $this->proxyHost = $this->_getProperty( $props, "proxyHost" );
        $this->proxyPort = $this->_getProperty( $props, "proxyPort" );
        $this->proxyUser = $this->_getProperty( $props, "proxyUser" );
        $this->proxyPassword = $this->_getProperty( $props, "proxyPassword" );
        if ( !is_null( $this->proxyHost ) && !is_null( $this->proxyPort ) )
        {
            $this->_log( "<Init> proxy = " . $this->proxyHost . ":" . $this->proxyPort );

            if ( !is_numeric( $this->proxyPort ) )
            {
                $this->handleInitialisationFailure( "Specified proxy port '" . 
                    $this->proxyPort . "' is not a number: " );
            }

            if ( !is_null( $this->proxyUser ) )
            {
                $this->_log( "<Init> proxyUser = " . $this->proxyUser );
            }
            if ( !is_null( $this->proxyPassword ) )
            {
                $this->_log( "<Init> proxyPassword = " . 
                    $this->_getStarString( strlen( $this->proxyPassword ) ) );
            }
        }

        $this->certFileName = $this->_getProperty( $props, "certificateFile" );
        $this->_log( "<Init> Loading certificate from file " . $this->certFileName );
        if ( !file_exists( $this->certFileName ) )
        {
            $this->handleInitialisationFailure( 
                "Certificate file does not exist: " . $this->certFileName );
        }
        if ( $this->_readFile( $this->certFileName ) == NULL )
        {
            $this->handleInitialisationFailure( 
                "Certificate file cannot be read: " . $this->certFileName );
        }
        $cert = openssl_x509_parse( $this->_readFile( $this->certFileName ) );
        $this->_log( "<Init> Certificate serial number: " . strtoupper(dechex($cert['serialNumber'])) );
        $this->_log( "<Init> Certificate valid to: " . date('d-M-Y H:i:s', $cert['validTo_time_t'] ) );

        $this->caFile = $this->_getProperty( $props, "caFile" );
        $this->_log( "<Init> Loading CA certificates from file " . $this->caFile );
        if ( !file_exists( $this->caFile ) )
        {
            $this->handleInitialisationFailure( 
                "Certificate Authority file does not exist: " . $this->caFile );
        }
        if( $this->_readFile( $this->caFile ) == NULL )
        {
            $this->handleInitialisationFailure( 
                "CA file cannot be read: " . $this->caFile );
        }

        $this->initialised = true;
        $this->_log( "<Init> Initialisation complete" );
    }

    function handleInitialisationFailure( $message )
    {
        if ( !is_null( $this->logDirectory ) )
        {
            $this->_log( "<Init> PayWay API Client initialisation failed: " . $message );
        }

        trigger_error( "PayWay API Client initialisation failed: " . $message, E_USER_ERROR );
    }

	function parseResponseParameters( $parametersString )
    {
        $parameterArray = explode( "&", $parametersString );
        $props = array();

        foreach ( $parameterArray as $parameter )
        {
            list( $paramName, $paramValue ) = explode( "=", $parameter );
            $props[ urldecode( $paramName ) ] = urldecode( $paramValue );
        }
        return $props;
    }

     function formatRequestParameters( $parametersArray )
    {
        $parametersString = '';
        foreach ( $parametersArray as $paramName => $paramValue )
        {
            if ( $parametersString != '' )
            {
                $parametersString = $parametersString . '&';
            }  
            $parametersString = $parametersString . urlencode($paramName) . '=' . urlencode($paramValue);
        }
        return $parametersString;
    }

    function processCreditCard( $requestText )
    {
        if ( $this->initialised == false )
        {
            return $this->_getResponseString( "3", "QA", "This client has not been initialised!" );
        }

        $orderNumber = $this->_getOrderNumber( $requestText );

        $ch = curl_init( $this->url );
        curl_setopt( $ch, CURLOPT_POST,true );
        curl_setopt( $ch, CURLOPT_FAILONERROR, true );
        curl_setopt( $ch, CURLOPT_FORBID_REUSE, true );
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        if ( !is_null( $this->proxyHost ) && !is_null( $this->proxyPort ) )
        {
            curl_setopt( $ch, CURLOPT_HTTPPROXYTUNNEL, true );
            curl_setopt( $ch, CURLOPT_PROXY, $this->proxyHost . ":" . $this->proxyPort );
            if ( !is_null( $this->proxyUser ) )
            {
                if ( is_null( $this->proxyPassword ) )
                {
                    curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $this->proxyUser . ":" );
                }
                else
                {
                    curl_setopt( $ch, CURLOPT_PROXYUSERPWD, 
                        $this->proxyUser . ":" . $this->proxyPassword );
                }
            }
        }

        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->socketTimeout / 1000 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $this->socketTimeout / 1000 );

        curl_setopt( $ch, CURLOPT_SSLCERT, $this->certFileName );
        curl_setopt( $ch, CURLOPT_CAINFO, $this->caFile );

        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );   

        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestText );

        $this->_log( "<Request>  " . $orderNumber . " " .
            $this->_getMessageForLogging( $requestText ) );
        $responseText = curl_exec($ch);
        $errorNumber = curl_errno( $ch );
        if ( $errorNumber != 0 )
        {
            $responseText = $this->_getResponseString( "2", "QI", "Transaction " .
                "Incomplete - contact your acquiring bank to confirm reconciliation" );
            $this->_log( "<Response> " .$orderNumber . " ERROR during processing: " . 
                $this->_getMessageForLogging( $responseText ) .
                "\r\n  Error Number: " . $errorNumber . ", Description: '" . 
                curl_error( $ch ) . "'" );
        }
        else
        {
            $this->_log( "<Response> " . $orderNumber . " " .
                $this->_getMessageForLogging( $responseText ) );
        }

        curl_close( $ch );

        return $responseText;
    }

    function _getOrderNumber( $message )
    {
        $parameters = $this->parseResponseParameters( $message );

        return $parameters[ "customer.orderNumber" ];
    }

    function _getResponseString( $summaryCode, $responseCode, $responseText )
    {
        return "response.summaryCode=" . $summaryCode . 
            "&response.responseCode=" . $responseCode .
            "&response.text=" . $responseText .
            "&response.transactionDate=" . 
            strtoupper( date( "d-M-y H:i:s" ) );
    }

    function _getProperty( $props, $name )
    {
        if ( array_key_exists( $name, $props ) )
        {
            return $props[$name];
        }
        else
        {
            return NULL;
        }
    }

     function _log( $message )
    {
        list($usec, $sec) = explode(" ", microtime());
        $dtime = date( "Y-m-d H:i:s." . sprintf( "%03d", (int)(1000 * $usec) ), $sec );
        $entry_line = $dtime . " " . $message . "\r\n"; 
        $filename = $this->logDirectory . "/" . "ccapi_" . date( "Ymd" ) . ".log";
        $fp = fopen( $filename, "a" ); 
        fputs( $fp, $entry_line ); 
        fclose( $fp );
    }

    function _getMessageForLogging( $message )
    {
        $parameters = $this->parseResponseParameters( $message );

        if ( array_key_exists( "card.PAN", $parameters ) )
        {
            $card = $parameters[ "card.PAN" ];
            $parameters[ "card.PAN" ] = 
                $this->_formatCardNumberForDisplay( $card );
        }

        if ( array_key_exists( "card.CVN", $parameters ) )
        {
            $cvn = $parameters[ "card.CVN" ];
            $parameters[ "card.CVN" ] = 
                $this->_getStarString( strlen( $cvn ) );
        }

        if ( array_key_exists( "card.expiryMonth", $parameters ) )
        {
            $expiryMonth = $parameters[ "card.expiryMonth" ];
            $parameters[ "card.expiryMonth" ] = 
                $this->_getStarString( strlen( $expiryMonth ) );
        }

        if ( array_key_exists( "card.expiryYear", $parameters ) )
        {
            $expiryYear = $parameters[ "card.expiryYear" ];
            $parameters[ "card.expiryYear" ] = 
                $this->_getStarString( strlen( $expiryYear ) );
        }

        if ( array_key_exists( "customer.password", $parameters ) )
        {
            $customerPassword = $parameters[ "customer.password" ];
            $parameters[ "customer.password" ] = 
                $this->_getStarString( strlen( $customerPassword ) );
        }

        $logMessage = '';
        foreach ( $parameters as $paramName => $paramValue )
        {
            $logMessage = $logMessage . $paramName . '=' . $paramValue . ';';
        }
        return $logMessage;
    }

    function _formatCardNumberForDisplay( $cardNumber )
    {
        if ( is_null( $cardNumber ) )
        {
            return NULL;
        }

        $formattedCardNumber = '';
        if ( strlen( $cardNumber ) >= 16 )
        {
            $formattedCardNumber = substr( $cardNumber, 0, 6 ) . "..." . 
                substr( $cardNumber, -3 );
        }
        else if ( strlen( $cardNumber ) >= 14 )
        {
            $formattedCardNumber = substr( $cardNumber, 0, 4 ) . "..." . 
                substr( $cardNumber, -3 );
        }
        else
        {
            $formattedCardNumber = $cardNumber;
        }
        return $formattedCardNumber;
    }

    function _getStarString( $length )
    {
        $buf = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            $buf = $buf . '*';
        }
        return $buf;
    }
    function _readFile( $file )
	{
        $reader = fopen( $file, "r" );
        if( !$reader )
        {
          return NULL;
        }
        $data = fread( $reader, 8192 );
        fclose( $reader );
        return $data;
	}
}
?>
