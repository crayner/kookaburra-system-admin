ALTER TABLE __prefix__ModuleUpgrade
    ADD CONSTRAINT FOREIGN KEY (module) REFERENCES gibbonModule (id);
ALTER TABLE `gibbonAction`
    ADD CONSTRAINT FOREIGN KEY (`module`) REFERENCES `gibbonModule` (`id`);
ALTER TABLE __prefix__Permission ADD CONSTRAINT FOREIGN KEY (role) REFERENCES gibbonRole (id);
ALTER TABLE __prefix__Permission ADD CONSTRAINT FOREIGN KEY (action) REFERENCES gibbonAction (id);

