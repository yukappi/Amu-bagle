-- AMU BAGEL sample data
USE amu_bagel;
-- Categories (id 1-5 are assigned in this order)
INSERT INTO categories (name_ja, name_en, sort_order) VALUES
    ('プレーン', 'Plain', 1),
    ('おかず', 'Savory', 2),
    ('クリームチーズ', 'Cream Cheese', 3),
    ('スイーツ', 'Sweets', 4),
    ('サンド', 'Sandwich', 5);

    -- Products (id 1-3 are assigned in this order)
INSERT INTO products (category_id, name_ja, name_en, description_ja, description_en, price) VALUES
    (1, 'プレーン', 'Plain',
        '基本のプレーン。ブランド小麦「春よ恋」をつかっています。',
        'Our basic bagel, made with Haruyokoi wheat.',
        250),
    (2, 'ベーコンチーズ', 'Bacon & Cheese',
        '中にはベーコンとチーズがたっぷり。黒こしょう入り。',
        'Filled with bacon and cheese, with black pepper.',
        350),
    (5, 'あんバターサンド', 'Red Bean & Butter Sandwich',   
        '北海道産小豆のつぶあんと、太めのバター。',
        'Hokkaido red bean paste and a thick slab of butter.',
        420);

-- Open days (id 1-2). cutoff_at = 21:00 on the previous day
INSERT INTO open_days (open_date, cutoff_at) VALUES
    ('2026-11-18', '2026-11-17 21:00:00'),
    ('2026-11-25', '2026-11-24 21:00:00');

-- Which bagels are sold on which open day
INSERT INTO open_day_products (open_day_id, product_id) VALUES
    (1, 1), (1, 2), (1, 3),
    (2, 1), (2, 3);

-- Admin account (password: amubagel2026)
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$ISNM2r0B.LJXWZynpAZHy.A3bhC.O7diNKSLLLsNYjqgryjGUjIZq');