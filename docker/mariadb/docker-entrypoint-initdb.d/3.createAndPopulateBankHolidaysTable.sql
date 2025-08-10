CREATE TABLE bank_holidays (
    id INT AUTO_INCREMENT NOT NULL,
    date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    PRIMARY KEY(id),
    UNIQUE KEY uk_bank_holiday_date (date)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

INSERT INTO bank_holidays (date, title) VALUES
('2025-01-01', 'New Year\'s Day'),
('2025-04-18', 'Good Friday'),
('2025-04-21', 'Easter Monday'),
('2025-05-05', 'Early May bank holiday'),
('2025-05-26', 'Spring bank holiday'),
('2025-08-25', 'Summer bank holiday'),
('2025-12-25', 'Christmas Day'),
('2025-12-26', 'Boxing Day');
