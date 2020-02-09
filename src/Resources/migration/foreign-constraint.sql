ALTER TABLE `__prefix__ModuleUpgrade`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
ALTER TABLE `__prefix__Action`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
ALTER TABLE `__prefix__Permission`
    ADD CONSTRAINT FOREIGN KEY (`role`) REFERENCES `__prefix__Role` (`id`);
ALTER TABLE `__prefix__Permission`
    ADD CONSTRAINT FOREIGN KEY (`action`) REFERENCES `__prefix__Action` (`id`);
ALTER TABLE `__prefix__Notification`
    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);
ALTER TABLE `__prefix__Notification`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `__prefix__Module` (`id`);
ALTER TABLE `__prefix__NotificationListener`
    ADD CONSTRAINT FOREIGN KEY (`notification_event`) REFERENCES `__prefix__NotificationEvent` ('id');
ALTER TABLE `__prefix__NotificationListener`
    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);

