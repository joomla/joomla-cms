# To Do

## Issues Detected by Rector Rules

### Joomla\CMS\Application\CMSApplication

* [X] System error: Property `$registeredurlparams` was not found in reflection of class
  `Joomla\CMS\Application\CMSApplication`, reported in
	* `libraries/src/MVC/Controller/BaseController.php`
	* `libraries/src/MVC/Controller/ControllerInterface.php`
	* `libraries/src/MVC/Controller/Exception/CheckinCheckout.php`
	* `libraries/src/MVC/Controller/Exception/ResourceNotFound.php`
	* `libraries/src/MVC/Controller/Exception/Save.php`
	* `libraries/src/MVC/Controller/Exception/SendEmail.php`
	* `libraries/src/MVC/Controller/FormController.php`
	* `libraries/src/MVC/Factory/ApiMVCFactory.php`
	* `libraries/src/MVC/Factory/LegacyFactory.php`
	* `libraries/src/MVC/Factory/MVCFactory.php`
	* `libraries/src/MVC/Factory/MVCFactoryAwareTrait.php`
	* `libraries/src/MVC/Factory/MVCFactoryInterface.php`
	* `libraries/src/MVC/Factory/MVCFactoryServiceInterface.php`
	* `libraries/src/MVC/View/CategoryFeedView.php`
	* `libraries/src/MVC/View/CategoryView.php`
	* `libraries/src/MVC/View/Event/OnGetApiFields.php`
	* `libraries/src/MVC/View/FormView.php`
	* `libraries/src/MVC/View/JsonView.php`
	* `libraries/src/MVC/View/ListView.php`
	* `libraries/src/Mail/Mail.php`
	* `libraries/src/Mail/MailHelper.php`
	* `plugins/fields/text/tmpl/text.php`
	* `plugins/fields/textarea/textarea.php`
	* `plugins/fields/textarea/tmpl/textarea.php`
	* `plugins/fields/url/tmpl/url.php`
	* `plugins/fields/url/url.php`
	* `plugins/fields/user/tmpl/user.php`
	* `plugins/fields/user/user.php`
	* `plugins/fields/usergrouplist/tmpl/usergrouplist.php`
	* `plugins/fields/usergrouplist/usergrouplist.php`
	* `plugins/filesystem/local/local.php`
	* `plugins/filesystem/local/src/Adapter/LocalAdapter.php`

  > Fixed by adding `@property array $registeredurlparams` to `Joomla\CMS\Application\CMSApplication`.

### Joomla\CMS\Application\WebApplication

* [ ] Fatal error: Declaration of
    ~~~php
    Joomla\CMS\Application\WebApplication::triggerEvent($eventName, Joomla\Event\Event|array $args = [])
    ~~~
  must be compatible with
    ~~~php
    Joomla\CMS\Application\EventAwareInterface::triggerEvent($eventName, $args = [])
    ~~~

    * `libraries/src/Application/EventAware.php:84`
    > Fixed by adding type hint to interface.

### Joomla\CMS\Date\Date

* [ ] System error: "Property `$noSuchProperty` was not found in reflection of class `Joomla\CMS\Date\Date`, reported in
	* `tests/Unit/Libraries/Cms/Date/DateTest.php`

### Joomla\CMS\Feed\Feed

* [ ] System error: Property `$unknown` was not found in reflection of class `Joomla\CMS\Feed\Feed`, reported in
	* `tests/Unit/Libraries/Cms/Feed/FeedTest.php`

### Joomla\CMS\Feed\FeedEntry

* [ ] System error: Property `$unknown` was not found in reflection of class `Joomla\CMS\Feed\FeedEntry`, reported in
	* `tests/Unit/Libraries/Cms/Feed/FeedEntryTest.php`

* [ ] System error: Property `$guid` was not found in reflection of class `Joomla\CMS\Feed\FeedEntry`, reported in
	* `libraries/src/Feed/Parser/RssParser.php`

### Joomla\Component\Finder\Administrator\Indexer\Result

* [ ] System error: Property `$context` was not found in reflection of class
`Joomla\Component\Finder\Administrator\Indexer\Result`, reported in
  * `administrator/components/com_finder/src/Indexer/Helper.php`

* [ ] System error: Property `$summary` was not found in reflection of class
`Joomla\Component\Finder\Administrator\Indexer\Result`, reported in
  * `administrator/components/com_finder/src/Indexer/Indexer.php`

* [ ] System error: Property `$params` was not found in reflection of class
`Joomla\Component\Finder\Administrator\Indexer\Result`, reported in
  * `plugins/finder/contacts/contacts.php`
  * `plugins/finder/content/content.php`
  * `plugins/finder/newsfeeds/newsfeeds.php`
  * `plugins/finder/tags/tags.php`

* [ ] System error: Property `$extension` was not found in reflection of class
`Joomla\Component\Finder\Administrator\Indexer\Result`, reported in
  * `plugins/finder/categories/categories.php`

* [ ] System error: "Property `$mime` was not found in reflection of class
`Joomla\Component\Finder\Administrator\Indexer\Result`, reported in
  * `plugins/system/highlight/highlight.php`

### Joomla\Component\Installer\Administrator\Model\DiscoverModel

* [ ] System error: Property `$_message` was not found in reflection of class
`Joomla\Component\Installer\Administrator\Model\DiscoverModel`, reported in
  * `administrator/components/com_installer/src/Controller/DiscoverController.php`

### Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel

* [ ] System error: Property `$_message` was not found in reflection of class
`Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel`, reported in
  * `administrator/components/com_joomlaupdate/src/Controller/UpdateController.php`

### Joomla\Input\Input

* [X] System error: Property `$Json` was not found in reflection of class `Joomla\Input\Input`, reported in
  * `administrator/components/com_config/src/Model/ApplicationModel.php`
  > Fixed by `\Joomla\JoomlaRector\Rules\Input\V2\PropertyFetch\ToLowerMagicPropertyRector`

* [ ] System error: Property `$json` was not found in reflection of class `Joomla\Input\Input`, reported in
  * `administrator/components/com_media/src/Controller/ApiController.php`
  * `api/components/com_categories/src/Controller/CategoriesController.php`
  * `api/components/com_config/src/Controller/ApplicationController.php`
  * `api/components/com_config/src/Controller/ComponentController.php`
  * `api/components/com_contact/src/Controller/ContactController.php`
  * `api/components/com_languages/src/Controller/OverridesController.php`
  * `api/components/com_languages/src/Controller/StringsController.php`
  * `api/components/com_media/src/Controller/MediaController.php`
  * `api/components/com_menus/src/Controller/ItemsController.php`
  * `api/components/com_plugins/src/Controller/PluginsController.php`
  * `libraries/src/MVC/Controller/ApiController.php`

  > To be fixed in `\Joomla\Input\Input` by adding `@property-read Json $json`.

* [ ] System error: Property `$executable` was not found in reflection of class `Joomla\Input\Input`, reported in
  * `libraries/src/Application/DaemonApplication.php`

* [X] System error: Property `$method` was not found in reflection of class `Joomla\Input\Input`, reported in
  * `libraries/src/Session/Session.php`
    > This one is bogus; the actual code is `$input->$method` (variable variable), which is ok.

### Missing Scope

* [ ] `administrator/components/com_banners/src/Table/BannerTable.php` – System error: Node "PhpParser\Node\Expr\BinaryOp\BooleanAnd" with parent of "PhpParser\Node\Stmt\If_" is missing scope required for scope refresh
* [ ] `components/com_newsfeeds/src/Helper/RouteHelper.php` – System error: Node "PhpParser\Node\Stmt\If_" with parent of "PhpParser\Node\Stmt\Else_" is missing scope required for scope refresh
* [ ] `libraries/src/Console/TasksRunCommand.php` – System error: Node "PhpParser\Node\Expr\Array_" with parent of "PhpParser\Node\Expr\BinaryOp\Identical" is missing scope required for scope refresh
* [ ] `libraries/src/Environment/Browser.php` – System error: Node "PhpParser\Node\Expr\BinaryOp\NotEqual" with parent of "PhpParser\Node\Stmt\If_" is missing scope required for scope refresh
* [ ] `plugins/sampledata/blog/blog.php` – System error: Node "PhpParser\Node\Expr\Array_" with parent of "PhpParser\Node\Stmt\Return_" is missing scope required for scope refresh
* [ ] `plugins/sampledata/testing/testing.php` – System error: Node "PhpParser\Node\Expr\Array_" with parent of "PhpParser\Node\Stmt\Return_" is missing scope required for scope refresh

### Indentation Error

Some of these seem to be subsequent errors from 'unknown property'.

* [ ] System error: PhpParser\Internal\TokenStream::getIndentationBefore(): Return value must be of type int, null
returned, reported in
  * `administrator/components/com_categories/tmpl/categories/default.php`
  * `administrator/components/com_checkin/tmpl/checkin/default.php`
  * `administrator/components/com_contenthistory/tmpl/history/modal.php`
  * `administrator/components/com_installer/src/Model/InstallerModel.php`
  * `administrator/components/com_installer/tmpl/languages/default.php`
  * `administrator/components/com_joomlaupdate/src/Model/UpdateModel.php`
  * `administrator/components/com_languages/tmpl/overrides/default.php`
  * `administrator/components/com_menus/tmpl/item/edit_container.php`
  * `administrator/components/com_modules/src/Helper/ModulesHelper.php`
  * `administrator/components/com_scheduler/src/Controller/DisplayController.php`
  * `administrator/components/com_tags/tmpl/tags/default.php`
  * `administrator/components/com_templates/tmpl/template/default_tree.php`
  * `administrator/components/com_templates/tmpl/template/default_tree_media.php`
  * `administrator/components/com_users/src/Service/HTML/Users.php`
  * `administrator/modules/mod_feed/tmpl/default.php`
  * `build/psr12/clean_errors.php`
  * `components/com_contact/tmpl/categories/default_items.php`
  * `components/com_contact/tmpl/category/default_children.php`
  * `components/com_content/tmpl/category/blog_children.php`
  * `components/com_newsfeeds/tmpl/categories/default_items.php`
  * `components/com_newsfeeds/tmpl/category/default_children.php`
  * `components/com_newsfeeds/tmpl/newsfeed/default.php`
  * `libraries/src/Console/ExtensionDiscoverInstallCommand.php`
  * `modules/mod_articles_news/tmpl/vertical.php`
  * `modules/mod_breadcrumbs/tmpl/default.php`
  * `templates/cassiopeia/error.php`
