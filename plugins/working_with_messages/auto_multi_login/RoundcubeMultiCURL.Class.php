<?php
class RoundcubeMultiCURL
{
    /**
    * Получить отладочную информацию: TRUE в четвёртом аргументе в конструкторе при создании объекта.
    * @var bool
    */
    public $debugEnabled;

    /**
    * Включить запись отладки в лог-файл: TRUE в пятом аргументе в конструкторе при создании объекта.
    * @var bool
    */
    public $writeLogEnabled;

    /**
    * Запись отладочных сообщений в стёк.
    * @var array
    */
    public $debugStack;

    /**
    * Набор дескрипторов cURL.
    * @var string
    * @return resourceID
    */
    public $multiCurl;

    /**
    * Массив WEB-адресов хостов которые будем обрабатывать.
    * @param string $urls
    */
    public $urls;

    /**
    * Логин.
    * @param string $user
    */
    public $user;

    /**
    * Пароль.
    * @param string $password
    */
    public $password;

    /**
    * Создаём новую сессию: для каждого url.
    * @param array $curls
    */
    public $curls;

    /**
    * Получаем ответ от сервера.
    *
    * @param array $content_auth
    */
    //public $content_auth;

    /**
    * Получаем данные по авторизации отправляемые серверу.
    *
    * @param array $content_auth_info
    */
    //public $content_auth_info;

    /**
    * Получаем контент POST-запроса.
    *
    * @param array $content_post
    */
    //public $content_post;

    /**
    * Получаем данные POST-запроса отправляемые серверу.
    *
    * @param array $content_post_info
    */
    //public $content_post_info;

    /**
    * Получаем контент данные GET-запроса на выход получаемые от сервера.
    *
    * @param array $content_h
    */
    public $content_h;

    /**
    * Получаем контент данные GET-запроса на выход отправляемые серверу.
    *
    * @param array $content_get_aut_info
    */
    public $curl_info;

    /**
    * Получаем куки с сервера.
    *
    * @param array $cookies
    */
    public $cookies;

    /**
    * Массив полученных токенов от WEB-сервера.
    * @param array $tokens
    */
    public $tokens;

    /**
    * Создаём новый класс RoundcubeMultiCURL.
    */
    public function __construct($urls, $user, $password, $debugEnabled = FALSE, $writeLogEnabled = FALSE)
    {
        // Массив содержит WEB - адреса хостов которые будем обрабатывать.
        $this->urls = $urls;
        // Логин.
        $this->user = $user;
        // Пароль.
        $this->password = $password;
        // Создаём новую сессию: для каждого url.
        $this->curls = array();
        // Получаем страницу контента от сервера по нашему запросу.
        $this->content_h = array();
        // Получаем данные отправляемые серверу.
        $this->curl_info = array();
        // Получаем куки с сервера
        $this->cookies = array();
        // Запишем в массив "$tokens" полученные токены.
        $this->tokens = array();
        /**
        * curl_multi_init — Создаёт набор cURL-дескрипторов.
        * curl_multi_init():CurlMultiHandle
        * Позволяет асинхронную обработку множества cURL-дескрипторов.
        * У этой функции нет параметров.
        * Возвращает набор cURL-дескрипторов в случае успешного выполнения или false в случае возникновения ошибки.
        */
        // Создаём набор дескрипторов cURL.
        $this->multiCurl = curl_multi_init();
        // Инициализируем переменные класса:
        // Создаём массив для записи отладочных сообщений в стёк.
        $this->debugStack = array();
        // Вывод отладочной информации в браузер.
        $this->debugEnabled = $debugEnabled;
        // Запись отладочной информации в  в лог - файл.
        $this->writeLogEnabled = $writeLogEnabled;
    }

