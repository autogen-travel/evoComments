<?php

class EvoComments
{
    protected $evo;
    public $config;
    public $session;

    public function __construct() {
        $this->evo = evolutionCMS();
        $this->db = $this->evo->db;
        $this->basePath = MODX_BASE_PATH . 'assets/modules/evocomments/';
        $this->getSession();
        $this->getConfig();
        $this->commentTpl = $this->evo->getTpl($this->config['commentTpl']);
        $this->commentChildTpl = $this->evo->getTpl($this->config['commentChildTpl']);
        $this->outerTpl = $this->evo->getTpl($this->config['outerTpl']);
        $this->formTpl = $this->evo->getTpl($this->config['formTpl']);
        $this->authBlockTpl = $this->evo->getTpl($this->config['authBlockTpl']);
        $this->profileDropdownTpl = $this->evo->getTpl($this->config['profileDropdownTpl']);
    }



    public function getConfig() {
        if ($this->db->getRecordCount($this->db->query('SHOW TABLES FROM ' . $this->db->config['dbase'] . ' LIKE "' . $this->db->config['table_prefix'] . 'evocomments_config' . '"')) > 0 && empty($this->config)) {
            $sql = $this->db->select('*', $this->evo->getFullTableName('evocomments_config'));
            while ($config = $this->db->getRow($sql)) {
                $this->config[$config['name']] = $config['value'];
            }
        }

        return $this->config;
    }


    public function getCommentsTable($offset=0, $status=0) {
        $c_table = $this->evo->getFullTableName('evocomments');
        $doc_table = $this->evo->getFullTableName('site_content');
        $sql = "select * from {$c_table} as comments
            left join (SELECT id as doc_id, pagetitle FROM {$doc_table}) as docs
            on comments.document = docs.doc_id
            where comments.status={$status}
          order by comments.created_at DESC
        LIMIT {$offset},500";
       
        $q = $this->db->query($sql);
        if($this->db->getRecordCount($q)==0) return '';
        $result = [];
        while($row = $this->db->getRow($q)) {
            $row['date'] = $this->showDate(strtotime($row['created_at']));
            $row['user'] = $this->getUserProfile($row['user']);
            $result[] = $row;
        }


        $commentsList = '';
        $rowTpl = $this->evo->getTpl('@FILE:assets/modules/evocomments/templates/module/row.tpl.php');
        foreach($result as $comment) {
            $comment['url'] = $this->evo->makeUrl($comment['doc_id']);
            $comment['user_author'] = $comment['user']['author'];
            $commentsList .= $this->evo->parseText($rowTpl, $comment, '[+', '+]');
        }
        return $commentsList;
    }


    public function getSession($key = '', $default = '') {
        if ($key == '') {
            $this->session = &$_SESSION['evo-comments']; 
            $out = $this->session;
        } elseif ($key != '') {
            if (isset($this->session[$key])) {
                $out = $this->session[$key];
            } else {
                $out = $default;
            }
        } else {
            $out = $this->session;
        }
        return $out;
    }



    public function setSession($key, $value = '') {
        return $this->session[$key] = $value;
    }



    public function render($page_id=false, $display=false) {
        $display = !$display ? $this->config['display'] : $display;
        $page_id = !$page_id ? $this->evo->documentIdentifier : $page_id;
        $total_comments = $this->getTotalCount($page_id);
        $comments = $this->getComments($page_id, $display);
        

        $new_arr = [];
        foreach($comments as $c) {
            if($c['parent_id']==0) {
                $new_arr[$c['id']] = $c;
            } else {
                $new_arr[$c['main_id']]['childs'][] = $c;
            }
        }

        //echo '<pre>'.print_r($new_arr, true).'</pre>';
        //exit();

        $html = '';
        foreach($new_arr as $com) {
            $childrens = '';
            if(isset($com['childs'])) {
                foreach($com['childs'] as $child) {
                    $parent_user_id = $this->db->getValue($this->db->select('user', '[+prefix+]evocomments', 'id='.$child['parent_id']));
                    $parent_user = $this->getUserProfile($parent_user_id);
                    $comment_arr = array_merge($child, $this->getUserProfile($child['user']), ['reply_name'=>$parent_user['author']]); 
                    if($comment_arr['status'] == 9) {
                        $comment_arr['comment'] = 'Комментарий удалён';
                    }
                    $comment_arr['deleted'] = $comment_arr['status'] == 9 ? 'ec_comment_deleted' : '';
                    $childrens .= $this->evo->parseText($this->commentChildTpl, $comment_arr, '[+', '+]');
                } 
            }
            $user = isset($com['user']) ? $this->getUserProfile($com['user']) : [];
            $comment_arr = array_merge($com, $user, ['wrap'=>$childrens]); 
            if(!isset($comment_arr['status']) || $comment_arr['status'] == 9) {
                $comment_arr['comment'] = 'Комментарий удалён';
            }
            $comment_arr['deleted'] = !isset($comment_arr['status']) || $comment_arr['status'] == 9 ? 'ec_comment_deleted' : '';
            $html .= $this->evo->parseText($this->commentTpl, $comment_arr, '[+', '+]'); 
        }


        
        //Проверка на авторизацию
        $user_profile = $this->getUserProfile();
        if(!$user_profile) {
            $form = '<div data-evocomments-form><div data-evocomments-auth>'.$this->authBlockTpl.'</div></div>';
            $profile = '';
        } else {
            $avatar = $user_profile['avatar'];
            $profile = $this->evo->parseText($this->profileDropdownTpl, $user_profile, '[+', '+]'); 
            $form = '<div data-evocomments-form>'.$this->evo->parseText($this->formTpl, ['avatar'=>$avatar], '[+', '+]').'</div>';
        }

        
        $moreBtn = $total_comments > $display ? 'style="display:block;"' : '';
        
        return $this->evo->parseText($this->outerTpl, ['comments'=>$html, 'form'=>$form, 'profile'=>$profile, 'moreBtn'=>$moreBtn], '[+', '+]');
    } 



