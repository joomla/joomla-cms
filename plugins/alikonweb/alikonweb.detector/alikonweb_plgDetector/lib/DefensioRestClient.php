<?php
require_once('exceptions.php');

class Defensio_REST_Client
{
    public $host; 
    public $http_version;
    public $use_sockets;

    public function __construct($host, $use_sockets = TRUE, $http_version = '1.0')
    {
        $this->host = $host;
        $this->http_version = $http_version;
        $this->use_sockets = $use_sockets;
    }

    /**
    * @param string $path The path in relation to $host
    * @param Array $data  an array of data ('key' => 'value') that will be posted into the server
    * @return Array an array with two elements ( status, data, header )
    */
    public function post($path, $data = Array())
    {
        return $this->do_request('POST', $path, $data);
    }

    /**
    * @param string $path The path in relation to $host
    * @param Array $data  an array of data ('key' => 'value') that will be put into the server
    * @return Array an array with three elements ( status, data, header )
    */
    public function put($path, $data = Array())
    {
        return $this->do_request('PUT', $path, $data);
    }

    /**
    * @param string $path The path in relation to $host /
    * @return Array an array with three elements ( status, data, header )
    */
    public function delete($path) 
    {
        return $this->do_request('DELETE', $path);
    }

    /**
    * @param string $path The path in relation to $host 
    * @return Array an array with three elements ( status, data, header )
    */
    public function get($path)
    {
        return $this->do_request('GET', $path);
    }

    /** 
    * Do actual HTTP requests in here, absctract from the rest of phpDefensio so we can easily change
    * the library or technique used as of now it uses a simple sockets implementation, and might throw 
    * an exception if no socket can be open 
    */
    private function do_request($verb, $path,  $data = Array(), $timeout = 10)
    {
        return $this->use_sockets ? $this->do_request_with_sockets($verb, $path, $data, $timeout) : $this->do_request_with_curl($verb, $path, $data, $timeout );
    }

    private function do_request_with_curl($verb, $path, $data, $timeout = NULL)
    {
        if($verb == 'DELETE')
            throw new Exception('DELETE not implemeted yet');

        $url  = $this->host . $path;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        // Output:
        // HTTP status, body, headers
        $out  = array(NULL, '', array());

        if ($verb == 'POST' || $verb == 'PUT'){

            if($verb == 'PUT')
                $data = array_merge(array('_method' => 'put'), $data);

            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if($response = curl_exec($curl)){
            $out[0] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $header_lenght = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $result = array(); 
            $result[2] = substr($response, 0, $header_lenght);
            $result[1] = substr($response, $header_lenght);
            $out[1] = $result[1];

            foreach( explode("\r\n", $result[0]) as  $v) {
                $header = explode(": ", $v);
                $out[2][$header[0]] = $header[1];
            }
        }

        curl_close($curl);
        return $out;
    }

    private function do_request_with_sockets($verb, $path,  $data = Array(), $conn_timeout)
    {
        $str_data = http_build_query($data);
        $url = parse_url('http://'. $this->host . $path);


        if (!isset($url['port']) || empty($url['port']) )
            $url['port'] = 80;

        $sock = fsockopen($url['host'], $url['port'], $errno, $errstr, $conn_timeout );

        if ($sock === FALSE){
            $msg = 'Impossible to open socket to ' . $url['host'] . ':' . $url['port'];

            if($errno == 110)
                $ex = new DefensioConnectionTimedout($msg);
            else
                $ex = new DefensioConnectionError($msg);

            $ex->error_code   = $errno;
            $ex->error_string = $errstr;

            throw $ex;
        }


        $target = $url['path'];

        if($url['query'])
            $target .= "?$url[query]";

        fputs($sock, "$verb $target HTTP/$this->http_version\r\n");
        fputs($sock, "Host: ". $url['host'] . "\r\n");

        if ($verb == 'POST' || $verb == 'PUT') {
            fputs($sock, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($sock, "Content-length: ". strlen($str_data) ."\r\n");
        }

        fputs($sock, "Accept: text/xml\r\n");
        fputs($sock, "Connection: close\r\n\r\n");

        if ($verb == 'POST' || $verb == 'PUT')
            fputs($sock, $str_data);

        $result = ''; 

        stream_set_timeout($sock, 3);
        $info = stream_get_meta_data($sock);

        while(!feof($sock) && !$info['timed_out']) {
            $result .= fgets($sock, 128);
            $info    = stream_get_meta_data($sock);
        }

        fclose($sock);

        if($info['timed_out'])
            throw new DefensioConnectionTimeout();

        $result = explode("\r\n\r\n", $result, 2);
        $header = isset($result[0]) ? explode("\r\n", $result[0]) : '';
        $content = isset($result[1]) ? $result[1] : '';
        $status = isset($header[0]) ? explode(' ', $header[0] ) : NULL;
        $status = $status[1];

        return Array($status, $content, $header);
    }

}

?>
