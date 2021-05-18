UPDATE "#__mail_templates"
   SET "subject" = 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_SUBJECT',
       "body" = 'COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_BODY'
 WHERE "template_id" = 'com_privacy.userdataexport';
