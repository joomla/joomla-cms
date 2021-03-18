<?php

namespace Negotiation;

use Negotiation\Exception\InvalidMediaType;

final class Accept extends BaseAccept implements AcceptHeader
{
    private $basePart;

    private $subPart;

    public function __construct($value)
    {
        parent::__construct($value);

        if ($this->type === '*') {
            $this->type = '*/*';
        }

        $parts = explode('/', $this->type);

        if (count($parts) !== 2 || !$parts[0] || !$parts[1]) {
            throw new InvalidMediaType();
        }

        $this->basePart = $parts[0];
        $this->subPart  = $parts[1];
    }

    /**
     * @return string
     */
    public function getSubPart()
    {
        return $this->subPart;
    }

    /**
     * @return string
     */
    public function getBasePart()
    {
        return $this->basePart;
    }
}
