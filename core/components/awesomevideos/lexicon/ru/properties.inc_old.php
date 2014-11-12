<?php

$_lang['awesomeVideos_prop_ajax'] = 'Использовать AJAX';

$_lang['awesomeVideos_prop_pagination'] = 'Способ пагинации по-умолчанию. Вы можете выбрать свой сниппет, который будет работать в соответствии
с правилами пагинации описаной в RTFM.';

$_lang['awesomeVideos_prop_paginationSnippet'] = 'Снипет выполняющий пагинацию.';
$_lang['awesomeVideos_prop_part'] = 'Тип данных получаемых по-умолчанию.';

$_lang['awesomeVideos_prop_totalVar'] = 'TotalVar имя для getPage';
$_lang['awesomeVideos_prop_additionalPlaceholders'] = 'Дополнительные плейсхолдеры, в виде JSON или array, в зависимости от способа вызова сниппета.';

$_lang['awesomeVideos_prop_setOfProperties'] = 'Имя набора параметров из которого будут браться основные параметры. Это нужно для этого чтобы можно было
сделать безопасную ссылку, которую можно использовать для открытия данного документа. Такое решение было вызвано использованием ключей, по которым в сессии
хранятся текущие настройки, чьи данные используются при переходах посредством ajax. <b>Имя должно обязательно начинаться с "aw_"</b>';

$_lang['awesomeVideos_prop_fastMode'] = 'Выполнять некешируемые сниппеты в чанках? Это немного замедляет выполнение.';

$_lang['awesomeVideos_prop_includeTVs'] = 'Список ТВ параметров для выборки, через запятую. Например: "action,time" дадут плейсхолдеры [[+action]] и [[+time]].';
$_lang['awesomeVideos_prop_prepareTVs'] = 'Список ТВ параметров, которые нужно подготовить перед выводом. По умолчанию, установлено в "1", что означает подготовку всех ТВ, указанных в "&includeTVs=``"';
$_lang['awesomeVideos_prop_processTVs'] = 'Список ТВ параметров, которые нужно обработать перед выводом. Если установить в "1" - будут обработаны все ТВ, указанные в "&includeTVs=``". По умолчанию параметр пуст.';
// $_lang['awesomeVideos_prop_tvFilters'] = 'Список фильтрова по ТВ, с разделителями AND и OR. Разделитель, указанный в параметре "&tvFiltersOrDelimiter" представляет логическое условие OR и по нему условия группируются в первую очередь.  Внутри каждой группы вы можете задать список значений, разделив их "&tvFiltersAndDelimiter". Поиск значений может проводиться в каком-то конкретном ТВ, если он указан ("myTV==value"), или в любом ("value"). Пример вызова: "&tvFilters=`filter2==one,filter1==bar&#37;||filter1==foo`". <br />Обратите внимание: фильтрация использует оператор LIKE и знак "&#37;" является метасимволом. <br />И еще: Поиск идёт по значениям, которые физически находятся в БД, то есть, сюда не подставляются значения по умолчанию из настроек ТВ.';


$_lang['awesomeVideos_prop_tvFilters'] = ' {"&';


$_lang['awesomeVideos_prop_tvFiltersAndDelimiter'] = 'Разделитель для условий AND в параметре "&tvFilters". По умолчанию: ",".';
$_lang['awesomeVideos_prop_tvFiltersOrDelimiter'] = 'Разделитель для условий OR в параметре "&tvFilters". По умолчанию: "||".';
$_lang['awesomeVideos_prop_tvPrefix'] = 'Префикс для ТВ параметров.';


