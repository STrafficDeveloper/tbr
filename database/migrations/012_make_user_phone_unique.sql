-- Members can log in with their phone number, so one number = one account.
-- NULLs (e.g. the seeded admin) are allowed to repeat under a UNIQUE index.
ALTER TABLE users
    ADD UNIQUE KEY uq_users_phone (phone)
