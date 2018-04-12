<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerHelper', JPATH_ADMINISTRATOR . '/components/com_installer/helpers/installer.php');

JFormHelper::loadFieldClass('list');

/**
 * Package field.
 *
 * Provides a list box of installed extension packages.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldPackagelist extends JFormFieldList
{
	/**
	 * The form field package.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Packagelist';

	/**
	 * Only packages containing extensions of specified type
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extensiontype = null;

	/**
	 * Only packages with the given value of enabled field
	 *
	 * @var    int
	 * @since  __DEPLOY_VERSION__
	 */
	protected $packageenabled = null;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->extensiontype = isset($this->element['extensiontype']) ? $this->element['extensiontype'] : null;
			$this->packageenabled = isset($this->element['packageenabled']) ? (int) $this->element['packageenabled'] : "1";
		}

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
					->select('a.extension_id, a.name')
					->from('#__extensions AS a')
					->where('a.type = ' . $db->quote('package'))
					->order('a.name');

		if (!empty($this->extensiontype))
		{
			$query->where('EXISTS ( ' .
				'SELECT NULL FROM #__extensions AS b ' .
				'WHERE b.package_id = a.extension_id ' .
				'AND b.type = ' . $db->quote($this->extensiontype) .
			')');
		}

		if (strlen($this->packageenabled))
		{
			$query->where('a.enabled = ' . $db->quote($this->packageenabled));
		}

		$db->setQuery($query);
		$packages = $db->loadObjectList();

		$options = array();

		foreach ($packages as $package)
		{
			$options[] = JHtml::_('select.option', $package->extension_id, $package->name);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
