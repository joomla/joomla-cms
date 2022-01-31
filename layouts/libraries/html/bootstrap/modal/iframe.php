<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $selector  Unique DOM identifier for the modal. CSS id without #
 * @var   array   $params    Modal parameters. Default supported parameters:
 *                             - title        string   The modal title
 *                             - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true)
 *                                                     The string 'static' includes a backdrop which doesn't close the modal on click.
 *                             - keyboard     boolean  Closes the modal when escape key is pressed (default = true)
 *                             - closeButton  boolean  Display modal close button (default = true)
 *                             - animation    boolean  Fade in from the top of the page (default = true)
 *                             - footer       string   Optional markup for the modal footer
 *                             - url          string   URL of a resource to be inserted as an <iframe> inside the modal body
 *                             - height       string   height of the <iframe> containing the remote resource
 *                             - width        string   width of the <iframe> containing the remote resource
 * @var   string  $body      Markup for the modal body. Appended after the <iframe> if the URL option is set
 */

$iframeAttributes = array(
	'class' => 'iframe',
	'src'   => $params['url']
);

if (isset($params['title']))
{
	$iframeAttributes['name'] = addslashes($params['title']);
	$iframeAttributes['title'] = addslashes($params['title']);
}

if (isset($params['height']))
{
	$iframeAttributes['height'] = $params['height'];
}

if (isset($params['width']))
{
	$iframeAttributes['width'] = $params['width'];
}
?>
<iframe <?php echo ArrayHelper::toString($iframeAttributes); ?>></iframe>
