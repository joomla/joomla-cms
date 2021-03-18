<?php

namespace Negotiation;

class CharsetNegotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept)
    {
        return new AcceptCharset($accept);
    }
}
