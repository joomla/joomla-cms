# $Id: joomla.sql 17225 2010-05-24 03:01:15Z dextercowley $

#
# Database updates for 1.6 Beta 12 to Beta 13
#

ALTER TABLE `#__template_styles`
 CHANGE `params` `params` varchar(10240) NOT NULL DEFAULT '';