    // Выполняем авторизацию и установку параметров cURL.
    public function curl_multi_set_auth()
    {
        // В цикле перебираем элементы массива "$urls" и присвоим параметры сесии cURL.
        foreach ($this->urls as $key=>$url) {
            // Создаём новую сессию: для каждого url создаем отдельный curl - механизм для отправки запроса.
            $this->curls[$url] = curl_init();
            // Установка URL и других соответствующих параметров:
            // CURLOPT_AUTOREFERER для автоматической установки поля Referer: в запросах, перенаправленных заголовком Location:.
            curl_setopt($this->curls[$url], CURLOPT_AUTOREFERER, TRUE);
            // CURLOPT_POST    для использования обычного HTTP POST. Данный метод POST использует обычный application / x - www - form - urlencoded, обычно используемый в HTML - формах.
            curl_setopt($this->curls[$url], CURLOPT_POST, TRUE);
            // CURLOPT_POSTFIELDS    Все данные, передаваемые в HTTP POST - запросе.
            // Этот параметр может быть передан как в качестве url - закодированной строки, наподобие 'para1 = val1 & para2 = val2&...', так и в виде массива,
            // ключами которого будут имена полей, а значениями - их содержимое. Если value является массивом, заголовок Content - Type будет установлен в значение
            // multipart / form - data. Файлы можно отправлять с использованием CURLFile или CURLStringFile, в этом случае value должен быть массивом.
            curl_setopt($this->curls[$url], CURLOPT_POSTFIELDS, '_task=login&_action=login&_timezone=Europe/Moscow&_url=&_user=' . $this->user . '&_pass=' . $this->password);
            // CURLOPT_URL    Загружаемый URL. Данный параметр может быть также установлен при инициализации сеанса с помощью curl_init().
            curl_setopt($this->curls[$url], CURLOPT_URL, $url . '?_task=login');
            // CURLOPT_VERBOSE    Для вывода дополнительной информации. Записывает вывод в поток STDERR, или файл, указанный параметром CURLOPT_STDERR.
            curl_setopt($this->curls[$url], CURLOPT_VERBOSE, TRUE);
            // CURLINFO_HEADER_OUT    true для отслеживания строки запроса дескриптора.
            curl_setopt($this->curls[$url], CURLINFO_HEADER_OUT, TRUE);
            // CURLOPT_HEADER    true для включения заголовков в вывод.
            curl_setopt($this->curls[$url], CURLOPT_HEADER, TRUE);
            // CURLOPT_RETURNTRANSFER для возврата результата передачи в качестве строки из curl_exec() вместо прямого вывода в браузер.
            curl_setopt($this->curls[$url], CURLOPT_RETURNTRANSFER, TRUE);
            // CURLOPT_USERAGENT    Содержимое заголовка "User - Agent: ", посылаемого в HTTP - запросе.
            curl_setopt($this->curls[$url], CURLOPT_USERAGENT, 'curl/7.81.0');
            // CURLOPT_FRESH_CONNECT	Для принудительного использования нового соединения вместо закешированного.
            curl_setopt($this->curls[$url], CURLOPT_FRESH_CONNECT, TRUE);
            // curl_multi_add_handle — Добавляет обычный cURL - дескриптор к набору cURL - дескрипторов:
            // Добавляем текущий механизм к числу работающих параллельно.
            curl_multi_add_handle($this->multiCurl, $this->curls[$url]);
        }
    }

