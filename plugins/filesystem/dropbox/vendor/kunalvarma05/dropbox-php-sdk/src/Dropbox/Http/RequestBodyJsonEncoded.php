<?php
namespace Kunnu\Dropbox\Http;

/**
 * RequestBodyJsonEncoded
 */
class RequestBodyJsonEncoded implements RequestBodyInterface
{

    /**
     * Request Params
     *
     * @var array
     */
    protected $params;

    /**
     * Create a new RequestBodyJsonEncoded instance
     *
     * @param array $params Request Params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Get the Body of the Request
     *
     * @return string|null
     */
    public function getBody()
    {
        //Empty body
        if (empty($this->params)) {
            return null;
        }

        return json_encode($this->params);
    }
}
