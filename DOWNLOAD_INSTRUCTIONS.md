# Download and Installation Instructions

## 🚀 Quick Start

### Download the Latest Release

**Current Version:** v1.1.2

```bash
# Download release archive
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz

# Extract
tar -xzf v1.1.2.tar.gz

# Navigate to directory
cd acute-pain-service-1.1.2
```

### Install on LAMP Server

```bash
# Make installation script executable
chmod +x deployment/scripts/install.sh

# Run automated installation
sudo ./deployment/scripts/install.sh
```

**Installation time:** 15-20 minutes

---

## 📥 Download Options

### Option 1: Direct Download (Recommended)

**Latest Release Archive:**
```
https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz
```

**Using wget:**
```bash
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz
```

**Using curl:**
```bash
curl -L -O https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz
```

### Option 2: Git Clone

```bash
# Clone repository
git clone https://github.com/drjagan/acute-pain-service.git

# Navigate to directory
cd acute-pain-service

# Checkout specific version
git checkout v1.1.2
```

### Option 3: GitHub Web Interface

1. Visit: https://github.com/drjagan/acute-pain-service
2. Click "Releases" on the right sidebar
3. Find v1.1.2
4. Download "Source code (tar.gz)"

---

## 📋 What You Get

### Package Contents

```
acute-pain-service-1.1.2/
├── deployment/
│   ├── scripts/
│   │   └── install.sh              # Automated installer
│   ├── config/
│   │   └── apache-vhost.conf       # Apache configuration
│   └── DEPLOYMENT_CHECKLIST.md     # Installation checklist
├── DEPLOY.md                        # Main deployment guide
├── LAMP_INSTALL.md                  # LAMP stack setup
├── DEPLOYMENT_SUMMARY.md            # Package overview
├── .env.example                     # Configuration template
├── [application files...]
```

### Package Size

- **Compressed:** 180 KB
- **Extracted:** ~2 MB

---

## 🔧 Installation Methods

### Automated Installation (Recommended)

**For:** Fresh Ubuntu 20.04/22.04 server

**Steps:**
1. Download and extract package
2. Run `install.sh`
3. Save displayed credentials
4. Access your site

**Time:** 15-20 minutes

**What it installs:**
- Apache 2.4
- MySQL 8.0
- PHP 8.3
- Application with database
- Admin user
- Security configuration

### Manual Installation

**For:** Existing LAMP server or custom setup

**Steps:**
1. Follow `LAMP_INSTALL.md` (if needed)
2. Follow `DEPLOY.md` manual section
3. Configure `.env` file
4. Run database migrations

**Time:** 30-45 minutes

---

## ✅ System Requirements

### Server
- Ubuntu 20.04/22.04 LTS
- 2GB RAM minimum
- 10GB disk space
- Root/sudo access

### Software (installed automatically)
- Apache 2.4+
- MySQL 8.0+
- PHP 8.3+

---

## 📖 Documentation

After download, read these files:

1. **DEPLOY.md** - Start here for deployment
2. **LAMP_INSTALL.md** - LAMP stack setup
3. **DEPLOYMENT_SUMMARY.md** - Quick overview
4. **deployment/DEPLOYMENT_CHECKLIST.md** - Step-by-step

---

## 🆘 Troubleshooting

### Download Issues

**Problem:** 404 Error when downloading

**Solution:** Verify you're using the correct URL:
```
https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz
```

**Problem:** Slow download

**Solution:** Use a mirror or clone via git:
```bash
git clone --depth 1 --branch v1.1.2 https://github.com/drjagan/acute-pain-service.git
```

### Extraction Issues

**Problem:** tar.gz won't extract

**Solution:** Install tar if missing:
```bash
sudo apt-get install tar
tar -xzf v1.1.2.tar.gz
```

---

## 📞 Support

**Repository:** https://github.com/drjagan/acute-pain-service

**Issues:** https://github.com/drjagan/acute-pain-service/issues

**Documentation:** Included in package

---

## 🔐 Verification

### Verify Download

Check file size:
```bash
ls -lh v1.1.2.tar.gz
# Should show approximately 180K
```

Check contents:
```bash
tar -tzf v1.1.2.tar.gz | head -20
```

### Verify Extraction

```bash
cd acute-pain-service-1.1.2
ls -la

# Should see:
# - deployment/
# - src/
# - public/
# - DEPLOY.md
# - install.sh (in deployment/scripts/)
```

---

## 🎯 Next Steps

After download:

1. **Read Documentation**
   ```bash
   cat DEPLOY.md | less
   ```

2. **Run Installation**
   ```bash
   sudo chmod +x deployment/scripts/install.sh
   sudo ./deployment/scripts/install.sh
   ```

3. **Access Application**
   ```
   http://your-server-ip
   ```

4. **Login**
   - Use credentials from installation output
   - Saved in `/root/aps-credentials.txt`

---

## 📊 Version History

### v1.1.2 (Current - Recommended)
- Production deployment package
- Automated installation
- Bug fixes
- Complete documentation

### v1.1.1
- Admin role enhancements
- Physician assignments

### v1.1.0
- Notification system
- SMTP configuration

### v1.0.0
- Initial release

---

## ⚡ Quick Command Reference

```bash
# Download
wget https://github.com/drjagan/acute-pain-service/archive/refs/tags/v1.1.2.tar.gz

# Extract
tar -xzf v1.1.2.tar.gz

# Navigate
cd acute-pain-service-1.1.2

# Install
sudo chmod +x deployment/scripts/install.sh
sudo ./deployment/scripts/install.sh

# Done! Access at http://your-server-ip
```

---

**Version:** 1.1.2  
**Release Date:** January 12, 2026  
**Package Size:** 180 KB  
**Installation Time:** 15-20 minutes  
**Ready for Production:** YES ✅
