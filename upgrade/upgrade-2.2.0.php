<?php

function upgrade_module_2_2_0($module)
{
    return $module->registerHook('actionCartGridDataModifier');
}
