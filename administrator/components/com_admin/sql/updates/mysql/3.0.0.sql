# Placeholder file for database changes for version 3.0.0
UPDATE  `#__assets` SET name=REPLACE( name, 'com_user.notes.category','com_users.category'  );
UPDATE  `#__categories` SET extension=REPLACE( extension, 'com_user.notes.category','com_users.category'  );
