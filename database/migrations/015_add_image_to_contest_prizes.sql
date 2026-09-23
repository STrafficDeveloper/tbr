-- Prize photos shown on the contest page (helmet and gear, accessories, ...).
ALTER TABLE contest_prizes
    ADD COLUMN image VARCHAR(255) NULL AFTER prize_value;
