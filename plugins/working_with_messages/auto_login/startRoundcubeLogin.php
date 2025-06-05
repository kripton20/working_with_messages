<?php
/**
* Использование: вам нужен только RoundcubeAutoLogin.php,
* cookiejar.txt создается и удаляется на лету.
* Использование из php-скрипта: включите класс и следующий код в свой php-скрипт
* и сделайте вызов функций.
*/
/**
* Выражение require_once аналогично require за исключением того, что PHP проверит,
* включался-ли уже данный файл, и если да, не будет включать его ещё раз.
*
* Вместо указания абсолютного пути используем предопределенную константу __DIR__,
* сообщающую текущий каталог скрипта.
*/
// Загружаем файл класса "RoundcubeLogin" - инициируем конструкторы класса.
require_once(__DIR__ . '/RoundcubeLogin.class.php');

// Получаем глобальные переменные:
//   глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
//   обрезаем строку: вместо массива - используем список состоящий из двух элементов
//   (первый элемент - не обязательный).
//$server_folder = dirname($_SERVER['SCRIPT_NAME'], 4);

// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rcl", и передаём следующие параметры:
// 1 параметр: $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// 2 параметр: $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило,
//   совпадает с доменным именем сайта, расположенного на сервере.
// 3 параметр: $server_folder - содержит путь к папке где распологается Roundcube.
// 4 параметр: если - TRUE - включаем отладку.
// 5 параметр: если - TRUE - включаем запись отладки в лог-файл.
// 6 параметр: если - TRUE - включаем перенаправление в Roundcube для браузера.
$rcl           = new RoundcubeLogin(
  //$_SERVER['REQUEST_SCHEME'] . '://' .
  //$_SERVER['HTTP_HOST'] .
  //$server_folder . '/',
  TRUE, TRUE, TRUE);
  
// Выполняем все операции в обработчике ошибок.
try{
  // Если глобальный массив GET[] содержит "logout" - вызываем функцию "logout()" - выход.
  if(isset($_GET['logout'])){
    // Проверяем что вернёт функция "isLoggedIn()":
    //   если TRUE - вход в Roundcube выполнен,
    //   если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
    // Если вход уже выполнен - делаем перенаправление в приложение Roundcube.
    if($rcl->isLoggedIn()){
      // Вызываем функцию "logout()" - для выхода из системы Roundcube.
      $rcl->logout();
      // В условии проверяем если включен режим перенаправления в Roundcube для браузера.
      // Тогда вызываем функцию sent_redirect().
      if($rcl->sentRedirectEnabled == TRUE) sent_redirect($rcl, NULL);
    }
    // Иначе если вход не выполнен:
    else{
      // перезагрузим страницу.
      $rcl->redirect();
    }
    // В условии проверяем если включен режим отладки: вызываем функцию записи в лог-файл.
    if($rcl->enableDebug == TRUE && $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);
  }
  // Условие проверки передаваемых параметров в глобальном массиве "_GET":
  // от этого зависит какую функцию вызываем: авторизация - "login()" или выход - "logout()".
  // Если глобальный массив GET[] содержит "email" и "password" - вызываем функцию "login()" - авторизация.
  elseif(isset($_GET['email']) && isset($_GET['password'])){
    // Проверяем что вернёт функция "isLoggedIn()":
    // если TRUE - вход в Roundcube выполнен, если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
    // Если вход уже выполнен - делаем перенаправление в приложение Roundcube.
    if($rcl->isLoggedIn()){
      // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
      // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
      if(isset($_GET['folder'])){
        // тогда вызываем функцию "create_path_folders()".
        $path_folders = create_path_folders();
        // В условии проверяем если включен режим перенаправления в Roundcube для браузера.
        // Тогда вызываем функцию sent_redirect().
        if($rcl->sentRedirectEnabled == TRUE) sent_redirect($rcl, $path_folders);
      }
      // В условии проверяем если включен режим отладки (enableDebug = TRUE) и указание
      // записи в лог-файл (writeLogEnabled = TRUE): вызываем функцию записи в лог-файл.
      if($rcl->enableDebug == TRUE && $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);
    }
    // Иначе выполняем авторизацию.
    else{
      // Получим параметры из массива "GET" и создадим переменные:
      //   переменным присвоим значения логина и пароля.
      $email    = $_GET['email'];
      $password = $_GET['password'];
      // Выполняем вход в почтовую систему Roundcube и перенаправляем в случае успеха:
      // Вызываем функцию "login()" из класса "RoundcubeLogin".
      // $logout = FALSE означает не делать выход из системы.
      $rcl->login($email, $password, $logout   = FALSE);
      // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
      if(isset($_GET['folder'])){
        // тогда вызываем функцию "create_path_folders()".
        $path_folders = create_path_folders();
        // В условии проверяем если включен режим перенаправления в Roundcube для браузера.
        // Тогда вызываем функцию sent_redirect().
        if($rcl->sentRedirectEnabled == TRUE) sent_redirect($rcl, $path_folders);
      }
      // В условии проверяем если включен режим отладки (enableDebug = TRUE) и указание
      // записи в лог-файл (writeLogEnabled = TRUE): вызываем функцию записи в лог-файл.
      if($rcl->enableDebug == TRUE && $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);
    }
    // Функция отправляет команду WEB-серверу которую должен выполнить Roundcube.
    msg_request($rcl);
  }
  // Иначе если вход не удался.
  else{
    // Выводим сообщение об ошибке.
    die("<b>ERROR: </b><br /><br />Вы не указали параметры!");
  }
}
catch(RoundcubeLoginException $ex){
  // Вызываем функцию "dumpDebugStack()".
  $rcl->dumpDebugStack();
  // Если вход не удался, выводим сообщение об ошибке.
  die("<b>ERROR: </b><br /><br />" . $ex->getMessage());
}

