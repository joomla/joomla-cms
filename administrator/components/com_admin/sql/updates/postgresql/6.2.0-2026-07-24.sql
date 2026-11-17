ALTER TABLE "#__finder_links" ADD COLUMN "cat_access" integer DEFAULT 1 NOT NULL /** CAN FAIL **/;

DROP INDEX "#__finder_links_idx_published_list" /** CAN FAIL **/;
DROP INDEX "#__finder_links_idx_published_sale" /** CAN FAIL **/;

CREATE INDEX "#__finder_links_idx_published_list" on "#__finder_links" ("published", "state", "access", "cat_access", "publish_start_date", "publish_end_date", "list_price") /** CAN FAIL **/;
CREATE INDEX "#__finder_links_idx_published_sale" on "#__finder_links" ("published", "state", "access", "cat_access", "publish_start_date", "publish_end_date", "sale_price") /** CAN FAIL **/;
