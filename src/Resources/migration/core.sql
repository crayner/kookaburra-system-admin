INSERT INTO `__prefix__Role` (`category`, `name`, `nameShort`, `description`, `type`, `canLoginRole`, `futureYearsLogin`, `pastYearsLogin`, `restriction`) VALUES
('Staff', 'Administrator', 'Adm', 'Controls all aspects of the system', 'Core', 'Y', 'Y', 'Y', 'Admin Only'),
('Staff', 'Teacher', 'Tcr', 'Regular, classroom teacher', 'Core', 'Y', 'Y', 'Y', 'None'),
('Student', 'Student', 'Std', 'Person studying in the school', 'Core', 'Y', 'Y', 'Y', 'None'),
('Parent', 'Parent', 'Prt', 'Parent or guardian of person studying in', 'Core', 'Y', 'Y', 'Y', 'None'),
('Staff', 'Support Staff', 'SSt', 'Staff who support teaching and learning', 'Core', 'Y', 'Y', 'Y', 'None');