    // POST - запрос на выполнение команды WEB - серверу.
    public function curl_multi_set_rm_post()
    {
        // В цикле перебираем элементы массива "$urls" и присвоим параметры сесии cURL.
        foreach ($this->urls as $key=>$url) {
            /**
            * explode — Разбивает строку с помощью разделителя.
            * Описание:
            * explode(string $separator, string $string, int $limit=PHP_INT_MAX):array
            *
            * Возвращает массив строк, полученных разбиением строки string с использованием separator в качестве разделителя.
            * Список параметров:
            * @var separator Разделитель.
            * @var string    Входная строка.
            * @var limit     Если аргумент limit является положительным, возвращаемый массив будет содержать максимум limit
            *                элементов, при этом последний элемент будет содержать остаток строки string.
            * Если параметр limit отрицателен, то будут возвращены все компоненты, кроме последних -limit.
            * Если limit равен нулю, то он расценивается как 1.
            */
            // Разабьём строку на массив а потом подставим в заголовок то что нужно от URL - адреса.
            $host         = explode('/', $url);

            // Создаём заголовок для запроса "POST" с заданными параметрами:
            // Переменной "$post_headers" присвоим заголовки POST - запроса.
            $post_headers = array
            (
                // Принимаемые типы данных.
                'Accept: text/html',
                'Accept-Language: ru-RU, ru; q=0.8, en-US; q=0.5, en; q=0.3',
                // Заголовок Accept - Encoding HTTP запроса указывает кодировку контента, которую может понять клиент.
                'Accept-Encoding: *',
                'Accept-Charset: utf-8, windows-1251; q=0.7, *; q=0.7',
                // Тип передаваемых данных (URL - кодированные данные).
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: ' . $host['2'],
                'Origin: '. $host['0'] . '//' . $host['2'],
                /**
				* Директива Pragma: no-cache используется для протокола http/1.0. На данный момент можно
				* с полным правом считать ее устаревшей конструкцией. Однако, для корректного запрета
				* кэширования стоит все же выставлять и этот заголовок – ни-когда не знаешь наверняка,
				* какой еще пользовательский агент обратится к серве-ру и какой протокол он будет использовать.
				*/
                'Pragma: no-cache',
                'Referer: ' . $url . '?_task=mail',
                'Sec-Ch-Ua: "Chromium"; v="94", "Yandex"; v="21", "; Not A Brand"; v="99"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "Windows"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'User-Agent: "curl/7.81.0"',
                // Отключим браузерное кэширование.
                /**
				* Инструкции не чувствительны к регистру и имеют необязательный аргумент, который может
				* быть указан как в кавычках, так и без них. Несколько инструкций разделяются запятыми.
				* https://developer.mozilla.org/ru/docs/Web/HTTP/Headers/Cache-Control
				* no-cache, 
				*/
                'Cache-Control: post-check=0, pre-check=0, max-age=0',
                'Cache-Control: no-store, must-revalidate, private',
                /**
				* Заголовок Expires устанавливает время актуальности информации. Указывает на дату истечения
				* срока годности запрашиваемой страницы. Для ресурсов, которые не должны кэшироваться, его
				* нужно выставлять в текущий момент (документ устаревает сразу же после получения), для
				* форсирования кэширования его можно определять на достаточно далекую дату в будущем.
				*/
                //'Expires: Sun, 02 Jan 2000 00:00:00 GMT',
                //'Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT',
                'Upgrade-Insecure-Requests: 1',
                'X-Requested-With: XMLHttpRequest',
                'X-Roundcube-Request: ' . $this->tokens[$url],
                'X-Compress: null',
            );

            // Отправляем POST - запрос с указанием команды:
            // Установка URL и других соответствующих параметров.
            curl_setopt($this->curls[$url], CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_POST, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_VERBOSE, TRUE);
            // CURLOPT_NOBODY    Для исключения тела ответа из вывода.
            // Метод запроса устанавливается в HEAD. Смена этого параметра в false не меняет его обратно в GET.
            curl_setopt($this->curls[$url], CURLOPT_NOBODY, TRUE);
            curl_setopt($this->curls[$url], CURLINFO_HEADER_OUT, TRUE);
            // CURLOPT_HTTPHEADER    Массив устанавливаемых HTTP - заголовков,
            // в формате array('Content - type: text / plain', 'Content - length: 100')
            curl_setopt($this->curls[$url], CURLOPT_HTTPHEADER, $post_headers);
            curl_setopt($this->curls[$url], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_HEADER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_URL, $url . '?_task=mail&_action=plugin.msg_request1');
            curl_setopt($this->curls[$url], CURLOPT_POSTFIELDS, '_remote=1&_unlock=0');
            // CURLOPT_COOKIE    Содержимое заголовка "Cookie: ", используемого в HTTP - запросе.
            // Обратите внимание, что несколько cookies разделяются точкой с запятой с последующим пробелом (например, "fruit = apple; colour = red")
            curl_setopt($this->curls[$url], CURLOPT_COOKIE, $this->cookies[$url]);
            curl_setopt($this->curls[$url], CURLOPT_USERAGENT, 'curl/7.81.0');
            curl_setopt($this->curls[$url], CURLOPT_FRESH_CONNECT, TRUE);
            curl_multi_add_handle($this->multiCurl, $this->curls[$url]);
        }
    }

