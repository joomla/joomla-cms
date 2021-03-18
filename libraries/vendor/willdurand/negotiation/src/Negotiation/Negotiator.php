<?php

namespace Negotiation;

class Negotiator extends AbstractNegotiator
{
    /**
     * {@inheritdoc}
     */
    protected function acceptFactory($accept)
    {
        return new Accept($accept);
    }

    /**
     * {@inheritdoc}
     */
    protected function match(AcceptHeader $accept, AcceptHeader $priority, $index)
    {
        if (!$accept instanceof Accept || !$priority instanceof Accept) {
            return null;
        }

        $acceptBase = $accept->getBasePart();
        $priorityBase = $priority->getBasePart();

        $acceptSub = $accept->getSubPart();
        $prioritySub = $priority->getSubPart();

        $intersection = array_intersect_assoc($accept->getParameters(), $priority->getParameters());

        $baseEqual = !strcasecmp($acceptBase, $priorityBase);
        $subEqual  = !strcasecmp($acceptSub, $prioritySub);

        if (($acceptBase === '*' || $baseEqual)
            && ($acceptSub === '*' || $subEqual)
            && count($intersection) === count($accept->getParameters())
        ) {
            $score = 100 * $baseEqual + 10 * $subEqual + count($intersection);

            return new Match($accept->getQuality() * $priority->getQuality(), $score, $index);
        }

        if (!strstr($acceptSub, '+') || !strstr($prioritySub, '+')) {
            return null;
        }

        // Handle "+" segment wildcards
        list($acceptSub, $acceptPlus) = $this->splitSubPart($acceptSub);
        list($prioritySub, $priorityPlus) = $this->splitSubPart($prioritySub);

        // If no wildcards in either the subtype or + segment, do nothing.
        if (!($acceptBase === '*' || $baseEqual)
            || !($acceptSub === '*' || $prioritySub === '*' || $acceptPlus === '*' || $priorityPlus === '*')
        ) {
            return null;
        }

        $subEqual  = !strcasecmp($acceptSub, $prioritySub);
        $plusEqual = !strcasecmp($acceptPlus, $priorityPlus);

        if (($acceptSub === '*' || $prioritySub === '*' || $subEqual)
            && ($acceptPlus === '*' || $priorityPlus === '*' || $plusEqual)
            && count($intersection) === count($accept->getParameters())
        ) {
            $score = 100 * $baseEqual + 10 * $subEqual + $plusEqual + count($intersection);

            return new Match($accept->getQuality() * $priority->getQuality(), $score, $index);
        }

        return null;
    }

    /**
     * Split a subpart into the subpart and "plus" part.
     *
     * For media-types of the form "application/vnd.example+json", matching
     * should allow wildcards for either the portion before the "+" or
     * after. This method splits the subpart to allow such matching.
     */
    protected function splitSubPart($subPart)
    {
        if (!strstr($subPart, '+')) {
            return [$subPart, ''];
        }

        return explode('+', $subPart, 2);
    }
}
