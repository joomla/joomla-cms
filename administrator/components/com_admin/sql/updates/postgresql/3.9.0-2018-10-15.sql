CREATE INDEX "#__action_logs_idx_user_id" ON "#__action_logs" ("user_id"); 
CREATE INDEX "#__action_logs_idx_user_id_logdate" ON "#__action_logs" ("user_id", "log_date"); 
CREATE INDEX "#__action_logs_idx_user_id_extension" ON "#__action_logs" ("user_id", "extension");
CREATE INDEX "#__action_logs_idx_extension_itemid" ON "#__action_logs" ("extension", "item_id");