    // GET-запрос на выход. Функции передаём массив "$del_urls" - список url доменов в которых нужно сделать процедуру выхода.
    public function curl_multi_set_get_aut($del_urls)
    {
        // В цикле перебираем элементы массива "$del_url" получаем значение "$url".
        // Подставляем это значение в массив "$this->curls[]" и присвоим параметры сесии cURL.
        foreach ($del_urls as $url) {
            curl_setopt($this->curls[$url], CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_HTTPGET, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_URL, $url . '?_task=logout&_token=' . $this->tokens[$url]);
            curl_setopt($this->curls[$url], CURLOPT_VERBOSE, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_NOBODY, TRUE);
            curl_setopt($this->curls[$url], CURLINFO_HEADER_OUT, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_HEADER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->curls[$url], CURLOPT_COOKIE, $this->cookies[$url]);
            curl_setopt($this->curls[$url], CURLOPT_USERAGENT, 'curl/7.81.0');
            curl_multi_add_handle($this->multiCurl, $this->curls[$url]);
        }
    }

    // Получаем контент от WEB - сервера. Переменная "$getcontent" показывает из какого раздела массива "content_h" или "curl_info" мы хотим получить данные.
    public function curl_multi_getcontent($getcontent)
    {
        // В цикле перебираем элементы массива "$this->curls" получаем содержимое ответа сервера.
        foreach ($this->curls as $key=>$value) {
            /**
            * curl_multi_getcontent — Возвращает результат операции, если была установлена опция CURLOPT_RETURNTRANSFER.
            * Описание:
            * curl_multi_getcontent(CurlHandle $handle):string|null
            * Если для определённого дескриптора была установлена опция CURLOPT_RETURNTRANSFER,
            * то эта функция вернёт содержимое этого cURL-дескриптора в виде строки.
            * Список параметров:
            * @var handle Дескриптор cURL, полученный из curl_init().
            * Возвращаемые значения:
            * Возвращает содержимое cURL-дескриптора, если была использована опция CURLOPT_RETURNTRANSFER.
            */
            // Получаем страницу контента от сервера по нашему запросу.
            $this->content_h[$getcontent][$key] = curl_multi_getcontent($this->curls[$key]);

            /**
            * curl_getinfo — Возвращает информацию об определённой операции.
            *
            * curl_getinfo(CurlHandle $handle, ?int $option=null):mixed
            * Возвращает информацию о последней операции.
            * @var handle Дескриптор cURL, полученный из curl_init().
            * @var option Одна из перечисленных констант.
            * Возвращаемые значения:
            * Если параметр option указан, то возвращается его значение.
            * Иначе возвращается ассоциативный массив со следующими элементами (которые соответствуют значениям
            * аргумента option) или false в случае возникновения ошибки:
            * https://www.php.net/manual/ru/function.curl-getinfo.php
            */
            // Получаем данные отправляемые серверу.
            $this->curl_info[$getcontent][$key] = curl_getinfo($value);

            // Следующий блок работает если переменная "$getcontent" равно "auth".
            if ($getcontent == 'auth') {
                /**
                * preg_match — Выполняет проверку на соответствие регулярному выражению.
                * Описание:
                * preg_match(string $pattern, string $subject, array &$matches=null, int $flags=0, int $offset=0):int|false
                *
                * Ищет в заданном тексте subject совпадения с шаблоном pattern.
                * Список параметров:
                * @var pattern Искомый шаблон в виде строки.
                * @var subject Входная строка.
                * @var matches В случае, если указан дополнительный параметр matches, он будет заполнен результатами поиска.
                *              Элемент $matches[0] будет содержать часть строки, соответствующую вхождению всего шаблона,
                *              $matches[1] - часть строки, соответствующую первой подмаске и так далее.
                * @var flags   flags может быть комбинацией следующих флагов:
                *              PREG_OFFSET_CAPTURE - В случае, если этот флаг указан, для каждой найденной подстроки будет
                *              указана её позиция (в байтах) в исходной строке. Необходимо помнить, что этот флаг меняет формат
                *              возвращаемого массива matches в массив, каждый элемент которого содержит массив, содержащий в
                *              индексе с номером 0 найденную подстроку, а смещение этой подстроки в параметре subject - в индексе 1.
                *              PREG_UNMATCHED_AS_NULL - Если этот флаг передан, несовпадающие подмаски будут представлены значениями
                *              null; в противном случае они отображаются в виде пустых строк (string).
                * @var offset  Обычно поиск осуществляется слева направо, с начала строки. Можно использовать дополнительный
                *              параметр offset для указания альтернативной начальной позиции для поиска (в байтах).
                * Замечание:
                * Использование параметра offset не эквивалентно замене сопоставляемой строки выражением substr($subject, $offset)
                * при вызове функции preg_match(), поскольку шаблон pattern может содержать такие условия как ^, $ или (?<=x).
                * Возвращаемые значения:
                * preg_match() возвращает 1, если параметр pattern соответствует переданному параметру subject, 0 если нет,
                * или false в случае ошибки.
                */
                // Получаем токен.
                preg_match('/^Location:\s\.\/\?_task=mail&_token=([A-Za-z0-9]+)[^\\n]/im', $this->content_h[$getcontent][$key], $token);

                // Запишем в массив "$tokens" полученные токены.
                $this->tokens[$key] = $token['1'];

                // Настройки нашего сервера не позволяют использовать CURLOPT_COOKIEJAR и CURLOPT_COOKIEFILE.
                // Тогда возмём данные Cookie из заголовка ответа сервера используя регулярное выражение "preg_match_all" и функцию "implode":
                // preg_match_all(' / Set - Cookie: (.*); / U', $content, $results);
                // $cookies = implode(';', $results[1]);
                // и установим Cookie с помощью curl_setopt($this->curls, CURLOPT_COOKIE, $cookies);
                /**
                * preg_match_all — Выполняет глобальный поиск шаблона в строке.
                * Описание:
                * preg_match_all(string $pattern, string $subject, array &$matches=null, int $flags=PREG_PATTERN_ORDER, int $offset=0):int|false|null
                *
                * Ищет в строке subject все совпадения с шаблоном pattern и помещает результат в массив matches в порядке, определяемом комбинацией флагов flags.
                * После нахождения первого соответствия последующие поиски будут осуществляться не с начала строки, а от конца последнего найденного вхождения.
                * Список параметров:
                * @var pattern Искомый шаблон в виде строки.
                * @var subject Входная строка.
                * @var matches Массив совпавших значений, отсортированный в соответствии с параметром flags.
                * @var flags   Может быть комбинацией флагов.
                */
                preg_match_all('/Set-Cookie: (.*);/U', $this->content_h[$getcontent][$key], $results);

                /**
                * implode — Объединяет элементы массива в строку.
                * Описание:
                * implode(string $glue, array $pieces):string
                * implode(array $pieces):string
                *
                * Объединяет элементы массива с помощью строки glue.
                * Замечание:
                * По историческим причинам функции implode() можно передавать аргументы в любом порядке, однако для согласованности с функцией explode()
                * не рекомендуется использовать недокументированный порядок аргументов.
                * Список параметров:
                * @var glue По умолчанию равен пустой строке.
                * @var pieces Массив объединяемых строк.
                * Возвращаемые значения:
                * Возвращает строку, содержащую строковое представление всех элементов массива в указанном порядке, со строкой glue между каждым элементом.
                */
                // Получаем куки с сервера.
                $this->cookies[$key] = implode(';', $results[1]);
            }

            /**
            * curl_multi_remove_handle — Удаляет cURL дескриптор из набора cURL дескрипторов.
            * Описание:
            * curl_multi_remove_handle(CurlMultiHandle $multi_handle, CurlHandle $handle):int
            *
            * Удаляет указанный дескриптор handle из указанного набора дескрипторов multi_handle. После того, как
            * дескриптор handle удалён, его можно снова использовать в функции curl_exec(). Удаление дескриптора handle
            * во время использования также остановит текущую передачу, идущую на этом дескрипторе.
            * Список параметров:
            * @var multi_handle Мультидескриптор cURL, полученный из curl_multi_init().
            * @var handle       Дескриптор cURL, полученный из curl_init().
            * Возвращаемые значения:
            * В случае успеха возвращает 0 или одну из констант CURLM_XXX, где XXX - код ошибки.
            */
            // Закрываем все дескрипторы.
            curl_multi_remove_handle($this->multiCurl, $this->curls[$key]);
        }
    }

