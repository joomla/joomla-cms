<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Load bootstrap-tooltip-extended plugin for additional tooltip positions in modal
JHtml::_('bootstrap.tooltip');

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
 *                             - url          string   URL of a resource to be inserted as an <iframe> inside the modal body
 *                             - height       string   height of the <iframe> containing the remote resource
 *                             - width        string   width of the <iframe> containing the remote resource
 *                             - bodyHeight   int      Optional height of the modal body in viewport units (vh)
 *                             - modalWidth   int      Optional width of the modal in viewport units (vh)
 *                             - footer       string   Optional markup for the modal footer
 * @param   string  $body      Markup for the modal body. Appended after the <iframe> if the url option is set
 *
 */

$modalClasses = array('modal');

if (!isset($params['animation']) || $params['animation'])
{
	$modalClasses[] = 'fade';
}

$modalWidth       = isset($params['modalWidth']) ? round((int) $params['modalWidth'], -1) : '';
$modalDialogClass = '';

if ($modalWidth && $modalWidth > 0 && $modalWidth <= 100)
{
	$modalDialogClass = ' jviewport-width' . $modalWidth;
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

?>
<div id="<?php echo $selector; ?>" role="dialog" <?php echo JArrayHelper::toString($modalAttributes); ?>>
	<div class="modal-dialog modal-lg<?php echo $modalDialogClass; ?>" role="document">
		<div class="modal-content">
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
	</div>
</div>
