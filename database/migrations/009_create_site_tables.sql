-- Editable key/value pairs so Hive can change copy without a deploy.
CREATE TABLE settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The counter row on the home page (30 Destinasi, 12 Bulan, 2 Pax/Slot, 10+ Tahun).
CREATE TABLE site_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    value VARCHAR(20) NOT NULL,
    label VARCHAR(60) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sponsor and promo strips (Pit Stop, Freebiz and partner banners).
CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    placement VARCHAR(60) NOT NULL,
    image_desktop VARCHAR(255) NULL,
    image_mobile VARCHAR(255) NULL,
    alt_text VARCHAR(255) NULL,
    link_url VARCHAR(500) NULL,
    starts_on DATE NULL,
    ends_on DATE NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_banners_placement (placement, status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-record SEO overrides, keyed by the table the record lives in.
CREATE TABLE seo_meta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(60) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    meta_title VARCHAR(180) NULL,
    meta_description VARCHAR(320) NULL,
    og_image VARCHAR(255) NULL,
    no_index TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seo_meta_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