$_lang['awesomeVideos_prop_tpl'] = 'Имя чанка для оформления ресурса. Если не указан, то содержимое полей ресурса будет распечатано на экран.';
$_lang['awesomeVideos_prop_tplFirst'] = 'Имя чанка для первого ресурса в результатах.';
$_lang['awesomeVideos_prop_tplLast'] = 'Имя чанка для последнего ресурса в результатах.';
$_lang['awesomeVideos_prop_tplOdd'] = 'Имя чанка для каждого второго ресурса.';
$_lang['awesomeVideos_prop_tplWrapper'] = 'Чанк-обёртка, для заворачивания всех результатов. Понимает один плейсхолдер: [[+output]]. Не работает вместе с параметром "toSeparatePlaceholders".';
$_lang['awesomeVideos_prop_tplPagingButton'] = 'Имя чанка кнопки выполняющую для получения ресурсов';
$_lang['awesomeVideos_prop_tplPagingButtonEmpty'] = 'Имя чанка не активной (пустой) кнопки';
$_lang['awesomeVideos_prop_tplPagingSnippet'] = 'Чанк-обертка раздела постраничной навигации';
$_lang['awesomeVideos_prop_tplPagingCarousel'] = 'Чанк-обертка навигации в виде карусели';
$_lang['awesomeVideos_prop_tplPath'] = 'Путь к файлам чанков (можно указывать от корня системы или от корня сайта). Используется если чанк не найден в общем списке';


// QUERY
$_lang['awesomeVideos_prop_select'] = 'Список полей для выборки, через запятую. Можно указывать JSON строку с массивом, например {"modResource":"id,pagetitle,content"}.';
$_lang['awesomeVideos_prop_leftJoin'] = '';
$_lang['awesomeVideos_prop_rightJoin'] = '';
$_lang['awesomeVideos_prop_innnerJoin'] = '';
$_lang['awesomeVideos_prop_groupby'] = 'Указываем одно единственное имя поле для группировки';

$_lang['awesomeVideos_prop_limit'] = 'Максимальное кол-во элементов на странице';
$_lang['awesomeVideos_prop_offset'] = 'Первый элемент (0 = first)';
$_lang['awesomeVideos_prop_where'] = 'Массив дополнительных параметров выборки, закодированный в JSON.';
$_lang['awesomeVideos_prop_having'] = 'Т.к. having выполняется иначе, то его можно использовать для поиска по дефолтным параметрам, where не позволяет делать такого.';

$_lang['awesomeVideos_prop_ids'] = 'Список ID ресурсов через запятую, которые обязательны для вывода. Может также принимать массив, при вызове сниппета напрямую.';
$_lang['awesomeVideos_prop_parentIds'] = 'Критерий выборки по плейлистам, содержит список ресурсов через запятую, которые обязательны для вывода. Распростроняется только на получение видеоресурсов.
 Может также принимать массив, при вызове сниппета напрямую.';

