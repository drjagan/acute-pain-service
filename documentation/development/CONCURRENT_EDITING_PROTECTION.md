# Concurrent Editing Protection System
## Implementation Plan v1.0

**Date:** January 13, 2026  
**Status:** 📋 PLANNED (Not yet implemented)  
**Priority:** MEDIUM (Shelved for future implementation)  
**Estimated Effort:** ~38 hours  
**Version Target:** v1.2.0 or later

---

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [Problem Statement](#problem-statement)
3. [Solution Overview](#solution-overview)
4. [Configuration Decisions](#configuration-decisions)
5. [Architecture Design](#architecture-design)
6. [Phase 1: Database Schema](#phase-1-database-schema)
7. [Phase 2: Backend Implementation](#phase-2-backend-implementation)
8. [Phase 3: Frontend Implementation](#phase-3-frontend-implementation)
9. [Phase 4: Notification Integration](#phase-4-notification-integration)
10. [Phase 5: Queue System](#phase-5-queue-system)
11. [Phase 6: Autosave System](#phase-6-autosave-system)
12. [Phase 7: Admin Dashboard](#phase-7-admin-dashboard)
13. [Testing Strategy](#testing-strategy)
14. [Security Considerations](#security-considerations)
15. [Deployment Checklist](#deployment-checklist)
16. [Future Enhancements](#future-enhancements)

---

## Executive Summary

### Purpose
Implement a comprehensive concurrent editing protection system to prevent data conflicts when multiple privileged users attempt to edit the same patient records simultaneously.

### Approach
**Queue System with Advisory Locking** - A hybrid approach that:
- Tracks who is currently editing records
- Queues additional users and notifies them when available
- Implements visual indicators for user awareness
- Allows emergency override by Admins and Attending physicians
- Includes autosave functionality to prevent data loss

### Key Benefits
- ✅ Prevents "Lost Update" problems
- ✅ Improves user awareness of concurrent access
- ✅ Maintains audit trail for compliance
- ✅ Allows emergency access when needed
- ✅ Automatic data recovery via autosave

---

## Problem Statement

### Current Issues
1. **Lost Updates**: Multiple users editing simultaneously can overwrite each other's changes
2. **No Visibility**: Users don't know when someone else is editing a record
3. **Data Conflicts**: Last save wins, potentially losing important edits
4. **No Recovery**: No mechanism to recover unsaved changes
5. **Audit Gap**: No tracking of concurrent edit attempts

### Impact
- **Medical Safety**: Incorrect or incomplete data due to overwritten updates
- **User Frustration**: Repeated data entry when changes are lost
- **Compliance Risk**: Inadequate audit trail for healthcare regulations
- **Workflow Disruption**: Users unaware of conflicts until save fails

---

## Solution Overview

### Technical Approach
**Queue-Based Advisory Locking with Optimistic Concurrency Control**

### Components
1. **Lock Tracking Database** - Records who is editing what
2. **Queue Management System** - Manages waiting users
3. **Real-time Notifications** - Alerts users of queue status
4. **Visual Indicators** - Shows edit status in UI
5. **Autosave System** - Prevents data loss
6. **Version Control** - Detects concurrent modifications
7. **Admin Override** - Emergency access mechanism

### User Experience Flow

```
User clicks "Edit" 
    ↓
Is record locked?
    ↓
NO → Acquire lock → Show edit form
    ↓
    User edits with autosave
    ↓
    Save → Release lock → Notify queue
    
YES → Show queue position
    ↓
    "Dr. Sharma is editing (5 min ago)"
    ↓
    [Join Queue] [View Read-Only] [Override (Emergency)]
    ↓
    Wait in queue...
    ↓
    Lock released → Notify user
    ↓
    "Now available! Click to edit"
```

---

## Configuration Decisions

Based on user preferences, the system will be configured as follows:

| Setting | Value | Rationale |
|---------|-------|-----------|
| **Lock Timeout** | 30 minutes | Balanced - allows thorough edits without excessive locking |
| **Lock Behavior** | Queue System | Best UX - users notified when available |
| **Override Permission** | Admins + Attending | Emergency access with appropriate authority |
| **Autosave** | Yes, every 2 minutes | Prevents data loss during long edit sessions |
| **Viewer Tracking** | Optional per record type | Configurable - can enable for high-traffic records |
| **Implementation Priority** | Visual indicators first | Focus on user awareness and communication |

### Environment Variables

```env
# Lock System Configuration
LOCK_ENABLED=true
LOCK_TIMEOUT=1800                       # 30 minutes in seconds
LOCK_HEARTBEAT_INTERVAL=60              # Extend lock every 60 seconds
LOCK_WARNING_TIME=300                   # Warn at 5 minutes remaining
LOCK_GRACE_PERIOD=120                   # 2 minute grace before timeout

# Queue System
QUEUE_ENABLED=true
QUEUE_MAX_SIZE=10                       # Max users waiting per record
QUEUE_NOTIFICATION_ENABLED=true
QUEUE_AUTO_ACQUIRE=true                 # Auto-acquire when available

# Autosave Configuration
AUTOSAVE_ENABLED=true
AUTOSAVE_INTERVAL=120                   # 2 minutes in seconds
AUTOSAVE_RETENTION_HOURS=24             # Keep drafts for 24 hours

# Override Permissions
ALLOW_FORCE_UNLOCK=true
FORCE_UNLOCK_ROLES=admin,attending      # Comma-separated roles
FORCE_UNLOCK_REQUIRES_REASON=true
FORCE_UNLOCK_AUDIT_LOG=true

# Viewer Tracking (optional)
LOCK_TRACK_VIEWERS=false                # Disabled by default
LOCK_VIEWER_TIMEOUT=600                 # 10 minutes for viewers
```

---

## Architecture Design

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER INTERFACE                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Edit Button  │  │ Queue Modal  │  │ Lock Status  │     │
│  │   Handler    │  │   Display    │  │   Badge      │     │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘     │
│         │                  │                  │              │
└─────────┼──────────────────┼──────────────────┼──────────────┘
          │                  │                  │
          ▼                  ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                  JAVASCRIPT LAYER                            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Lock Manager (lock-manager.js)                      │   │
│  │  - acquireLock()    - checkLockStatus()             │   │
│  │  - releaseLock()    - joinQueue()                    │   │
│  │  - extendLock()     - handleLockNotification()       │   │
│  │  - heartbeat()      - showConflictModal()            │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Autosave Manager (autosave-manager.js)             │   │
│  │  - saveDraft()      - restoreDraft()                 │   │
│  │  - deleteDraft()    - listDrafts()                   │   │
│  └──────────────────────────────────────────────────────┘   │
└──────────────────────────┬───────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    API ENDPOINTS                             │
│                                                              │
│  POST   /api/locks/acquire                                  │
│  POST   /api/locks/release                                  │
│  POST   /api/locks/extend                                   │
│  GET    /api/locks/check/:type/:id                          │
│  POST   /api/locks/force-release                            │
│  GET    /api/locks/queue/:type/:id                          │
│  POST   /api/locks/queue/join                               │
│  DELETE /api/locks/queue/leave                              │
│  POST   /api/autosave/save                                  │
│  GET    /api/autosave/restore/:type/:id                     │
│                                                              │
└──────────────────────────┬───────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                  BUSINESS LOGIC LAYER                        │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │  RecordLock      │  │  LockQueue       │                │
│  │  Model           │  │  Model           │                │
│  │                  │  │                  │                │
│  │ - acquire()      │  │ - join()         │                │
│  │ - release()      │  │ - getPosition()  │                │
│  │ - check()        │  │ - notify()       │                │
│  │ - extend()       │  │ - remove()       │                │
│  │ - forceRelease() │  │ - cleanup()      │                │
│  └──────────────────┘  └──────────────────┘                │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │  AutosaveDraft   │  │  LockController  │                │
│  │  Model           │  │  Controller      │                │
│  │                  │  │                  │                │
│  │ - save()         │  │ - handleAcquire()│                │
│  │ - restore()      │  │ - handleRelease()│                │
│  │ - delete()       │  │ - handleQueue()  │                │
│  │ - cleanup()      │  │ - handleOverride()│               │
│  └──────────────────┘  └──────────────────┘                │
└──────────────────────────┬───────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                          │
│                                                              │
│  ┌─────────────────┐  ┌─────────────────┐                  │
│  │ record_locks    │  │ lock_queue      │                  │
│  │ (active locks)  │  │ (waiting users) │                  │
│  └─────────────────┘  └─────────────────┘                  │
│                                                              │
│  ┌─────────────────┐  ┌─────────────────┐                  │
│  │ autosave_drafts │  │ audit_logs      │                  │
│  │ (saved drafts)  │  │ (lock events)   │                  │
│  └─────────────────┘  └─────────────────┘                  │
│                                                              │
│  ┌─────────────────┐                                        │
│  │ patients        │  (with version column)                 │
│  │ catheters       │  (optimistic locking)                  │
│  │ drug_regimes    │                                        │
│  │ etc.            │                                        │
│  └─────────────────┘                                        │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

**Scenario 1: Clean Edit (No Queue)**
```
1. User clicks "Edit" → Check lock status
2. No lock found → Acquire lock for user
3. Store lock in database (record_locks table)
4. Render edit form with lock badge
5. Start heartbeat (extend lock every 60s)
6. Start autosave (save draft every 2 min)
7. User saves → Release lock
8. Delete autosave draft
9. Log to audit_logs
```

**Scenario 2: Record Locked (Queue)**
```
1. User clicks "Edit" → Check lock status
2. Lock found (Dr. Sharma editing)
3. Show queue modal with options:
   - [Join Queue]
   - [View Read-Only]
   - [Override (Emergency)] - if authorized
4. User joins queue → Add to lock_queue table
5. Show queue position: "You are #2 in queue"
6. Poll for updates every 10 seconds
7. Dr. Sharma saves → Lock released
8. Trigger queue processing
9. Notify next user (via notifications table)
10. User receives notification → Click to acquire lock
11. Remove from queue → Acquire lock
```

**Scenario 3: Emergency Override**
```
1. Admin/Attending clicks "Override Lock"
2. Modal prompts for reason (required)
3. Validate permission (admin or attending role)
4. Force release current lock
5. Send notification to displaced user:
   "Your edit session was overridden by Dr. Johnson (Emergency)"
6. Save autosave for displaced user (recovery)
7. Acquire lock for overriding user
8. Log override to audit_logs (high priority)
```

---

## Phase 1: Database Schema

### 1.1 New Table: `record_locks`

Track active edit sessions.

```sql
CREATE TABLE IF NOT EXISTS record_locks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- What's being edited
    record_type ENUM('patient', 'catheter', 'drug_regime', 'functional_outcome', 'catheter_removal') NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    
    -- Who's editing
    user_id INT UNSIGNED NOT NULL,
    user_name VARCHAR(200) NOT NULL COMMENT 'Cached for performance',
    user_role ENUM('attending', 'resident', 'nurse', 'admin') NOT NULL,
    
    -- Lock metadata
    lock_type ENUM('viewing', 'editing') DEFAULT 'editing',
    locked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL COMMENT 'Auto-expire after timeout',
    
    -- Session info for validation
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL COMMENT 'Browser info for debugging',
    
    -- Override tracking
    is_force_locked BOOLEAN DEFAULT FALSE COMMENT 'Was this an emergency override?',
    force_lock_reason TEXT NULL COMMENT 'Reason for override',
    force_locked_by INT UNSIGNED NULL COMMENT 'Who overrode?',
    force_locked_at DATETIME NULL,
    
    -- Performance indexes
    INDEX idx_record_type_id (record_type, record_id),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    INDEX idx_session (session_id),
    INDEX idx_lock_type (lock_type),
    
    -- Unique constraint: One active lock per record
    UNIQUE KEY unique_record_lock (record_type, record_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (force_locked_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks active edit sessions to prevent concurrent editing conflicts';
```

### 1.2 New Table: `lock_queue`

Manage users waiting to edit locked records.

```sql
CREATE TABLE IF NOT EXISTS lock_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Record being waited for
    record_type ENUM('patient', 'catheter', 'drug_regime', 'functional_outcome', 'catheter_removal') NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    
    -- Waiting user
    user_id INT UNSIGNED NOT NULL,
    user_name VARCHAR(200) NOT NULL,
    user_role ENUM('attending', 'resident', 'nurse', 'admin') NOT NULL,
    
    -- Queue metadata
    queue_position INT UNSIGNED NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME NULL COMMENT 'When user was notified it is available',
    expires_at DATETIME NOT NULL COMMENT 'Queue entry expires if not claimed',
    
    -- Session tracking
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    
    -- Status
    status ENUM('waiting', 'notified', 'expired', 'cancelled') DEFAULT 'waiting',
    
    -- Indexes
    INDEX idx_record_type_id (record_type, record_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_queue_position (record_type, record_id, queue_position),
    INDEX idx_expires (expires_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Queue for users waiting to edit locked records';
```

### 1.3 New Table: `autosave_drafts`

Store autosaved form data for recovery.

```sql
CREATE TABLE IF NOT EXISTS autosave_drafts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Record being edited
    record_type ENUM('patient', 'catheter', 'drug_regime', 'functional_outcome', 'catheter_removal') NOT NULL,
    record_id INT UNSIGNED NULL COMMENT 'NULL for new records',
    
    -- Owner
    user_id INT UNSIGNED NOT NULL,
    
    -- Draft data (JSON)
    draft_data JSON NOT NULL COMMENT 'Form field values as JSON',
    
    -- Metadata
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL COMMENT 'Auto-delete after 24 hours',
    
    -- Context
    form_url VARCHAR(500) NULL COMMENT 'Where user was editing',
    session_id VARCHAR(255) NOT NULL,
    
    -- Indexes
    INDEX idx_record_type_id (record_type, record_id),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    UNIQUE KEY unique_user_record (user_id, record_type, record_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Autosaved form drafts for recovery';
```

### 1.4 Alter Existing Tables: Add Version Column

For optimistic concurrency control.

```sql
-- Add version column to all editable tables
ALTER TABLE patients 
    ADD COLUMN version INT UNSIGNED DEFAULT 0 NOT NULL
    COMMENT 'Version number for optimistic locking';

ALTER TABLE catheters 
    ADD COLUMN version INT UNSIGNED DEFAULT 0 NOT NULL
    COMMENT 'Version number for optimistic locking';

ALTER TABLE drug_regimes 
    ADD COLUMN version INT UNSIGNED DEFAULT 0 NOT NULL
    COMMENT 'Version number for optimistic locking';

ALTER TABLE functional_outcomes 
    ADD COLUMN version INT UNSIGNED DEFAULT 0 NOT NULL
    COMMENT 'Version number for optimistic locking';

ALTER TABLE catheter_removals 
    ADD COLUMN version INT UNSIGNED DEFAULT 0 NOT NULL
    COMMENT 'Version number for optimistic locking';

-- Add indexes for version queries
ALTER TABLE patients ADD INDEX idx_version (version);
ALTER TABLE catheters ADD INDEX idx_version (version);
ALTER TABLE drug_regimes ADD INDEX idx_version (version);
ALTER TABLE functional_outcomes ADD INDEX idx_version (version);
ALTER TABLE catheter_removals ADD INDEX idx_version (version);
```

---

## Phase 2: Backend Implementation

### Summary

Create the following backend components:

1. **RecordLock Model** (`src/Models/RecordLock.php`)
   - `acquireLock()` - Acquire lock on record
   - `releaseLock()` - Release lock
   - `checkLock()` - Check if record is locked
   - `extendLock()` - Heartbeat to extend lock
   - `forceRelease()` - Admin override
   - `cleanExpiredLocks()` - Cleanup cron job

2. **LockQueue Model** (`src/Models/LockQueue.php`)
   - `joinQueue()` - Add user to queue
   - `leaveQueue()` - Remove from queue
   - `getQueue()` - Get queue status
   - `notifyNext()` - Notify next user when available
   - `cleanExpiredEntries()` - Cleanup expired queue entries

3. **AutosaveDraft Model** (`src/Models/AutosaveDraft.php`)
   - `saveDraft()` - Save form draft
   - `restoreDraft()` - Restore saved draft
   - `deleteDraft()` - Delete draft
   - `listUserDrafts()` - List all user's drafts
   - `cleanExpiredDrafts()` - Cleanup old drafts

4. **LockController** (`src/Controllers/LockController.php`)
   - API endpoints for all lock operations
   - Queue management endpoints
   - Autosave endpoints
   - CSRF validation and permission checks

5. **Routes Configuration** (`config/routes.php`)
   - Add all API routes for lock management

6. **Cron Job** (`cron/cleanup-locks.php`)
   - Scheduled cleanup of expired locks, queues, and drafts
   - Run every 5 minutes

**Note:** Due to the extensive size of the code (several thousand lines), the full implementation code is available in the main plan document sections. Each component includes:
- Complete PHP code with inline documentation
- Error handling and validation
- Security checks and audit logging
- Integration with existing notification system

---

## Phase 3: Frontend Implementation

### Summary

Create frontend components for lock management:

1. **Lock Manager JavaScript** (`public/assets/js/lock-manager.js`)
   - Lock acquisition and release
   - Heartbeat mechanism (extend lock every 60 seconds)
   - Queue management UI
   - Autosave functionality (every 2 minutes)
   - Lock status indicators
   - Browser event handlers (page unload, form submit)

2. **Lock Manager CSS** (`public/assets/css/lock-manager.css`)
   - Lock status badge styling
   - Queue modal styling
   - Warning banners
   - Responsive design

3. **Edit Page Templates**
   - Integration code for catheter, patient, drug regime edit pages
   - Lock status badge display
   - Force unlock modal (for admins/attending)
   - Queue status display
   - Draft recovery prompts

**Key Features:**
- Visual lock status indicator always visible
- Prominent warning when record is locked by another user
- Queue position display with real-time updates
- Autosave indicator showing last save time
- Emergency override modal for authorized users
- Draft recovery on page load

---

## Phase 4: Notification Integration

Integrates with existing notification system (`src/Models/Notification.php`).

**New Notification Types:**
- `lock_acquired` - When user starts editing
- `lock_released` - When editing completes
- `lock_available` - Notify queued user record is available
- `lock_override` - Notify user their session was overridden
- `lock_expiring` - Warn user lock is about to expire

**Integration Points:**
- RecordLock model sends notifications on force unlock
- LockQueue model sends notifications when record becomes available
- Frontend JavaScript listens for notifications via existing polling

---

## Phase 5: Queue System

Already implemented in Phase 2 (LockQueue model) and Phase 3 (frontend JavaScript).

**Features:**
- Automatic queue position assignment
- Real-time queue status updates
- Notification when record becomes available
- Queue expiration after timeout
- Leave queue functionality

---

## Phase 6: Autosave System

Already implemented in Phase 2 (AutosaveDraft model) and Phase 3 (frontend JavaScript).

**Features:**
- Automatic save every 2 minutes
- Draft retention for 24 hours
- Draft restoration on page load
- Draft deletion on successful submit
- Emergency draft save when lock is overridden

---

## Phase 7: Admin Dashboard

Add active locks management section to admin dashboard.

**Features:**
- View all active edit sessions
- See lock duration and last activity
- Force release locks (with reason)
- View queue status per record
- Monitor lock system health

**Location:** `src/Views/admin/dashboard.php`

**Displays:**
- Record being edited
- User name and role
- Lock duration
- Last activity time
- Time until expiration
- Force release button

---

## Testing Strategy

### Unit Tests

Test each model's methods:
- Lock acquisition and release
- Queue management
- Draft save and restore
- Version conflict detection
- Permission validation

### Integration Tests

Test multi-user scenarios:
- Concurrent edit attempts
- Queue flow with notifications
- Emergency override workflow
- Autosave recovery after crash
- Lock expiration and cleanup

### User Acceptance Testing

Real-world scenarios with doctors:
1. Normal edit workflow
2. Concurrent access handling
3. Emergency override usage
4. Data recovery after browser crash
5. Queue notification experience

---

## Security Considerations

1. **Lock Hijacking Prevention**
   - Validate session ID on all operations
   - Check IP address consistency
   - Audit all lock events

2. **DoS Prevention**
   - Limit locks per user (max 5)
   - Limit queue size (max 10 per record)
   - Auto-expire after timeout
   - Rate limit API calls

3. **Privilege Escalation**
   - Verify edit permission before lock
   - Validate role for force unlock
   - Log all override attempts

4. **Data Security**
   - Don't expose sensitive user data
   - Sanitize all displayed names
   - Use CSRF tokens on all APIs
   - Prepared statements for SQL

---

## Deployment Checklist

### Pre-Deployment
- [ ] Backup database
- [ ] Test on staging
- [ ] Run migration script
- [ ] Update .env configuration
- [ ] Test API endpoints
- [ ] Verify cron job syntax

### Deployment Steps
1. Run database migration
2. Deploy code changes
3. Set file permissions
4. Install cron job
5. Clear cache
6. Restart services

### Post-Deployment
- [ ] Test lock acquisition
- [ ] Test queue system
- [ ] Test autosave
- [ ] Test force unlock
- [ ] Monitor error logs
- [ ] Verify cron runs
- [ ] Test with real users

### Rollback Plan
If issues occur:
1. Drop new tables
2. Remove version columns
3. Revert code changes
4. Remove cron job

---

## Future Enhancements

### Phase 8: Advanced Features (Future)

1. **Real-time Collaboration**
   - WebSocket integration
   - Live cursor positions
   - Field-level locking

2. **Conflict Resolution UI**
   - Side-by-side diff viewer
   - Three-way merge tool
   - Automatic conflict detection

3. **Analytics Dashboard**
   - Lock duration metrics
   - Contentious records report
   - User edit patterns
   - Queue wait time analysis

4. **Mobile App Support**
   - Native mobile lock management
   - Push notifications
   - Offline editing with sync

---

## Maintenance & Support

### Monitoring Metrics
- Average lock duration
- Queue wait times
- Force unlock frequency
- Autosave recovery rate
- Lock timeout occurrences

### Alerts
- Lock held > 1 hour
- Queue size > 10 users
- High force unlock rate
- Cron job failures

### Common Issues

**"Lock acquisition failed"**
- Check database connection
- Verify record exists
- Check user permissions
- Review audit logs

**"Heartbeat failing"**
- Check network connectivity
- Verify session active
- Check server load
- Review error logs

**"Autosave not working"**
- Check browser console
- Verify JavaScript loaded
- Check API accessibility
- Review storage limits

---

## Appendix: Quick Reference

### Configuration (.env)
```env
LOCK_ENABLED=true
LOCK_TIMEOUT=1800
LOCK_HEARTBEAT_INTERVAL=60
AUTOSAVE_ENABLED=true
AUTOSAVE_INTERVAL=120
QUEUE_ENABLED=true
QUEUE_MAX_SIZE=10
FORCE_UNLOCK_ROLES=admin,attending
```

### API Endpoints

**Lock Management:**
- `POST /api/locks/acquire` - Acquire lock
- `POST /api/locks/release` - Release lock
- `POST /api/locks/extend` - Extend lock (heartbeat)
- `GET /api/locks/check/:type/:id` - Check status
- `POST /api/locks/force-release` - Admin override

**Queue Management:**
- `POST /api/locks/queue/join` - Join queue
- `DELETE /api/locks/queue/leave` - Leave queue
- `GET /api/locks/queue/:type/:id` - Queue status

**Autosave:**
- `POST /api/autosave/save` - Save draft
- `GET /api/autosave/restore/:type/:id` - Restore draft
- `DELETE /api/autosave/delete` - Delete draft

### Cron Job
```bash
*/5 * * * * php /path/to/cron/cleanup-locks.php >> /var/log/aps-lock-cleanup.log 2>&1
```

---

## Conclusion

This comprehensive plan provides a production-ready concurrent editing protection system tailored for the Acute Pain Management healthcare application. The queue-based approach with visual indicators prioritizes user awareness while maintaining flexibility for emergency access.

**Key Benefits:**
- ✅ Prevents data loss from concurrent edits
- ✅ Improves team awareness and communication
- ✅ Maintains complete audit trail
- ✅ Allows emergency override when needed
- ✅ Automatic data recovery via autosave

**Implementation Timeline:**
- Phase 1-2 (Backend): ~16 hours
- Phase 3 (Frontend): ~14 hours  
- Phase 4-7 (Integration): ~8 hours
- **Total:** ~38 hours

When ready to implement, follow the phases sequentially. Start with database migration, then backend models, then frontend integration. Test thoroughly at each phase before proceeding.

---

**Document Version:** 1.0  
**Last Updated:** January 13, 2026  
**Status:** READY FOR IMPLEMENTATION  
**Next Review:** After implementation or Q2 2026
