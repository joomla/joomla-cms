ALTER TABLE #__redirect_links MODIFY old_url varchar(255);
ALTER TABLE #__redirect_links MODIFY new_url varchar(255);
ALTER TABLE #__categories MODIFY access integer unsigned;
ALTER TABLE #__contact_details MODIFY access integer unsigned;
ALTER TABLE #__extensions MODIFY access integer unsigned;
ALTER TABLE #__menu MODIFY access integer unsigned;
ALTER TABLE #__modules MODIFY access integer unsigned;
ALTER TABLE #__newsfeeds MODIFY access integer unsigned;