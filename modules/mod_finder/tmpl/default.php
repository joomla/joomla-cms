<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::addIncludePath(JPATH_SITE.'/components/com_finder/helpers/html');

if (!defined('FINDER_PATH_INDEXER'))
{
	define('FINDER_PATH_INDEXER', JPATH_ADMINISTRATOR.'/components/com_finder/helpers/indexer');
}
JLoader::register('FinderIndexerQuery', FINDER_PATH_INDEXER.'/query.php');

// Instantiate a query object.
$query = new FinderIndexerQuery(array('filter' => $params->get('f')));

$formId	= 'mod-finder-'.$module->id;
$fldId	= 'mod_finder_q'.$module->id;
$suffix = $params->get('moduleclass_sfx');
$output = '<input type="text" name="q" id="'.$fldId.'" class="inputbox" size="'.$params->get('field_size', 20).'" value="'.htmlspecialchars(JRequest::getVar('q')).'" />';
$button = '';
$label	= '';

if ($params->get('show_label', 1))
{
	$label	= '<label for="'.$fldId.'" class="finder'.$suffix.'">'
			. $params->get('alt_label', JText::_('JSEARCH_FILTER_SUBMIT'))
			. '</label>';
}

if ($params->get('show_button', 1))
{
	$button	= '<button class="button'.$suffix.' finder'.$suffix.'" type="submit">'.JText::_('MOD_FINDER_SEARCH_BUTTON').'</button>';
}

switch ($params->get('label_pos', 'left')):
    case 'top' :
	    $label = $label.'<br />';
	    $output = $label.$output;
	    break;

    case 'bottom' :
	    $label = '<br />'.$label;
	    $output = $output.$label;
	    break;

    case 'right' :
	    $output = $output.$label;
	    break;

    case 'left' :
    default :
	    $output = $label.$output;
	    break;
endswitch;

switch ($params->get('button_pos', 'right')):
    case 'top' :
	    $button = $button.'<br />';
	    $output = $button.$output;
	    break;

    case 'bottom' :
	    $button = '<br />'.$button;
	    $output = $output.$button;
	    break;

    case 'right' :
	    $output = $output.$button;
	    break;

    case 'left' :
    default :
	    $output = $button.$output;
	    break;
endswitch;

JHtml::stylesheet('com_finder/finder.css', false, true, false);
?>

<script type="text/javascript">
//<![CDATA[
	window.addEvent('domready', function() {
<?php if ($params->get('show_text', 1)): ?>
		var value;

		// Set the input value if not already set.
		if (!document.id('<?php echo $fldId; ?>').getProperty('value')) {
			document.id('<?php echo $fldId; ?>').setProperty('value', '<?php echo JText::_('MOD_FINDER_SEARCH_VALUE', true); ?>');
		}

		// Get the current value.
		value = document.id('<?php echo $fldId; ?>').getProperty('value');

		// If the current value equals the previous value, clear it.
		document.id('<?php echo $fldId; ?>').addEvent('focus', function() {
			if (this.getProperty('value') == value) {
				this.setProperty('value', '');
			}
		});

		// If the current value is empty, set the previous value.
		document.id('<?php echo $fldId; ?>').addEvent('blur', function() {
			if (!this.getProperty('value')) {
				this.setProperty('value', value);
			}
		});
<?php endif; ?>

		document.id('<?php echo $formId; ?>').addEvent('submit', function(e){
			e = new Event(e);
			e.stop();

			// Disable select boxes with no value selected.
			if (document.id('<?php echo $formId; ?>-advanced') != null) {
				document.id('<?php echo $formId; ?>-advanced').getElements('select').each(function(s){
					if (!s.getProperty('value')) {
						s.setProperty('disabled', 'disabled');
					}
				});
			}

			document.id('<?php echo $formId; ?>').submit();
		});

		/*
		 * This segment of code sets up the autocompleter.
		 */
<?php if ($params->get('show_autosuggest', 1)): ?>
	<?php JHtml::script('com_finder/autocompleter.js', false, true); ?>
	var url = '<?php echo JRoute::_('index.php?option=com_finder&task=suggestions.display&format=json&tmpl=component', false); ?>';
	var ModCompleter = new Autocompleter.Request.JSON(document.id('<?php echo $fldId; ?>'), url, {'postVar': 'q'});
<?php endif; ?>
	});
//]]>
</script>

<div class="finder<?php echo $suffix; ?>">
	<form id="<?php echo $formId; ?>" action="<?php echo JRoute::_($route); ?>" method="get">
		<?php
		echo modFinderHelper::getGetFields($route);

		// Show the form fields.
		echo $output;
		?>

<?php if ($params->get('show_advanced', 1)): ?>
	<?php if ($params->get('show_advanced', 1) == 2): ?>
		<br />
		<a href="<?php echo JRoute::_($route); ?>"><?php echo JText::_('COM_FINDER_ADVANCED_SEARCH'); ?></a>
	<?php elseif ($params->get('show_advanced', 1) == 1): ?>
		<div id="mod-finder-advanced">
			<?php echo JHtml::_('filter.select', $query, $params); ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
	</form>
</div>