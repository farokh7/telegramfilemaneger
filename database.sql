
CREATE DATABASE IF NOT EXISTS telegram_files CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE telegram_files;

CREATE TABLE folders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  parent_id INT DEFAULT NULL
);

CREATE TABLE files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  telegram_url TEXT NOT NULL,
  folder_id INT,
  FOREIGN KEY (folder_id) REFERENCES folders(id)
);
