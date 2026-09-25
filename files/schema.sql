-- =========================================================
-- PetAdoption Database Schema
-- =========================================================

CREATE DATABASE IF NOT EXISTS petadoption
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE petadoption;

-- ---------------------------------------------------------
-- users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)        NOT NULL,
    email         VARCHAR(150)        NOT NULL UNIQUE,
    password      VARCHAR(255)        NOT NULL,      -- password_hash() output
    role          ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at    TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- pets  (owned/managed by admins)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pets (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)        NOT NULL,
    species       VARCHAR(50)         NOT NULL,       -- Dog, Cat, Bird, etc.
    breed         VARCHAR(100)        DEFAULT NULL,
    age           INT                 DEFAULT NULL,
    description   TEXT                DEFAULT NULL,
    image_path    VARCHAR(255)        DEFAULT NULL,
    status        ENUM('available', 'adopted') NOT NULL DEFAULT 'available',
    added_by      INT                 NOT NULL,       -- admin user id
    created_at    TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pets_admin FOREIGN KEY (added_by) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- adoption_requests
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS adoption_requests (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT                 NOT NULL,
    pet_id        INT                 NOT NULL,
    message       TEXT                DEFAULT NULL,
    status        ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    requested_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    decided_at    TIMESTAMP           NULL DEFAULT NULL,
    CONSTRAINT fk_req_customer FOREIGN KEY (customer_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_req_pet FOREIGN KEY (pet_id) REFERENCES pets(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Optional: seed a couple of sample pets once you have an admin user.
-- INSERT INTO pets (name, species, breed, age, description, added_by)
-- VALUES ('Buddy', 'Dog', 'Labrador', 2, 'Friendly and energetic.', 1);
