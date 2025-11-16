-- Modify the transaction_type column to handle Arabic text
ALTER TABLE passports MODIFY COLUMN transaction_type VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 