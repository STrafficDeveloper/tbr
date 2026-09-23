-- Categories match the Figma tabs: bike paling hensem / meriah / raya / sempoi.
CREATE TABLE gallery_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_gallery_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE galleries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description VARCHAR(500) NULL,
    cover_image VARCHAR(255) NULL,
    likes_count INT UNSIGNED NOT NULL DEFAULT 0,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_galleries_slug (slug),
    KEY idx_galleries_listing (status, category_id, published_at),
    CONSTRAINT fk_galleries_category FOREIGN KEY (category_id) REFERENCES gallery_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gallery_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gallery_id INT UNSIGNED NOT NULL,
    path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    caption VARCHAR(500) NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_gallery_images_gallery (gallery_id, sort_order),
    CONSTRAINT fk_gallery_images_gallery FOREIGN KEY (gallery_id) REFERENCES galleries (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
