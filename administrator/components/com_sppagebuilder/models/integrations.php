<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.modellist');

class SppagebuilderModelIntegrations extends JModelList {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function getListQuery() {

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.description, a.component, a.state'
			)
		);

		$query->from('#__sppagebuilder_integrations as a');

		return $query;
	}

	public function storeInstall($integration) {
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$component = $input->get('integration', 'com_content', 'STRING');
		$result = $this->checkInstall($component);

		if($result) { // Update
			self::toggleActivate($component, 0);
		} else {
			$values = array(
				$db->quote($integration->title),
				$db->quote($integration->description),
				$db->quote($component),
				$db->quote(json_encode($integration->plugin)),
				0
			);
			$this->insertInstall($values);
		}

		return true;
	}

	private function checkInstall($component = 'com_content') {
		$db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id', 'state')));
    $query->from($db->quoteName('#__sppagebuilder_integrations'));
    $query->where($db->quoteName('component') . ' = ' . $db->quote($component));
    $db->setQuery($query);

    return $db->loadObject();
	}

	private function insertInstall($values = array()) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$columns = array('title', 'description', 'component', 'plugin', 'state');
		$query
		    ->insert($db->quoteName('#__sppagebuilder_integrations'))
		    ->columns($db->quoteName($columns))
		    ->values(implode(',', $values));

		$db->setQuery($query);
		$db->execute();
		$insertid = $db->insertid();

		return $insertid;
	}

	public function uninstall($component) {
		$integration = self::getIntegration($component);
		$plugin = self::getPlugin($integration);
		$installer = new JInstaller;
		$result = $installer->uninstall('plugin', $plugin);

		if($result) {
			self::toggleActivate($component, 2);
		}
	}

	public function toggleActivate($component = 'com_content', $status = 0) {

		$db = JFactory::getDbo();

		// Change state to database
		$query = $db->getQuery(true);
		$fields = array( $db->quoteName('state') . ' = ' . $status );
		$conditions = array( $db->quoteName('component') . ' = ' . $db->quote($component) );
		$query->update($db->quoteName('#__sppagebuilder_integrations'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

		// Enable or disable plugin
		if($status == 0 || $status == 1) {
			$plugin = self::getIntegration($component);
			if($plugin) {
				$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $fields = array( $db->quoteName('enabled') . ' = ' . $status );

        $conditions = array(
          $db->quoteName('type') . ' = ' . $db->quote('plugin'),
          $db->quoteName('element') . ' = ' . $db->quote($plugin->name),
          $db->quoteName('folder') . ' = ' . $db->quote($plugin->group)
        );

        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
			}
		}

		return $result;
	}

	private static function getPlugin($integration) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('extension_id')));
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = '. $db->quote('plugin'));
		$query->where($db->quoteName('element') . ' = '. $db->quote($integration->name));
		$query->where($db->quoteName('folder') . ' = '. $db->quote($integration->group));
		$db->setQuery($query);
		$plugin = $db->loadResult();

		return $plugin;
	}

	private static function getIntegration($component) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.plugin');
		$query->from('#__sppagebuilder_integrations as a');
		$query->where($db->quoteName('component') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$plugin = $db->loadResult();

		if($plugin) {
			return json_decode($plugin);
		}

		return false;
	}

}
