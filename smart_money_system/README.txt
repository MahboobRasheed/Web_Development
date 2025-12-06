Smart Money Management System - Project Files

How to use:
1. Copy the 'smart_money_system' folder to your webserver root (e.g., C:/xampp/htdocs/).
2. Start Apache and MySQL.
3. Import init.sql into phpMyAdmin or run it to create the database and tables.
4. Edit db_connect.php if your DB credentials differ.
5. Visit http://localhost/smart_money_system/register.php to create an account.
6. Login and use the app.

Notes:
- generate_pdf.php currently outputs a CSV file. To produce PDF, follow the comment inside the file and integrate FPDF or TCPDF.
- Tailwind is loaded via CDN for convenience.
