CREATE DATABASE loyaltyhub_db;

CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'ordinary') NOT NULL,
    company_id INT,
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
);

-- Insert a sample company first
INSERT INTO companies (company_name) VALUES ('Default Company');

-- Insert the default 'uoc' user
INSERT INTO users (username, password, user_type, company_id) VALUES ('uoc', 'uoc', 'ordinary', 1);

-- Insert a sample admin user for testing
INSERT INTO users (username, password, user_type) VALUES ('admin', 'adminpass', 'admin');

CREATE TABLE loyalty_points (
    points_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_id INT NOT NULL,
    points_balance INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
);

-- Assuming user_id 1 is 'uoc' and company_id 1 is 'Default Company'
INSERT INTO loyalty_points (user_id, company_id, points_balance) VALUES (1, 1, 1500);
