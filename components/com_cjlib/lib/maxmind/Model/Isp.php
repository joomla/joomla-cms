<?php

namespace GeoIp2\Model;

/**
 * This class provides the GeoIP2 Connection-Type model.
 *
 * @property integer $autonomousSystemNumber The autonomous system number
 *     associated with the IP address.
 *
 * @property string $autonomousSystemOrganization The organization associated
 *     with the registered autonomous system number for the IP address.
 *
 * @property string $isp The name of the ISP associated with the IP address.
 *
 * @property string $organization The name of the organization associated with
 *     the IP address.
 *
 * @property string $ipAddress The IP address that the data in the model is
 *     for.
 *
 */
class Isp extends AbstractModel
{
    protected $autonomousSystemNumber;
    protected $autonomousSystemOrganization;
    protected $isp;
    protected $organization;
    protected $ipAddress;

    /**
     * @ignore
     */
    public function __construct($raw)
    {
        parent::__construct($raw);
        $this->autonomousSystemNumber = $this->get('autonomous_system_number');
        $this->autonomousSystemOrganization =
            $this->get('autonomous_system_organization');
        $this->isp = $this->get('isp');
        $this->organization = $this->get('organization');

        $this->ipAddress = $this->get('ip_address');
    }
}
