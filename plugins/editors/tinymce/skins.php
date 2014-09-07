<?php
// Check
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldSkins extends JFormFieldList 
{

	protected $type = 'skins';

	public function getOptions() 
	{

		$options = array();

		$directories = glob(JPATH_ROOT . '/media/editors/tinymce/skins' . '/*' , GLOB_ONLYDIR);

		for($i = 0; $i < count($directories); ++$i)
		{
			$dir = end(explode("/", $directories[$i]));//basename($directory);
			$options[] = JHtml::_('select.option', $i, $dir);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	protected function getInput()
	{
		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a regular list.
		$html[] = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value, $this->id);


		return implode($html);
	}
}