    // Выполнение запросов cURL.
    public function curl_multi_exec()
    {
        // Переменная "$running" показывает число работающих процессов.
        // Переменная "$status" показывает отсутствие ошибок. Инициализируем переменные.
        $status = $running= NULL;

        // Запускаем множественный обработчик:
        // curl_mult_exec запишет в переменную "$running" количество еще не завершившихся процессов.
        // Пока они есть - продолжаем выполнять запросы.
        do {
            /**
            * curl_multi_exec — Запускает подсоединения текущего дескриптора cURL.
            * Описание:
            * curl_multi_exec(CurlMultiHandle $multi_handle, int &$still_running):int
            * Обрабатывает каждый дескриптор в стеке. Этот метод может быть вызван вне зависимости
            * от необходимости дескриптора читать или записывать данные.
            * Список параметров:
            * @var multi_handle  Мультидескриптор cURL, полученный из curl_multi_init().
            * @var still_running Ссылка на флаг, указывающий, идут-ли ещё какие-либо действия.
            * Возвращаемые значения:
            * Код cURL, указанный в предопределённых константах cURL.
            * Замечание:
            * Здесь возвращаются ошибки, относящиеся только ко всему стеку.
            * Проблемы всё ещё могут произойти на индивидуальных запросах, даже когда эта функция возвращает CURLM_OK.
            */
            // Выполняем запросы cURL.
            $status = curl_multi_exec($this->multiCurl, $running);
            
            /**
            * curl_multi_select — Ждёт активности на любом curl_multi соединении.
            * Описание:
            * curl_multi_select(CurlMultiHandle $multi_handle, float $timeout=1.0):int
            * Блокирует выполнение скрипта, пока какое-либо из соединений curl_multi не станет активным.
            * * Список параметров:
            * @var multi_handle Мультидескриптор cURL, полученный из curl_multi_init().
            * @var timeout      Время в секундах для ожидания ответа.
            * Возвращаемые значения:
            * В случае успеха возвращает количество дескрипторов, содержащихся в наборах дескрипторов.
            * Может вернуть 0, если не было активности ни на одном дескрипторе. В случае неудачи эта функция
            * вернёт -1 при ошибке выборки (из нижележащего системного вызова выборки).
            */
            // Ждём окончания активности на любом curl - соединении.
            if (curl_multi_select($this->multiCurl) == - 1) {
                usleep(5);
            }
        } while ($running && $status == CURLM_OK);
    }

