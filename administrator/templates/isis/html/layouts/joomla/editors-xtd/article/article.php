<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.modal');

$button = $displayData;

$onclick  = ($button->get('onclick')) ? ' onclick="' . $button->get('onclick') . '"' : ' onclick="IeCursorFix(); return false;"';
/*
 * Javascript to insert the link
 * View element calls jSelectArticle when an article is clicked
 * jSelectArticle creates the link tag, sends it to the editor,
 * and closes the select frame.
 */
JFactory::getDocument()->addScriptDeclaration("
	function jSelectArticle(id, title, catid, object, link, lang)
	{
		var hreflang = '';
		if (lang !== '')
		{
			var hreflang = ' hreflang = \"' + lang + '\"';
		}
		var tag = '<a' + hreflang + ' href=\"' + link + '\">' + title + '</a>';
		jInsertEditorText(tag, '" . $button->editor . "');
		jQuery('#articleModal').modal('hide');
	}
");

$tmptitle = str_replace(' ', '', strtolower(htmlspecialchars($button->plugin)));

echo JHtmlBootstrap::renderModal($tmptitle . 'Modal', array('url' => $button->link, 'title' => $button->text, 'height' => '500px', 'width' => '800px'));

?>
<a href="#<?php echo $tmptitle; ?>Modal" class="<?php echo $button->class; ?>" role="button" title="<?php echo $button->text; ?>" data-toggle="modal"><i class="icon-<?php echo $button->name; ?>"></i> <?php echo $button->text; ?></a>