// PAGINATION
// pdoPage
$_lang['awesomeVideos_prop_tplPage'] = 'Чанк оформления обычной ссылки на страницу.';
$_lang['awesomeVideos_prop_tplPageActive'] = 'Чанк оформления ссылки на текущую страницу.';
$_lang['awesomeVideos_prop_tplPageFirst'] = 'Чанк оформления ссылки на первую страницу.';
$_lang['awesomeVideos_prop_tplPagePrev'] = 'Чанк оформления ссылки на предыдущую страницу.';
$_lang['awesomeVideos_prop_tplPageLast'] = 'Чанк оформления ссылки на последнюю страницу.';
$_lang['awesomeVideos_prop_tplPageNext'] = 'Чанк оформления ссылки на следующую страницу.';
$_lang['awesomeVideos_prop_tplPageFirstEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на первую страницу.';
$_lang['awesomeVideos_prop_tplPagePrevEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на предыдущую страницу.';
$_lang['awesomeVideos_prop_tplPageLastEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на последнюю страницу.';
$_lang['awesomeVideos_prop_tplPageNextEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на следующую страницу.';
$_lang['awesomeVideos_prop_tplPageSkip'] = 'Чанк оформления пропущенных страниц при продвинутом режиме отображения (&pageLimit >= 7).';
$_lang['awesomeVideos_prop_tplPageWrapper'] = 'Чанк оформления всего блока пагинации, содержит плейсхолдеры страниц.';
// getPage

$_lang['awesomeVideos_prop_pageNavTpl'] = 'Чанк оформления обычной ссылки на страницу.';
$_lang['awesomeVideos_prop_pageActiveTpl'] = 'Чанк оформления ссылки на текущую страницу.';
$_lang['awesomeVideos_prop_pageFirstTpl'] = 'Чанк оформления ссылки на первую страницу.';
$_lang['awesomeVideos_prop_pageLastTpl'] = 'Чанк оформления ссылки на последнюю страницу.';
$_lang['awesomeVideos_prop_pageNavOuterTpl'] = 'Content representing the layout of the page navigation controls.';
$_lang['awesomeVideos_prop_pageNextTpl'] = 'Чанк оформления ссылки на следующую страницу.';
$_lang['awesomeVideos_prop_pagePrevTpl'] = 'Чанк оформления ссылки на предыдущую страницу.';


$_lang['awesomeVideos_prop_outputSeparator'] = 'Необязательная строка для разделения результатов работы.';
$_lang['awesomeVideos_prop_scheme'] = 'Схема формирования url, передаётся в modX::makeUrl().';
$_lang['awesomeVideos_prop_showLog'] = 'Показывать дополнительную информацию о работе сниппета. Только для авторизованных в контекте "mgr".';
$_lang['awesomeVideos_prop_showSpecial'] = '';
$_lang['awesomeVideos_prop_showUnactive'] = 'Показывать неопубликованные ресурсы.';
$_lang['awesomeVideos_prop_sortby'] = 'Любое поле ресурса для сортировки, включая ТВ параметр, если он указан в параметре "includeTVs". Можно указывать JSON строку с массивом нескольких полей. Для случайно сортировки укажите "RAND()"';
$_lang['awesomeVideos_prop_sortdir'] = 'Направление сортировки: по убыванию или возрастанию.';
$_lang['awesomeVideos_prop_toPlaceholder'] = 'Если не пусто, сниппет сохранит все данные в плейсхолдер с этим именем, вместо вывода не экран.';
$_lang['awesomeVideos_prop_toSeparatePlaceholders'] = 'Если вы укажете слово в этом параметре, то ВСЕ результаты будут выставлены в разные плейсхолдеры, начинающиеся с этого слова и заканчивающиеся порядковым номером строки, от нуля. Например, указав в параметре "myPl", вы получите плейсхолдеры [[+myPl0]], [[+myPl1]] и т.д.';
$_lang['awesomeVideos_prop_wrapIfEmpty'] = 'Включает вывод чанка-обертки (tplWrapper) даже если результатов нет.';

$_lang['awesomeVideos_prop_prepareSnippet'] = 'Имя сниппета, который запускается перед обработкой строки (row), в него передается массив в
элементе row которого, содержутся все данные текущей строки. Данные получаемые после обработки сливаются с массивом row и передаются в плейсхолдеры.
Если именая ключей row совпадают с именами ключей массива, то данные row бelen переписаны.';

// остальные будем добавлять по мере появления


/*$_lang['pdotools_prop_last'] = 'Номер последней итерации вывода результатов. По умолчанию он рассчитается автоматически, по формуле (total + first - 1).';
$_lang['pdotools_prop_limit'] = 'Ограничение количества результатов выборки. Можно использовать "0".';
$_lang['pdotools_prop_neighbors_limit'] = 'Количество соседних документов справа и слева. По умолчанию - 1.';
$_lang['pdotools_prop_offset'] = 'Пропуск результатов от начала.';
$_lang['pdotools_prop_parents'] = 'Список родителей, через запятую, для поиска результатов. По умолчанию выборка ограничена текущим родителем. Если поставить 0 - выборка не ограничивается. Если id родителя начинается с дефиса, он и его потомки исключается из выборки.';
$_lang['pdotools_prop_resources'] = 'Список ресурсов, через запятую, для вывода в результатах. Если id ресурса начинается с дефиса, этот ресурс исключается из выборки.';
$_lang['pdotools_prop_templates'] = 'Список шаблонов, через запятую, для фильтрации результатов. Если id шаблона начинается с дефиса, ресурсы с ним исключается из выборки.';
$_lang['pdotools_prop_from'] = 'Id ресурса, от которого строить хлебные крошки. Обычно это корень сайта, то есть "0".';
$_lang['pdotools_prop_to'] = 'Id ресурса для которого строятся хлебные крошки. По умолчанию это id текущей страницы.';
$_lang['pdotools_prop_users'] = 'Список пользователей для вывода, через запятую. Можно использовать usernames и id. Если значение начинается с тире, этот пользователь исключается из выборки.';
$_lang['pdotools_prop_groups'] = 'Список групп пользователей, через запятую. Можно использовать имена и id. Если значение начинается с тире, значит пользователь не должен присутствовать в этой группе.';
$_lang['pdotools_prop_roles'] = 'Список ролей пользователей, через запятую. Можно использовать имена и id. Если значение начинается с тире, значит такой роли у пользователя быть не должно.';
$_lang['pdotools_prop_exclude'] = 'Список id ресурсов, которые нужно исключить из выборки.';
$_lang['pdotools_prop_returnIds'] = 'Возвращать строку со списком id ресурсов, вместо оформленных результатов.';
$_lang['pdotools_prop_showDeleted'] = 'Показывать удалённые ресурсы.';
$_lang['pdotools_prop_showHidden'] = 'Показывать ресурсы, скрытые в меню.';
$_lang['pdotools_prop_showAtHome'] = 'Показывать хлебные крошки на главной странице сайта.';
$_lang['pdotools_prop_showHome'] = 'Выводить ссылку на главную в начале навигации.';
$_lang['pdotools_prop_showCurrent'] = 'Выводить текущий документ в навигации.';
$_lang['pdotools_prop_hideSingle'] = 'Не выводить результат, если он один единственный.';
$_lang['pdotools_prop_hideUnsearchable'] = 'Скрыть ресурсы, которые не участвуют в поиске.';

$_lang['pdotools_prop_totalVar'] = 'Имя плейсхолдера для сохранения общего количества результатов.';

$_lang['pdotools_prop_neighbors_tplWrapper'] = 'Чанк-обёртка, для заворачивания результатов. Понимает плейсхолдеры: [[+left]], [[+top]], [[+right]] и [[+log]]. Не работает вместе с параметром "toSeparatePlaceholders".';
$_lang['pdotools_prop_tplOperator'] = 'Необязательный оператор для проведения сравнения поля ресурса в "tplCondition" с массивом значений и чанков в "conditionalTpls".';
$_lang['pdotools_prop_tplCondition'] = 'Поле ресурса, из которого будет получено значение для выбора чанка по условию в "conditionalTpls".';
$_lang['pdotools_prop_conditionalTpls'] = 'JSON строка с массивом, у которого в ключах указано то, с чем будет сравниваться "tplCondition", а в значениях - чанки, которые будут использованы для вывода, если сравнение будет успешно. Оператор сравнения указывается в "tplOperator". Для операторов типа "isempty" можно использовать массив без ключей.';
$_lang['pdotools_prop_tplCurrent'] = 'Чанк оформления текущего документа в навигации.';
$_lang['pdotools_prop_tplHome'] = 'Чанк оформления ссылки на главную страницу.';
$_lang['pdotools_prop_tplMax'] = 'Чанк, который добавляется в начало результатов, если их больше чем "&limit".';
$_lang['pdotools_prop_tplPrev'] = 'Чанк ссылки на предыдущий документ.';
$_lang['pdotools_prop_tplUp'] = 'Чанк ссылки на родительский документ.';
$_lang['pdotools_prop_tplNext'] = 'Чанк ссылки на следующий документ.';

$_lang['pdotools_prop_loadModels'] = 'Список компонентов, через запятую, чьи модели нужно загрузить для построения запроса. Например: "&loadModels=`ms2gallery,msearch2`".';
$_lang['pdotools_prop_direction'] = 'Направление навигации: слева направо (ltr) или справа налево (rtl), например для Арабского языка.';
$_lang['pdotools_prop_id'] = 'Идентификатор ресурса.';
$_lang['pdotools_prop_field'] = 'Поле ресурса.';
$_lang['pdotools_prop_top'] = 'Выбирает родителя указанного "&id" на уровне "&top".';
$_lang['pdotools_prop_topLevel'] = 'Выбирает родителя указанного "&id" на уровне "&topLevel" от корня контекста.';

$_lang['pdotools_prop_forceXML'] = 'Принудительно выводить страницу как xml.';
$_lang['pdotools_prop_sitemapSchema'] = 'Схема карты сайта.';

$_lang['pdotools_prop_field_default'] = 'Укажите дополнительное поле ресурса, которое вернётся, если "&field" окажется пуст.';
$_lang['pdotools_prop_field_output'] = 'Указанная здесь строка вернётся, если и "&default" и "&field" оказались пусты.';

$_lang['pdotools_prop_cache'] = 'Кэширование результатов работы сниппета.';
$_lang['pdotools_prop_cachePageKey'] = 'Имя ключа кэширования.';
$_lang['pdotools_prop_cacheTime'] = 'Время актуальности кэша, в секундах.';
$_lang['pdotools_prop_cacheAnonymous'] = 'Включить кэширование только для неавторизованных посетителей.';
$_lang['pdotools_prop_element'] = 'Имя сниппета для запуска.';
$_lang['pdotools_prop_maxLimit'] = 'Максимально возможный лимит выборки. Перекрывает лимит, указанный пользователем через url.';
$_lang['pdotools_prop_page'] = 'Номер страницы для вывода. Перекрывается номером, указанным пользователем через url.';
$_lang['pdotools_prop_pageLimit'] = 'Количество ссылок на страницы. Если больше или равно 7 - включается продвинутый режим отображения.';
$_lang['pdotools_prop_pageNavVar'] = 'Имя плейсхолдера для вывода пагинации.';
$_lang['pdotools_prop_pageCountVar'] = 'Имя плейсхолдера для вывода количества страниц.';
$_lang['pdotools_prop_pageVarKey'] = 'Имя переменной для поиска номера страницы в url.';
$_lang['pdotools_prop_plPrefix'] = 'Префикс для выставляемых плейсхолдеров, по умолчанию "wf.".';

$_lang['pdotools_prop_tplPage'] = 'Чанк оформления обычной ссылки на страницу.';
$_lang['pdotools_prop_tplPageActive'] = 'Чанк оформления ссылки на текущую страницу.';
$_lang['pdotools_prop_tplPageFirst'] = 'Чанк оформления ссылки на первую страницу.';
$_lang['pdotools_prop_tplPagePrev'] = 'Чанк оформления ссылки на предыдущую страницу.';
$_lang['pdotools_prop_tplPageLast'] = 'Чанк оформления ссылки на последнюю страницу.';
$_lang['pdotools_prop_tplPageNext'] = 'Чанк оформления ссылки на следующую страницу.';
$_lang['pdotools_prop_tplPageFirstEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на первую страницу.';
$_lang['pdotools_prop_tplPagePrevEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на предыдущую страницу.';
$_lang['pdotools_prop_tplPageLastEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на последнюю страницу.';
$_lang['pdotools_prop_tplPageNextEmpty'] = 'Чанк, выводящийся при отсутствии ссылки на следующую страницу.';
$_lang['pdotools_prop_tplPageSkip'] = 'Чанк оформления пропущенных страниц при продвинутом режиме отображения (&pageLimit >= 7).';
$_lang['pdotools_prop_tplPageWrapper'] = 'Чанк оформления всего блока пагинации, содержит плейсхолдеры страниц.';

$_lang['pdotools_prop_previewUnpublished'] = 'Включить показ неопубликованных документов, если у пользователя есть на это разрешение.';
$_lang['pdotools_prop_checkPermissions'] = 'Укажите, каеи разрешения нужно проверять у пользователя при выводе документов.';
$_lang['pdotools_prop_displayStart'] = 'Включить показ начальных узлов меню. Полезно при указании более одного "parents".';
$_lang['pdotools_prop_hideSubMenus'] = 'Спрятать неактивные ветки меню.';
$_lang['pdotools_prop_useWeblinkUrl'] = 'Генерировать ссылку с учетом класса ресурса.';
$_lang['pdotools_prop_rowIdPrefix'] = 'Префикс id="" для выставления идентификатора в чанк.';
$_lang['pdotools_prop_level'] = 'Уровень генерируемого меню.';
$_lang['pdotools_prop_hereId'] = 'Id документа, текущего для генерируемого меню. Нужно указывать только если скрипт сам его неверно определяет, например при выводе меню из чанка другого сниппета.';

$_lang['pdotools_prop_webLinkClass'] = 'Класс документа-ссылки.';
$_lang['pdotools_prop_firstClass'] = 'Класс для первого пункта меню.';
$_lang['pdotools_prop_hereClass'] = 'Класс для активного пунтка меню.';
$_lang['pdotools_prop_innerClass'] = 'Класс внутренних ссылок меню.';
$_lang['pdotools_prop_lastClass'] = 'Класс последнего пункта меню.';
$_lang['pdotools_prop_levelClass'] = 'Класс уровня меню. Например, если укажите "level", то будет "level1", "level2" и т.д.';
$_lang['pdotools_prop_outerClass'] = 'Класс обертки меню.';
$_lang['pdotools_prop_parentClass'] = 'Класс категории меню.';
$_lang['pdotools_prop_rowClass'] = 'Класс одной строки меню.';
$_lang['pdotools_prop_selfClass'] = 'Класс текущего документа в меню.';

$_lang['pdotools_prop_tplCategoryFolder'] = 'Специальный чанк оформления категории. Категория - это документ с потомками и или нулевым шаблоном, или с атрибутом "rel=\"category\"".';
$_lang['pdotools_prop_tplHere'] = 'Чанк текущего документа';
$_lang['pdotools_prop_tplInner'] = 'Чанк обертки внутренних пунктов меню. Если пуст - будет использовать "tplInner".';
$_lang['pdotools_prop_tplInnerHere'] = 'Чанк обертка активного пунка меню.';
$_lang['pdotools_prop_tplInnerRow'] = 'Чанк обертка активного пункта меню.';
$_lang['pdotools_prop_tplOuter'] = 'Чанк обертка всего блока меню.';
$_lang['pdotools_prop_tplParentRow'] = 'Чанк оформления контейнера с потомками.';
$_lang['pdotools_prop_tplParentRowActive'] = 'Чанк оформления активного контейнера с потомками.';
$_lang['pdotools_prop_tplParentRowHere'] = 'Чанк оформления текущего контейнера с потомками.';
$_lang['pdotools_prop_tplStart'] = 'Чанк оформления корневого пункта, при условии, что включен "displayStart".';

$_lang['pdotools_prop_ultimate'] = 'Параметры &top и &topLevel работают как в сниппете UltimateParent.';

$_lang['awesomeVideos_prop_outputSeparator'] = 'Разделитель вывода строк.';
$_lang['awesomeVideos_prop_sortBy'] = 'Поле сортировки.';
$_lang['awesomeVideos_prop_sortDir'] = 'Направление сортировки.';
$_lang['awesomeVideos_prop_tpl'] = 'Чанк оформления каждого ряда Предметов.';
$_lang['awesomeVideos_prop_toPlaceholder'] = 'Усли указан этот параметр, то результат будет сохранен в плейсхолдер, вместо прямого вывода на странице.';

$_lang['awesomeVideos_prop_active'] = 'Активировать источник';
$_lang['awesomeVideos_prop_youtubeuser'] = 'Имя пользователя на Youtube';
$_lang['awesomeVideos_prop_tpl'] = 'Video item chunk (template)';
$_lang['awesomeVideos_prop_where'] = 'A JSON-style expression of criteria';
$_lang['awesomeVideos_prop_scripts'] = 'Добавить js/css (PrettyPhoto) скрипты для Lighbox';

*/