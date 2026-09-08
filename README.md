# Pastry Haven

Bakery website for Web Development 1. Cream and tan bakery design, PHP, MySQL (PDO prepared statements), sessions, and form validation.

## Local setup (XAMPP)

1. Copy this folder to `C:\xampp\htdocs\website`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open [http://localhost/website/sql/install.php](http://localhost/website/sql/install.php) once.
4. Visit [http://localhost/website/](http://localhost/website/).

The installer creates the `pastry_haven` database, tables, and menu products. Default MySQL login is `root` with an empty password (XAMPP default). Change this in `includes/db-config.php` if your MySQL password is different.

## What is included

- Home, menu, product pages, cart, custom cake, checkout, contact, FAQ
- Register, login, logout, forgot password
- Favorites stored in MySQL when logged in
- Orders and contact messages saved with prepared statements
- Client-side and server-side form validation
- Errors logged to `data/php-error.log` (not shown to visitors)

## Database CRUD

| Action | Example |
| --- | --- |
| Create | Register, place order, send message, add favorite |
| Read | Menu, product details, account orders |
| Update | Reset password, update cart quantity |
| Delete | Remove favorite, remove cart item |
