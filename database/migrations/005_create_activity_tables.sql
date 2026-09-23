-- Posts shown under the "Event" tab of Aktiviti TBR.
CREATE TABLE activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    excerpt VARCHAR(500) NULL,
    body MEDIUMTEXT NULL,
    cover_image VARCHAR(255) NULL,
    likes_count INT UNSIGNED NOT NULL DEFAULT 0,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_activities_slug (slug),
    KEY idx_activities_listing (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared by the Panas Atas Jalan page and the Hall of Fame "Content" tab.
CREATE TABLE videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    section ENUM('panas_atas_jalan', 'hall_of_fame') NOT NULL DEFAULT 'panas_atas_jalan',
    hof_profile_id INT UNSIGNED NULL,
    provider ENUM('youtube', 'tiktok', 'file') NOT NULL DEFAULT 'youtube',
    video_id VARCHAR(120) NULL,
    video_url VARCHAR(500) NULL,
    thumbnail VARCHAR(255) NULL,
    duration_seconds SMALLINT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_videos_slug (slug),
    KEY idx_videos_listing (section, status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
