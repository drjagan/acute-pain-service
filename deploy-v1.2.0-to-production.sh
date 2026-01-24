#!/bin/bash

################################################################################
# Acute Pain Service - Production Deployment Script
# Version: 1.2.0
# Target: aps.sbvu.ac.in (Cloudron)
# Date: January 24, 2026
################################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_URL="https://aps.sbvu.ac.in"
CLOUDRON_SSH="ssh cloudron@aps.sbvu.ac.in"
APP_PATH="/app/code"
DATA_PATH="/app/data"
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

confirm_action() {
    echo -e "${YELLOW}$1${NC}"
    read -p "Continue? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment cancelled by user"
        exit 1
    fi
}

################################################################################
# Pre-Deployment Checks
################################################################################

pre_deployment_checks() {
    print_header "PRE-DEPLOYMENT CHECKS"
    
    # Check if on correct branch
    CURRENT_BRANCH=$(git branch --show-current)
    if [ "$CURRENT_BRANCH" != "aps.sbvu.ac.in" ]; then
        print_error "Not on aps.sbvu.ac.in branch (currently on: $CURRENT_BRANCH)"
        exit 1
    fi
    print_success "On correct branch: aps.sbvu.ac.in"
    
    # Check for uncommitted changes
    if [ -n "$(git status --porcelain)" ]; then
        print_error "Uncommitted changes detected"
        git status --short
        exit 1
    fi
    print_success "No uncommitted changes"
    
    # Check if production .env exists
    if [ ! -f ".env.production.sbvu" ]; then
        print_error "Production .env file not found"
        exit 1
    fi
    print_success "Production .env file exists"
    
    # Verify version in .env
    VERSION_IN_ENV=$(grep "^APP_VERSION=" .env.production.sbvu | cut -d'=' -f2)
    if [ "$VERSION_IN_ENV" != "1.2.0" ]; then
        print_warning "Version in .env is $VERSION_IN_ENV (expected 1.2.0)"
    else
        print_success "Version in .env is correct: 1.2.0"
    fi
    
    # Check migration files exist
    if [ ! -f "database/migrations/013_create_new_lookup_tables.sql" ]; then
        print_error "Migration 013 not found"
        exit 1
    fi
    if [ ! -f "database/migrations/014_update_surgeries_with_specialties.sql" ]; then
        print_error "Migration 014 not found"
        exit 1
    fi
    print_success "Migration files present"
    
    # Test SSH connection (optional, commented out for manual deployment)
    # print_info "Testing SSH connection to production server..."
    # if $CLOUDRON_SSH "echo 'SSH connection successful'" >/dev/null 2>&1; then
    #     print_success "SSH connection successful"
    # else
    #     print_warning "Could not establish SSH connection (manual deployment may be required)"
    # fi
}

################################################################################
# Create Backup Plan
################################################################################

create_backup_plan() {
    print_header "BACKUP PLAN"
    
    cat <<EOF
The following backup will be created on production server:

1. Database Backup:
   Location: $BACKUP_DIR/database_backup.sql
   Command: mysqldump -h mysql -u a916f81cc97ef00e -p a916f81cc97ef00e

2. Application Files Backup:
   Location: $BACKUP_DIR/app_code.tar.gz
   Command: tar -czf $BACKUP_DIR/app_code.tar.gz $APP_PATH

3. Configuration Backup:
   - .env file
   - config/ directory
   - composer.json/lock

EOF
    print_success "Backup plan documented"
}

################################################################################
# Display Deployment Steps
################################################################################

