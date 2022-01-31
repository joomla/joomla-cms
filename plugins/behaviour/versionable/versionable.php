<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Versionable
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event as CmsEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\CMSHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Versioning\Versioning;
use Joomla\Event\DispatcherInterface;

/**
 * Implements the Versionable behaviour which allows extensions to automatically support content history for their content items.
 *
 * This plugin supersedes JTableObserverContenthistory.
 *
 * @since  4.0.0
 */
class PlgBehaviourVersionable extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                          (this list is not meant to be comprehensive).
	 *
	 * @since  4.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->allowLegacyListeners = false;

		parent::__construct($subject, $config);
	}

	/**
	 * Post-processor for $table->store($updateNulls)
	 *
	 * @param   CmsEvent\Table\AfterStoreEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableAfterStore(CmsEvent\Table\AfterStoreEvent $event)
	{
		// Extract arguments
		/** @var VersionableTableInterface $table */
		$table	= $event['subject'];
		$result = $event['result'];

		if (!$result)
		{
			return;
		}

		if (!(is_object($table) && $table instanceof VersionableTableInterface))
		{
			return;
		}

		// Get the Tags helper and assign the parsed alias
		$typeAlias = $table->getTypeAlias();
		$aliasParts = explode('.', $typeAlias);

		if ($aliasParts[0] === '' || !ComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			return;
		}

		$id = $table->getId();
		$helper = new CMSHelper;
		$data = $helper->getDataObject($table);
		$input = Factory::getApplication()->input;
		$jform = $input->get('jform', array(), 'array');
		$versionNote = '';

		if (isset($jform['version_note']))
		{
			$versionNote = InputFilter::getInstance()->clean($jform['version_note'], 'string');
		}

		Versioning::store($typeAlias, $id, $data, $versionNote);
	}

	/**
	 * Pre-processor for $table->delete($pk)
	 *
	 * @param   CmsEvent\Table\BeforeDeleteEvent  $event  The event to handle
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onTableBeforeDelete(CmsEvent\Table\BeforeDeleteEvent $event)
	{
		// Extract arguments
		/** @var VersionableTableInterface $table */
		$table			= $event['subject'];

		if (!(is_object($table) && $table instanceof VersionableTableInterface))
		{
			return;
		}

		$typeAlias  = $table->getTypeAlias();
		$aliasParts = explode('.', $typeAlias);

		if ($aliasParts[0] && ComponentHelper::getParams($aliasParts[0])->get('save_history', 0))
		{
			Versioning::delete($typeAlias, $table->getId());
		}
	}
}
