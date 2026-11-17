Deployment checklist — move from XAMPP (local) to a production server

1) Export the database from XAMPP
   - Using phpMyAdmin: Export > Quick > SQL
   - Or using mysqldump: mysqldump -u root ItsInventario > its_inventario_dump.sql

2) Create the database and a restricted user on the server
   - mysql -u root -p
   - CREATE DATABASE ItsInventario CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   - CREATE USER 'its_user'@'localhost' IDENTIFIED BY 'StrongPasswordHere';
   - GRANT SELECT, INSERT, UPDATE, DELETE ON ItsInventario.* TO 'its_user'@'localhost';
   - FLUSH PRIVILEGES;

3) Import the dump on the server
   - mysql -u its_user -p ItsInventario < its_inventario_dump.sql

4) Configure application credentials
   - On the server prefer environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASS).
   - Alternatively, edit `includes/config.php` on the server (don't commit it).

5) Secure the server
   - Enable HTTPS (Let's Encrypt / certbot).
   - Disable display_errors in php.ini and set log_errors = On.
   - Set proper file permissions; web root should be owned by www-data (or web user) and not writable by others.
   - Use firewall (ufw) to only open necessary ports.

6) Backups and monitoring
   - Setup cron to export DB periodically or use incremental backups.
   - Store backups off-site (S3, remote server).

7) Post-deploy checks
   - Verify forms work, entries are stored, and logs show no errors.
   - Run a security scan (e.g., OWASP ZAP) and fix findings.