    public function postComment($docid, $parent_id, $comment) {
        include_once MODX_BASE_PATH. 'assets/lib/APIHelpers.class.php';
        $user_profile = $this->getUserProfile();
        if(!$user_profile) {
            return ['error'=>'не авторизован'];
        }

        $insert = [];
        $insert['document'] = $docid;
        $insert['parent_id'] = $parent_id;
        $insert['comment'] = $this->db->escape(strip_tags($comment));
        $insert['user'] = $user_profile['user_id'];
        $insert['created_at'] = date('Y-m-d H:i:s');
        $insert['ip'] = APIHelpers::getUserIP();
        $insert['status'] = 0;
        if(isset($parent_id) && is_numeric($parent_id) && $parent_id>0) {
            $sql = $this->db->select('main_id, user', '[+prefix+]evocomments', 'id='.$parent_id, '', 1);
            $parent_comment = $this->db->getRow($sql);
            $main_id = $parent_comment['main_id'];
            $parent_user = $this->getUserProfile($parent_comment['user']);
            if(!empty($parent_user['email'])) {
                $page_url = $this->evo->makeUrl($docid, '', '', 'full');
                $this->sendEmail(
                    'Ответ на Ваш комментарий',
                    $parent_user['email'],
                    'На странице <a href="'.$page_url.'">'.$page_url.'</a> ответили на Ваш комментарий. <br/>Текст комментария:<br/>'.$insert['comment']
                );  
            }
            $insert['main_id'] = $main_id;
            $comment_id = $this->db->insert($insert, '[+prefix+]evocomments');
        } else {
            $main_id = $this->db->insert($insert, '[+prefix+]evocomments');
            $update_result = $this->db->update(['main_id'=>$main_id], '[+prefix+]evocomments', 'id='.$main_id);
            $comment_id = $main_id;
        }

        $comment_arr = $this->getCommentById($comment_id);
        $insert['html'] = $parent_id==0 ? $this->evo->parseText($this->commentTpl, $comment_arr, '[+', '+]') : $this->evo->parseText($this->commentChildTpl, $comment_arr, '[+', '+]');

        //$main_id = 

        return $insert;
    }

    protected function sendEmail($subject, $email, $message='') {   
        $email_param = array();
        $email_param['from']    = $this->evo->config['emailsender'];
        $email_param['subject'] = $subject;
        $email_param['body']    = $message;
        $email_param['to']      = $email;
        return $this->evo->sendmail($email_param);
    }

    protected function getCommentById($comment_id) {
        $q = $this->db->select('*', '[+prefix+]evocomments', 'id='.$comment_id, '', 1);
        $comment = $this->db->getRow($q);
        $comment['date'] = 'только что';
        $user = $this->getUserProfile($comment['user']);

        if($comment['parent_id']>0) {
            $parent_user_id = $this->db->getValue($this->db->select('user', '[+prefix+]evocomments', 'id='.$comment['parent_id']));
            $parent_user = $this->getUserProfile($parent_user_id);
            $comment['reply_name'] = $parent_user['author'];
        } else {
            $comment['wrap'] = '';
        }
        

        $comment = array_merge($comment, $user);
        return $comment;
    }

