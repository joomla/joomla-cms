<?php

namespace Negotiation;

abstract class BaseAccept
{
    /**
     * @var float
     */
    private $quality = 1.0;

    /**
     * @var string
     */
    private $normalized;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        list($type, $parameters) = $this->parseParameters($value);

        if (isset($parameters['q'])) {
            $this->quality = (float) $parameters['q'];
            unset($parameters['q']);
        }

        $type = trim(strtolower($type));

        $this->value      = $value;
        $this->normalized = $type . ($parameters ? "; " . $this->buildParametersString($parameters) : '');
        $this->type       = $type;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getNormalizedValue()
    {
        return $this->normalized;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return string|null
     */
    public function getParameter($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     *
     * @param  string $acceptPart
     * @return array
     */
    private function parseParameters($acceptPart)
    {
        $parts = explode(';', $acceptPart);
        $type  = array_shift($parts);

        $parameters = [];
        foreach ($parts as $part) {
            $part = explode('=', $part);

            if (2 !== count($part)) {
                continue; // TODO: throw exception here?
            }

            $key = strtolower(trim($part[0])); // TODO: technically not allowed space around "=". throw exception?
            $parameters[$key] = trim($part[1], ' "');
        }

        return [ $type, $parameters ];
    }

    /**
     * @param string $parameters
     *
     * @return string
     */
    private function buildParametersString($parameters)
    {
        $parts = [];

        ksort($parameters);
        foreach ($parameters as $key => $val) {
            $parts[] = sprintf('%s=%s', $key, $val);
        }

        return implode('; ', $parts);
    }
}
