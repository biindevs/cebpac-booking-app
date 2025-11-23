# Cebu Pacific Booking App - Render.com Deployment Guide

## Prerequisites
- GitHub account
- Render.com account (free)
- Git installed locally

## Step 1: Initialize Git Repository

```bash
cd cebpac-booking-app
git init
git add .
git commit -m "Initial commit"
```

## Step 2: Push to GitHub

1. Create new repository on GitHub (name: `cebpac-booking-app`)
2. Run:
```bash
git remote add origin https://github.com/YOUR_USERNAME/cebpac-booking-app.git
git branch -M main
git push -u origin main
```

## Step 3: Create MySQL Database on Render

1. Go to https://dashboard.render.com
2. Click "New +" → "MySQL"
3. Fill in:
   - **Name:** cebpac-db
   - **Database:** cebpac_booking_db
   - **Username:** cebpac_user
   - **Password:** (Generate strong password)
   - **Region:** Choose closest to you
4. Click "Create Database"
5. **Save the connection details** - you'll need them soon

## Step 4: Deploy Web Service

1. On Render dashboard, click "New +" → "Web Service"
2. Connect your GitHub repository
3. Fill in deployment settings:
   - **Name:** cebpac-booking
   - **Environment:** PHP
   - **Build Command:** `mkdir -p /opt/render/project/uploads && chmod 755 /opt/render/project/uploads`
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`
   - **Plan:** Free

## Step 5: Add Environment Variables

In Render dashboard for your web service, add these under "Environment":

```
DATABASE_HOST=your_db_host_from_step3
DATABASE_USER=cebpac_user
DATABASE_PASS=your_password_from_step3
DATABASE_NAME=cebpac_booking_db
APP_ENV=production
```

## Step 6: Initialize Database

1. Once deployed, SSH into Render or run SQL script manually
2. Upload and run: `cebpac_booking_db.sql`
3. This creates all tables

Or from your local machine with MySQL installed:
```bash
mysql -h your_db_host -u cebpac_user -p cebpac_booking_db < cebpac_booking_db.sql
```

## Step 7: Verify Deployment

1. Visit your Render URL (format: `https://cebpac-booking.onrender.com`)
2. Test login and booking functionality
3. Check logs in Render dashboard if issues

## Important Notes

### Free Tier Limitations
- **Spins down** after 15 minutes of inactivity (auto-wakes on request)
- Limited to **MySQL database on same region**
- Monthly bandwidth limit
- No custom domain (can add later)

### File Uploads
- Stored in `/opt/render/project/uploads/`
- Persists between deployments (Render preserves volume)

### Performance
- First request after idle = 30-40 second wake-up
- After that, normal performance
- Use Render's paid tier ($7/month) for always-on

## Troubleshooting

### Database Connection Error
- Verify environment variables are set correctly
- Check database credentials in Render dashboard
- Ensure IP allowlist is open (Render does this automatically)

### File Upload Issues
- Check uploads folder permissions
- Verify UPLOAD_PATH in config is correct

### Logs
- View live logs in Render dashboard
- SSH option available for debugging

## Custom Domain (Optional)

1. In Render dashboard → Settings → Custom Domain
2. Add your domain and follow DNS setup
3. SSL automatically enabled

## Next Steps

- Monitor logs regularly
- Set up error logging to Sentry (free tier available)
- Consider upgrading to paid tier when traffic increases
- Regular database backups recommended

## Support

- Render Support: https://render.com/docs
- MySQL Setup: https://render.com/docs/mysql
