UPDATE `#__mail_templates`
   SET `params` = '{"tags":["name","sitename","siteurl","username"]}'
 WHERE `template_id` = 'com_users.registration.user.registration_mail' AND `params` = '{"tags":["name","sitename","activate","siteurl","username"]}';

UPDATE `#__mail_templates`
   SET `params` = '{"tags":["name","sitename","siteurl","username","password_clear"]}'
 WHERE `template_id` = 'com_users.registration.user.registration_mail_w_pw' AND `params` = '{"tags":["name","sitename","activate","siteurl","username","password_clear"]}';
