# Deployment Checklist - Acute Pain Service

Use this checklist to ensure a smooth deployment.

## Pre-Deployment

### Server Preparation
- [ ] Ubuntu 20.04/22.04 LTS server available
- [ ] Root/sudo access confirmed
- [ ] Domain name registered (optional but recommended)
- [ ] DNS configured to point to server IP
- [ ] SSH access configured
- [ ] Server has minimum 2GB RAM
- [ ] Server has minimum 10GB free disk space

### Backup Existing System (if upgrading)
- [ ] Database backed up
- [ ] Application files backed up
- [ ] Configuration files saved
- [ ] Backup verified and downloadable

## Deployment

### Method 1: Automated Installation
- [ ] Downloaded application package
- [ ] Uploaded to server
- [ ] Extracted files
- [ ] Made install script executable (`chmod +x`)
- [ ] Ran installation script as root
- [ ] Saved generated credentials
- [ ] Verified installation completed successfully

### Method 2: Manual Installation
- [ ] LAMP stack installed (Apache, MySQL, PHP 8.3)
- [ ] Database created
- [ ] Application files uploaded
- [ ] `.env` file configured
- [ ] File permissions set correctly
- [ ] Apache virtual host configured
- [ ] Database migrations run
- [ ] Admin user created

## Post-Deployment

### Testing
- [ ] Application loads in browser
- [ ] Login page displays correctly
- [ ] Admin login works
- [ ] Dashboard loads
- [ ] Can create new patient
- [ ] Can create catheter record
- [ ] Can add drug regime
- [ ] Can record functional outcome
- [ ] Can document catheter removal
- [ ] Reports generate correctly
- [ ] Search functionality works
- [ ] Notifications working

### Security
- [ ] Changed default admin password
- [ ] Firewall configured (UFW)
- [ ] SSL certificate installed
- [ ] HTTPS working correctly
- [ ] HTTP redirects to HTTPS
- [ ] Security headers configured
- [ ] Directory listing disabled
- [ ] Sensitive files protected
- [ ] PHP version hidden
- [ ] Database credentials secured
- [ ] `.env` file permissions correct (600)

### Configuration
- [ ] App URL configured correctly
- [ ] Timezone set properly
- [ ] SMTP configured (if using email)
- [ ] Tested email notifications
- [ ] File upload limits configured
- [ ] Session timeout configured
- [ ] Error logging configured
- [ ] Log rotation configured

### User Management
- [ ] Created additional admin users
- [ ] Created attending physician accounts
- [ ] Created resident accounts
- [ ] Created nurse accounts
- [ ] Tested different role permissions
- [ ] Documented user credentials

### Performance
- [ ] PHP opcache enabled
- [ ] MySQL tuned for production
- [ ] Apache configured for production
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Tested page load times
- [ ] Checked memory usage
- [ ] Verified no memory leaks

## Maintenance Setup

### Backups
- [ ] Automated database backup configured
- [ ] Backup script tested
- [ ] Backup storage location set
- [ ] Backup retention policy configured
- [ ] Restore procedure documented
- [ ] Test restore performed

### Monitoring
- [ ] Log files location confirmed
- [ ] Error monitoring configured
- [ ] Disk space monitoring
- [ ] Uptime monitoring (optional)
- [ ] Performance monitoring (optional)

### Documentation
- [ ] Installation notes documented
- [ ] Custom configurations documented
- [ ] User manual provided to staff
- [ ] Admin procedures documented
- [ ] Emergency contacts listed
- [ ] Escalation procedures defined

## Final Verification

### Production Readiness
- [ ] All test cases passed
- [ ] All security measures implemented
- [ ] All credentials changed from defaults
- [ ] All documentation completed
- [ ] All stakeholders notified
- [ ] Training completed
- [ ] Support procedures in place
- [ ] Rollback plan documented

### Go-Live
- [ ] Production URL announced
- [ ] Staff informed
- [ ] Initial users created
- [ ] System operational
- [ ] Monitoring active
- [ ] Support team ready

## Post Go-Live

### Week 1
- [ ] Monitor error logs daily
- [ ] Check database performance
- [ ] Verify backup completion
- [ ] Address user feedback
- [ ] Update documentation as needed

### Month 1
- [ ] Review security logs
- [ ] Check disk usage
- [ ] Verify backup integrity
- [ ] Collect user feedback
- [ ] Plan improvements

## Emergency Contacts

**Server Administrator:**
- Name: _______________
- Phone: _______________
- Email: _______________

**Database Administrator:**
- Name: _______________
- Phone: _______________
- Email: _______________

**Application Support:**
- Name: _______________
- Phone: _______________
- Email: _______________

**Hosting Provider:**
- Company: _______________
- Support: _______________
- Account: _______________

---

## Notes

Use this space for deployment-specific notes:

```
Date Deployed: _______________
Deployed By: _______________
Server IP: _______________
Domain: _______________
Database Name: _______________
Backup Location: _______________

Special Configurations:
- 
- 
- 

Known Issues:
- 
- 
- 
```

---

**Checklist Version:** 1.0  
**Last Updated:** January 12, 2026  
**Application Version:** 1.1.1
