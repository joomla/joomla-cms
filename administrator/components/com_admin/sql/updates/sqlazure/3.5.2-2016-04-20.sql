UPDATE [#__categories] SET [extension] = 'com_users.notes' WHERE [extension] = 'com_users';
UPDATE [#__content_types] SET [type_alias] = 'com_users.notes.category' WHERE [type_alias] = 'com_users.category';
