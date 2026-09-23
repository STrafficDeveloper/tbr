CREATE TABLE port_riders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    category ENUM('bike_shop', 'pitstop', 'food', 'fuel', 'other') NOT NULL DEFAULT 'other',
    address VARCHAR(255) NULL,
    city VARCHAR(80) NULL,
    state VARCHAR(40) NOT NULL,
    postcode VARCHAR(10) NULL,
    phone VARCHAR(30) NULL,
    maps_url VARCHAR(500) NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    likes_count INT UNSIGNED NOT NULL DEFAULT 0,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_port_riders_slug (slug),
    KEY idx_port_riders_listing (status, state, name),
    FULLTEXT KEY ft_port_riders_search (name, city, address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guests are deduplicated on a hashed IP so raw addresses are never stored here.
CREATE TABLE port_rider_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    port_rider_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    visitor_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_port_rider_likes_user (port_rider_id, user_id),
    UNIQUE KEY uq_port_rider_likes_visitor (port_rider_id, visitor_hash),
    CONSTRAINT fk_port_rider_likes_rider FOREIGN KEY (port_rider_id) REFERENCES port_riders (id) ON DELETE CASCADE,
    CONSTRAINT fk_port_rider_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
