<?php
// Логин и пароль для  доступа на почтовый аккаунт.
$user     = 'ocik@niiemp.local';
$password = 'ocik1905niiemp';

// Массив содержит WEB - адреса хостов которые будем обрабатывать.
$urls     = Array(
    //'http://cadmail:10002/rc147 - 1/',
    //'http://cadmail:10002/rc147 - 2/',
    //    'http://cadmail:10002/rc147 - 3/',
    //    'http://cadmail:10002/rc147 - 4/',
    'http://cadmail:10002/rc147-5/',
    'http://cadmail:10002/rc147-6/',
    'http://cadmail:10002/rc147-7/',
    'http://cadmail:10002/rc147-8/',
    //    'http://cadmail:10002/rc147-9/',
    //    'http://cadmail:10002/rc147-10/',
    //    'http://cadmail:10002/rc147-11/',
    //    'http://cadmail:10002/rc147-12/',
    //    'http://cadmail:10002/rc147-13/',
    //    'http://cadmail:10002/rc147-14/',
    //    'http://cadmail:10002/rc147-15/',
    //    'http://cadmail:10002/rc147-16/',
    //    'http://cadmail:10002/rc147-17/',
    //    'http://cadmail:10002/rc147-18/',
    //    'http://cadmail:10002/rc147-19/',
    //    'http://cadmail:10002/rc147-20/',
    //    'http://cadmail:10002/rc147-21/',
    //    'http://cadmail:10002/rc147-22/',
    //    'http://cadmail:10002/rc147-23/',
    //    'http://cadmail:10002/rc147-24/',
    //    'http://cadmail:10002/rc147-25/',
    //    'http://cadmail:10002/rc147-26/',
    //    'http://cadmail:10002/rc147-27/',
    //    'http://cadmail:10002/rc147-28/',
    //    'http://cadmail:10002/rc147-29/',
    //    'http://cadmail:10002/rc147-30/',
    //    'http://cadmail:10002/rc147-31/',
    //    'http://cadmail:10002/rc147-32/',
    //    'http://cadmail:10002/rc147-33/',
    //    'http://cadmail:10002/rc147-34/',
    //    'http://cadmail:10002/rc147-35/',
    //    'http://cadmail:10002/rc147-36/',
    //    'http://cadmail:10002/rc147-37/',
    //    'http://cadmail:10002/rc147-38/',
    //    'http://cadmail:10002/rc147-39/',
    //    'http://cadmail:10002/rc147-40/',
    //    'http://cadmail:10002/rc147-41/',
    //    'http://cadmail:10002/rc147-42/',
    //    'http://cadmail:10002/rc147-43/',
    //    'http://cadmail:10002/rc147-44/',
    //    'http://cadmail:10002/rc147-45/',
    //    'http://cadmail:10002/rc147-46/',
    //    'http://cadmail:10002/rc147-47/',
    //    'http://cadmail:10002/rc147-48/',
    //    'http://cadmail:10002/rc147-49/',
    //    'http://cadmail:10002/rc147-50/'
);

// Загружаем файл класса "RoundcubeMultiCURL" - инициируем конструкторы класса.
require_once(__DIR__ . '/RoundcubeMultiCURL.Class.php');

// Создаём объект:
// Создаём экземпляр класса "RoundcubeMultiCURL" через переменную "$rcMcURL", и передаём следующие параметры:
// Массив содержит WEB - адреса хостов которые будем обрабатывать.
// 4 параметр: если - TRUE - включаем отладку.
// 5 параметр: если - TRUE - включаем запись отладки в лог - файл.
$rcMcURL = new RoundcubeMultiCURL($urls, $user, $password, TRUE, TRUE);

// Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
$msg_start = date("Y.m.d") . " ";
// Время (17:16:18).
$msg_start .= date("H:i:s") . "\t";
// Сообщение начала обработки.
$msg_start .= "RC multi cURL starting\n";
// Выводим сообщение.
echo "$msg_start";
// В условии проверяем если включен режим отладки (debugEnabled = TRUE) и указание
// записи в лог - файл (writeLogEnabled = TRUE): вызываем функцию записи в лог - файл.
if ($rcMcURL->writeLogEnabled == TRUE) $rcMcURL->write_log_file($msg_start);

// Выполняем установку параметров cURL для авторизации в Roundcube.
$rcMcURL->curl_multi_set_auth();

// Выполнение парарельных запросов в cURL.
$rcMcURL->curl_multi_exec();

// Получаем контент авторизации.
$getcontent = 'auth';
$rcMcURL->curl_multi_getcontent($getcontent);