    // Сбрасываем все установленные опции. Массив "$curl_reset" содержит url которые нужно обработать.
    public function curl_reset($curl_reset)
    {
        // Сбрасываем все установленные опции.
        foreach ($curl_reset as $reset) {
            /**
            * curl_reset — Сбросить все настройки обработчика сессии libcurl.
            * Описание:
            * curl_reset(CurlHandle $handle):void
            *
            * Эта функция переинициализирует все настройки заданного обработчика сессии cURL значениями по умолчанию.
            * Список параметров:
            * @var handle Дескриптор cURL, полученный из curl_init().
            * Возвращаемые значения:
            * Эта функция не возвращает значения после выполнения.
            */
            curl_reset($this->curls[$reset]);
        }
    }

    // Закрываем набор cURL - дескрипторов.
    public function curl_multi_close()
    {
        /**
        * curl_multi_close — Закрывает набор cURL-дескрипторов.
        * curl_multi_close(CurlMultiHandle $multi_handle):void
        * @var multi_handle Мультидескриптор cURL, полученный из curl_multi_init().
        */
        curl_multi_close($this->multiCurl);
    }

    /**
    * Добавим отладочное сообщение в стёк.
    * @param string Короткое сообщение о действии.
    * @param string Выходные данные.
    */
    private function addDebug($action, $data)
    {
        // Если значение свойства "debugEnabled" отсутствует: вернём "FALSE".
        if (!$this->debugEnabled) return FALSE;
        // В массив "debugStack[]" печатаем отладачное сообщение.
        $this->debugStack[] = sprintf(
            "<b>%s:</b><br /><pre>%s</pre>",
            /**
            * htmlspecialchars — Преобразует специальные символы в HTML-сущности.
            *
            * Описание:
            * htmlspecialchars(string $string, int $flags = ENT_COMPAT, string|null $encoding = null, bool $double_encode = true):string
            *
            * В HTML некоторые символы имеют особый смысл и должны быть представлены в виде HTML-сущностей, чтобы сохранить их значение.
            * Эта функция возвращает строку, над которой проведены эти преобразования.
            */
            // Короткое сообщение о действии и выходные данные. Преобразуем специальные символы в HTML - сущности для вывода в браузер.
            $action, htmlspecialchars($data)
        );
    }

