-- SNAP-JE-MENANG 01/02/03 and any future peraduan.
CREATE TABLE contests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    tagline VARCHAR(500) NULL,
    rules MEDIUMTEXT NULL,
    cover_image VARCHAR(255) NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    announce_on DATE NULL,
    whatsapp_number VARCHAR(20) NULL,
    whatsapp_message VARCHAR(500) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_contests_slug (slug),
    KEY idx_contests_listing (status, starts_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contest_prizes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contest_id INT UNSIGNED NOT NULL,
    rank_label VARCHAR(80) NOT NULL,
    prize_name VARCHAR(180) NOT NULL,
    prize_value VARCHAR(80) NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    KEY idx_contest_prizes_contest (contest_id, sort_order),
    CONSTRAINT fk_contest_prizes_contest FOREIGN KEY (contest_id) REFERENCES contests (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- plate_masked holds the partially hidden plate the design shows (e.g. "V** **21").
CREATE TABLE contest_winners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contest_id INT UNSIGNED NOT NULL,
    prize_id INT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    bike VARCHAR(120) NULL,
    plate_masked VARCHAR(20) NULL,
    photo VARCHAR(255) NULL,
    position SMALLINT UNSIGNED NULL,
    is_consolation TINYINT(1) NOT NULL DEFAULT 0,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_contest_winners_contest (contest_id, is_consolation, sort_order),
    CONSTRAINT fk_contest_winners_contest FOREIGN KEY (contest_id) REFERENCES contests (id) ON DELETE CASCADE,
    CONSTRAINT fk_contest_winners_prize FOREIGN KEY (prize_id) REFERENCES contest_prizes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
