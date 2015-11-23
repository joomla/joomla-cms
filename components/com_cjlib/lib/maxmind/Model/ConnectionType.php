<?php

namespace GeoIp2\Model;

/**
 * This class provides the GeoIP2 Connection-Type model.
 *
 * @property string $connectionType The connection type may take the following
 *     values: "Dialup", "Cable/DSL", "Corporate", "Cellular". Additional
 *     values may be added in the future.
 *
 * @property string $ipAddress The IP address that the data in the model is
 *     for.
 *
 */
class ConnectionType extends AbstractModel
{
    protected $connectionType;
    protected $ipAddress;

    /**
     * @ignore
     */
    public function __construct($raw)
    {
        parent::__construct($raw);

        $this->connectionType = $this->get('connection_type');
        $this->ipAddress = $this->get('ip_address');
    }
}
