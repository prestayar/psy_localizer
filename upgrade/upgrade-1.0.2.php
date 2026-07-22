<?php

function upgrade_module_1_0_2($module)
{
    if ($module->isRegisteredInHook('actionAdminControllerSetMedia')) {
        return true;
    }

    return $module->registerHook('actionAdminControllerSetMedia');
}
