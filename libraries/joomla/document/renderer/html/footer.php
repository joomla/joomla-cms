<?php
// No direct access
defined('JPATH_BASE') or die;

/**
 * JDocument footer renderer
 *
 * @since  3.7
 */
class JDocumentRendererHtmlFooter extends JDocumentRenderer
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param   string  $footer   (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.7
	 */
	public function render($footer, $params = array(), $content = null)
	{
		ob_start();
		echo $this->fetchFooter($this->_doc);
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}

	/**
	 * Generates the footer HTML and return the results as a string
	 *
	 * @param   JDocumentHtml  $document  The document for which the head will be created
	 *
	 * @return  string  The footer hTML
	 *
	 * @since   3.7
	 */
	public function fetchFooter(&$document)
	{
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCompileFooter');
		$lnEnd  = $document->_getLineEnd();
		$tab    = $document->_getTab();
		$buffer = '';
		foreach ($document->_footer_scripts as $strSrc => $strType)
		{
			$buffer .= $tab . '<script type="' . $strType['mime'] . '" src="' . $strSrc . '"></script>' . $lnEnd;
		}

		return $buffer;
	}
}
