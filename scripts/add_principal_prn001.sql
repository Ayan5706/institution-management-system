-- Add principal account PRN001 (safe to re-run)
START TRANSACTION;

INSERT INTO `users` (
  `role`,
  `login_id`,
  `password_hash`,
  `full_name`,
  `email`,
  `phone`,
  `is_active`,
  `must_change_password`,
  `created_by`,
  `created_at`
)
SELECT
  'PRINCIPAL',
  'PRN001',
  '$2y$10$NyEqQD7lLA3AlTl65tBNouDp6xR6/LGc7xd6m3P0cvQwVI7c5CyzK',
  'Rajesh Khanna',
  'rajeshkhanna68378@gmail.com',
  NULL,
  1,
  1,
  NULL,
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `login_id` = 'PRN001'
);

COMMIT;
