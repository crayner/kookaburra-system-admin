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
    KEY `__prefix__ModuleID` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE `__prefix__Module` (
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
CREATE TABLE `__prefix__Role` (
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
    version VARCHAR(20) NOT NULL,
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
CREATE TABLE `__prefix__Notification` (
    `id` INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT, 
    `status` VARCHAR(8) DEFAULT 'New' NOT NULL, 
    `count` INT(4), 
    `text` LONGTEXT NOT NULL,
    `actionLink` VARCHAR(255) NOT NULL COMMENT 'Relative to absoluteURL, start with a forward slash', 
    `timestamp` DATETIME NOT NULL, 
    `person` INT(10) UNSIGNED ZEROFILL, 
    `module` INT(4) UNSIGNED ZEROFILL, 
    INDEX IDX_D5180450CC6782D6 (`person`), INDEX IDX_D5180450CB86AD4B (`module`), PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE `__prefix__NotificationEvent` (
  `id` int(6) UNSIGNED ZEROFILL AUTO_INCREMENT,
  `event` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `moduleName` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `actionName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Core',
  `scopes` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'All',
  `active` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `module` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
  `action` int(7) UNSIGNED ZEROFILL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event` (`event`,`moduleName`),
  KEY `FK_A364BEAD9E834449` (`module`),
  KEY `FK_A364BEADB6AA0365` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE `__prefix__NotificationListener` (
    `id` INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT,
    `scopeType` VARCHAR(30) DEFAULT NULL,
    `scopeID` INT(20) UNSIGNED,
    `notification_event` INT(6) UNSIGNED ZEROFILL,
    `person` INT(10) UNSIGNED ZEROFILL,
    INDEX IDX_6313F17E26A39C71 (`notification_event`), INDEX IDX_6313F17ECC6782D6 (`person`), PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE `__prefix__String` (
    `id` INT(8) UNSIGNED AUTO_INCREMENT,
    `original` VARCHAR(100) NOT NULL,
    `replacement` VARCHAR(100) NOT NULL,
    `mode` VARCHAR(8) NOT NULL,
    `caseSensitive` VARCHAR(1) NOT NULL,
    `priority` INT(2),
    PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE `__prefix__I18n` (
    `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
    `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
    `active` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
    `installed` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `systemDefault` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    `dateFormat` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
    `dateFormatRegEx` longtext COLLATE utf8_unicode_ci NOT NULL,
    `dateFormatPHP` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
    `rtl` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `__prefix__Setting` (
     `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
     `scope` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     `nameDisplay` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     `value` longtext COLLATE utf8_unicode_ci,
     PRIMARY KEY (`id`),
     UNIQUE KEY `scope_name` (`scope`,`name`) USING BTREE,
     UNIQUE KEY `scope_display` (`scope`,`nameDisplay`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
