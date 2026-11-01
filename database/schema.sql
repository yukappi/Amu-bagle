-- AMU BAGEL reservation site: table definitions
-- Written to work on both MySQL 8 and MariaDB

CREATE DATABASE IF NOT EXISTS amu_bagel
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE amu_bagel;

-- Drop existing tables first so this file can be re-imported at any time
-- Reverse creation order: child tables that hold foreign keys go first
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS open_day_products;
DROP TABLE IF EXISTS open_days;
DROP TABLE IF EXISTS product_allergens;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS allergens;
DROP TABLE IF EXISTS categories;

-- Create referenced tables before the tables that point to them

CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name_ja    VARCHAR(100) NOT NULL,
    name_en    VARCHAR(100) NOT NULL DEFAULT '',
    sort_order INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE allergens (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name_ja    VARCHAR(100) NOT NULL,
    name_en    VARCHAR(100) NOT NULL DEFAULT '',
    sort_order INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_allergens_name_ja (name_ja)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Partner shops that sell our bagels wholesale
CREATE TABLE shops (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name_ja        VARCHAR(100) NOT NULL,
    name_en        VARCHAR(100) NOT NULL DEFAULT '',
    days_ja        VARCHAR(100) NOT NULL DEFAULT '',
    days_en        VARCHAR(100) NOT NULL DEFAULT '',
    address        VARCHAR(255) NULL,
    hours          VARCHAR(100) NULL,
    description_ja TEXT         NULL,
    description_en TEXT         NULL,
    url            VARCHAR(255) NULL,
    sort_order     INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products are soft-deleted by setting is_active = 0
CREATE TABLE products (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id    INT UNSIGNED NOT NULL,
    name_ja        VARCHAR(100) NOT NULL,
    name_en        VARCHAR(100) NOT NULL DEFAULT '',
    description_ja TEXT         NULL,
    description_en TEXT         NULL,
    price          INT UNSIGNED NOT NULL,
    image_path     VARCHAR(255) NULL,
    is_sold_out    TINYINT(1)   NOT NULL DEFAULT 0,
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_allergens (
    product_id  INT UNSIGNED NOT NULL,
    allergen_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, allergen_id),
    CONSTRAINT fk_product_allergens_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_product_allergens_allergen
        FOREIGN KEY (allergen_id) REFERENCES allergens (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A date with a row is open; a date without one is closed
-- PHP sets the default cutoff_at (21:00 on the previous day)
CREATE TABLE open_days (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    open_date  DATE         NOT NULL,
    open_time  TIME         NOT NULL DEFAULT '11:00:00',
    close_time TIME         NOT NULL DEFAULT '16:00:00',
    max_items  INT UNSIGNED NOT NULL DEFAULT 40,
    cutoff_at  DATETIME     NOT NULL,
    note       TEXT         NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_open_days_open_date (open_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- max_qty = NULL means no per-product limit
CREATE TABLE open_day_products (
    open_day_id INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NOT NULL,
    max_qty     INT UNSIGNED NULL,
    PRIMARY KEY (open_day_id, product_id),
    CONSTRAINT fk_open_day_products_open_day
        FOREIGN KEY (open_day_id) REFERENCES open_days (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_open_day_products_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_code    VARCHAR(20)  NOT NULL,
    open_day_id   INT UNSIGNED NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone         VARCHAR(30)  NOT NULL,
    email         VARCHAR(254) NOT NULL,
    pickup_time   TIME         NOT NULL,
    total_items   INT UNSIGNED NOT NULL,
    total_price   INT UNSIGNED NOT NULL,
    status        ENUM('received', 'ready', 'picked_up', 'cancelled') NOT NULL DEFAULT 'received',
    note          TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_order_code (order_code),
    CONSTRAINT fk_orders_open_day
        FOREIGN KEY (open_day_id) REFERENCES open_days (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product name and unit price are copied at order time
CREATE TABLE order_items (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity     INT UNSIGNED NOT NULL,
    unit_price   INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
