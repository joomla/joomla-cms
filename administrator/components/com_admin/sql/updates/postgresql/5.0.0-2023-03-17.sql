DELETE FROM "#__scheduler_tasks" WHERE "type" = 'demoTask_r1.sleep';
DELETE FROM "#__extensions" WHERE "type" = 'plugin' AND "element" = 'demotasks' AND "folder" = 'task' AND "client_id" = 0;
