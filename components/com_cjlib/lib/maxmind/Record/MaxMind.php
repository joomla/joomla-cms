<?php

namespace GeoIp2\Record;

/**
 * Contains data about your account.
 *
 * This record is returned by all the end points.
 *
 * @property int $queriesRemaining The number of remaining queries you have
 * for the end point you are calling.
 */
class MaxMind extends AbstractRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array('queriesRemaining');
}
