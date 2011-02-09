<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Document
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument head renderer
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRendererHead extends JDocumentRenderer
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param	string $name	(unused)
	 * @param	array $params	Associative array of values
	 * @return	string			The output of the script
	 */
	public function render($head, $params = array(), $content = null)
	{
		ob_start();
		echo $this->fetchHead($this->_doc);
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}

	/**
	 * Generates the head html and return the results as a string
	 *
	 * @return string
	 */
	public function fetchHead(&$document)
	{
		// Trigger the onBeforeCompileHead event (skip for installation, since it causes an error)
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCompileHead');
		// get line endings
		$lnEnd	= $document->_getLineEnd();
		$tab	= $document->_getTab();
		$tagEnd	= ' />';
		$buffer	= '';

		// Generate base tag (need to happen first)
		$base = $document->getBase();
		if (!empty($base)) {
			$buffer .= $tab.'<base href="'.$document->getBase().'" />'.$lnEnd;
		}

		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($document->_metaTags as $type => $tag)
		{
			foreach ($tag as $name => $content)
			{
				if ($type == 'http-equiv') {
					$content.= '; charset=' . $document->getCharset();
					$buffer .= $tab.'<meta http-equiv="'.$name.'" content="'.htmlspecialchars($content).'"'.$tagEnd.$lnEnd;
				}
				else if ($type == 'standard') {
					$buffer .= $tab.'<meta name="'.$name.'" content="'.htmlspecialchars($content).'"'.$tagEnd.$lnEnd;
				}
			}
		}

		// dont add empty descriptions
		$documentDescription = $document->getDescription();
		if ($documentDescription) {
			$buffer .= $tab.'<meta name="description" content="'.htmlspecialchars($documentDescription).'" />'.$lnEnd;
		}

		$buffer .= $tab.'<meta name="generator" content="'.htmlspecialchars($document->getGenerator()).'" />'.$lnEnd;
		$buffer .= $tab.'<title>'.htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8').'</title>'.$lnEnd;

		// Generate link declarations
		foreach ($document->_links as $link) {
			$buffer .= $tab.$link.$tagEnd.$lnEnd;
		}

		// Generate stylesheet links
		foreach ($document->_styleSheets as $strSrc => $strAttr)
		{
			$buffer .= $tab . '<link rel="stylesheet" href="'.$strSrc.'" type="'.$strAttr['mime'].'"';
			if (!is_null($strAttr['media'])) {
				$buffer .= ' media="'.$strAttr['media'].'" ';
			}
			if ($temp = JArrayHelper::toString($strAttr['attribs'])) {
				$buffer .= ' '.$temp;;
			}
			$buffer .= $tagEnd.$lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($document->_style as $type => $content)
		{
			$buffer .= $tab.'<style type="'.$type.'">'.$lnEnd;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html') {
				$buffer .= $tab.$tab.'<![CDATA['.$lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if ($document->_mime != 'text/html') {
				$buffer .= $tab.$tab.']]>'.$lnEnd;
			}
			$buffer .= $tab.'</style>'.$lnEnd;
		}

		// Generate script file links
		foreach ($document->_scripts as $strSrc => $strType) {
			$buffer .= $tab.'<script type="'.$strType.'" src="'.$strSrc.'"></script>'.$lnEnd;
		}

		// Generate script declarations
		foreach ($document->_script as $type => $content)
		{
			$buffer .= $tab.'<script type="'.$type.'">'.$lnEnd;

			// This is for full XHTML support.
			if ($document->_mime != 'text/html') {
				$buffer .= $tab.$tab.'<![CDATA['.$lnEnd;
			}

			$buffer .= $content.$lnEnd;

			// See above note
			if ($document->_mime != 'text/html') {
				$buffer .= $tab.$tab.']]>'.$lnEnd;
			}
			$buffer .= $tab.'</script>'.$lnEnd;
		}

		// Generate script language declarations.
		if (count(JText::script())) {
			$buffer .= $tab.'<script type="text/javascript">'.$lnEnd;
			$buffer .= $tab.$tab.'(function() {'.$lnEnd;
			$buffer .= $tab.$tab.$tab.'var strings = '.json_encode(JText::script()).';'.$lnEnd;
			$buffer .= $tab.$tab.$tab.'if (typeof Joomla == \'undefined\') {'.$lnEnd;
			$buffer .= $tab.$tab.$tab.$tab.'Joomla = {};'.$lnEnd;
			$buffer .= $tab.$tab.$tab.$tab.'Joomla.JText = strings;'.$lnEnd;
			$buffer .= $tab.$tab.$tab.'}'.$lnEnd;
			$buffer .= $tab.$tab.$tab.'else {'.$lnEnd;
			$buffer .= $tab.$tab.$tab.$tab.'Joomla.JText.load(strings);'.$lnEnd;
			$buffer .= $tab.$tab.$tab.'}'.$lnEnd;
			$buffer .= $tab.$tab.'})();'.$lnEnd;
			$buffer .= $tab.'</script>'.$lnEnd;
		}

		foreach($document->_custom as $custom) {
			$buffer .= $tab.$custom.$lnEnd;
		}

		return $buffer;
	}
}