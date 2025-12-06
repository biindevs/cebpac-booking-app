# 🚀 Hostinger Deployment Guide
## Complete Step-by-Step Instructions

This guide will walk you through deploying your CebuPac Booking App to Hostinger.

---

## 📋 Pre-Deployment Checklist

Before starting, make sure you have:
- [ ] Hostinger account created
- [ ] Hosting plan activated
- [ ] Domain name configured (or subdomain)
- [ ] All local files ready
- [ ] Database backup from local (if you want to migrate data)

---

## 🗄️ STEP 1: Create Database in Hostinger

### 1.1 Access Hostinger hPanel
1. Log into your Hostinger account
2. Go to **hPanel** (hosting control panel)
3. Navigate to **Databases** → **MySQL Databases**

### 1.2 Create Database
1. Click **"Create Database"**
2. Enter database name (e.g., `cebpac_booking_db`)
3. Click **"Create"**

### 1.3 Create Database User
1. Scroll down to **"MySQL Users"** section
2. Enter username (e.g., `cebpac_user`)
3. Enter a strong password (save this!)
4. Click **"Create User"**

### 1.4 Link User to Database
1. Scroll to **"Add User to Database"** section
2. Select your database and user
3. Click **"Add"**
4. Check **"ALL PRIVILEGES"**
5. Click **"Make Changes"**

### 1.5 Save Your Credentials
**IMPORTANT:** Write down these details:
```
Database Host: localhost (usually)
Database Name: [your_database_name]
Database User: [your_username]
Database Password: [your_password]
Database Port: 3306
```

---

## 📤 STEP 2: Upload Files to Hostinger

### 2.1 Access File Manager
1. In hPanel, go to **Files** → **File Manager**
2. Navigate to `public_html` folder (this is your website root)

### 2.2 Upload Application Files

**Option A: Using File Manager (Recommended for beginners)**
1. Click **"Upload"** button
2. Select all files from your `cebpac-booking-app` folder
3. Wait for upload to complete
4. Extract ZIP file if you uploaded as ZIP

**Option B: Using FTP (Faster for large files)**
1. In hPanel, go to **Files** → **FTP Accounts**
2. Note your FTP credentials
3. Use FileZilla (free) to connect:
   - Host: `ftp.yourdomain.com` or IP address
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21
4. Upload all files to `public_html/`

### 2.3 File Structure on Hostinger
Your files should be in:
```
public_html/
├── config/
│   ├── db.php (you'll create this)
│   └── db.php.example
├── controllers/
├── models/
├── views/
├── public/
│   ├── css/
│   ├── js/
│   ├── uploads/ (create this folder)
│   └── index.php
├── vendor/ (if using Composer)
├── .htaccess
└── cebpac_booking_db_production.sql
```

