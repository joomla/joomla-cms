The Model-View-Controller Packages
==================================

Introduction
------------

Version 12.1 of the platform introduced a new format for
model-view-controller paradigm. Principly, the classes JModel, JView and
JController are now interfaces and the base abstract classes are now
JModelBase, JViewBase and JControllerBase respectively. In additional,
all classes have been simplified removing a lot of coupling with the
Joomla CMS that is unnecessary for standalone Joomla Platform
applications.

All the API for controllers, models and views has moved from the
Application package into separate Controller, Model and View packages
respectively. Much of the API previously devoted to adding include paths
for each of the classes has been removed because of improvements in the
auto-loader or by registering or discovering classes explicitly using
JLoader.

Controllers only support one executable task per class via the execute
method. This differs from the legacy JController class which mapped
tasks to methods in a single class. Messages and redirection are not
always required so have been dropped in this base class. They can be
provided in a downstream class to suit individual applications.
Likewise, methods to create models and views have been dropped in favor
of using application or package factory classes.

Models have been greatly simplified in comparison to their legacy
counterpart. The base model is nothing more than a class to hold state.
All database support methods have been dropped except for database
object support in JModelDatabase. Extended model classes such as
JModelAdmin, JModelForm, JModelItem and JModelList are part of the
legacy platform. Most of their function has been replaced by API
availble in the Content package also new in 12.1.

Views have also been greatly simplified. Views are now injected with a
single model and a controller. Magic get methods have been dropped in
favor of using the model directly. Similarly, assignment methods have
also been dropped in favor of setting class properties explicitly. The
JViewHtml class still implements layout support albeit in a simplified
manner.
