# $Id$

#
# Database updates for 1.7.0 to 1.7.1
#
ALTER TABLE #__categories MODIFY description LONGTEXT;
ALTER TABLE #__session MODIFY data MEDIUMTEXT;
ALTER TABLE #__session MODIFY session_id varchar(200);
