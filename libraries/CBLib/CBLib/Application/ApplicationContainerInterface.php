<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 12/10/13 12:55 AM $
* @package CBLib\Application
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
namespace CBLib\Application;

use CBLib\DependencyInjection\ContainerInterface;

defined('CBLIB') or die();

/**
 * Interface ApplicationContainerInterface
 *
 * @method static \CBFramework                             CBFramework()     get CB Framework (legacy one)
 * @method static \CBLib\Database\DatabaseDriverInterface  Database()        get CBLib Database
 * @method static \CBLib\Application\Application           Application()     get CBLib Application
 * @method static \CBLib\Application\ApplicationContainerInterface   DI()    get CBLib Application
 * @method static \CBLib\Application\Config                Config()          get CBLib Config
 * @method static \CBLib\Cms\CmsInterface                  Cms()             get CBLib Cms
 * @method static \CBLib\Cms\CmsPermissionsInterface       CmsPermissions()  get CBLib CmsPermissions
 * @method static \CBLib\Controller\RouterInterface        Router()          get CBLib Router
 * @method static \CBLib\Input\Input                       Input()           get CBLib Input
 * @method static \CBLib\Output\Output                     Output()          get CBLib Output
 * @method static \CBLib\Session\Session                   Session()         get CBLib Session
 * @method static \CBLib\Entity\User\User                  User( $idOrConditions = null )  get CBLib User
 * @method static \CBLib\Entity\User\User                  MyUser()          get CBLib User of current user
 * @method \CBFramework                             getCBFramework()     get CB Framework
 * @method \CBLib\Database\DatabaseDriverInterface  getDatabase()        get CBLib DatabaseDriverInterface
 * @method \CBLib\Application\Application           getApplication()     get CBLib Application
 * @method \CBLib\Application\ApplicationContainerInterface   getDI()    get CBLib Application
 * @method \CBLib\Application\Config                getConfig()          get CBLib Config
 * @method \CBLib\Cms\CmsInterface                  getCms()             get CBLib Cms
 * @method \CBLib\Cms\CmsPermissionsInterface       getCmsPermissions()  get CBLib CmsPermissions
 * @method \CBLib\Controller\RouterInterface        getRouter()          get CBLib Router
 * @method \CBLib\Input\Input                       getInput()           get CBLib Input
 * @method \CBLib\Output\Output                     getOutput()          get CBLib Output
 * @method \CBLib\Session\Session                   getSession()         get CBLib Session
 * @method \CBLib\Entity\User\User                  getUser( $idOrConditions = null )      get CBLib User
 * @method \CBLib\Entity\User\User                  getMyUser()          get CBLib User
 */
interface ApplicationContainerInterface extends ContainerInterface
{
}
