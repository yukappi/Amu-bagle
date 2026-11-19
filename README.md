Amu Bagel — Online Pre-order Site
A pre-order website for Amu Bagel, a bagel shop in Nakama, Fukuoka, Japan. Customers choose a pickup day, add bagels to a cart and reserve them online; the shop manages products and pickup days from an admin panel. The site is planned to go live for the shop's customers.

Tech: PHP 8.2, MySQL, HTML5, vanilla CSS (no frameworks)

Features
For customers

Browse the menu by pickup day, with allergen information for each product
Cart and checkout with a confirmation page
Japanese / English interface
For the shop (admin panel, login required)

Manage products, categories, allergens and shops
Set pickup days, order deadlines and how many of each product can be sold that day
View and manage orders
Under the hood

Overselling is prevented: when an order is placed, the pickup day is locked with SELECT … FOR UPDATE inside a transaction, so two customers cannot buy the last item at the same time
All SQL uses prepared statements (protects against SQL injection)
Forms are protected with CSRF tokens; the admin panel uses session-based login
10-table MySQL schema (database/schema.sql) with sample data (database/seed.sql)
Project structure
amu-bagel/
├─ public/          Pages opened in the browser (customer pages; admin/ for the admin panel)
├─ includes/        Shared PHP: DB connection, helpers, CSRF, authentication, reservation rules
├─ lang/            UI text (ja.php / en.php)
├─ config/          Settings such as the DB connection (config.php is not committed)
├─ database/        schema.sql (tables) and seed.sql (sample data)
└─ docs/            Documentation and screenshots
Run locally (XAMPP)
Start Apache and MySQL in XAMPP (PHP 8.2)
Clone this repository into htdocs/amu-bagel
In phpMyAdmin, create a database amu_bagel (utf8mb4_unicode_ci), then import database/schema.sql and database/seed.sql
Copy config/config.sample.php to config/config.php and fill in your DB settings
Open http://localhost/amu-bagel/public/
