ALTER TABLE `__prefix__ModuleUpgrade`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
ALTER TABLE `__prefix__Action`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
ALTER TABLE `__prefix__Permission`
    ADD CONSTRAINT FOREIGN KEY (`role`) REFERENCES `__prefix__Role` (`id`);
ALTER TABLE `__prefix__Permission`
    ADD CONSTRAINT FOREIGN KEY (`action`) REFERENCES `__prefix__Action` (`id`);

