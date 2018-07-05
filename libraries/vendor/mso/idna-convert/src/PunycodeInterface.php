<?php
 /*
 * @author  Matthias Sommerfeld <mso@phlylabs.de>
 * @copyright 2004-2016 phlyLabs Berlin, http://phlylabs.de
 */

namespace Mso\IdnaConvert;

interface PunycodeInterface 
{
   
    public function __construct(NamePrepDataInterface $NamePrepData, UnicodeTranscoderInterface $UCTC);

    public function getPunycodePrefix();

    public function decode($encoded);

    public function encode($decoded);

}
