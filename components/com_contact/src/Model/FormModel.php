<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Model;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Contact Component Contact Model
 *
 * @since  4.0.0
 */
class FormModel extends \Joomla\Component\Contact\Administrator\Model\ContactModel
{
	/**
	 * Model typeAlias string. Used for version history.
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	public $typeAlias = 'com_contact.contact';

	/**
	 * Name of the form
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $formName = 'form';

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		// Prevent messing with article language and category when editing existing contact with associations
		if ($id = $this->getState('contact.id') && Associations::isEnabled())
		{
			$associations = Associations::getAssociations('com_contact', '#__contact_details', 'com_contact.item', $id);

			// Make fields read only
			if (!empty($associations))
			{
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('language', 'filter', 'unset');
			}
		}

		return $form;
	}

	/**
	 * Method to get contact data.
	 *
	 * @param   integer  $itemId  The id of the contact.
	 *
	 * @return  mixed  Contact item data object on success, false on failure.
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function getItem($itemId = null)
	{
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('contact.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		try
		{
			if (!$table->load($itemId))
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage());

			return false;
		}

		$properties = $table->getProperties();
		$value      = ArrayHelper::toObject($properties, \Joomla\CMS\Object\CMSObject::class);

		// Convert field to Registry.
		$value->params = new Registry($value->params);

		// Convert the metadata field to an array.
		$registry        = new Registry($value->metadata);
		$value->metadata = $registry->toArray();

		if ($itemId)
		{
			$value->tags = new TagsHelper;
			$value->tags->getTagIds($value->id, 'com_contact.contact');
			$value->metadata['tags'] = $value->tags;
		}

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string  The return URL.
	 *
	 * @since   4.0.0
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 *
	 * @throws  Exception
	 */
	public function save($data)
	{
		// Associations are not edited in frontend ATM so we have to inherit them
		if (Associations::isEnabled() && !empty($data['id'])
			&& $associations = Associations::getAssociations('com_contact', '#__contact_details', 'com_contact.item', $data['id']))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			$data['associations'] = $associations;
		}

		return parent::save($data);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @throws  Exception
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('contact.id', $pk);

		$this->setState('contact.catid', $app->input->getInt('catid'));

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->getString('layout'));
	}

	/**
	 * Allows preprocessing of the JForm object.
	 *
	 * @param   Form    $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'contact')
	{
		if (!Multilanguage::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'hidden');
			$form->setFieldAttribute('language', 'default', '*');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  bool|Table  A Table object
	 *
	 * @since   4.0.0

	 * @throws  Exception
	 */
	public function getTable($name = 'Contact', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}
}
