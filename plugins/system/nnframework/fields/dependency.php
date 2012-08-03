<?php
/**
 * Element: Dependency
 * Displays an error if given file is not found
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Dependency Element
 *
 * Available extra parameters:
 * label	The name of the extension that is needed
 * file		The file to check (from the root)
 */
class nnFieldDependency
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		JHtml::_('behavior.mootools');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/js/script.js?v='.$this->_version);

		$file = $this->def('file');
		if (!$file) {
			$path = ($this->def('path') == 'site') ? '' : '/administrator';
			$label = $this->def('label');
			$file = $this->def('alias', $label);
			$file = preg_replace('#[^a-z-]#', '', strtolower($file));
			$extension = $this->def('extension');
			switch ($extension) {
				case 'com';
					$file = $path.'/components/com_'.$file.'/com_'.$file.'.xml';
					break;
				case 'mod';
					$file = $path.'/modules/mod_'.$file.'/mod_'.$file.'.xml';
					break;
				case 'plg_editors-xtd';
					$file = '/plugins/editors-xtd/'.$file.'.xml';
					break;
				default:
					$file = '/plugins/system/'.$file.'.xml';
					break;
			}
			$label = JText::_($label).' ('.JText::_('NN_'.strtoupper($extension)).')';
		} else {
			$label = $this->def('label', 'the main extension');
		}

		$this->setMessage($file, $label);

		return;
	}

	static function setMessage($file, $name)
	{
		jimport('joomla.filesystem.file');

		$file = str_replace('\\', '/', $file);
		if (strpos($file, '/administrator') === 0) {
			$file = str_replace('/administrator', JPATH_ADMINISTRATOR, $file);
		} else {
			$file = JPATH_SITE.'/'.$file;
		}
		$file = str_replace('//', '/', $file);

		$file_alt = preg_replace('#(com|mod)_([a-z-_]+\.)#', '\2', $file);

		if (!JFile::exists($file) && !JFile::exists($file_alt)) {
			$app = JFactory::getApplication();
			$msg = JText::sprintf('NN_THIS_EXTENSION_NEEDS_THE_MAIN_EXTENSION_TO_FUNCTION', JText::_($name));
			$message_set = 0;
			$messageQueue = $app->getMessageQueue();
			foreach ($messageQueue as $queue_message) {
				if ($queue_message['type'] == 'error' && $queue_message['message'] == $msg) {
					$message_set = 1;
					break;
				}
			}
			if (!$message_set) {
				$app->enqueueMessage($msg, 'error');
			}
		}
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

jimport('joomla.form.formfield');

class JFormFieldNN_Dependency extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Dependency';

	protected function getLabel()
	{
		return;
	}

	protected function getInput()
	{
		$this->_nnfield = new nnFieldDependency();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}