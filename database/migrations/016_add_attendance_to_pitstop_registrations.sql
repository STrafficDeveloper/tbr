-- On-site check-in: the crew ticks riders off in the admin instead of scanning QR codes.
ALTER TABLE pitstop_registrations
    ADD COLUMN attended_at DATETIME NULL AFTER reviewed_at;
