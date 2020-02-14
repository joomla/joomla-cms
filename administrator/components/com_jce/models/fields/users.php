<?php

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user ID from a modal list.
 *
 * @since  1.6
 */
class JFormFieldUsers extends JFormFieldUser
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.7
	 */
	public $type = 'Users';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		$options = $this->getOptions();

		$name  = $this->name;

		// clear name
		$this->name = "";

		$this->onchange = "(function(){WfSelectUsers();})();";

		$html  = $this->getRenderer($this->layout)->render($this->getLayoutData());

		$html  .= '<div class="users-select">';

		$html  .= '<select name="' . $name . '" id="' . $this->id . '_select" class="custom-select" multiple>';
		
		foreach ($options as $option) {
			$html  .= '<option value="' . $option->value . '" selected>' . $option->text . '</option>';
		}

		$html  .= '</select>';

		$html  .= '</div>';

		return $html;

	}
	
	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getLayoutData()
	{
		// clear value
		$this->value = json_encode($this->value);
		
		// Get the basic field data
		return parent::getLayoutData();
	}

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutPaths()
    {
        return array(JPATH_ADMINISTRATOR . '/components/com_jce/layouts', JPATH_SITE . '/layouts');
	}
	
	/**
     * Method to get the field options.
     *
     * @return array The field option objects
     *
     * @since   11.1
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);

		$options = array();
		
		if (empty($this->value)) {
			return $options;
		}

		$this->value = json_decode($this->value);

        foreach ($this->value as $user) {

            $tmp = array(
                'value' => $user->value,
                'text' => JText::alt($user->text, $fieldname),
                'selected' => true,
            );

            // Add the option object to the result set.
            $options[] = (object) $tmp;
        }

        reset($options);

        return $options;
    }
}
