CREATE TABLE bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    is_primary TINYINT(1) DEFAULT 0,
    added_on DATETIME DEFAULT CURRENT_TIMESTAMP
);