display_deployment_steps() {
    print_header "DEPLOYMENT STEPS FOR aps.sbvu.ac.in"
    
    cat <<'EOF'

═══════════════════════════════════════════════════════════════════════════
STEP 1: SSH INTO CLOUDRON SERVER
═══════════════════════════════════════════════════════════════════════════

ssh cloudron@aps.sbvu.ac.in

# If prompted for password, check Cloudron dashboard for SSH key setup

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 2: NAVIGATE TO APP DIRECTORY
═══════════════════════════════════════════════════════════════════════════

cd /app/code

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 3: CREATE BACKUP DIRECTORY
═══════════════════════════════════════════════════════════════════════════

mkdir -p /app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="/app/data/backups/v1.2.0-$(date +%Y%m%d-%H%M%S)"

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 4: BACKUP CURRENT DATABASE
═══════════════════════════════════════════════════════════════════════════

mysqldump -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e > $BACKUP_DIR/database_backup.sql

# Verify backup was created
ls -lh $BACKUP_DIR/database_backup.sql

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 5: BACKUP CURRENT APPLICATION FILES
═══════════════════════════════════════════════════════════════════════════

tar -czf $BACKUP_DIR/app_code.tar.gz /app/code

# Verify backup
ls -lh $BACKUP_DIR/app_code.tar.gz

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 6: PULL LATEST CODE FROM GITHUB
═══════════════════════════════════════════════════════════════════════════

cd /app/code
git fetch origin
git status

# Verify current branch
git branch --show-current

# Pull latest changes
git pull origin aps.sbvu.ac.in

# Verify version
cat VERSION

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 7: RUN DATABASE MIGRATIONS
═══════════════════════════════════════════════════════════════════════════

# Migration 013: Create new lookup tables
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/migrations/013_create_new_lookup_tables.sql

# Verify new tables created
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SHOW TABLES LIKE 'lookup_%';" a916f81cc97ef00e

# Migration 014: Update surgeries with specialties
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/migrations/014_update_surgeries_with_specialties.sql

# Verify specialty_id column added
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "DESCRIBE lookup_surgeries;" a916f81cc97ef00e

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 8: SEED MASTER DATA (OPTIONAL BUT RECOMMENDED)
═══════════════════════════════════════════════════════════════════════════

# Check if seeder file exists
ls -la database/seeders/MasterDataSeeder.sql

# Run seeder (this will populate all lookup tables with sample data)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < database/seeders/MasterDataSeeder.sql

# Verify data inserted
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SELECT COUNT(*) FROM lookup_specialties;" a916f81cc97ef00e

mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SELECT COUNT(*) FROM lookup_catheter_indications;" a916f81cc97ef00e

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 9: CLEAR CACHE AND SESSIONS (IF APPLICABLE)
═══════════════════════════════════════════════════════════════════════════

# Clear PHP session files
rm -rf /tmp/php_sessions/*

# Clear any application cache
rm -rf /app/data/cache/*

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 10: RESTART APPLICATION
═══════════════════════════════════════════════════════════════════════════

# Restart via Cloudron Dashboard:
# 1. Go to https://my.sbvu.ac.in
# 2. Find "Acute Pain Service" app
# 3. Click "Restart" button

# OR restart Apache (if direct access available):
# sudo systemctl restart apache2

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 11: VERIFY DEPLOYMENT
═══════════════════════════════════════════════════════════════════════════

# Check version via command line
cd /app/code
cat VERSION

# Check database tables
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  -e "SHOW TABLES;" a916f81cc97ef00e | grep lookup

# Expected tables (9 total):
# - lookup_adjuvants
# - lookup_catheter_indications (NEW)
# - lookup_comorbidities
# - lookup_drugs
# - lookup_red_flags
# - lookup_removal_indications (NEW)
# - lookup_sentinel_events (NEW)
# - lookup_specialties (NEW)
# - lookup_surgeries

───────────────────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════════════════
STEP 12: WEB-BASED VERIFICATION
═══════════════════════════════════════════════════════════════════════════

Open browser and test:

1. Main Application:
   https://aps.sbvu.ac.in
   - Should show version 1.2.0 in footer

2. Master Data Dashboard:
   https://aps.sbvu.ac.in/masterdata
   - Should display all 9 master data types
   - Check each type has data

3. Test CRUD Operations:
   - Create new specialty
   - Edit existing surgery
   - Test drag & drop reordering
   - Test CSV export
   - Test soft delete and restore

4. Test Specialty Filtering:
   - Go to patient registration form
   - Select a specialty
   - Verify surgery dropdown filters correctly

───────────────────────────────────────────────────────────────────────────

EOF

    print_success "Deployment steps displayed"
}

################################################################################
# Create Quick Reference Card
################################################################################

create_quick_reference() {
    print_header "QUICK REFERENCE CARD"
    
    cat <<'EOF'

╔═══════════════════════════════════════════════════════════════════════════╗
║                    PRODUCTION SERVER QUICK REFERENCE                      ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌───────────────────────────────────────────────────────────────────────────┐
│ SERVER ACCESS                                                             │
└───────────────────────────────────────────────────────────────────────────┘
SSH:          ssh cloudron@aps.sbvu.ac.in
Web:          https://aps.sbvu.ac.in
Dashboard:    https://my.sbvu.ac.in

┌───────────────────────────────────────────────────────────────────────────┐
│ DATABASE CREDENTIALS                                                      │
└───────────────────────────────────────────────────────────────────────────┘
Host:         mysql
Port:         3306
Database:     a916f81cc97ef00e
User:         a916f81cc97ef00e
Password:     33050ba714a937bf69970570779e802c33b9faa11e4864d4

┌───────────────────────────────────────────────────────────────────────────┐
│ MYSQL CONNECT COMMAND                                                     │
└───────────────────────────────────────────────────────────────────────────┘
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e

┌───────────────────────────────────────────────────────────────────────────┐
│ KEY PATHS                                                                 │
└───────────────────────────────────────────────────────────────────────────┘
App Code:     /app/code
App Data:     /app/data
Migrations:   /app/code/database/migrations
Seeders:      /app/code/database/seeders
Backups:      /app/data/backups

┌───────────────────────────────────────────────────────────────────────────┐
│ ROLLBACK COMMANDS (IF NEEDED)                                             │
└───────────────────────────────────────────────────────────────────────────┘

# Restore database from backup
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e < /app/data/backups/v1.2.0-YYYYMMDD-HHMMSS/database_backup.sql

# Rollback to previous Git version
cd /app/code
git log --oneline -10  # Find previous commit hash
git reset --hard <COMMIT_HASH>

# Drop new tables (if needed)
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e \
  -e "DROP TABLE IF EXISTS lookup_catheter_indications, lookup_removal_indications, lookup_sentinel_events, lookup_specialties;"

# Remove specialty_id from surgeries
mysql -h mysql -u a916f81cc97ef00e \
  -p33050ba714a937bf69970570779e802c33b9faa11e4864d4 \
  a916f81cc97ef00e \
  -e "ALTER TABLE lookup_surgeries DROP COLUMN specialty_id;"

┌───────────────────────────────────────────────────────────────────────────┐
│ POST-DEPLOYMENT VERIFICATION URLS                                         │
└───────────────────────────────────────────────────────────────────────────┘
Homepage:             https://aps.sbvu.ac.in/
Master Data:          https://aps.sbvu.ac.in/masterdata
Specialties:          https://aps.sbvu.ac.in/masterdata/specialties
Catheter Indications: https://aps.sbvu.ac.in/masterdata/catheter_indications
Removal Indications:  https://aps.sbvu.ac.in/masterdata/removal_indications
Sentinel Events:      https://aps.sbvu.ac.in/masterdata/sentinel_events
Surgeries:            https://aps.sbvu.ac.in/masterdata/surgeries

EOF
}

################################################################################
# Create Post-Deployment Checklist
################################################################################

create_verification_checklist() {
    print_header "POST-DEPLOYMENT VERIFICATION CHECKLIST"
    
    cat <<'EOF'

┌───────────────────────────────────────────────────────────────────────────┐
│ DATABASE VERIFICATION                                                     │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. All 9 lookup tables exist (run SHOW TABLES query)
[ ] 2. lookup_surgeries has specialty_id column
[ ] 3. lookup_specialties has sample data (at least 20 rows)
[ ] 4. lookup_catheter_indications has sample data (at least 18 rows)
[ ] 5. lookup_removal_indications has sample data (at least 7 rows)
[ ] 6. lookup_sentinel_events has sample data (at least 30 rows)
[ ] 7. All tables have sort_order column
[ ] 8. All tables have deleted_at column

┌───────────────────────────────────────────────────────────────────────────┐
│ WEB INTERFACE VERIFICATION                                                │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. Homepage loads without errors (https://aps.sbvu.ac.in)
[ ] 2. Version shows as 1.2.0 in footer/about page
[ ] 3. Master Data Dashboard accessible (/masterdata)
[ ] 4. All 9 master data types listed on dashboard
[ ] 5. Can view specialties list
[ ] 6. Can view catheter indications list
[ ] 7. Can view removal indications list
[ ] 8. Can view sentinel events list

┌───────────────────────────────────────────────────────────────────────────┐
│ FUNCTIONALITY VERIFICATION                                                │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. Can create new specialty
[ ] 2. Can edit existing specialty
[ ] 3. Can delete (soft delete) specialty
[ ] 4. Can restore deleted specialty
[ ] 5. Drag & drop reordering works
[ ] 6. CSV export works for all types
[ ] 7. Specialty filtering works in patient forms
[ ] 8. Surgery dropdown updates when specialty selected
[ ] 9. Search functionality works
[ ] 10. Pagination works for large datasets

┌───────────────────────────────────────────────────────────────────────────┐
│ SECURITY & PERFORMANCE                                                    │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. Login required for master data access
[ ] 2. Unauthorized users cannot access /masterdata
[ ] 3. CSRF protection working on forms
[ ] 4. SQL injection protection verified
[ ] 5. Page load times acceptable (<2 seconds)
[ ] 6. No PHP errors in logs
[ ] 7. No JavaScript console errors

┌───────────────────────────────────────────────────────────────────────────┐
│ BACKUP VERIFICATION                                                       │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. Database backup file exists
[ ] 2. Database backup file size reasonable (>100KB)
[ ] 3. Application backup exists
[ ] 4. Backup directory accessible

┌───────────────────────────────────────────────────────────────────────────┐
│ ROLLBACK PLAN READY                                                       │
└───────────────────────────────────────────────────────────────────────────┘

[ ] 1. Backup location documented
[ ] 2. Rollback commands tested/ready
[ ] 3. Team notified of deployment
[ ] 4. Monitoring in place for first 24 hours

EOF
}

################################################################################
# Main Execution
################################################################################

main() {
    print_header "ACUTE PAIN SERVICE v1.2.0 - PRODUCTION DEPLOYMENT"
    
    echo "This script will guide you through deploying v1.2.0 to production."
    echo "Target: https://aps.sbvu.ac.in (Cloudron)"
    echo ""
    
    # Run pre-deployment checks
    pre_deployment_checks
    
    # Display backup plan
    create_backup_plan
    
    # Confirm before proceeding
    confirm_action "Ready to display deployment steps?"
    
    # Display deployment steps
    display_deployment_steps
    
    # Create quick reference
    create_quick_reference
    
    # Create verification checklist
    create_verification_checklist
    
    # Final summary
    print_header "DEPLOYMENT GUIDE COMPLETE"
    print_success "All pre-deployment checks passed"
    print_info "Follow the steps above to deploy to production"
    print_info "This is a MANUAL deployment - commands must be run on the server"
    print_warning "IMPORTANT: Create backups before running migrations"
    print_warning "IMPORTANT: Test thoroughly after deployment"
    
    echo ""
    echo "For quick reference during deployment, see above sections"
    echo "Documentation available in: INSTALL.md, MIGRATION_QUICK_REFERENCE.md"
    echo ""
}

# Run main function
main
