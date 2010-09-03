<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * User controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerUser extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_USERS_USER';

	/**
	 * Overrides parent save method to check the submitted passwords match.
	 *
	 * @return	mixed	Boolean or JError.
	 * @since	1.6
	 */
	public function save()
	{
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// TODO: JForm should really have a validation handler for this.
		if (isset($data['password']) && isset($data['password2'])) {
			// Check the passwords match.
			if ($data['password'] != $data['password2']) {
				$this->setMessage(JText::_('JLIB_USER_ERROR_PASSWORD_NOT_MATCH'), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				return false;
			}

			unset($data['password2']);
		}

		return parent::save();
	}

	/**
	 * Debugs a Users Access Control settings.
	 *
	 * Note: this method needs to be broken into a view and model.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function debug()
	{
		// Access control check on this test.
		if (!JFactory::getUser()->authorise('core.admin', 'com_users')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Get the user id to test.
		$userId = JRequest::getInt('id');
		if (empty($userId)) {
			echo 'No user id.';

			return;
		}

		// Load the user.
		$user = JFactory::getUser($userId);
		if ($user == false) {
			// JUser already throws an error.
			return;
		}

		// Load the asset table.
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('a.name, a.title, a.level')
			->from('#__assets AS a')
			->order('a.lft');
		$db->setQuery($query);
		$assets = $db->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			return JError::raiseWarning(500, $error);
		}

		// This should be a lot smarter and drill into the access.xml files of each component.
		$actions = array(
			'JACTION_LOGIN_SITE'	=> array('core.login.site',		0),
			'JACTION_LOGIN_ADMIN'	=> array('core.login.admin',	0),
			'JACTION_ADMIN'			=> array('core.admin',			1),
			'JACTION_MANAGE'		=> array('core.manage',			1),
			'JACTION_CREATE'		=> array('core.create',			null),
			'JACTION_DELETE'		=> array('core.delete',			null),
			'JACTION_EDIT'			=> array('core.edit',			null),
			'JACTION_EDIT_STATE'	=> array('core.edit.state',		null),
			//'JACTION_EDIT_OWN'	=> array('core.edit.own',		null),
		);


		// Break into results layout. Being lazy here just to get a quick result.
		// Yes JM, will put the language strings in later :)
?>
		<style>
			.test-0 {
				background-color: #FFFFCF;
				text-align: center;
				width: 40px;
			}
			.test-a {
				background-color: #CFFFDA;
				text-align: center;
				width: 40px;
			}
			.test-d {
				background-color: #FFCFCF;
				text-align: center;
				width: 40px;
			}
		</style>
		<h1>Debug Report for User #<?php echo $user->id;?> <?php echo $user->name; ?></h1>
		<table class="adminlist">
			<thead>
				<tr>
					<th>
						Asset Title
					</th>
					<th>
						Asset Name
					</th>
					<?php foreach (array_keys($actions) as $action) : ?>
					<th>
						<?php echo JText::_($action); ?>
					</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($assets as $asset) : ?>
				<tr>
					<th>
						<?php echo $asset->title; ?>
					</th>
					<th>
						<?php echo $asset->name; ?>
					</th>
					<?php foreach ($actions as $action) :
						if ($action[1] === null || $action[1] >= $asset->level) {
							// Do the access check.
							$test	= JAccess::check($user->id, $action[0], $asset->name);

							if ($test === null) {
								$test = 0;
								$text = '-';
							}
							else if ($test == false) {
								$test = 'd';
								$text = '&#10007;';
							}
							else {
								$test = 'a';
								$text = '&#10003;';
							}
						}
						else {
							$test = '';
							$text = '';
						}
					?>
					<th class="test-<?php echo $test;?>">
						<?php echo $text; ?>
					</th>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
<?php
	}
}