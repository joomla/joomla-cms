<?php

namespace GuzzleHttp\Command\Guzzle\ResponseLocation;

use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Command\Guzzle\GuzzleCommandInterface;

/**
 * Extracts elements from a JSON document.
 */
class JsonLocation extends AbstractLocation
{
    /** @var array The JSON document being visited */
    private $json = array();

    public function before(
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        Parameter $model,
        &$result,
        array $context = array()
    ) {
        $this->json = $response->json() ?: array();
    }

    public function after(
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        Parameter $model,
        &$result,
        array $context = array()
    ) {
        // Handle additional, undefined properties
        $additional = $model->getAdditionalProperties();
        if ($additional instanceof Parameter &&
            $additional->getLocation() == $this->locationName
        ) {
            foreach ($this->json as $prop => $val) {
                if (!isset($result[$prop])) {
                    // Only recurse if there is a type specified
                    $result[$prop] = $additional->getType()
                        ? $this->recurse($additional, $val)
                        : $val;
                }
            }
        }

        $this->json = array();
    }

    public function visit(
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        Parameter $param,
        &$result,
        array $context = array()
    ) {
        $name = $param->getName();
        $key = $param->getWireName();

        // Check if the result should be treated as a list
        if ($param->getType() == 'array') {
            // Treat as javascript array
            if ($name) {
                // name provided, store it under a key in the array
                $result[$name] = $this->recurse($param, $this->json);
            } else {
                // top-level `array` or an empty name
                $result = array_merge($result, $this->recurse($param, $this->json));
            }
        } elseif (isset($this->json[$key])) {
            $result[$name] = $this->recurse($param, $this->json[$key]);
        }
    }

    /**
     * Recursively process a parameter while applying filters
     *
     * @param Parameter $param API parameter being validated
     * @param mixed     $value Value to process.
     * @return mixed|null
     */
    private function recurse(Parameter $param, $value)
    {
        if (!is_array($value)) {
            return $param->filter($value);
        }

        $result = array();
        $type = $param->getType();

        if ($type == 'array') {
            $items = $param->getItems();
            foreach ($value as $val) {
                $result[] = $this->recurse($items, $val);
            }
        } elseif ($type == 'object' && !isset($value[0])) {
            // On the above line, we ensure that the array is associative and
            // not numerically indexed
            if ($properties = $param->getProperties()) {
                foreach ($properties as $property) {
                    $key = $property->getWireName();
                    if (isset($value[$key])) {
                        $result[$property->getName()] = $this->recurse(
                            $property,
                            $value[$key]
                        );
                        // Remove from the value so that AP can later be handled
                        unset($value[$key]);
                    }
                }
            }
            // Only check additional properties if everything wasn't already
            // handled
            if ($value) {
                $additional = $param->getAdditionalProperties();
                if ($additional === null || $additional === true) {
                    // Merge the JSON under the resulting array
                    $result += $value;
                } elseif ($additional instanceof Parameter) {
                    // Process all child elements according to the given schema
                    foreach ($value as $prop => $val) {
                        $result[$prop] = $this->recurse($additional, $val);
                    }
                }
            }
        }

        return $param->filter($result);
    }
}
