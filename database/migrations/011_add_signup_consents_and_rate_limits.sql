-- Opt-ins from the home page sign-up form. consent_at / consent_ip record
-- when and from where the member agreed, as the PDPA audit trail.
ALTER TABLE users
    ADD COLUMN follows_tbr TINYINT(1) NOT NULL DEFAULT 0 AFTER avatar_path,
    ADD COLUMN follows_raja_kapcai TINYINT(1) NOT NULL DEFAULT 0 AFTER follows_tbr,
    ADD COLUMN whatsapp_opt_in TINYINT(1) NOT NULL DEFAULT 0 AFTER follows_raja_kapcai,
    ADD COLUMN contest_opt_in TINYINT(1) NOT NULL DEFAULT 0 AFTER whatsapp_opt_in,
    ADD COLUMN consent_at DATETIME NULL AFTER contest_opt_in,
    ADD COLUMN consent_ip VARBINARY(16) NULL AFTER consent_at;

-- One row per hit on a throttled action; counted inside a sliding window.
CREATE TABLE rate_limit_hits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bucket VARCHAR(190) NOT NULL,
    hit_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_rate_limit_hits_bucket (bucket, hit_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
