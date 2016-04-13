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
 *      - max-height    .modal-body     Max-height for the modal body
 *                                      When height of the modal is too high for the window viewport height.
 *      - max-height    .iframe         Max-height for the iframe (Deducting the padding of the modal-body)
 *                                      When url option is set and height of the iframe is higher than max-height of the modal body.
 *
 * Fix iOS scrolling inside bootstrap modals
 *      - overflow-y    .modal-body     When max-height is set for modal-body
 *
 * Specific hack for Bootstrap 2.3.x
 */
$script[] = "jQuery(document).ready(function($) {";
$script[] = "   $('#" . $selector . "').on('shown.bs.modal', function() {";

$script[] = "       $('body').addClass('modal-open');";

// Get height of the modal elements.
$script[] = "       var modalHeaderHeight = $('div.modal-header:visible').outerHeight(true);";
$script[] = "       var modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true);";
$script[] = "       var modalBodyHeight = $('div.modal-body:visible').height();";
$script[] = "       var modalFooterHeight = $('div.modal-footer:visible').outerHeight(true);";

// Get padding top (jQuery position().top not working on iOS devices and webkit browsers, so use of Javascript instead)
$script[] = "       var padding = document.getElementById('" . $selector . "').offsetTop;";

// Calculate max-height of the modal, adapted to window viewport height.
$script[] = "       var maxModalHeight = ($(window).height()-(padding*2));";

// Calculate max-height for modal-body.
$script[] = "       var modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight);";
$script[] = "       var maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);";

if (isset($params['url']))
{
	$iframeHtml = JLayoutHelper::render('joomla.modal.iframe', $displayData);

	// Script for destroying and reloading the iframe
	$script[] = "       var modalBody = $(this).find('.modal-body');";
	$script[] = "       modalBody.find('iframe').remove();";
	$script[] = "       modalBody.prepend('" . trim($iframeHtml) . "');";

	// Set max-height for iframe if needed, to adapt to viewport height.
	$script[] = "       var iframeHeight = $('.iframe').height();";
	$script[] = "       if (iframeHeight > maxModalBodyHeight){;";
	$script[] = "           $('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});";
	$script[] = "           $('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);";
	$script[] = "       }";
}
else
{
	// Set max-height for modal-body if needed, to adapt to viewport height.
	$script[] = "       if (modalBodyHeight > maxModalBodyHeight){;";
	$script[] = "           $('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});";
	$script[] = "       }";
}

$script[] = "   }).on('hide.bs.modal', function () {";
$script[] = "       $('body').removeClass('modal-open');";
$script[] = "       $('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});";
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
