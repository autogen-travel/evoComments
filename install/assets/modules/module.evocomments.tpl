/**
 * EvoComments
 *
 * EvoComments module
 *
 * @category    module
 * @internal    @modx_category Manager and Admin
 */
//<?php

if (!$modx->hasPermission('exec_module')) {
    $modx->sendRedirect('index.php?a=106');
}

if (!is_array($modx->event->params)) {
    $modx->event->params = [];
}

include_once('../assets/modules/evocomments/evocomments.module.php');