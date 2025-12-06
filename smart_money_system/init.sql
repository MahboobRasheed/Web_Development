-- init.sql
CREATE DATABASE IF NOT EXISTS smart_money_db;
USE smart_money_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  description VARCHAR(255),
  amount DECIMAL(10,2),
  category VARCHAR(50),
  expense_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  monthly_limit DECIMAL(10,2),
  month INT,
  year INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- You can optionally insert a test user (password: test123)
-- INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', '<hashed-password>');
