<?php
namespace Kunnu\Dropbox;

use Kunnu\Dropbox\Http\RequestBodyStream;
use Kunnu\Dropbox\Http\RequestBodyJsonEncoded;

/**
 * DropboxRequest
 */
class DropboxRequest
{

    /**
     * Access Token to use for this request
     *
     * @var string
     */
    protected $accessToken = null;

    /**
     * The HTTP method for this request
     *
     * @var string
     */
    protected $method = "GET";

    /**
     * The params for this request
     *
     * @var array
     */
    protected $params = null;

    /**
     * The Endpoint for this request
     *
     * @var string
     */
    protected $endpoint = null;

    /**
     * The Endpoint Type for this request
     *
     * @var string
     */
    protected $endpointType = null;

    /**
     * The headers to send with this request
     *
     * @var array
     */
    protected $headers = [];

    /**
     * File to upload
     *
     * @var \Kunnu\Dropbox\DropboxFile
     */
    protected $file = null;

    /**
     * Content Type for the Request
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * If the Response needs to be validated
     * against being a valid JSON response.
     * Set this to false when an endpoint or
     * request has no return values.
     *
     * @var boolean
     */
    protected $validateResponse = true;


    /**
     * Create a new DropboxRequest instance
     *
     * @param string $method       HTTP Method of the Request
     * @param string $endpoint     API endpoint of the Request
     * @param string $accessToken Access Token for the Request
     * @param string $endpointType Endpoint type ['api'|'content']
     * @param mixed  $params       Request Params
     * @param array  $headers      Headers to send along with the Request
     */
    public function __construct($method, $endpoint, $accessToken, $endpointType = "api", array $params = [], array $headers = [], $contentType = null)
    {
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setAccessToken($accessToken);
        $this->setEndpointType($endpointType);
        $this->setParams($params);
        $this->setHeaders($headers);

        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Get the Request Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

     /**
     * Get the Request Params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get Access Token for the Request
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the Endpoint of the Request
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get the Endpoint Type of the Request
     *
     * @return string
     */
    public function getEndpointType()
    {
        return $this->endpointType;
    }

    /**
     * Get the Content Type of the Request
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get Request Headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the File to be sent with the Request
     *
     * @return \Kunnu\Dropbox\DropboxFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the Request Method
     *
     * @param string
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

     /**
     * Set the Request Params
     *
     * @param array
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
     public function setParams(array $params = [])
     {
         //Process Params
        $params = $this->processParams($params);

        //Set the params
        $this->params = $params;

         return $this;
     }

    /**
     * Set Access Token for the Request
     *
     * @param string
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the Endpoint of the Request
     *
     * @param string
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Set the Endpoint Type of the Request
     *
     * @param string
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setEndpointType($endpointType)
    {
        $this->endpointType = $endpointType;

        return $this;
    }


    /**
     * Set Request Headers
     *
     * @param array
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Set the Content Type of the Request
     *
     * @param string
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Set the File to be sent with the Request
     *
     * @param \Kunnu\Dropbox\DropboxFile
     *
     * @return \Kunnu\Dropbox\DropboxRequest
     */
    public function setFile(DropboxFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get JSON Encoded Request Body
     *
     * @return \Kunnu\Dropbox\Http\RequestBodyJsonEncoded
     */
    public function getJsonBody()
    {
        return new RequestBodyJsonEncoded($this->getParams());
    }

    /**
     * Get Stream Request Body
     *
     * @return \Kunnu\Dropbox\Http\RequestBodyStream
     */
    public function getStreamBody()
    {
        return new RequestBodyStream($this->getFile());
    }

    /**
     * Returns true if Request has file to be uploaded
     *
     * @return boolean
     */
    public function hasFile()
    {
        return !is_null($this->file) ? true : false;
    }

    /**
     * Whether to validate response or not
     *
     * @return boolean
     */
    public function validateResponse()
    {
        return $this->validateResponse;
    }

    /**
     * Process Params for the File parameter
     *
     * @param  array  $params Request Params
     *
     * @return array
     */
    protected function processParams(array $params)
    {
        //If a file needs to be uploaded
        if (isset($params['file']) && $params['file'] instanceof DropboxFile) {
            //Set the file property
            $this->setFile($params['file']);
            //Remove the file item from the params array
            unset($params['file']);
        }

        //Whether the response needs to be validated
        //against being a valid JSON response
        if (isset($params['validateResponse'])) {
            //Set the validateResponse
            $this->validateResponse = $params['validateResponse'];
            //Remove the validateResponse from the params array
            unset($params['validateResponse']);
        }

        return $params;
    }
}
