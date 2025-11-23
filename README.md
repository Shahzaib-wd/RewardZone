# RewardZone - Complete Earning Web Application

## 🎯 Overview
RewardZone is a feature-rich earning platform where users can earn money by completing missions, spinning the wheel, and referring friends. Built with modern web technologies and includes payment integration, referral system, admin panel, and more.

## ✨ Features

### User Features
- **User Authentication**: Secure signup, login, and password reset
- **Payment Integration**: JazzCash & EasyPaisa payment gateways
- **Referral System**: Earn commissions by inviting friends
- **Missions System**: 20+ mission types (daily, weekly, premium)
- **Spin Wheel**: Daily spin with guaranteed rewards
- **Withdrawal System**: Request payouts to JazzCash, EasyPaisa, or Bank
- **Profile Management**: Update profile and track progress
- **Level & XP System**: Gamified progression
- **Daily Streak**: Reward consecutive logins
- **Notifications**: Real-time notifications for all activities
- **Responsive Design**: Mobile-friendly interface

### Admin Features
- **User Management**: View and manage all users
- **Payment Approval**: Manually approve/reject payments
- **Withdrawal Processing**: Process withdrawal requests
- **Transaction Monitoring**: View all transactions
- **Reports & Analytics**: Export user and revenue reports
- **Mission Management**: Create and manage missions
- **Settings**: Configure site settings

## 🛠 Tech Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+ (OOP), MySQL
- **Libraries**: Font Awesome, Chart.js
- **Architecture**: MVC pattern, RESTful APIs

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- OpenSSL extension
- PDO MySQL extension

## 🚀 Installation

### Step 1: Extract Files
Extract the RewardZone folder to your web server directory:
- **XAMPP**: `C:/xampp/htdocs/RewardZone`
- **WAMP**: `C:/wamp64/www/RewardZone`
- **Linux**: `/var/www/html/RewardZone`

### Step 2: Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `rewardzone_db`
3. Import the `database.sql` file:
   - Click on the `rewardzone_db` database
   - Go to "Import" tab
   - Choose `database.sql` file
   - Click "Go"

### Step 3: Configure Database Connection
Edit `config/database.php` and update the credentials:
```php
private $host = "localhost";
private $db_name = "rewardzone_db";
private $username = "root";
private $password = "";
```

### Step 4: Configure Site Settings
Edit `config/config.php` and update:
```php
define('SITE_URL', 'http://localhost/RewardZone/');
define('SITE_EMAIL', 'your-email@example.com');

// Payment API Credentials
define('JAZZCASH_MERCHANT_ID', 'your_merchant_id');
define('JAZZCASH_PASSWORD', 'your_password');
define('JAZZCASH_INTEGRITY_SALT', 'your_salt');

define('EASYPAISA_STORE_ID', 'your_store_id');
define('EASYPAISA_SECRET_KEY', 'your_secret_key');

// Email Configuration (Gmail SMTP)
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Step 5: Set Permissions (Linux/Mac)
```bash
chmod -R 755 /var/www/html/RewardZone
chmod -R 777 /var/www/html/RewardZone/logs
```

### Step 6: Access the Application
Open your browser and navigate to:
- **User Interface**: http://localhost/RewardZone/
- **Admin Panel**: http://localhost/RewardZone/admin/

## 🔐 Default Admin Credentials
```
Username: admin
Email: admin@rewardzone.com
Password: admin123
```
**⚠️ IMPORTANT: Change these credentials immediately after first login!**

## 📁 Directory Structure
```
RewardZone/
├── admin/               # Admin panel files
├── api/                 # API endpoints
├── assets/              # Static assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Images
├── config/              # Configuration files
│   ├── config.php      # Main configuration
│   └── database.php    # Database connection
├── includes/            # PHP classes
│   ├── User.php        # User management
│   ├── Mission.php     # Mission system
│   ├── Payment.php     # Payment processing
│   ├── Withdrawal.php  # Withdrawal handling
│   └── SpinWheel.php   # Spin wheel logic
├── logs/               # Error logs
├── database.sql        # Database structure
├── index.php           # Landing page
├── login.php           # Login page
├── register.php        # Registration page
├── dashboard.php       # User dashboard
└── README.md           # This file
```

## 💳 Payment Gateway Setup

### JazzCash Integration
1. Sign up for JazzCash Merchant Account: https://sandbox.jazzcash.com.pk/
2. Get your Merchant ID, Password, and Integrity Salt
3. Update credentials in `config/config.php`
4. Test in sandbox mode first
5. Switch to production after testing

### EasyPaisa Integration
1. Register at: https://easypay.easypaisa.com.pk/
2. Obtain Store ID and Secret Key
3. Update credentials in `config/config.php`
4. Test transactions before going live

## 🎮 How It Works

### For Users
1. **Sign Up**: Create free account
2. **Complete Profile**: Earn initial bonus
3. **Upgrade to Premium**: One-time payment of PKR 350
4. **Complete Missions**: Earn rewards by completing tasks
5. **Spin Daily**: Get guaranteed rewards from spin wheel
6. **Refer Friends**: Earn commission on referrals
7. **Withdraw**: Request payout when balance reaches PKR 670

### For Admins
1. Login to admin panel
2. Monitor user activities and transactions
3. Approve/reject payment requests
4. Process withdrawal requests
5. Manage missions and settings
6. Export reports

## 🔒 Security Features

- CSRF Token Protection
- SQL Injection Prevention (PDO Prepared Statements)
- XSS Protection (Input Sanitization)
- Password Hashing (Bcrypt)
- Server-Side Validation
- Session Security
- Secure Database Queries

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database `rewardzone_db` exists

### Payment Integration Issues
- Verify API credentials are correct
- Check if sandbox/production mode is set correctly
- Review error logs in `logs/error.log`

### Email Not Sending
- Configure Gmail App Password (not regular password)
- Enable "Less secure app access" or use App Password
- Check SMTP settings in `config/config.php`

### Blank Page / PHP Errors
- Enable error reporting in `config/config.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Check Apache/PHP error logs

