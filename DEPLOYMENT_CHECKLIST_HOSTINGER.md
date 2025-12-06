# ✅ Hostinger Deployment Checklist

## Quick Reference Guide

---

## 📦 Files Ready for Deployment

### ✅ Database Files:
- [x] `cebpac_booking_db_production.sql` - Complete database with travel_funds
- [x] `config/db.php.example` - Template for production config

### ✅ Application Files:
- [x] All PHP files (controllers, models, views)
- [x] CSS and JavaScript files
- [x] `.htaccess` - Updated for production
- [x] `public/index.php` - Updated with relative paths
- [x] `.gitignore` - Protects sensitive files

### ✅ Documentation:
- [x] `HOSTINGER_DEPLOYMENT_GUIDE.md` - Complete step-by-step guide

---

## 🚀 Deployment Steps (Quick Version)

### 1. Database Setup (5 minutes)
- [ ] Log into Hostinger hPanel
- [ ] Go to **Databases** → **MySQL Databases**
- [ ] Create database
- [ ] Create database user
- [ ] Link user to database with ALL PRIVILEGES
- [ ] **SAVE CREDENTIALS** (host, name, user, password)

### 2. Upload Files (10 minutes)
- [ ] Go to **Files** → **File Manager**
- [ ] Navigate to `public_html/`
- [ ] Upload all files from `cebpac-booking-app/` folder
- [ ] Create `public/uploads/` folder
- [ ] Set `public/uploads/` permissions to **755**

### 3. Configure Database (5 minutes)
- [ ] Copy `config/db.php.example` to `config/db.php`
- [ ] Edit `config/db.php` with your database credentials:
  ```php
  DB_HOST: localhost
  DB_USER: [your_username]
  DB_PASS: [your_password]
  DB_NAME: [your_database_name]
  ```

### 4. Import Database (2 minutes)
- [ ] Go to **Databases** → **phpMyAdmin**
- [ ] Select your database
- [ ] Click **Import** tab
- [ ] Upload `cebpac_booking_db_production.sql`
- [ ] Click **Go**

### 5. Test Application (5 minutes)
- [ ] Visit your website: `https://yourdomain.com`
- [ ] Test: Create account
- [ ] Test: Create booking
- [ ] Test: Upload PDF
- [ ] Test: Travel funds display

### 6. Enable SSL (5 minutes)
- [ ] Go to **SSL** in hPanel
- [ ] Install **Let's Encrypt** certificate
- [ ] Wait 5-10 minutes

---

## 🔍 Verification Checklist

After deployment, verify:

- [ ] Website loads: `https://yourdomain.com`
- [ ] No PHP errors on pages
- [ ] Database connection works
- [ ] Can create accounts
- [ ] Can create bookings
- [ ] Travel funds display correctly
- [ ] PDF uploads work
- [ ] Search by passenger name works
- [ ] SSL certificate active (HTTPS in address bar)
- [ ] Mobile responsive works

---

## 🐛 Quick Troubleshooting

| Issue | Quick Fix |
|-------|-----------|
| Database connection failed | Check `config/db.php` credentials |
| 500 Internal Server Error | Check `.htaccess` RewriteBase path |
| File upload not working | Set `public/uploads/` to 755 permissions |
| 404 Page Not Found | Verify `.htaccess` and file paths |
| Permission denied | Set folders to 755, files to 644 |

---

## 📞 Need Help?

- **Hostinger Support**: 24/7 Live Chat in hPanel
- **Full Guide**: See `HOSTINGER_DEPLOYMENT_GUIDE.md`
- **Error Logs**: hPanel → Advanced → Error Logs

---

## 📝 Important Notes

1. **Never commit `config/db.php`** to git (contains passwords)
2. **Backup database regularly** via phpMyAdmin
3. **Keep `public/uploads/` writable** (755 permissions)
4. **Use HTTPS** (SSL certificate from Hostinger)
5. **Monitor error logs** weekly

---

## ✅ Ready to Deploy!

All files are prepared and ready for Hostinger deployment.

**Estimated Total Time:** 30-45 minutes

**Next Step:** Follow `HOSTINGER_DEPLOYMENT_GUIDE.md` for detailed instructions.

---

**Good luck with your deployment! 🚀**