// Сбрасываем все установленные опции.
$rcMcURL->curl_reset($rcMcURL->urls);

// В цикле выполняем запросы пока объект "$rcMcURL" содержит массив "$curls".
do {
    // Выполняем установку параметров cURL для POST - запроса на выполнение команды WEB - серверу в Roundcube.
    $rcMcURL->curl_multi_set_rm_post();

    // Выполнение парарельных запросов в cURL.
    // POST - запрос на выполнение команды WEB - серверу:
    $rcMcURL->curl_multi_exec();

    // Получаем контент POST - запроса.
    $getcontent = 'post';
    $rcMcURL->curl_multi_getcontent($getcontent);

    // Разбираем ответ сервера и в регулярном выражении ищем строку "X - RM - Processing: yes"
    foreach ($rcMcURL->content_h['post'] as $url=>$response) {
        /**
        * preg_match — Выполняет проверку на соответствие регулярному выражению.
        * Ищет в заданном тексте subject совпадения с шаблоном pattern.
        **/
        // Получаем строку "X - RM - Processing: yes" из результатов выдачи сервера.
        preg_match('/^X-RM-Processing:\s(yes)[^\\n]/im', $response, $resultX);

        /**
        * preg_match_all — Выполняет глобальный поиск шаблона в строке.
        * Описание:
        * preg_match_all(string $pattern, string $subject, array &$matches=null, int $flags=PREG_PATTERN_ORDER, int $offset=0):int|false|null
        *
        * Ищет в строке subject все совпадения с шаблоном pattern и помещает результат в массив matches в порядке,
        * определяемом комбинацией флагов flags.
        * После нахождения первого соответствия последующие поиски будут осуществляться не с начала строки,
        * а от конца последнего найденного вхождения.
        * Список параметров:
        * @var pattern Искомый шаблон в виде строки.
        * @var subject Входная строка.
        * @var matches Массив совпавших значений, отсортированный в соответствии с параметром flags.
        * @var flags   Может быть комбинацией флагов.
        * @var offset  Обычно поиск осуществляется слева направо, с начала строки. Дополнительный параметр offset
        *              может быть использован для указания альтернативной начальной позиции для поиска.
        * Замечание:
        * Использование параметра offset не эквивалентно замене сопоставляемой строки выражением
        * substr($subject, $offset) при вызове функции preg_match_all(), поскольку шаблон pattern может содержать
        * такие условия как ^, $ или (?<=x). Вы можете найти соответствующие примеры в описании функции preg_match().
        * Возвращаемые значения:
        * Возвращает количество найденных вхождений шаблона (которое может быть и нулём) либо false, если во время
        * выполнения возникли какие-либо ошибки.
        */
        // Получаем строку "Set - Cookie" из результатов выдачи сервера.
        preg_match_all('/^Set-Cookie:.*/im', $response, $resultC);

        /**
        * Условный оператор ?, возвращает y, в случае если x принимает значение true,
        * и z в случае, если x принимает значение false. Пример: x ? y : z.
        */
        // Если массив "$resultX" сформирован - то переменной "$processing" присвоим TRUE иначе FALSE.
        $processing = $resultX ? TRUE : FALSE;
        // Если массив "$resultC" с элементом "['0']" сформирован - то переменной "$set_cookie" присвоим TRUE иначе FALSE.
        $set_cookie = $resultC['0'] ? TRUE : FALSE;

        // Если переменные "$processing" и "$set_cookie" равны FALSE:
        if ($processing == FALSE & $set_cookie == FALSE) {
            // В массив "$del_urls" добавляем адрес домена который нужно исключить из обработки.
            $del_urls[$url] = $url;
        }

        // Если переменная "$set_cookie" равна TRUE:
        if ($set_cookie == TRUE) {
            // В терминал выводим сообщение о выполнении процедуры перерегистрации в Roundcube:
            // Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
            $new_reg = date("Y.m.d") . " ";
            // Время (17:16:18).
            $new_reg .= date("H:i:s") . "\t";
            // Сообщение.
            $new_reg .= "RC multi cURL new registration on: $url\n";
            // Выводим сообщение.
            echo "$new_reg";
            // Запишем это сообщение в лог - файл.
            $rcMcURL->write_log_file($new_reg);
            // Выполняем установку параметров cURL для авторизации в Roundcube.
            $rcMcURL->curl_multi_set_auth();
            // Выполнение парарельных запросов в cURL.
            $rcMcURL->curl_multi_exec();
            // Получаем контент авторизации.
            $getcontent = 'auth';
            $rcMcURL->curl_multi_getcontent($getcontent);
            // Сбрасываем все установленные опции.
            $rcMcURL->curl_reset($rcMcURL->urls);
        }
        // Удалим переменную вспомогательные массивы.
        unset($resultC, $resultX, $processing, $set_cookie, $new_reg);
    }

    // Если массив "$del_urls" сформирован - выполняем функциию выхода из Roundcube и очистку массивов.
    if (isset($del_urls)) {
        // Сбрасываем все установленные опции: передаём в функцию массив "$del_urls" по которому будем проходить.
        $rcMcURL->curl_reset($del_urls);

        // GET - запрос на выход:
        $rcMcURL->curl_multi_set_get_aut($del_urls);

        // Выполнение парарельных запросов в cURL.
        $rcMcURL->curl_multi_exec();

        // Получим значение массивов по ссылке через & из объекта "$rcMcURL".
        $rc_curls     = & $rcMcURL->curls;
        $rc_tokens    = & $rcMcURL->tokens;
        $rc_content_h = & $rcMcURL->content_h;
        $rc_urls      = & $rcMcURL->urls;

        // Инициализируем переменную - счётчик для уменьшения колличества url в обработке.
        // Колличество URLs в обработке на данный момент.
        $runing_urls  = (count($rcMcURL->urls));

        // В цикле удаляем не нужные нам адреса
        foreach ($del_urls as $url) {
            // Уменьшим нашу переменную - счётчик на единицу.
            $runing_urls--;

            // Сообщение об исключении url из обработки:
            // Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
            $url_exception = date("Y.m.d") . " ";

            // Время (17:16:18).
            $url_exception .= date("H:i:s") . "\t";

            // Сообщение об окончании обработки.
            // Добавим новое сообщение к нашему сообщению.
            $url_exception .= "RC multi cURL exception URL: $url. Runing URLs: $runing_urls\n";

            // Выводим сообщение.
            echo "$url_exception";

            // В условии проверяем если включен режим отладки (debugEnabled = TRUE) и указание
            // записи в лог - файл (writeLogEnabled = TRUE): вызываем функцию записи в лог - файл.
            if ($rcMcURL->writeLogEnabled == TRUE) $rcMcURL->write_log_file($url_exception);

            // Закрываем все дескрипторы.
            curl_multi_remove_handle($rcMcURL->multiCurl, $rc_curls[$url]);

            // Удаляем элемент массива по ключу.
            unset($rc_curls[$url], $rc_tokens[$url], $rc_content_h['auth'][$url], $rc_content_h['post'][$url]);

            /**
            * array_search — Осуществляет поиск данного значения в массиве и возвращает ключ
            * первого найденного элемента в случае успешного выполнения.
            *
            * array_search(mixed $needle, array $haystack, bool $strict=false):int|string|false
            *
            * Ищет в haystack значение needle.
            * Список параметров:
            * @var needle Искомое значение.
            *             Замечание:
            *             Если needle является строкой, сравнение происходит с учётом регистра.
            * @var haystack Массив.
            * @var strict Если третий параметр strict установлен в true, то функция array_search()
            *             будет искать идентичные элементы в haystack. Это означает, что также будут
            *             проверяться типы needle в haystack, а объекты должны быть одним и тем же экземпляром.
            * Возвращаемые значения:
            * Возвращает ключ для needle, если он был найден в массиве, иначе false.
            * Если needle присутствует в haystack более одного раза, будет возвращён первый найденный ключ.
            * Для того, чтобы возвратить ключи для всех найденных значений, используйте функцию array_keys() с
            * необязательным параметром search_value.
            */
            unset($rc_urls[array_search($url, $rc_urls)]);
        }
        // Удалим массив "$del_urls".
        unset($del_urls);
    }
    // Пока в объекте "$rcMcURL" существует массив "$curls" выполняем тело цикла.
} while ($rcMcURL->curls);

// Закрываем набор cURL - дескрипторов.
$rcMcURL->curl_multi_close();

// Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
$msg_stop = date("Y.m.d") . " ";
// Время (17:16:18).
$msg_stop .= date("H:i:s") . "\t";
// Сообщение об окончании обработки.
$msg_stop .= "RC multi cURL stopping\n";
// Выводим сообщение.
echo "$msg_stop";
// Сообщение об окончании работы программы.
// В условии проверяем если включен режим отладки (debugEnabled = TRUE) и указание
// записи в лог - файл (writeLogEnabled = TRUE): вызываем функцию записи в лог - файл.
if ($rcMcURL->writeLogEnabled == TRUE) $rcMcURL->write_log_file($msg_stop);
?>
