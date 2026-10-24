-- Acrescenta a aprovação de contas de mentor. Seguro executar mais de uma vez.
-- Use este ficheiro se a base de dados já foi importada antes.
USE kuntima_bolsas;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'account_status');
SET @sql := IF(@existe = 0,
  "ALTER TABLE users ADD COLUMN account_status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved' AFTER role",
  'SELECT 1');
PREPARE passo FROM @sql;
EXECUTE passo;
DEALLOCATE PREPARE passo;

-- Todas as contas que já existem ficam aprovadas.
UPDATE users SET account_status = 'approved' WHERE account_status IS NULL;
