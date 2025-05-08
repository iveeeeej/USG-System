-- Update lst_fnd table structure
ALTER TABLE `lst_fnd`
ADD COLUMN `category` varchar(50) DEFAULT NULL AFTER `lst_name`,
ADD COLUMN `date_found` date DEFAULT NULL AFTER `category`,
ADD COLUMN `location` varchar(255) DEFAULT NULL AFTER `date_found`,
ADD COLUMN `description` text DEFAULT NULL AFTER `location`,
ADD COLUMN `status` varchar(20) DEFAULT 'Unclaimed' AFTER `description`; 