    protected function getComments($docid, $display) {
        $comments = [];
        $q = $this->db->select('*', '[+prefix+]evocomments', 'document='.$docid, 'main_id DESC, parent_id ASC, id ASC', $display);
        if($this->db->getRecordCount($q)==0) return [];
        while($row = $this->db->getRow($q)) {
            $row['date'] = $this->showDate(strtotime($row['updated_at']));
            $comments[] = $row;
        }
        return $comments;
    }

    protected function getTotalCount($docid){
        $q = $this->db->select('id', '[+prefix+]evocomments', 'document='.$docid);
        return $this->db->getRecordCount($q);
    }

    protected function showDate($time) { // Определяем количество и тип единицы измерения
      $time = time() - $time;
      if ($time < 60) {
        return 'меньше минуты назад';
      } elseif ($time < 3600) {
        return $this->dimension((int)($time/60), 'i');
      } elseif ($time < 86400) {
        return $this->dimension((int)($time/3600), 'G');
      } elseif ($time < 2592000) {
        return $this->dimension((int)($time/86400), 'j');
      } elseif ($time < 31104000) {
        return $this->dimension((int)($time/2592000), 'n');
      } elseif ($time >= 31104000) {
        return $this->dimension((int)($time/31104000), 'Y');
      }
    }

    protected function dimension($time, $type) { // Определяем склонение единицы измерения
      $dimension = array(
        'n' => array('месяцев', 'месяц', 'месяца', 'месяц'),
        'j' => array('дней', 'день', 'дня'),
        'G' => array('часов', 'час', 'часа'),
        'i' => array('минут', 'минуту', 'минуты'),
        'Y' => array('лет', 'год', 'года')
      );
        if ($time >= 5 && $time <= 20)
            $n = 0;
        else if ($time == 1 || $time % 10 == 1)
            $n = 1;
        else if (($time <= 4 && $time >= 1) || ($time % 10 <= 4 && $time % 10 >= 1))
            $n = 2;
        else
            $n = 0;
        return $time.' '.$dimension[$type][$n]. ' назад';
    }

    protected function getUserProfile($userid=0) {
        if(!$userid) {
            $token = isset($this->session['token']) && !empty($this->session['token']) ? $this->session['token'] : false;
            if(!$token) return false;

            $q = $this->db->select('*', '[+prefix+]evocomments_users', 'token="'.$token.'"', '', 1);
            if($this->db->getRecordCount($q)==0) return false;
            $profile = $this->db->getRow($q);
        } else {
            $q = $this->db->select('*', '[+prefix+]evocomments_users', 'id='.$userid, '', 1);
            if($this->db->getRecordCount($q)==0) return false;
            $profile = $this->db->getRow($q);
        }

        $profile['avatar'] = !empty($profile['photoURL']) ? $profile['photoURL'] : '/assets/modules/evocomments/img/avatar.png';
        $profile['author'] = trim($profile['firstName'].' '.$profile['lastName']);
        $profile['user_id'] = $profile['id'];
        unset($profile['id']);
        return $profile;
    }

    
    public function login($provider, $profile) {
        $token = md5($provider.$profile['identifier'].'evocomments');
        $q = $this->db->select('id', '[+prefix+]evocomments_users', 'token="'.$token.'"', '', 1);

        if (isset($profile['email']) && !empty($profile['email']) && !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error'=>'email'];
        }
        //Если отсутствует - создаём, иначе авторизуем
        if($this->db->getRecordCount($q)==0) {    
            $user_id = $this->db->insert([
                'provider'=>$provider,
                'identifier'=> isset($profile['identifier']) ? $this->db->escape($profile['identifier']) : '',
                'profileURL'=> isset($profile['profileURL']) ? $this->db->escape($profile['profileURL']) : '',
                'photoURL'=> isset($profile['photoURL']) ? str_replace('http://', 'https://', $this->db->escape($profile['photoURL'])) : '',
                'firstName'=> isset($profile['firstName']) ? $this->db->escape($profile['firstName']) : '',
                'lastName'=> isset($profile['lastName']) ? $this->db->escape($profile['lastName']) : '',
                'gender'=> isset($profile['gender']) ? $this->db->escape($profile['gender']) : '',
                'email'=> isset($profile['email']) ? $this->db->escape($profile['email']) : '',
                'phone'=> isset($profile['phone']) ? $this->db->escape($profile['phone']) : '',
                'country'=> isset($profile['country']) ? $this->db->escape($profile['country']) : '',
                'city'=> isset($profile['city']) ? $this->db->escape($profile['city']) : '',
                'token'=>$token
            ], '[+prefix+]evocomments_users');
        }
        $this->setSession('token', $token);
        return ['token'=>$this->getSession('token')];
    }




}