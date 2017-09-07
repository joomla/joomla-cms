<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Associations\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\ListModel;

/**
 * Methods supporting a list of article records.
 *
 * @since  3.7.0
 */
class Association extends ListModel
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A \JForm object on success, false on failure
	 *
	 * @since  3.7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{

		// Get the form.
		$form = $this->loadForm('com_associations.association', 'association', array('control' => 'jform', 'load_data' => $loadData));

		return !empty($form) ? $form : false;
	}
    public function approved($id, $targetId)
    {
        if (!empty($targetId))
        {
            $parentId=0;
            $db = $this->getDbo();
            $selectQuery = $db->getQuery(true);
            $selectQuery
                ->select('id,parentid')
                ->from($db->quoteName('#__item_associations'))
                ->where($db->quoteName('id') . " = " . $targetId, 'OR')
                ->where($db->quoteName('id') . " = " . $id);
            $db->setQuery($selectQuery);
            //$row = $db->loadColumn();
            $row = $db->loadObjectList();
            $result = sizeof($row);
            echo "result=".$result;
            foreach($row as $rows):
               $id1=(int)$rows->id;

            if ($id1 == (int)$targetId)
                {
                    $parentId=(int)$rows->parentid;      //parent Article  from slave Article
                }
            endforeach;

            /*  $articlealready =0; //Master and slave-Article are not in the tabelle
              $articlealready =1; //Master Article is in  the tabelle
             $articlealready =2; // Slave Article is in  the tabelle
              $articlealready =3; //Master and child-Article are already in the tabelle
            */

            $articlealready=0;
            if ( $result==1) {
                if ($row[0] == $id) {
                    $articlealready = 1;
                } else {
                    $parentId=$row[1];
                    $articlealready = 2;
                }
            }
            if ( $result==2) {
                $articlealready =3;
            }
            switch ($articlealready) {
                case 0: //save master and slave Article
                    $insertQueryParent = $db->getQuery(true);

                    $columns = array('id', 'parentid', 'approved');

                    $values = array($id, 0, 1);

                    $insertQueryParent
                        ->insert($db->quoteName('#__item_associations'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));


                    $db->setQuery($insertQueryParent);
                    $db->execute();

                    $insertQueryChild = $db->getQuery(true);

                    $columns = array('id', 'parentid', 'approved');

                    $values = array($targetId, $id, 1);

                    $insertQueryChild
                        ->insert($db->quoteName('#__item_associations'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    $db->setQuery($insertQueryChild);
                    $db->execute();
                    $message=\JText::_('COM_ASSOCIATIONS_MESSAGE_APPROVED');
                    break;
                case 1: //save child Article
                    $insertQuery = $db->getQuery(true);

                    $columns = array('id', 'parentid', 'approved');

                    $values = array($targetId, $id, 1);

                    $insertQuery
                        ->insert($db->quoteName('#__item_associations'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    $db->setQuery($insertQuery);
                    $db->execute();
                    $message=\JText::_('COM_ASSOCIATIONS_MESSAGE_APPROVED');
                    break;
                case 2: //save parent Article and update the status of the child Article
                    $insertQuery = $db->getQuery(true);

                    $columns = array('id', 'parentid', 'approved');

                    $values = array($id, 0, 1);

                    $insertQuery
                        ->insert($db->quoteName('#__item_associations'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    $db->setQuery($insertQuery);
                    $db->execute();

                    $updateQuery = $db->getQuery(true);

                    $updateQuery
                        ->update($db->quoteName('#__item_associations'))
                        ->set($db->quoteName('approved') . ' = ' . 1)
                        ->where($db->quoteName('id') . " = " . $targetId);

                    $db->setQuery($updateQuery);
                    $db->execute();
                    $message=\JText::_('COM_ASSOCIATIONS_MESSAGE_APPROVED');


                    break;
                case 3:// update  the satuts of the slave Article
                    if ($parentId==$id) //  If the parent Article from thechild Article is thesame as the given parent ID
                    {
                        $updateQuery = $db->getQuery(true);

                        $updateQuery
                            ->update($db->quoteName('#__item_associations'))
                            ->set($db->quoteName('approved') . ' = ' . 1)
                            ->where($db->quoteName('id') . " = " . $targetId);

                        $db->setQuery($updateQuery);
                        $db->execute();
                        $message=\JText::_('COM_ASSOCIATIONS_MESSAGE_APPROVED');
                        break;
                    }
                    else// PReference Article is not Master
                    {
                        $message=\JText::_('COM_ASSOCIATIONS_NOT_APPROVED_NOPARENT');
                    }

            }
        }
        else
        {
            $message=\JText::_('COM_ASSOCIATIONS_NOTCHILD');
        }

        return $message;

    }


}
