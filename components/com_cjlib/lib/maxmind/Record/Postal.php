<?php

namespace GeoIp2\Record;

/**
 * Contains data for the postal record associated with an IP address
 *
 * This record is returned by all the end points except the Country end point.
 *
 * @property string $code The postal code of the location. Postal codes are
 * not available for all countries. In some countries, this will only contain
 * part of the postal code. This attribute is returned by all end points
 * except the Country end point.
 *
 * @property int $confidence A value from 0-100 indicating MaxMind's
 * confidence that the postal code is correct. This attribute is only
 * available from the Insights end point.
 */
class Postal extends AbstractRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array('code', 'confidence');
}
