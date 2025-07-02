# TEC Sunday School Audio App - Production Deployment Checklist

## 🚀 Pre-Deployment

### Environment Setup
- [ ] Copy `.env.production` to `.env` and configure for your environment
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Update `APP_URL` to your production domain
- [ ] Generate new `APP_KEY` if needed
- [ ] Configure database credentials
- [ ] Set up mail configuration
- [ ] Verify Bunny.net storage credentials

### Security Configuration
- [ ] Review and update security headers in `SecurityHeaders` middleware
- [ ] Configure Content Security Policy for your domain
- [ ] Set up SSL certificate
- [ ] Configure rate limiting values
- [ ] Review file permissions

## 🔧 Server Requirements

### PHP Requirements
- [ ] PHP 8.1 or higher
- [ ] Required extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, getID3
- [ ] Composer installed
- [ ] PHP-FPM configured

### Web Server
- [ ] Nginx or Apache configured
- [ ] SSL certificate installed
- [ ] Gzip compression enabled
- [ ] Rate limiting configured
- [ ] Security headers configured

### Database
- [ ] SQLite file created with proper permissions (or MySQL/PostgreSQL configured)
- [ ] Database migrations run
- [ ] Database backups configured

## 📦 Deployment Steps

### 1. Code Deployment
```bash
# Clone repository
git clone https://github.com/your-repo/tec-sunday-school.git
cd tec-sunday-school

# Run deployment script
./deploy.sh
```

### 2. Manual Steps
- [ ] Configure web server (copy `nginx.conf` and adapt)
- [ ] Set up SSL certificate
- [ ] Configure firewall rules
- [ ] Set up monitoring
- [ ] Configure log rotation

### 3. Post-Deployment Testing
- [ ] Test homepage loads correctly
- [ ] Test audio playback functionality
- [ ] Test metadata management interface
- [ ] Test sync functionality
- [ ] Test download functionality
- [ ] Test mobile responsiveness
- [ ] Test rate limiting
- [ ] Test security headers

## 🔒 Security Checklist

### Application Security
- [ ] Debug mode disabled
- [ ] Error reporting configured appropriately
- [ ] Security headers implemented
- [ ] Rate limiting active
- [ ] Input validation in place
- [ ] CSRF protection enabled

### Server Security
- [ ] Firewall configured (only ports 80, 443, 22 open)
- [ ] SSH key authentication enabled
- [ ] Regular security updates scheduled
- [ ] Fail2ban or similar intrusion prevention
- [ ] Log monitoring configured

### File Permissions
```bash
# Set correct permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chown -R www-data:www-data storage bootstrap/cache
```

## 📊 Monitoring & Maintenance

### Performance Monitoring
- [ ] Set up application performance monitoring
- [ ] Configure server resource monitoring
- [ ] Set up uptime monitoring
- [ ] Configure error tracking

### Backup Strategy
- [ ] Database backup automation
- [ ] File backup strategy
- [ ] Backup restoration testing
- [ ] Off-site backup storage

### Maintenance Tasks
- [ ] Log rotation configured
- [ ] Cache clearing scheduled
- [ ] Security update schedule
- [ ] Performance optimization review

## 🎵 Application-Specific

### Audio Content
- [ ] Verify Bunny.net CDN is accessible
- [ ] Test audio streaming performance
- [ ] Verify cover art displays correctly
- [ ] Test download functionality
- [ ] Verify metadata sync works

### User Experience
- [ ] Test on multiple browsers
- [ ] Test mobile responsiveness
- [ ] Verify keyboard shortcuts work
- [ ] Test Now Playing panel functionality
- [ ] Verify progress tracking works

## 🚨 Troubleshooting

### Common Issues
- **500 Error**: Check file permissions, .env configuration
- **Audio not playing**: Verify Bunny.net URLs, check CORS
- **Slow performance**: Enable caching, optimize database
- **Rate limiting**: Adjust limits in configuration

### Log Locations
- Application logs: `storage/logs/laravel.log`
- Web server logs: `/var/log/nginx/` or `/var/log/apache2/`
- PHP-FPM logs: `/var/log/php8.2-fpm.log`

### Useful Commands
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Check application status
php artisan about

# Sync audio files
php artisan music:sync --force

# Import metadata
php artisan tracks:import-metadata metadata.csv
```

## 📞 Support

For issues or questions:
1. Check application logs
2. Review this checklist
3. Test in development environment
4. Check Laravel documentation
5. Review security best practices

---

**Last Updated**: $(date)
**Version**: 1.0.0
