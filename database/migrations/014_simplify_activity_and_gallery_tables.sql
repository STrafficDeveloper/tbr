-- The "Event" tab of Aktiviti TBR lists pit stops, so the separate
-- activities table was never used.
DROP TABLE activities;

-- In the design each album *is* the category ("Bike Paling Hensem",
-- "... Meriah"...), so albums don't need a category table of their own.
ALTER TABLE galleries
    DROP FOREIGN KEY fk_galleries_category,
    DROP INDEX idx_galleries_listing,
    DROP COLUMN category_id,
    ADD COLUMN sort_order SMALLINT NOT NULL DEFAULT 0 AFTER cover_image,
    ADD KEY idx_galleries_listing (status, sort_order, published_at);

DROP TABLE gallery_categories;

-- Longer rules text for each peraduan (eligibility, how to join).
ALTER TABLE contests
    ADD COLUMN eligibility TEXT NULL AFTER rules
