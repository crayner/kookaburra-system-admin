CREATE TABLE `__prefix__Action` (
    `id` int(7) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The action name should be unqiue to the module that it is related to',
    `precedence` int(2) DEFAULT NULL,
    `category` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
    `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `URLList` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Comma seperated list of all URLs that make up this action',
    `entryURL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `entrySidebar` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `menuShow` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `defaultPermissionAdmin` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `defaultPermissionTeacher` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `defaultPermissionStudent` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `defaultPermissionParent` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `defaultPermissionSupport` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `categoryPermissionStaff` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `categoryPermissionStudent` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `categoryPermissionParent` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `categoryPermissionOther` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `module` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `moduleActionName` (`name`,`module`),
    KEY `gibbonModuleID` (`module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE IF NOT EXISTS `__prefix__Module` (
    `id` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'This name should be globally unique preferably, but certainly locally unique',
    `description` longtext COLLATE utf8_unicode_ci NOT NULL,
    `entryURL` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'index.php',
    `type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Core',
    `active` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `category` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
    `version` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
    `author` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
    `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE IF NOT EXISTS `__prefix__Role` (
    `id` int(3) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `category` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Staff',
    `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
    `nameShort` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
    `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
    `type` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Core',
    `canLoginRole` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `futureYearsLogin` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `pastYearsLogin` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `restriction` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'None',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `nameShort` (`nameShort`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE __prefix__ModuleUpgrade (
    id INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT,
    module INT(4) UNSIGNED ZEROFILL,
    version VARCHAR(14) NOT NULL,
    executed_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_3B5BDC02C242628 (module),
    UNIQUE INDEX module_version (module, version),
    PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE __prefix__Permission (
    `id` INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT,
    `role` INT(3) UNSIGNED ZEROFILL,
    `action` INT(7) UNSIGNED ZEROFILL,
    INDEX `role` (`role`),
    INDEX `action` (`action`),
    UNIQUE INDEX `roleAction` (`role`, `action`),
    PRIMARY KEY(`id`)
    ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;

