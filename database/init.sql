-- Create the database with proper character set and collation
CREATE DATABASE IF NOT EXISTS bitrader
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE bitrader;

-- Users table with enhanced security and audit
CREATE TABLE users (
    user_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED') NOT NULL DEFAULT 'ACTIVE',
    last_login_at TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_by BIGINT,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Trading pairs with market data
CREATE TABLE trading_pairs (
    pair_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(20) NOT NULL,
    base_currency VARCHAR(10) NOT NULL,
    quote_currency VARCHAR(10) NOT NULL,
    min_trade_amount DECIMAL(20, 8) NOT NULL,
    max_trade_amount DECIMAL(20, 8) NOT NULL,
    price_decimal_places TINYINT NOT NULL,
    amount_decimal_places TINYINT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_symbol (symbol),
    INDEX idx_base_currency (base_currency),
    INDEX idx_quote_currency (quote_currency),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- Orders with better tracking
CREATE TABLE orders (
    order_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    pair_id BIGINT NOT NULL,
    order_type ENUM('MARKET', 'LIMIT', 'STOP_LOSS', 'TAKE_PROFIT') NOT NULL,
    side ENUM('BUY', 'SELL') NOT NULL,
    price DECIMAL(20, 8) NOT NULL,
    amount DECIMAL(20, 8) NOT NULL,
    filled_amount DECIMAL(20, 8) DEFAULT 0,
    status ENUM('PENDING', 'PARTIALLY_FILLED', 'FILLED', 'CANCELLED', 'REJECTED') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (pair_id) REFERENCES trading_pairs(pair_id),
    INDEX idx_user_id (user_id),
    INDEX idx_pair_id (pair_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Wallets with better precision and tracking
CREATE TABLE wallets (
    wallet_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    currency VARCHAR(10) NOT NULL,
    available_balance DECIMAL(20, 8) DEFAULT 0,
    locked_balance DECIMAL(20, 8) DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_audit_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE INDEX idx_user_currency (user_id, currency),
    INDEX idx_currency (currency)
) ENGINE=InnoDB;

-- Transactions with detailed tracking
CREATE TABLE transactions (
    transaction_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NULL,
    user_id BIGINT NOT NULL,
    type ENUM('DEPOSIT', 'WITHDRAWAL', 'TRADE', 'FEE', 'ADJUSTMENT') NOT NULL,
    amount DECIMAL(20, 8) NOT NULL,
    fee_amount DECIMAL(20, 8) DEFAULT 0,
    currency VARCHAR(10) NOT NULL,
    status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REVERSED') NOT NULL,
    reference_id VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_user_id (user_id),
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_reference_id (reference_id)
) ENGINE=InnoDB;

-- Audit log table
CREATE TABLE audit_logs (
    log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id BIGINT NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;