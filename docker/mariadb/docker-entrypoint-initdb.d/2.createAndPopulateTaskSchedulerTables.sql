-- Create the main task configuration table
CREATE TABLE dynamic_task_message (
    id INT AUTO_INCREMENT NOT NULL,
    type VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    schedule VARCHAR(500) NOT NULL,
    timezone VARCHAR(255) NOT NULL,
    priority INT DEFAULT 50 NOT NULL,
    active TINYINT(1) DEFAULT 1 NOT NULL,
    working_days_only TINYINT(1) DEFAULT 0 NOT NULL,
    metadata JSON DEFAULT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Create the task execution tracking table
CREATE TABLE task_execution (
    id INT AUTO_INCREMENT NOT NULL,
    task_id INT NOT NULL,
    next_scheduled_at DATETIME DEFAULT NULL,
    executed_at DATETIME DEFAULT NULL,
    execution_time INT DEFAULT NULL,
    last_result LONGTEXT DEFAULT NULL,
    failure_count int DEFAULT 0 NOT NULL,
    PRIMARY KEY(id),
    UNIQUE INDEX UNIQ_task_execution_task_id (task_id),
    CONSTRAINT FK_task_execution_task_id FOREIGN KEY (task_id) REFERENCES dynamic_task_message (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Insert task configurations (execution tracking records will be created automatically via cascade)
INSERT INTO dynamic_task_message (type, name, schedule, timezone, priority, active, working_days_only, metadata) VALUES
-- Basic Periodic Tasks
('system_health_check', 'System Health Monitor', '@daily', 'UTC', 10, 1, 0, '{"alertEmail":"ops@company.com","checkServices":["db","redis","api"]}'),
('data_backup', 'Nightly Database Backup', '0 2 * * *', 'UTC', 5, 1, 0, '{"backupType":"full","retentionDays":30}'),
('sales_summary', 'Weekly Sales Summary', '0 9 * * 1', 'Europe/London', 50, 1, 0, '{"recipients":["sales@company.com"],"includeCharts":true}'),

-- Time-Constrained Tasks (Business Hours Only)
('customer_sync', 'Customer Data Sync', '0 9-17 * * 1-5', 'Europe/London', 30, 1, 0, '{"batchSize":500,"timeoutSeconds":300}'),
('invoice_processor', 'Process Pending Invoices', '0,30 8-18 * * 1-5', 'Europe/London', 25, 1, 1, '{"maxInvoices":100,"notifyFinance":true}'),
('stock_alerts', 'Low Stock Alerts', '0 7-19 * * *', 'Europe/London', 40, 1, 0, '{"thresholdPercent":10,"recipients":["warehouse@company.com"]}'),

-- High-Frequency Monitoring Tasks
('queue_monitor', 'Message Queue Monitor', '5 minutes', 'UTC', 15, 1, 0, '{"maxDepth":1000,"alertThreshold":500}'),
('api_healthcheck', 'External API Health Check', '2 minutes', 'UTC', 20, 1, 0, '{"endpoints":["payment","shipping","crm"],"timeoutMs":5000}'),

-- Same Task Type, Different Instances - Sales Reports
('sales_report', 'Monthly Sales Report (Management)', '0 9 1 * *', 'Europe/London', 45, 1, 0, '{"timespan":"last_month","recipients":["jane@example.com"],"format":"executive_summary"}'),
('sales_report', 'Weekly Sales Report (Team)', '0 9 * * 1', 'Europe/London', 50, 1, 0, '{"timespan":"last_7_days","recipients":["beth@example.com"],"format":"detailed"}'),
('sales_report', 'Daily Sales Flash', '0 17 * * 1-5', 'Europe/London', 55, 0, 1, '{"timespan":"today","recipients":["sales-team@example.com"],"format":"summary"}'),

-- Same Task Type, Different Instances - Inventory Reports
('inventory_summary', 'Daily Warehouse A Inventory', '0 8 * * 1-5', 'Europe/London', 40, 0, 0, '{"warehouse":"A","includeForecasting":false,"recipients":["warehouse-a@example.com"]}'),
('inventory_summary', 'Weekly All Warehouses Report', '0 9 * * 1', 'Europe/London', 35, 1, 0, '{"warehouse":"all","includeForecasting":true,"recipients":["logistics@example.com","procurement@example.com"]}'),

-- Same Task Type, Different Instances - Data Exports
('data_export', 'Customer Export (Marketing)', '0 3 * * 2', 'UTC', 60, 1, 0, '{"exportType":"customers","filters":["active","opted_in"],"destination":"s3://marketing-bucket"}'),
('data_export', 'Customer Export (Analytics)', '0 4 1 * *', 'UTC', 65, 1, 0, '{"exportType":"customers","filters":["all"],"includeHistory":true,"destination":"s3://analytics-bucket"}'),

-- Complex Business Logic Tasks
('subscription_renewal', 'Process Subscription Renewals', '0 6 * * 1-5', 'Europe/London', 20, 1, 0, '{"daysAhead":7,"sendReminders":true,"maxBatchSize":200}'),
('payment_reconciliation', 'Daily Payment Reconciliation', '30 9 * * 1-5', 'Europe/London', 25, 1, 1, '{"paymentProviders":["stripe","paypal"],"tolerance":0.01}'),

-- Weekend/24-7 Tasks
('log_archival', 'Archive Old Logs', '0 1 * * 0', 'UTC', 70, 1, 0, '{"retentionDays":90,"compressFiles":true,"archiveLocation":"s3"}'),
('cache_warmup', 'Warm Application Cache', '@hourly', 'UTC', 60, 1, 0, '{"cacheKeys":["products","categories","promotions"],"preloadPercent":80}'),

-- High-Frequency Demo Tasks (for testing/demo purposes)
('send_emails', 'Send Pending Emails', '60 seconds', 'UTC', 80, 1, 0, '{"batchSize":50,"maxRetries":3,"provider":"smtp"}'),
('send_sms', 'Send Pending SMS Messages', '30 seconds', 'UTC', 85, 1, 0, '{"batchSize":25,"maxRetries":2,"provider":"twilio"}');

-- Create corresponding task_execution records for all tasks
-- This ensures each task has an execution tracking record from the start
INSERT INTO task_execution (task_id)
SELECT id FROM dynamic_task_message;