// Здесь выполняем полезную работу


// Завершение работы программы.
$rcl->logout();
exit;

// Функция отправляет команду WEB-серверу которую должен выполнить Roundcube.
function msg_request($rcl)
{
  // Обновим страницу.
  //$rcl->redirect();
  // Формируем строку данных для POST-запроса:
  // Команда передаваемая приложению Roundcube для выполнения:
  // task (задача) - "mail", action (действие) "plugin.msg_request".
  //$data = '_task=mail&_action=plugin.msg_request';
  $data        = '_remote=1&_unlock=0';

  // В цикле выполняем // Периодически методом POST вызываем на выполнение функцию "msg_request". В ответе ищем '"rm_dublicate_messages_uids":null' на странице.
  //    for ($i = 0; $i <= 10; $i++) {
  // Вызываем функцию "sendRequest()" - отправляем запрос серверу.
  // Функция "msg_request" на GET-запрос срабатывает.
  //$rcl->sendRequest($rcl->rcPath . "?_task = mail & _action = plugin.msg_request");
  //$fp=$rcl->sendRequest($rcl->rcPath . "?_task=mail&_action=plugin.msg_request", $data);
  $fp          =$rcl->sendRequest($rcl->rcPath . "?_task=mail&_action=plugin.msg_request");
  //$fp=$rcl->sendRequest($rcl->rcPath . '?_task=mail', $data);
  //    }

  // Инициализируем переменную "response" (отклик - ответ от сервера).
  $rm_response = '';

  // Функция feof — проверяет, достигнут ли конец файла (выполняется долго).
  // Читаем ответ от сервера - присланная страница ответа в браузер.
  // Читаем содержимое переменной "fp" и формируем страницу ответа от WEB-сервера (страница входа).
  // Прочитаем ответ от сервера и установим полученные куки.
  while(!feof($fp)){
    // Переменной "line" присвоим значение функции "fgets()":
    // Прочитаем очередную строку длиной 700 символов из файла (переменная "fp").
    $line = fgets($fp, 700);

    // Иначе если сервер прислал страницу с формой входа с полем для ввода пароля - значит мы не вошли в систему.
    if(preg_match('/<div.+id = "messagetoolbar" / mi', $line)){
      // Запишем отладочное сообщение в массив "debugStack[]": вызываем функцию "addDebug()" и передаём ей сообщение.
      //$this->addDebug("LOGGED IN", "Обнаружено, что мы ВОШЛИ в систему." . "\r\n");
      // Присвоим статус - вошли в систему.
      //$this->rcLoginStatus = 1;
      // Прекратим поиск.
      //break;
      $b = 1;
    }

    // В условии проверяем: если переменная "line" содержит строку с тегом "</html > " - значит страница закончилась:
    if(preg_match('/<\/html>/i', $line)){
      // Добавим очередную строку в переменную "response".
      $rm_response .= $line;

      // Запишем отладочное сообщение в массив "debugStack[]": вызываем функцию "addDebug()" и передаём ей страницу полученную от сервера
      // в переменной "$rm_response".
      //$rcl->addDebug("RM_RESPONSE", $rm_response . "\r\n");

      // Прекратим поиск.
      break;
    }
    //            // Иначе если сервер прислал страницу сстраницу с ошибкой 404.
    //            elseif (preg_match('/^HTTP\/1\.\d\s+404\s+/', $line)) {
    //                // Генерируем отладочное сообщение.
    //                throw new RoundcubeLoginException("Установка Roundcube не найдена на '$path'." . "\r\n");
    //            }
    // Добавим очередную строку в переменную "response".
    $rm_response .= $line;
  }
  $a =1;
  echo($rm_response);
    $a =2;
  // Обновим страницу.
  //$rcl->redirect();
  // А на POST-запрос не срабатывает, нужно узнать какую строку отправлять в запросе.
  //$rcl->sendRequest($rcl->rcPath . "?_task = mail & _action = plugin.msg_request", $data);
  //$fp = $rcl->sendRequest($rcl->rcPath . "?_task = mail & _action = refresh", $data);

  //    // ожидание в течениe 10 секунд
  //    //sleep(10);
  //
  //        // Получаем страницу со списком писем.
  //    $fp = $rcl->sendRequest($rcl->rcPath . "?_task = mail & _mbox = INBOX");
  //
  //
  //    $fp2 = $rcl->sendRequest($rcl->rcPath . "?_task = mail & _mbox = TEST_DUBLIKAT");
  //
  //    // Инициализируем переменную "response" (отклик - ответ от сервера).
  //    $response2 = "";
  //
  //    // Функция feof — проверяет, достигнут ли конец файла (выполняется долго).
  //    // Читаем ответ от сервера - присланная страница ответа в браузер.
  //    // Читаем содержимое переменной "fp" и формируем страницу ответа от WEB-сервера (страница входа).
  //    // Прочитаем ответ от сервера и установим полученные куки.
  //    while (!feof($fp2)) {
  //        // Переменной "line" присвоим значение функции "fgets()":
  //        // Прочитаем очередную строку длиной 700 символов из файла (переменная "fp").
  //        $line = fgets($fp2, 700);
  //
  //
  //        // Иначе если сервер прислал страницу с формой входа с полем для ввода пароля - значит мы не вошли в систему.
  //        if (preg_match('/<div.+id = "messagetoolbar" / mi', $line)) {
  //            // Запишем отладочное сообщение в массив "debugStack[]": вызываем функцию "addDebug()" и передаём ей сообщение.
  //            //$this->addDebug("LOGGED IN", "Обнаружено, что мы ВОШЛИ в систему." . "\r\n");
  //
  //            // Присвоим статус - вошли в систему.
  //            //$this->rcLoginStatus = 1;
  //
  //            // Прекратим поиск.
  //            //break;
  //            $b = 1;
  //        }
  //
  //        // В условии проверяем: если переменная "line" содержит строку с тегом "</html > " - значит страница закончилась:
  //        if (preg_match('/<\ / html>/mi', $line)) {
  //            // Добавим очередную строку в переменную "response".
  //            $response2 .= $line;
  //
  //            // Запишем отладочное сообщение в массив "debugStack[]": вызываем функцию "addDebug()" и передаём ей страницу полученную от сервера
  //            // в переменной "$response2".
  //            //$this->addDebug("RESPONSE", $response2 . "\r\n");
  //
  //            // Прекратим поиск.
  //            break;
  //        }
  //        // Иначе если сервер прислал страницу сстраницу с ошибкой 404.
  //        elseif (preg_match('/^HTTP\ / 1\.\d\s + 404\s+/', $line)) {
  //            // Генерируем отладочное сообщение.
  //            throw new RoundcubeLoginException("Установка Roundcube не найдена на '$path'." . "\r\n");
  //        }
  //                // Добавим очередную строку в переменную "response".
  //        $response2 .= $line;
  //    }
  //    // Запишем отладочное сообщение в массив "debugStack[]":
  //    // вызываем функцию "addDebug()" и передаём ей страницу полученную от сервера в переменной "$response2".
  //    //$this->addDebug("RESPONSE", $response2 . "\r\n");

  // Вызываем функцию "isLoggedIn()" и вернём её отрицательное значение.
  //$a = !$rcl->isLoggedIn();
  //$a = $rcl->isLoggedIn();
  //return !$rcl->isLoggedIn();

}

