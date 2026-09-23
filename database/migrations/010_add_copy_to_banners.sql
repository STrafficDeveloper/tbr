-- The shared promo strips carry a heading, a line of copy and a button label.
ALTER TABLE banners
    ADD COLUMN body VARCHAR(500) NULL AFTER title,
    ADD COLUMN cta_label VARCHAR(60) NULL AFTER link_url
