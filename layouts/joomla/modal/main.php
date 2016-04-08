<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 * @param   string  $selector  Unique DOM identifier for the modal. CSS id without #
 * @param   array   $params    Modal parameters. Default supported parameters:
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
 * @param   string  $body      Markup for the modal body. Appended after the <iframe> if the url option is set
 *
 */

$modalClasses = array('modal', 'hide');

if (!isset($params['animation']) || $params['animation'])
{
	array_push($modalClasses, 'fade');
}

$modalAttributes = array(
	'tabindex' => '-1',
	'class'    => implode(' ', $modalClasses)
);

if (isset($params['backdrop']))
{
	$modalAttributes['data-backdrop'] = (is_bool($params['backdrop']) ? ($params['backdrop'] ? 'true' : 'false') : $params['backdrop']);
}

if (isset($params['keyboard']))
{
	$modalAttributes['data-keyboard'] = (is_bool($params['keyboard']) ? ($params['keyboard'] ? 'true' : 'false') : 'true');
}

/**
 * These lines below are for disabling scrolling of parent window.
 * $('body').addClass('modal-open');
 * $('body').removeClass('modal-open')
 *
 * Scrolling inside bootstrap modals on small screens (adapt to window viewport and avoid modal off screen).
 * - max-height on .modal-body  if param height is set but too high for the window viewport height.
 *                              (147 = modal-header height + modal-footer height + 20px padding top and bottom)
 * - max-height on .iframe      max-height of the modal-body (deducting the 1% of the padding of the modal-body class)
 *
 * Specific hack for Bootstrap 2.3.x
 */
$script[] = "jQuery(document).ready(function($) {";
$script[] = "   $('#" . $selector . "').on('show', function() {";

// Set max-height on modal-body.
$script[] = "       var modalBodyHeight = $(window).height()-147;";
$script[] = "       $('.modal-body').css('max-height', modalBodyHeight);";

$script[] = "       $('body').addClass('modal-open');";

if (isset($params['url']))
{
	$iframeHtml = JLayoutHelper::render('joomla.modal.iframe', $displayData);

	// Script for destroying and reloading the iframe
	$script[] = "       var modalBody = $(this).find('.modal-body');";
	$script[] = "       modalBody.find('iframe').remove();";
	$script[] = "       modalBody.prepend('" . trim($iframeHtml) . "');";

	// Set max-height for iframe.
	$script[] = "       $('.iframe').css('max-height', modalBodyHeight*0.98);";
}

$script[] = "   }).on('hide', function () {";
$script[] = "       $('body').removeClass('modal-open');";
$script[] = "   });";
$script[] = "});";

JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<div id="<?php echo $selector; ?>" <?php echo JArrayHelper::toString($modalAttributes); ?>>
	<?php
		// Header
		if (!isset($params['closeButton']) || isset($params['title']) || $params['closeButton'])
		{
			echo JLayoutHelper::render('joomla.modal.header', $displayData);
		}

		// Body
		echo JLayoutHelper::render('joomla.modal.body', $displayData);

		// Footer
		if (isset($params['footer']))
		{
			echo JLayoutHelper::render('joomla.modal.footer', $displayData);
		}
	?>
</div>