### 2.4 Set File Permissions
1. Right-click on `public/uploads/` folder
2. Select **"Change Permissions"**
3. Set to **755** (or 777 if 755 doesn't work)
4. Click **"Change"**

---

## ⚙️ STEP 3: Configure Database Connection

### 3.1 Create Production Config File
1. In File Manager, go to `public_html/config/`
2. Copy `db.php.example` and rename to `db.php`
3. Edit `db.php` with your database credentials:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username_from_step_1');
define('DB_PASS', 'your_password_from_step_1');
define('DB_NAME', 'your_database_name_from_step_1');
define('DB_PORT', 3306);

// ... rest of file stays the same
```

### 3.2 Verify Config File
Make sure `db.php` has:
- ✅ Correct database host (usually `localhost`)
- ✅ Correct username
- ✅ Correct password
- ✅ Correct database name
- ✅ Port 3306

---

## 🗃️ STEP 4: Import Database

### 4.1 Access phpMyAdmin
1. In hPanel, go to **Databases** → **phpMyAdmin**
2. Click **"Open phpMyAdmin"**

### 4.2 Select Your Database
1. In left sidebar, click on your database name
2. You should see an empty database

### 4.3 Import SQL File
1. Click **"Import"** tab at the top
2. Click **"Choose File"**
3. Select `cebpac_booking_db_production.sql` from your local computer
4. Click **"Go"** at the bottom
5. Wait for import to complete (should see "Success" message)

### 4.4 Verify Import
1. Click on your database in left sidebar
2. You should see:
   - `accounts` table
   - `bookings` table
   - Views: `account_booking_summary`, `upcoming_bookings`

---

## 🔧 STEP 5: Configure .htaccess

### 5.1 Check RewriteBase
1. Open `.htaccess` file in File Manager
2. Verify `RewriteBase /` is set correctly:
   - If app is in root: `RewriteBase /`
   - If app is in subfolder: `RewriteBase /subfolder/`

### 5.2 Verify mod_rewrite is Enabled
Hostinger has mod_rewrite enabled by default, but if you get 500 errors:
1. Contact Hostinger support
2. Or check if `.htaccess` syntax is correct

---

## ✅ STEP 6: Test Your Application

### 6.1 Access Your Website
1. Go to `https://yourdomain.com` (or your subdomain)
2. You should see the accounts page

### 6.2 Test Basic Functions
- [ ] Can view accounts list
- [ ] Can create new account
- [ ] Can edit account
- [ ] Can create booking
- [ ] Can upload PDF
- [ ] Travel funds display correctly

### 6.3 Check for Errors
1. If you see errors, check:
   - Database connection in `config/db.php`
   - File permissions on `public/uploads/`
   - PHP error logs in hPanel → **Advanced** → **Error Logs**

---

## 🔒 STEP 7: Security & Optimization

### 7.1 Enable SSL (HTTPS)
1. In hPanel, go to **SSL** section
2. Click **"Install SSL Certificate"**
3. Select **"Let's Encrypt"** (free)
4. Click **"Install"**
5. Wait 5-10 minutes for activation

### 7.2 Protect Config File
1. Verify `config/db.php` is NOT publicly accessible
2. Test: Try accessing `https://yourdomain.com/config/db.php`
3. Should show 403 Forbidden or blank page

### 7.3 Set Proper Permissions
- Folders: **755**
- Files: **644**
- `public/uploads/`: **755** (writable)

---

## 🐛 Troubleshooting Common Issues

### Issue: "Database Connection Failed"
**Solution:**
1. Double-check credentials in `config/db.php`
2. Verify database user has ALL PRIVILEGES
3. Check database host (might not be `localhost` - check hPanel)
4. Verify database name is correct

### Issue: "500 Internal Server Error"
**Solution:**
1. Check `.htaccess` syntax
2. Verify `RewriteBase` path is correct
3. Check PHP error logs in hPanel
4. Try temporarily renaming `.htaccess` to test

### Issue: "File Upload Not Working"
**Solution:**
1. Set `public/uploads/` permissions to **755** or **777**
2. Verify folder exists
3. Check PHP `upload_max_filesize` in php.ini (via hPanel)
4. Verify `post_max_size` is large enough

### Issue: "Page Not Found / 404 Errors"
**Solution:**
1. Check `.htaccess` RewriteBase path
2. Verify mod_rewrite is enabled
3. Check file paths are correct
4. Verify `public/index.php` exists

### Issue: "Permission Denied"
**Solution:**
1. Set folder permissions to **755**
2. Set file permissions to **644**
3. For uploads folder, try **777** (less secure but works)

---

## 📊 STEP 8: Verify Everything Works

### Final Checklist:
- [ ] Website loads without errors
- [ ] Database connection successful
- [ ] Can create accounts
- [ ] Can create bookings
- [ ] Travel funds display correctly
- [ ] PDF uploads work
- [ ] Search by passenger name works
- [ ] SSL certificate active (HTTPS)
- [ ] No PHP errors in logs
- [ ] Mobile responsive works

---

## 📝 Post-Deployment

### Regular Maintenance:
1. **Backup Database Weekly**
   - hPanel → Databases → phpMyAdmin
   - Select database → Export → Go

2. **Monitor Error Logs**
   - hPanel → Advanced → Error Logs
   - Check weekly for issues

3. **Update PHP Version** (if needed)
   - hPanel → PHP Configuration
   - Use PHP 8.0+ for best performance

4. **Monitor Disk Space**
   - hPanel → Usage Statistics
   - Keep an eye on storage and bandwidth

---

## 🆘 Need Help?

### Hostinger Support:
- **24/7 Live Chat**: Available in hPanel
- **Knowledge Base**: https://support.hostinger.com
- **Email Support**: support@hostinger.com

### Application Issues:
- Check PHP error logs
- Review browser console for JavaScript errors
- Verify database connection
- Test file permissions

---

## 📋 Quick Reference

### Database Credentials Location:
```
hPanel → Databases → MySQL Databases
```

### File Manager Location:
```
hPanel → Files → File Manager
```

### phpMyAdmin Location:
```
hPanel → Databases → phpMyAdmin
```

### SSL Certificate:
```
hPanel → SSL → Install SSL Certificate
```

### Error Logs:
```
hPanel → Advanced → Error Logs
```

---

## ✅ Deployment Complete!

Once all steps are done, your CebuPac Booking App should be live on Hostinger!

**Your website URL:** `https://yourdomain.com`

**Next Steps:**
1. Test all features thoroughly
2. Set up regular backups
3. Monitor for any issues
4. Enjoy your deployed application! 🎉

---

**Last Updated:** 2025
**For:** CebuPac Booking App
**Hosting:** Hostinger

