# Placeholder file for database changes for version 2.5.7
UPDATE  `#__assets` SET name=REPLACE( name, 'com_user.notes.category','com_users.category'  );
UPDATE  `#__categories` SET extension=REPLACE( extension, 'com_user.notes.category','com_users.category'  );
