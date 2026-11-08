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
-- More products (id 4-13 are assigned in this order)
INSERT INTO products (category_id, name_ja, name_en, description_ja, description_en, price) VALUES
    (1, 'アールグレイオレンジ', 'Earl Grey Orange',
        '生地にはアールグレイ茶葉をたっぷり。中にはオレンジピール。',
        'Dough packed with Earl Grey tea leaves, with orange peel inside.', 300),
    (2, 'チーズ＆チーズ', 'Cheese & Cheese',
        '上には2種のチーズ、中にはごろごろのプロセスチーズ。黒こしょうがきいています。',
        'Two cheeses on top and chunks of cheese inside, with a kick of black pepper.', 340),
    (2, 'チェダーポテト', 'Cheddar Potato',
        '中にはシンプルなポテトサラダ。上からチェダーチーズとスパイス。ずっしり具だくさん。',
        'Potato salad inside, topped with cheddar and spices. Hearty and filling.', 360),
    (3, 'くるみクリームチーズ', 'Walnut Cream Cheese',
        '中にはKiriのクリームチーズとくるみがごろっと。トッピングのくるみが目じるし。',
        'Kiri cream cheese and chunky walnuts inside. Look for the walnut on top.', 350),
    (3, 'ブルーベリークリームチーズ', 'Blueberry Cream Cheese',
        'Kiriのクリームチーズとブルーベリージャム。人気ですぐなくなりがち！',
        'Kiri cream cheese with blueberry jam. Popular, so it sells out fast!', 350),
    (4, 'シナモンバターシュガー', 'Cinnamon Butter Sugar',
        '中にもたっぷりシナモンときび砂糖。バターもしみこんでいます。',
        'Plenty of cinnamon and cane sugar inside, soaked with butter.', 320),
    (4, 'Wチョコオレオ', 'Double Chocolate Oreo',
        'みんな大すきチョコのベーグルにオレオ。子どもたちにも大人気。',
        'Everyone''s favorite chocolate bagel with Oreo. A big hit with kids.', 340),
    (4, 'キャラメルナッツ', 'Caramel Nuts',
        'ほどよいキャラメルソースとナッツのごろごろ感。ナッツは日によってかわります。',
        'Just-right caramel sauce with chunky nuts. The nuts change day to day.', 360),
    (5, 'タンドリーチキンサンド', 'Tandoori Chicken Sandwich',
        '国産とりもも使用。辛みはおさえてあるので子どもも食べやすい。お昼にぴったり！',
        'Japanese chicken thigh, mildly spiced so kids can enjoy it too. Perfect for lunch!', 520),
    (2, 'めんたいマヨチーズ', 'Mentaiko Mayo Cheese',
        '明太マヨとチーズの季節メニュー。アレルギー表示はお店に確認中です。',
        'Seasonal bagel with spicy cod roe mayo and cheese. Allergen info is being confirmed.', 380);

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

-- Shops that sell our bagels (address, hours and URL are not confirmed yet)
INSERT INTO shops (name_ja, name_en, days_ja, days_en, description_ja, description_en, sort_order) VALUES
    ('CRAMBOX 美原町店', 'CRAMBOX Miharamachi',
        '毎週 木・金', 'Every Thu & Fri',
        'AMU BAGELのベーグルがならぶお店です。',
        'A shop where you can find AMU BAGEL bagels.',
        1),
    ('カミトンノ珈琲焙煎所', 'Kamitonno Coffee Roastery',
        '毎週 金・土', 'Every Fri & Sat',
        'AMU BAGELのベーグルを販売しているコーヒー焙煎所です。',
        'A coffee roastery that sells AMU BAGEL bagels.',
        2);

-- Allergens (id 1-11 are assigned in this order)
INSERT INTO allergens (name_ja, name_en, sort_order) VALUES
    ('小麦', 'Wheat', 1),
    ('乳', 'Milk', 2),
    ('卵', 'Egg', 3),
    ('大豆', 'Soy', 4),
    ('くるみ', 'Walnut', 5),
    ('アーモンド', 'Almond', 6),
    ('カシューナッツ', 'Cashew', 7),
    ('ピーナッツ', 'Peanut', 8),
    ('そば', 'Buckwheat', 9),
    ('えび', 'Shrimp', 10),
    ('かに', 'Crab', 11);

-- Which allergens each bagel has (product_id, allergen_id)
INSERT INTO product_allergens (product_id, allergen_id) VALUES
    (1, 1),
    (2, 1), (2, 2), (2, 3),
    (3, 1), (3, 2);

-- Allergens for products 4-13 (13 has none yet = being confirmed)
INSERT INTO product_allergens (product_id, allergen_id) VALUES
    (4, 1),
    (5, 1), (5, 2),
    (6, 1), (6, 2),
    (7, 1), (7, 2), (7, 5),
    (8, 1), (8, 2),
    (9, 1), (9, 2),
    (10, 1), (10, 2), (10, 4),
    (11, 1), (11, 2), (11, 4), (11, 5), (11, 6), (11, 7), (11, 8),
    (12, 1);

-- Sell the new bagels on the open days too
INSERT INTO open_day_products (open_day_id, product_id) VALUES
    (1, 4), (1, 5), (1, 7), (1, 9), (1, 10), (1, 12),
    (2, 6), (2, 8), (2, 11), (2, 13);