// Функция формирует папки почтового ящика для перенаправления.
function create_path_folders()
{
  // Формируем путь: папка и подпапка для перехода в конкретную папку.
  // Сформируем путь и присвоим значение массива $_GET['sub_folder'] в переменную "$path_folders".
  //$path_folders = "?_task=mail&_mbox=INBOX%2FArchive%2F" . $_GET['folder'];
  $path_folders = "?_task=mail&_mbox=" . $_GET['folder'];
  // В условии проверяем если глобальный массив $_GET[] содержит "$path_folders":
  //    if (isset($_GET['sub_folder'])) {
  //        // добавим значение этой переменной к переменной "$folders".
  //        $path_folders .= "%2F" . $_GET['sub_folder'];
  //    }
  // Возвращаем сформированный путь из папок.
  return $path_folders;
}

function sent_redirect($rcl, $path_folders)
{
  // Если вход выполнен - делаем перенаправление в приложение Roundcube:
  // - формируем строку запроса (URL) и выполняем обновление страницы.
  // Условие проверки существования переменной "$path_folders":
  if(isset($path_folders)){
    // Если переменная "$path_folders" существует - включаем её в состав URL,
    // и функцию "redirect()" выполним с параметром.
    $rcl->redirect($path_folders);
  }
  // Иначе если вход не выполнен.
  else{
    // Функцию "redirect()" выполним без параметра.
    $rcl->redirect();
  }
}

function set_params_logfile($rcl)
{
  // Запишем в масив "$args" данные для записи в лог-файл.
  $args = array(
    // Извлекаем значение $_COOKIE[''], а если оно не задано, то возвращаем пустую строку.
    "COOKIE['roundcube_sessid']"=> isset($_COOKIE['roundcube_sessid']) ? $_COOKIE['roundcube_sessid'] : '',
    "COOKIE['roundcube_sessauth']"=> isset($_COOKIE['roundcube_sessauth']) ? $_COOKIE['roundcube_sessauth'] : '',
    "lastToken"                   => $rcl->lastToken,
    "rcLoginStatus"               => $rcl->rcLoginStatus,
    "rcSessionID"                 => $rcl->rcSessionID,
    "debugStack"                  => $rcl->debugStack
  );
  // Запишем сообщение в лог-файл: вызываем функцию "write_log_file()" из класса "RoundcubeLogin" с аргументом "$args".
  RoundcubeLogin::write_log_file($args);
}
?>
