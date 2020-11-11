<?php if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>[+module_title+]</title>
    <link rel="stylesheet" type="text/css" href="media/style/[+theme+]/style.css" />
    <script type="text/javascript" src="media/script/tabpane.js"></script>
    <script type="text/javascript" src="media/script/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="media/script/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="media/script/mootools/mootools.js"></script>
    <script type="text/javascript" src="../assets/modules/evocomments/js/module.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/modules/evocomments/css/module.css" />
</head>
<body id="commentsModule">

  <h1>
      <i class="fa fa-file-text"></i>[+module_title+]
  </h1>

  <div id="actions">
      <div class="btn-group">
          <a id="Button1" class="btn btn-success" href="javascript:;" onclick="window.location.href='index.php?a=106';">
              <i class="fa fa-times-circle"></i><span>[+module_close+]</span>
          </a>
      </div>
  </div>

  <div class="tab-pane" id="docManagerPane">
      <script type="text/javascript">
        tpResources = new WebFXTabPane(document.getElementById('docManagerPane'));
      </script>

      <div class="tab-page" id="commentsList">
          <h2 class="tab"><i class="fa fa-list"></i> Комментарии</h2>
          <div class="tab-body">
            <div style="text-align:right;margin-bottom: 1rem;">
              <select name="statusSelect" class="form-control" style="width: auto;">
                <option value="0">Опубликованные</option>
                <option value="9">Удаленные</option>
              </select>
            </div>
            <table class="grid" cellpadding="1" cellspacing="1">
              <thead>
                <tr>
                  <td class="gridHeader" width="30%">Статья</td>
                  <td class="gridHeader" width="40%">Комментарий</td>
                  <td class="gridHeader" width="10%">Автор</td>
                  <td class="gridHeader" width="10%">Дата</td>
                  <td class="gridHeader" width="10%"></td>
                </tr>
              </thead>
              <tbody>
                [+commentsList+]
              </tbody>
            </table>
          </div>
      </div>
      <div class="tab-page" id="settings">
          <h2 class="tab"><i class="fa fa-cogs"></i> Настройки</h2>
          <div class="tab-body">
            <form id="settingsForm">
             <table style="width: 100%; margin-top: 1rem;">
                <tbody>
                 <tr>
                   <th style="width: 10rem">Загружать по:</th>
                   <td><input type="number" name="display" value="[+display+]"></td>
                 </tr>
                 </tbody>
             </table>
             <hr/>
             <h5><strong>Шаблоны (путь к файлу или название чанка)</strong></h5>
             
               <table style="width: 100%; margin-top: 1rem;">
                  <tbody>
                   <tr>
                     <th style="width: 10rem">Шаблон обёртки:</th>
                     <td><input type="text" name="outerTpl" value="[+outerTpl+]"></td>
                   </tr>
                    <tr>
                     <th style="width: 10rem">Шаблон комментария:</th>
                     <td><input type="text" name="commentTpl" value="[+commentTpl+]"></td>
                   </tr>
                    <tr>
                     <th style="width: 10rem">Шаблон ответного комментария:</th>
                     <td><input type="text" name="commentChildTpl" value="[+commentChildTpl+]"></td>
                   </tr>
                    <tr>
                     <th style="width: 10rem">Шаблон формы:</th>
                     <td><input type="text" name="formTpl" value="[+formTpl+]"></td>
                   </tr>
                   <tr>
                     <th style="width: 10rem">Шаблон блока авторизации:</th>
                     <td><input type="text" name="authBlockTpl" value="[+authBlockTpl+]"></td>
                   </tr>
                   <tr>
                     <th style="width: 10rem">Шаблон профиля:</th>
                     <td><input type="text" name="profileDropdownTpl" value="[+profileDropdownTpl+]"></td>
                   </tr>
                 </tbody>
               </table>
               <hr/>
               <button class="btn btn-success" data-evocomments-action="saveSettings">Сохранить настройки</button>
            </form>
          </div>
      </div>
  </div>
</body>
</html>