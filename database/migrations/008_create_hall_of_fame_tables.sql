CREATE TABLE hof_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    handle VARCHAR(80) NULL,
    headline VARCHAR(255) NULL,
    summary TEXT NULL,
    avatar VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    facebook_url VARCHAR(500) NULL,
    instagram_url VARCHAR(500) NULL,
    tiktok_url VARCHAR(500) NULL,
    is_hero_of_month TINYINT(1) NOT NULL DEFAULT 0,
    featured_month DATE NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_hof_profiles_slug (slug),
    KEY idx_hof_profiles_listing (status, is_hero_of_month, featured_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The Biodata tab: free-form headed blocks (pendidikan, kerjaya, pencapaian...).
CREATE TABLE hof_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hof_profile_id INT UNSIGNED NOT NULL,
    heading VARCHAR(160) NOT NULL,
    body TEXT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    KEY idx_hof_sections_profile (hof_profile_id, sort_order),
    CONSTRAINT fk_hof_sections_profile FOREIGN KEY (hof_profile_id) REFERENCES hof_profiles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hof_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hof_profile_id INT UNSIGNED NOT NULL,
    path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    KEY idx_hof_images_profile (hof_profile_id, sort_order),
    CONSTRAINT fk_hof_images_profile FOREIGN KEY (hof_profile_id) REFERENCES hof_profiles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE videos
    ADD CONSTRAINT fk_videos_hof_profile FOREIGN KEY (hof_profile_id) REFERENCES hof_profiles (id) ON DELETE CASCADE
