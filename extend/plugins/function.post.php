<?php
/**
 * Date: 2019/5/24
 * Time: 15:37
 */

function smarty_function_post($params, Smarty_Internal_Template $template)
{
    $name = $params['name'] ?? '';
    if (empty($name)) {
        return '';
    }
    return getPostString($name);
}