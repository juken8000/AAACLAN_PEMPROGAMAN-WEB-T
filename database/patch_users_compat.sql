USE kostrack;

ALTER TABLE users
  ADD COLUMN nama_depan VARCHAR(100) NULL AFTER id,
  ADD COLUMN nama_belakang VARCHAR(100) NULL AFTER nama_depan,
  ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama_belakang,
  ADD COLUMN email VARCHAR(100) NULL AFTER role,
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;

UPDATE users
SET
  nama_depan = COALESCE(nama_depan, SUBSTRING_INDEX(full_name, ' ', 1)),
  nama_belakang = COALESCE(nama_belakang, NULLIF(TRIM(SUBSTRING(full_name, LENGTH(SUBSTRING_INDEX(full_name, ' ', 1)) + 1)), '')),
  no_hp = COALESCE(no_hp, phone),
  email = COALESCE(email, CONCAT(username, '@kostrack.test')),
  role = CASE WHEN role IN ('pemilik', 'admin', 'owner') THEN 'owner' ELSE 'penghuni' END;

ALTER TABLE users
  MODIFY email VARCHAR(100) NOT NULL,
  ADD UNIQUE KEY uq_users_email (email);
