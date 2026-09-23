-- A member books one slot per pit stop. NULL user_ids (e.g. walk-ins an
-- admin adds by hand later) are not constrained by a UNIQUE index.
ALTER TABLE pitstop_registrations
    ADD UNIQUE KEY uq_pitstop_registration_member (event_id, user_id)