    /**
    * Формируем дамп стека отладки.
    */
    public function dumpDebugStack()
    {
        print "<p>".join("\n", $this->debugStack)."</p>";
    }

    /**
    * Запись лог-файла в папку скрипта на жёсткий диск.
    */
    static function write_log_file($args)
    {
        // Пишем содержимое (строку) в файл,
        // используя флаг FILE_APPEND flag для дописывания содержимого в конец файла и флаг LOCK_EX
        // для предотвращения записи данного файла кем - нибудь другим в данное время.
        /**
        * Функция записи отладочной информации в log-файл.
        * file_put_contents — Пишет данные в файл
        * file_put_contents(string $filename , mixed $data , int $flags = 0 , resource $context = ?):int
        * Функция идентична последовательным успешным вызовам функций fopen(), fwrite() и fclose().
        * Если filename не существует, файл будет создан. Иначе, существующий файл будет
        * перезаписан, за исключением случая, если указан флаг FILE_APPEND.
        * @param filename   Путь к записываемому файлу.
        * @param data       Записываемые данные. Может быть типа string, array или ресурсом потока.
        *                   Если data является потоковым ресурсом (stream), оставшийся буфер этого потока
        *                   будет скопирован в указанный файл.
        *                   Это похоже на использование функции stream_copy_to_stream().
        *                   Также вы можете передать одномерный массив в качестве параметра data.
        *                   Это будет эквивалентно вызову file_put_contents($filename, implode('', $array)).
        * @param flags      Значением параметра flags может быть любая комбинация следующих флагов,
        *                   соединённых бинарным оператором ИЛИ (|).
        * Доступные флаги:  FILE_USE_INCLUDE_PATH - Ищет filename в подключаемых директориях. Подробнее смотрите
        * директиву include_path; FILE_APPEND - Если файл filename уже существует, данные будут дописаны в конец
        * файла вместо того, чтобы его перезаписать; LOCK_EX - Получить эксклюзивную блокировку на файл на время
        * записи. Другими словами, между вызовами fopen() и fwrite() произойдёт вызов функции flock().
        * Это не одно и то же, что вызов fopen() с флагом "x".
        */
        // Определим путь хранения лог - файла.
        $path = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME);
        file_put_contents(
            $path . '/logs/rc_multi_curl_connector.log',
            /**
            * print_r — Выводит удобочитаемую информацию о переменной.
            * Если вы хотите перехватить вывод print_r(), используйте параметр return. Если его значение равно true,
            * то print_r() вернёт информацию вместо вывода в браузер.
            */
            print_r($args, true),
            FILE_APPEND | LOCK_EX
        );
    }
}

/**
* Это исключение входа в систему Roundcube будет сгенерировано, если две попытки входа не удастся.
*/
class RoundcubeLoginException extends Exception
{
}
// Конец класса RoundcubeMultiCURL.
