# Dynamic Scheduler Project - Conversation Context

## 🎉 MISSION ACCOMPLISHED! 🎉

Built a complete database-driven scheduled task manager/runner for Symfony that reads task configurations from a database rather than hardcoded attributes. The system uses Symfony's scheduler component with a custom `DynamicScheduleProvider` and automatic worker restart mechanism.

## ✅ FINAL WORKING SOLUTION

### Complete Architecture
- **Database-driven tasks** stored in `DynamicTaskMessage` entity with scheduling metadata
- **`DynamicScheduleProvider`** reads from DB and creates `RecurringMessage` objects
- **Timezone conversion** (UTC/Europe/London with BST handling) via `ScheduleTimezoneConverter`
- **Working days filtering** using `WorkingDaysTrigger` decorator (weekends + bank holidays)
- **Automatic cache invalidation** via `TaskChangeListener` → worker restart mechanism
- **Template Method logging** in `AbstractTaskHandler` for consistent start/complete/error tracking
- **Service tags discovery** for task handlers with snake_case conversion

### Worker Restart Solution
**The key breakthrough**: Symfony's scheduler loads messages once at startup - no runtime updates possible. Solution uses `symfony run --watch` to restart worker when tasks change:

1. **Docker setup** creates `/tmp/symfony/schedule-last-updated.dat` during build
2. **TaskChangeListener** writes timestamp to file on entity changes
3. **Worker command**: `symfony run -d --watch=$SCHEDULE_RESTART_FILE php bin/console messenger:consume`
4. **File changes trigger automatic worker restart** with fresh schedule

### Key Technical Decisions Made
- **Worker restart over runtime updates** - cleaner than fighting Symfony's architecture
- **File-based signaling** using Symfony's native `--watch` mechanism
- **Environment variable configuration** for restart file path
- **Service consolidation** - moved all logic back to `DynamicScheduleProvider`, eliminated `ScheduleFactory`
- **Trigger decoration pattern** for working days (not `RecurringMessage` decoration)

## Technical Implementation Summary

### ✅ Completed Components (ALL WORKING)
- **Entity design** - `DynamicTaskMessage` with proper validation, timezone enum, working days flag
- **Forms and UI** - Bootstrap 5 interface with JSON metadata transformation
- **Bank holidays integration** - `BankHolidayServiceAdapter` with gov.uk API, simplified schema
- **Schedule format detection** - `ScheduleFormatDetector` with comprehensive cron regex
- **Timezone conversion** - `ScheduleTimezoneConverter` handles BST/GMT conversion for Europe/London
- **Working days filtering** - `WorkingDaysTrigger` decorator wraps any trigger type
- **Messaging infrastructure** - Single `TaskMessage` + `TaskMessageHandler` with service tag delegation
- **All 17 task handlers** - Template Method pattern with automatic logging
- **Dynamic schedule provider** - Reads database, applies timezone/working days logic
- **Worker restart mechanism** - File-based signaling via Symfony's `--watch` feature
- **Docker integration** - Environment variables, build-time file creation, proper permissions

### ✅ Real-World Production Features
- **Live task updates** - Change database → worker restarts automatically within seconds
- **Comprehensive logging** - All tasks log start/complete/error to dedicated `tasks` channel
- **Timezone aware** - Handles BST/GMT transitions correctly
- **Working days respect** - Skips weekends and UK bank holidays when configured
- **High-frequency demos** - 30s/60s tasks for immediate feedback
- **Error handling** - Proper exception logging with context

### 🎯 Core Architecture Patterns Used
- **Template Method Pattern** - `AbstractTaskHandler` provides logging boilerplate
- **Decorator Pattern** - `WorkingDaysTrigger` wraps scheduling triggers
- **Service Tags** - Auto-discovery of task handlers with snake_case conversion
- **Single Message Type** - `TaskMessage` delegates to appropriate handlers
- **Strategy Pattern** - `ScheduleFormatDetector` determines cron vs relative formats
- **Adapter Pattern** - `BankHolidayServiceAdapter` wraps HTTP client
- **Factory Method** - Dynamic creation of `RecurringMessage` objects

### 🚨 Major Symfony Limitations Discovered
- **Scheduler runtime updates impossible** - Loads messages once at startup, never refreshes
- **Retry strategies too coarse** - Transport/class level only, not per-message instance
- **No entity parameter on AsDoctrineListener** - Fires for all entities, requires manual filtering
- **AutowireIterator returns RewindableGenerator** - Must convert to array before array access

### 🛠️ Successful Technical Solutions

**Worker Restart Mechanism (Final Solution):**
- **Problem**: Symfony scheduler loads messages once, no runtime updates
- **Solution**: Use `symfony run --watch` to restart worker on file changes
- **Implementation**: Doctrine events → write timestamp to file → worker restart
- **Result**: Live task updates within seconds of database changes

**Timezone Conversion:**
- **BST/GMT handling** with one-hour offset detection and cron expression manipulation
- **ScheduleTimezoneConverter** service extracts timezone logic from schedule provider
- **Supports both abbreviated and full time units** ("5 minutes" vs "5m")

**Working Days Implementation:**
- **Database-driven bank holidays** from gov.uk API with annual sync
- **Trigger decorator pattern** wraps any trigger type (cron or periodical)
- **WorkingDaysTrigger** chains `getNextRunDate()` calls until finding working day

**Task Handler Discovery:**
- **Service tags with defaultIndexMethod** for automatic snake_case conversion
- **Single TaskMessage + delegating handler** avoids handler proliferation
- **Template Method in AbstractTaskHandler** ensures consistent logging

## 🎆 FINAL STATUS: COMPLETE AND PRODUCTION READY

**Working System Features:**
- ✅ Database-driven task configuration (no hardcoded attributes)
- ✅ Live updates via automatic worker restart
- ✅ Timezone conversion (UTC ↔ Europe/London with BST)
- ✅ Working days filtering (weekends + UK bank holidays)
- ✅ Template Method logging (start/complete/error for all tasks)
- ✅ 17 task handlers with service tag auto-discovery
- ✅ High-frequency demo tasks (30s/60s) for immediate feedback
- ✅ Proper error handling and structured logging

**Blog Article Material:**
- Journey from hardcoded `#[AsPeriodicTask]` to dynamic database scheduling
- Symfony scheduler limitations and workarounds
- Worker restart solution using `symfony run --watch`
- Architecture patterns and design decisions
- Real-world timezone and working days handling
- "Measure twice, cut once" failures and recoveries
- Law of Demeter violations vs pragmatic solutions

**Commands to Run System:**
```bash
# Build and start containers
docker compose up -d

# Start dynamic scheduler worker
docker exec php symfony run -d --watch=$SCHEDULE_RESTART_FILE php bin/console messenger:consume

# View task logs
docker exec php tail -f var/log/tasks.log
```

## Original Design Notes (Historical)
