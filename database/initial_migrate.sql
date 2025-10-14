-- ============================================================================
-- Laravel Clean Architecture Starter Kit - Initial Database Migration
-- ============================================================================
-- Database: PostgreSQL
-- Description: Creates tables for user authentication with OTP and JWT support
-- ============================================================================

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS otp_verifications CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================================================
-- Table: users
-- Description: Stores user accounts with authentication details
-- ============================================================================
CREATE TABLE users (
    id VARCHAR(26) PRIMARY KEY,  -- ULID format
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    user_type VARCHAR(50) NOT NULL DEFAULT 'USER',  -- USER, ADMIN, etc.
    status VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',   -- ACTIVE, INACTIVE, SUSPENDED
    email_verified_at TIMESTAMP,
    phone_verified_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    CONSTRAINT users_user_type_check CHECK (user_type IN ('USER', 'ADMIN', 'CONTENT_CREATOR', 'ADVERTISER')),
    CONSTRAINT users_status_check CHECK (status IN ('ACTIVE', 'INACTIVE', 'SUSPENDED'))
);

-- Indexes for users table
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created_at ON users(created_at);

-- ============================================================================
-- Table: otp_verifications
-- Description: Stores OTP codes for phone/email verification
-- ============================================================================
CREATE TABLE otp_verifications (
    id VARCHAR(26) PRIMARY KEY,  -- ULID format
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    otp_code VARCHAR(10) NOT NULL,
    user_type VARCHAR(50) NOT NULL DEFAULT 'USER',
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    verified_at TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    max_attempts INTEGER NOT NULL DEFAULT 3,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    CONSTRAINT otp_user_type_check CHECK (user_type IN ('USER', 'ADMIN', 'CONTENT_CREATOR', 'ADVERTISER'))
);

-- Indexes for otp_verifications table
CREATE INDEX idx_otp_phone ON otp_verifications(phone);
CREATE INDEX idx_otp_email ON otp_verifications(email);
CREATE INDEX idx_otp_is_verified ON otp_verifications(is_verified);
CREATE INDEX idx_otp_expires_at ON otp_verifications(expires_at);
CREATE INDEX idx_otp_created_at ON otp_verifications(created_at);

-- ============================================================================
-- Table: sessions
-- Description: Stores JWT session tokens for authenticated users
-- ============================================================================
CREATE TABLE sessions (
    id VARCHAR(26) PRIMARY KEY,  -- ULID format
    user_id VARCHAR(26) NOT NULL,
    token TEXT NOT NULL,  -- Original JWT token (for reference, not used for validation)
    hashed_token VARCHAR(255) NOT NULL UNIQUE,  -- SHA-256 hash of token (used for lookup)
    device_name VARCHAR(255),
    ip_address VARCHAR(45),  -- Supports both IPv4 and IPv6
    user_agent TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at TIMESTAMP NOT NULL,
    last_used_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Foreign key
    CONSTRAINT fk_sessions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for sessions table
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_hashed_token ON sessions(hashed_token);
CREATE INDEX idx_sessions_is_active ON sessions(is_active);
CREATE INDEX idx_sessions_expires_at ON sessions(expires_at);
CREATE INDEX idx_sessions_last_used_at ON sessions(last_used_at);
CREATE INDEX idx_sessions_created_at ON sessions(created_at);

-- ============================================================================
-- Trigger: Update updated_at timestamp automatically
-- ============================================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply trigger to users table
CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Apply trigger to otp_verifications table
CREATE TRIGGER update_otp_updated_at
    BEFORE UPDATE ON otp_verifications
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- Initial Data (Optional)
-- ============================================================================
-- You can add seed data here if needed
-- Example:
-- INSERT INTO users (id, username, email, phone, password_hash, user_type, status, created_at, updated_at)
-- VALUES ('01EXAMPLE123456789ABCDEF', 'admin', 'admin@example.com', '+1234567890', '$2y$12$...', 'ADMIN', 'ACTIVE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- ============================================================================
-- Notes:
-- ============================================================================
-- 1. All IDs use ULID format (26 characters) for better performance and sortability
-- 2. Passwords are hashed using BCrypt (cost=12)
-- 3. JWT tokens are hashed with SHA-256 for secure storage and fast lookup
-- 4. Phone numbers should be stored in E.164 format (e.g., +1234567890)
-- 5. Timestamps are stored in UTC
-- 6. The updated_at column is automatically updated via trigger
-- 7. Sessions are automatically deleted when a user is deleted (CASCADE)
-- ============================================================================
