<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Exception Class for Selenium
 *
 * PHP versions 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

/**
 * uses PEAR_Exception
 */
require_once 'PEAR/Exception.php';

/**
 * Testing_Selenium_Exception
 *
 * @category   Testing
 * @package    Testing_Selenium
 * @author     Shin Ohno <ganchiku at gmail dot com>
 * @author     Bjoern Schotte <schotte at mayflower dot de>
 * @version    @package_version@
 */
class Testing_Selenium_Exception extends PEAR_Exception
{
}
?>