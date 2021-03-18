<?php

namespace Negotiation;

class EncodingNegotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept)
    {
        return new AcceptEncoding($accept);
    }
}
