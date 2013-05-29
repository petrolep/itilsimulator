--
-- SMAZÁNÍ SEZENÍ A OSTATNÍCH DAT VZNIKLÝCH ZA PROVOZU
-- 
truncate table workflow_activity_specifications_per_scenario_steps;
delete from workflow_activity_specifications where isDefault = 0;
truncate table service_specifications_per_scenario_steps;
delete from service_specifications where isDefault = 0;
truncate table operation_events;
truncate table operation_incidents;
truncate table operation_problems;
truncate table configuration_item_specifications_per_scenario_steps;
delete from configuration_item_specifications where isDefault = 0;
truncate table scenario_steps;
truncate table training_steps;
truncate table sessions;