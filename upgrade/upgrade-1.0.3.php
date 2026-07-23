<?php

function upgrade_module_1_0_3($module)
{
    $hookId = \Hook::getIdByName('dashboardZoneOne');

    if (!$hookId || !$module->isRegisteredInHook($hookId)) {
        return true;
    }

    return $module->unregisterHook($hookId);
}
