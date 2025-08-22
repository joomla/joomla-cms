UPDATE "#__scheduler_task" 
SET "params" = jsonb_set("params", '{email}', '""') 
WHERE "params" ? 'email';
