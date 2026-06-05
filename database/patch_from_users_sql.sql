USE kostrack;

SET @add_is_active = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT "is_active already exists"'
  )
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'is_active'
);
PREPARE stmt FROM @add_is_active;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_email_index = (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE users ADD UNIQUE KEY uq_users_email (email)',
    'SELECT "email index already exists"'
  )
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND INDEX_NAME = 'uq_users_email'
);
PREPARE stmt FROM @add_email_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE users
SET role = CASE WHEN role IN ('pemilik', 'admin', 'owner') THEN 'owner' ELSE 'penghuni' END;