## 📊 Database Schema

### Main Tables
- **users**: User accounts and profile data
- **transactions**: All financial transactions
- **withdrawals**: Withdrawal requests
- **missions**: Available missions
- **user_missions**: User mission progress
- **referrals**: Referral tracking
- **notifications**: User notifications
- **spin_history**: Spin wheel history
- **settings**: System settings

## 🔧 Customization

### Change Colors/Theme
Edit `assets/css/style.css` and `assets/css/dashboard.css` to modify colors and styling.

### Modify Missions
Edit missions directly in the database or through the admin panel.

### Adjust Commission Rates
Update in `config/config.php`:
```php
define('PACK_PRICE', 350);
define('OWNER_COMMISSION', 200);
define('ACTIVE_INVITER_COMMISSION', 150);
define('INACTIVE_INVITER_COMMISSION', 30);
define('MIN_WITHDRAWAL', 670);
```

## 📱 Mobile Responsiveness

The entire application is fully responsive and works seamlessly on:
- Desktop computers
- Tablets
- Mobile phones (iOS & Android)

## 🌐 Deployment to Production

### Steps
1. Upload files to web hosting via FTP/cPanel
2. Create MySQL database on server
3. Import `database.sql`
4. Update `config/config.php` with production settings
5. Set proper file permissions
6. Configure SSL certificate (HTTPS)
7. Update payment gateway to production mode
8. Test all features thoroughly

### Recommended Hosting
- Shared Hosting: Namecheap, Hostinger, Bluehost
- VPS: DigitalOcean, Linode, Vultr
- Requirements: PHP 7.4+, MySQL 5.7+, 1GB RAM minimum

## 📞 Support

For issues or questions:
- Review this README file
- Check `database.sql` for schema
- Review code comments
- Test in local environment first

## 📝 License

This project is provided as-is for educational and commercial use.

## 🎉 Features Checklist

- ✅ User Authentication (Signup, Login, Password Reset)
- ✅ Payment Integration (JazzCash, EasyPaisa)
- ✅ Referral System with Commission Tracking
- ✅ 20+ Mission Types (Daily, Weekly, Premium)
- ✅ Spin Wheel with Daily Cooldown
- ✅ Withdrawal System (PKR 670 minimum)
- ✅ Admin Panel (User Management, Approvals)
- ✅ Notifications & Email Alerts
- ✅ Level & XP Progression
- ✅ Daily Streak Rewards
- ✅ Mobile Responsive Design
- ✅ Security (CSRF, SQL Injection, XSS Protection)
- ✅ Transaction History
- ✅ Social Proof Notifications
- ✅ Interactive Dashboard

## 🚀 Future Enhancements

Potential features for future versions:
- SMS notifications
- Mobile app (React Native)
- Additional payment methods
- Video ads integration
- Survey integration
- Social media login
- Cryptocurrency payments
- Multi-language support

---

**Built with ❤️ for earning enthusiasts**

Version: 1.0  
Last Updated: 2025
#   R e w a r d Z o n e  
 