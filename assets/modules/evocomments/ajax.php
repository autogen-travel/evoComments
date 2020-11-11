<?php
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {return 'ajax, pls';}

define('MODX_API_MODE', true);

include_once("../../../index.php");

$modx->db->connect();

if (empty($modx->config)) {
    $modx->getSettings();
}

include_once(MODX_BASE_PATH . 'assets/modules/evocomments/evocomments.class.php');
$evocomments = new EvoComments();

$action = $modx->db->escape(trim($_POST['action']));

switch($action) {
	case 'login':
		if(isset($_POST['provider']) && $_POST['provider']=='simple') {
			$_POST['profile']['identifier'] = time();
			if(!isset($_POST['profile']['firstName']) || empty($_POST['profile']['firstName'])) {
				echo json_encode(['error'=>'name']);
				return '';
			}
		}
		$login_result = $evocomments->login(trim($_POST['provider']), $_POST['profile']);
		if(isset($login_result['token'])) {
			$result['token'] = $login_result['token'];
		} else {
			$result = $login_result;
		}	 
	break;

	case 'logout':
		$evocomments->setSession('token', 0);
		$result['status'] = 'ok';
	break;

	case 'post':
		$result['result'] = $evocomments->postComment(trim($_POST['page_id']), trim($_POST['parent_id']), $_POST['comment']);
		$result['status'] = 'ok';
	break;

	case 'saveSettings':
		$settings = $_POST['data'];
		foreach($settings as $k=>$v) {
			$modx->db->update(['value'=>$v], $modx->getFullTableName('evocomments_config'), 'name="'.$k.'"');
		}
		
		$result['status'] = 'ok';
	break;

	case 'loadmore':
		$offset = trim($_POST['offset']);
		$offset = $offset + $evocomments->config['display'];
		$result['result'] = $evocomments->render(trim($_POST['page_id']), $offset);
		$result['status'] = 'ok';
	break;

	case 'changeStatus':
		$status = intVal($_POST['data']['status']);
		$modx->db->update(['status'=>$status], $modx->getFulltableName('evocomments'), 'id='.$_POST['data']['id']);
		$result['status'] = 'ok';
	break;

	case 'destroyComment':
		$modx->db->delete($modx->getFulltableName('evocomments'), 'id='.$_POST['data']['id']);
		$result['status'] = 'ok';
	break;

	case 'loadComments':
		$result['rows'] = $evocomments->getCommentsTable(0, $_POST['data']['status']);
		$result['status'] = 'ok';
	break;
}

echo json_encode($result);
return;