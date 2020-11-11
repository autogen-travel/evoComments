<?php

if ($modx->db->getRecordCount($modx->db->query('SHOW TABLES FROM ' . $modx->db->config['dbase'] . ' LIKE "' . $modx->db->config['table_prefix'] . 'evocomments_config' . '"')) == 0) {
	$sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $modx->db->config['table_prefix'] . 'evocomments_config 
        (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255),
        `value` text,
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $modx->db->config['table_prefix'] . 'evocomments 
        (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `document` int(11) DEFAULT NULL,
        `main_id` int(11) DEFAULT NULL,
        `parent_id` int(11) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `status` int(11) DEFAULT NULL,
        `comment` text,
        `attachments` text,
        `user` int(11) DEFAULT NULL,
        `ip` text,
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $modx->db->config['table_prefix'] . 'evocomments_users 
        (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `provider` varchar(255),
        `identifier` varchar(255),
        `profileURL` text,
        `photoURL` text,
        `firstName` varchar(255),
        `lastName` varchar(255),
        `gender` varchar(255),
        `email` varchar(255),
        `phone` varchar(255),
        `country` varchar(255),
        `city` varchar(255), 
        `token` varchar(255), 
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "display", "20");';
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "outerTpl", "@FILE:/assets/modules/evocomments/templates/outerTpl.tpl");';
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "formTpl", "@FILE:/assets/modules/evocomments/templates/formTpl.tpl");';
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "authBlockTpl", "@FILE:/assets/modules/evocomments/templates/authBlockTpl.tpl");';
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "profileDropdownTpl", "@FILE:/assets/modules/evocomments/templates/profileDropdownTpl.tpl");';    
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "commentTpl", "@FILE:/assets/modules/evocomments/templates/commentTpl.tpl");';
        $sql[] = 'INSERT INTO ' . $modx->getFullTableName('evocomments_config') . ' VALUES (NULL, "commentChildTpl", "@FILE:/assets/modules/evocomments/templates/commentChildTpl.tpl");';

        foreach ($sql as $v) {
            $modx->db->query($v);
        }
} 

include_once(MODX_BASE_PATH . 'assets/modules/evocomments/evocomments.class.php');
$evocomments = new EvoComments();

$ph = [];
$ph['theme'] = $modx->config['manager_theme'];
$ph['module_title'] = 'Evo Comments';
$ph['module_close'] = 'Закрыть';
$ph['module_url'] = 'index.php?a=112&id='.$_GET['id'];
$ph = array_merge($ph, $evocomments->config);
$mainTpl = $modx->getTpl('@FILE:assets/modules/evocomments/templates/module/main.tpl.php');

$ph['commentsList'] = $evocomments->getCommentsTable();
echo $modx->parseText($mainTpl, $ph, '[+', '+]');
exit();
//include('templates/module/main.tpl.php');

//echo '<pre>'.print_r($evocomments->config, true).'</pre>';  
exit();        