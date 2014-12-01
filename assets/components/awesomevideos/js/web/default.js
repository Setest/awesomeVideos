// $(function() {
$(window).load(function() {
'use strict';

$.fn.hasScrollBar = function() {
  var hasScrollBar = {}, e = this.get(0);
  hasScrollBar.vertical = (e.scrollHeight > e.clientHeight) ? true : false;
  hasScrollBar.horizontal = (e.scrollWidth > e.clientWidth) ? true : false;
  return hasScrollBar;
}


	var awesomeVideos = {
		options: {
			baseTitle: '',
			wrapper: '.aw_wrap',
			contentSelector: ' .content',
			firstContentSelector: '> .content:eq(0)',
			firstPagingSelector: '> .content:eq(0)',
			log: '.aw_log',
			pausedTimer: 500,	// таймер паузы в мс., при нажатии back кнопки history и при наличии скрола, он не допускает выполнение подгрузки данных
												// сразу, дабы не переписать history не нужными данными, если же юзер остался на странице то происходит загрузка после ожидания.
			defScrollSize: 0,
			paddingBottom: 15,	// фактически это погрешность при вычислении скролинга, так как на разных устройствах, браузерах,
													// масштабах, размерах шрифта и неконтролируемом поведении при разных padding-ах, мы получаем величины
													// с тысяными долями, которые в конечном итоге перерастают в несколько пикселей, чтоб не париться на счет смещения
													// вводить такая погрешность.
													// величина пикселей не доходя до низа которых у нас начнется загрузка, к примеру скролинг не докурутили до 100px до низа, а загрузка уже началась

			// results: '#mse2_results',
			// total: '#mse2_total',
			// pagination: '#mse2_pagination',
			// sort: '#mse2_sort',
			// limit: '#mse2_limit',
			// slider: '.mse2_number_slider',
			// pagination_link: '#mse2_pagination a',
			// sort_link: '#mse2_sort a',
			// tpl_link: '#mse2_tpl a',
			// selected_tpl: '<a href="#" data-id="[[now]] [[*id]]" class="mse2_selected_link"><em>[[+title]]</em><sup>x</sup></a>',
			// active_class: 'active',
			// disabled_class: 'disabled',
			// disabled_class_fieldsets: 'disabled_fieldsets',
			// prefix: 'mse2_'
		},
		hashes: {},	// хранит изменения которые нужно сделать с кешем браузера
		cancelAction: false,
		backButtonClicked: false,
		eventCounterPrev: 0,
		eventCounter: 0, // счетчик действий, на основе него можно определять нажали юзер кнопку Back или переходит по действиям вперед
										 // это важно для пагинации в виде скролинга
		wrap : {}, // массив ссылок на объекты, ключами которых являются ключи
		initialize: function(selector) {
			var that=this;
			// return;

			this.baseTitle = document.title;
			this.timer = this._dateNow();

			// нужно найти все элементы среди родителей которых нет
			// элементов с классом .aw_wrap. Т.е. найдем всех первых родителей.
			// это нужно чтоб отработать history на первой странице.
			this.mainParents = $(this.options.wrapper).parents(this.options.wrapper);

			this.hash.initialize();


			this.options.defScrollSize=this._checkDefaultScrollSize();

			console.log('awesomeVideos initialize"', this);
			$(document).on("click", this.options.wrapper + " .btn.showMore", that.events.showMore.bind(this));
			$(document).on("click", this.options.wrapper + " .paging .pagination a", that.events.showPage.bind(this));
			$(document).on("click", this.options.wrapper + " a.link", that.events.getData.bind(this));
			// this.createCarousel( $( this.options.wrapper + '[data-aw-pagination="carousel"] .aw_wrap_video > .content' ) );
			this.createCarousel( $( this.options.wrapper + '[data-aw-pagination="carousel"] > ' + this.options.contentSelector ) );
			this._bindScroll();
			// $(document).on("click", ".paging a[href*='page=']", that.events.showPage.bind(this));
			// this.createCarousel($('.aw_wrap_video > .content'));
		},

		// _reloadDataByKeys: function(prop,eventCounter) {
		_reloadDataByKeys: function(prop,bindHistory) {
			prop = prop || {};
			bindHistory = (typeof bindHistory !=='undefined') ? bindHistory : true;
			// eventCounter = eventCounter || 0;

			var that=this, key;
			console.log('Reloading page',prop);
			// вызывается только в случае если пользователь воспользовался history и через кнопку back дошел до стартового окна
			// this.mainParents.each(function (index, obj) {
			// 	var $obj=$(obj);
			// 	console.log('Reloading page 1: ', $obj);
			// 	return that._loadData($obj,'getFirstData');
			// });

			var hash = that.hash.getState();
			var hashId = hash.id;
			// alert ('hash: '+hashId);
			// var hashConfig = hash.data[keyReal].config;
			// var hashConfig = History.getLastStoredState().data[keyReal].config;

			if ( bindHistory && that.hashes[hashId]) {
				// alert ('ups')
				prop = that.hashes[hashId];
				delete (that.hashes[hashId]);
			}
			for(key in prop) if (prop.hasOwnProperty(key)) {
				var props = prop[key];
				if (props.firstInit && props.config.pagination=="scroll"){
					// это сработает только при нажатии на кнопку Back в тот момент когда юзер дойдет до открытия данной страницы.
					props.config['offset']=0; // иначе мы откроем вторую запись
					// props.config['offset']
				}
				console.log('Reloading data by key: ', key, props);
				that.sendData(key,props);
			}

		},

		_checkDefaultScrollSize: function() {
			// узнаем размеры скрола в текущем браузере
			var $newElem = $('<div>',{
						width: 100,
						height: 100,
					}
				)
				.css({
					'overflow' : 'scroll',
					'font-size': '1px',
					'padding': '0',
					'margin': '0',
					'border': 'none 0px',
					'background': 'red'
				})
				.appendTo($('body')),
				scrollHeight = $newElem.height() - $newElem.prop("scrollHeight")
			;
			$newElem.remove();
			return scrollHeight;
		},

		_documentHasScroll: function() {
			// кросс браузерная функция проверки скролинга у , взял отсюда:
			// http://www.tylercipriani.com/2014/07/12/crossbrowser-javascript-scrollbar-detection.html

		  if (typeof window.innerWidth === 'number')
		    return window.innerWidth > document.documentElement.clientWidth

		  // rootElem for quirksmode
		  var rootElem = document.documentElement || document.body

		  // Check overflow style property on body for fauxscrollbars
		  var overflowStyle

		  if (typeof rootElem.currentStyle !== 'undefined')
		    overflowStyle = rootElem.currentStyle.overflow

		  overflowStyle = overflowStyle || window.getComputedStyle(rootElem, '').overflow

		    // Also need to check the Y axis overflow
		  var overflowYStyle

		  if (typeof rootElem.currentStyle !== 'undefined')
		    overflowYStyle = rootElem.currentStyle.overflowY

		  overflowYStyle = overflowYStyle || window.getComputedStyle(rootElem, '').overflowY

		  var contentOverflows = rootElem.scrollHeight > rootElem.clientHeight
		  var overflowShown    = /^(visible|auto)$/.test(overflowStyle) || /^(visible|auto)$/.test(overflowYStyle)
		  var alwaysShowScroll = overflowStyle === 'scroll' || overflowYStyle === 'scroll'

		  return (contentOverflows && overflowShown) || (alwaysShowScroll)
		},

		_checkHeightLimit: function($obj, scrollType) {
			var height = false,
					scrollCanSeen = false
			;

			scrollType = scrollType || 'element';

			if (typeof $obj !=='undefined') {
				var that=this,
						$content = $obj.find(this.options.firstContentSelector),
						maxHeight = $obj.css('max-height'),
						defScrollSize = this.options.defScrollSize,
						height = $obj.outerHeight()
				;

				// проверка на существование скролинга
				// scrollCanSeen = ( $obj.css('overflow') !== 'hidden' && ($obj.prop("scrollHeight") == $obj.get(0).offsetHeight) ) ? false : true ;
				// scrollCanSeen = ( ($obj.prop("scrollHeight") - defScrollSize) == $obj.outerHeight() ) ? false : true ;

				// т.к. во всех случаях я не могу со 100% уверенностью определить наличие скрола, то будем
				// делать такую простую проверку.
				if (typeof $content !=='undefined'){
					// console.warn ($obj, $content);
					// console.warn ($obj.innerHeight(), $content.outerHeight());
					scrollCanSeen = ( $obj.innerHeight() < $content.outerHeight() ) ? true : false ;
				}

				// если height > или < $obj.prop("scrollHeight") - то это 100% есть height

				// height = ( ($obj.prop("scrollHeight") - defScrollSize) == $obj.outerHeight() ) ? 'auto' : $obj.outerHeight()
				// height = ( $obj.css('overflow-y') == 'scroll' || $obj.css('overflow') == 'scroll' || $obj.css('overflow') == 'hidden' || $obj.prop("scrollHeight") == $obj.height() ) ? $obj.height() : $obj.prop("scrollHeight")
				// height = ( $obj.css('overflow') == 'hidden' || $obj.prop("scrollHeight") == $obj.height() ) ? $obj.height() : $obj.prop("scrollHeight")

				if ($obj.css('overflow') == 'hidden'){
					// вероятно используется сторонний скролинг - не родной
					// поэтому выясним размер скрола браузера
					height = $obj.height();
					scrollCanSeen = true;
				}
				// hidden
				maxHeight = ( !maxHeight || maxHeight == 'none' ) ? false : Number(maxHeight.replace( /px/g, "" ));
				if ( maxHeight ) height = maxHeight;

				if (scrollType == 'element'){
					$obj.css({
						'height' : height,
						// 'max-height' : height,
					});
				}

				// $obj.prop("scrollHeight") >	$obj.height();
			}
			return {
				'height' : height,
				'scrollCanSeen' : scrollCanSeen,
			};
		},
		_bindScroll: function() {
			var that=this,
					_$window = $(window),
		      _$body = $('body'),
		      _$contents = $( this.options.wrapper + that.options.contentSelector ),
		      _$aw_wrap = $( this.options.wrapper + '[data-aw-pagination="scroll"]' ),
		      _bodyScroll=this._documentHasScroll()
		      // _$scroll = _isWindow ? _$window : $e
		  ;

			console.log ('размер скрола по-умолчанию', this.options.defScrollSize );
			console.log ('есть ли скрол у DOC: ', _bodyScroll );
			console.log ('элементы со скролом: ', _$aw_wrap );


			if ( _$aw_wrap.length ){

				_$window.unbind( '.pageScrolling').bind('scroll.pageScrolling', that._checkScroll.bind(this) );
				_$aw_wrap.unbind( '.pageScrolling').bind('scroll.pageScrolling', that._checkScroll.bind(this) );

				_$aw_wrap.each(function (index, obj) {
					var $obj = $ ( obj ),
							heightLimit = {},
				      maxHeight = $obj.css('max-height'),
							objProp = $obj.data('aw-config'),
							aw_scrollType = $obj.data('aw-scrolltype')
					;
					// проходимся по каждому элементу и выясняем есть ли у него ограничения по высоте
					// если они есть, но скролинга не видно, и balance > 0, то запускаем ajax.
					// если ограничения нет и нет среди родителей элемента aw_wrap
					// то делаем ограничение по body
					// если у body нет скролинга и balance > 0, запускаем ajax, если скролинг не появился зацикливаем ajax.
					//
					// в ходе проверки каждый элемент обозначаем как проверенный и к какому типу скролинга относиться, body или content
					// console.log ("Текущий wrap", heightLimit ,$obj);

					// делаем проверку на принадлежность к BODY.
					if ( !aw_scrollType ){
						aw_scrollType = ($obj.parents(that.options.wrapper).length) ? 'element' : 'body';
						heightLimit = that._checkHeightLimit($obj, aw_scrollType);	// хранит минимальную высоту и данные о том есть ли скролинг у элемента
						// $.data($obj,'aw-scrolltype',aw_scrollType);
						if ( maxHeight && maxHeight !== 'none' ) aw_scrollType = 'element';
						$obj.attr('data-aw-scrolltype',aw_scrollType);

						if (
								(objProp.pagination=='snippet' && !objProp.total)
								|| (objProp.pagination!=='snippet' && !objProp.balance)
							 )
						{
							console.log ("Блокируем запуск пагинации");
							return; // значит все показали не будем лишний раз ничего вытаскивать
						}
					}
					heightLimit = that._checkHeightLimit($obj, aw_scrollType);	// хранит минимальную высоту и данные о том есть ли скролинг у элемента

						if (( !_bodyScroll && aw_scrollType=='body' ) || ( !heightLimit.scrollCanSeen && aw_scrollType=='element' )){
							console.log ("Скролинга нет - запускаем AJAX");
							that._loadData($obj);
							// console.log ("Мало данных запускаем получение через ajax", balance);
							// that._checkScroll({'currentTarget':$wrap});
						}else{
							console.log ("Скролинг есть ниче не делаем");
						}
					// }


				});
			}




			// т.к. этот метод запускается один раз, то нам нужно
			// пробежаться по всем элементам .content или просто вызывать у них событие scroll
			// _$body.trigger( "scroll" );


			// console.log('bindScroll',this);
		},

		_scrollTo: function($obj) {
			if (typeof $obj === 'undefined' || !$obj ) return false;
			var offset = $obj.offset().top;
			offset=(offset<0)?0:offset;
			$(window).scrollTop(offset);
		},

		_scrollObserve: function($obj, _isWindow) {
			// console.log ('Вычисляем размер скрола и выполянем загрузку данных по необходимости', $obj);
			_isWindow = _isWindow || false;
			var that = this,
					_$window = $(window),
					_$scroll = _isWindow ? _$window : $obj,
					// borderTopWidthInt = isNaN(borderTopWidth) ? 0 : borderTopWidth,

					paddingHorTop = isNaN(parseInt($obj.css('padding-top'))) ? 0 : parseInt($obj.css('padding-top')),
					paddingHorBottom = isNaN(parseInt($obj.css('padding-bottom'))) ? 0 : parseInt($obj.css('padding-bottom')),
					paddingHor = paddingHorTop + paddingHorBottom,

					borderHorTop = isNaN(parseInt($obj.css('border-top'))) ? 0 : parseInt($obj.css('border-top')),
					borderHorBottom = isNaN(parseInt($obj.css('border-bottom'))) ? 0 : parseInt($obj.css('border-bottom')),
					borderHor = borderHorTop + borderHorBottom,

					// $inner = $obj.find(this.options.firstContentSelector),
					$inner = $obj.find(this.options.firstPagingSelector),
					outerBottom = Math.ceil($obj.offset().top+$obj.outerHeight()),
					innerBottom = Math.ceil($inner.offset().top+$inner.outerHeight()-this.options.paddingBottom)
          // data = $obj.data('jscroll'),
          // borderTopWidth = parseInt($obj.css('borderTopWidth')),
          // borderTopWidthInt = isNaN(borderTopWidth) ? 0 : borderTopWidth,
          // iContainerTop = parseInt($obj.css('paddingTop')) + borderTopWidthInt,
          // iTopHeight = _isWindow ? _$scroll.scrollTop() : $obj.offset().top,
          // innerTop = $inner.length ? $inner.offset().top : 0,
          // iTotalHeight = Math.ceil(iTopHeight - innerTop + _$scroll.height() + iContainerTop)
			;

			// console.log('INFO',_isWindow,outerBottom,innerBottom);
			// console.log('_$window.scrollTop()',_$window.scrollTop());
			// console.log('$obj.offset().top',$obj.offset().top);
			// console.log('$obj.outerHeight()',$obj.outerHeight());
			// console.log('window.innerHeight',window.innerHeight);
			// console.log('paddingHor+borderHor',paddingHor+borderHor);
			// console.log('paddingHor',$obj.css('border-top'));
			// console.log('RESULT',$obj.offset().top + $obj.outerHeight() - window.innerHeight- (paddingHor+borderHor));

			if (
					(_isWindow && _$window.scrollTop() >= $obj.offset().top + $obj.outerHeight() - window.innerHeight- (paddingHor+borderHor))
					||
					( !_isWindow &&  outerBottom >= innerBottom )
				)
			{
				// console.log('GLOBAL');
				console.log('LOADING',outerBottom,innerBottom);
				that._loadData($obj);
			}
		},

		_dateNow: function () {
			return (!Date.now)
				? new Date().getTime()
				: Date.now()
			;
		},

		_checkScroll: function($obj) {
			var that=this,
					$target = $($obj.currentTarget),
					timer = 0
			;
			// console.log ('СРАБОТАЛО СОБЫТИЕ!!!', backButtonClicked);
			// console.log ('СРАБОТАЛО СОБЫТИЕ!!!', that.backButtonClicked );

			// if (that.backButtonClicked){
				// that.timer = this._dateNow() + that.options.pausedTimer;
				// that.backButtonClicked = false;
			// }

			if ( that.timer > this._dateNow() ) return;
			// if (that.cancelAction) return;

			if ($target.get(0) == window) {
				// сработал общий скролинг ищем все объекты, которые реагируют
				// на прокрутку главного скролинга
				console.log ('сработал общий скролинг');
				$( that.options.wrapper + '[data-aw-scrolltype="body"]' ).each(function (index, obj) {
					var $obj = $ ( obj );
					that._scrollObserve($obj,true);
				});
			}else{
				that._scrollObserve($target);
			}

		},


		createCarousel: function($obj,config) {
			if (typeof $obj === 'undefined' || !$obj ) return false;
			$( $obj ).owlCarousel({
				nav : true
				,items: 5
				,margin: 20
				,center: false
				,loop: true
				,navText: ['<','>']
			});
		},

		setConfig: function($obj,config) {
			if (typeof $obj === 'undefined' || !$obj ) return false;
			$obj.data('aw-config',config);
			if ( config.pagination!=='undefined' ){
				$obj.attr('data-aw-pagination', config.pagination);
			}
			return true;
		},

		getCurrent: function(type,$obj) {
			var type=type||'key';
			var result=false;
			if (typeof $obj === 'undefined' || !$obj ) return false;

			this.wrap=this.wrap||{};
			// var $wrap = $obj.closest('div[class^="aw_wrap_"]');
			var $wrap = $obj.closest('div[class~="aw_wrap"]');

			if ($wrap && $wrap.length) {
				result = $wrap.data('aw-config');
				if (!result) return false;
				this.wrap[result['key']]=$wrap;
				switch (type) {
					case 'object':
						result = $wrap;
					break;
					case 'config':
					break;
					case 'key':
						result = result['key'];
					default:
						result = $wrap.data('aw-'+type);
					break;
				}
			}

			// console.log('wrap',$wrap);
			return result;
		},

		animation: function ($obj,status) {
			if (typeof $obj =='undefined') return false;

			if ( typeof status == 'undefined' ){
				status = ( typeof $obj.wrap.attr('data-aw-loading') !== 'undefined' ) ? !$obj.wrap.attr('data-aw-loading') : true;
			}

			$obj.wrap.attr('data-aw-loading',status);

			if (status){
				var $loading = $("<div>",{
						class: 'aw-loading-animate aw-loading-spinner glyphicon glyphicon-refresh',
					}),
					$loading_wrapper = $("<div>",{
						class: 'aw-loading',
					}).html( $loading )
				;

				// включаем
				console.log('enable animation');
				$obj.paging.append( $loading_wrapper );

			}else{

				$obj.paging.find('> .aw-loading').remove();
				// выключаем
				console.log('disable animation');

			}
		},

		_loadData: function($obj, action, params) {
			params = params || {};
			action = action || 'showMore';	// getData

// console.log("WWW",$obj);

			var that = this,
					$body=$('body'),
					temp = {},
					// $target = $(event.currentTarget),	// нужно выяснить это объект или
					// $target = $obj,
					$target = ( typeof $obj.currentTarget !=='undefined' )
						? $($obj.currentTarget)
						: $obj,

					$wrapCurrent = this.getCurrent('object', $target),

					title = $target.attr('title') || '',

					$wrap=( action =='getData' )
						? $(that.options.wrapper+':first', $body)
						: $wrapCurrent,

					$content = ( action =='getData' )
						? $wrap.find('> '+that.options.contentSelector)
						: $wrap.find(that.options.contentSelector).filter(':first'),

					$paging = $wrap.find('> .paging'),

					key=this.getCurrent('key', $target),
					pagination = this.getCurrent('pagination', $target),
					idx = this.getCurrent('idx', $target),

					configOriginal = this.getCurrent('config', $target),

					bindHistory = (typeof configOriginal['bindHistory'] ==='undefined') || ( configOriginal['bindHistory'] )?true:false,

					parentsWrap = $wrapCurrent.parents(this.options.wrapper),
					// backButtonClicked = (that.eventCounterPrev > (that.eventCounter)) ? true : false,

					config = ( action =='getData' )
						? {}
						: this.getCurrent('config', $target),

					// config = this.getCurrent('config', $target),

					href = $target.attr('href') || '',
					targetUrl = URI.parse(href),
					paramsUrl = URI.parseQuery(targetUrl.query) || {},
					newConfig = {};
			;

			// console.log('LoadDataыыыы: ', $wrap, $content, $paging, config);
			console.log('LoadData: перед отправкой на сервер ', action, $wrap, $content, $paging, config);
			// if  ((action !=='getData' || action !=='getFirstData') && ( ($wrap.attr('data-aw-loading') == 'true' )
			if  ( action !=='getData' && (
									// || action =='getFirstData' && $wrap.attr('data-aw-loading') == 'true'
									   $wrap.attr('data-aw-loading') == 'true'
									|| (config.pagination=='snippet' && !config.total)
									|| (config.pagination!=='snippet' && !config.balance)
					)) return;	// прерываем на случай повторного вызова

			// если отключен ajax значит
			if ( typeof configOriginal['ajax'] !=='undefined' && (
					 configOriginal['ajax']=='0' || String(configOriginal['ajax']).toLowerCase()=='false' || !configOriginal['ajax']
				 ) && $target.attr('href') && $target.attr('href').length ){
				window.location.href = $target.attr('href');
				return;
			}

			switch (action){
				case 'getData':
					$content.html( '' );
					$paging.html( '' );
					params = paramsUrl;

					// delete (configOriginal['action']);
					// temp[key]=configOriginal;
					// that.hash.set( params, temp );	// нужно передавать сюда текущие конфиги всех вложенных и текущего wrap.
																										// интересный момент если идет пагинация, то мы меняем только тек wrap
																										// если это возврат назад, например выходим из некого wrap
																										// то мы должы брать конфиги родителя...
					break;
				case 'showPage':
					$paging.html( '' );
				  params = {
				  	'page' : ( typeof paramsUrl['page']!=='undefined' && paramsUrl['page'] ) ? paramsUrl['page'] : 1
				  };
					console.info('PARAMS = ', params);

				break;
			}



			console.warn('CURRENT WRAP',$wrapCurrent);
			console.log('PARENT WRAP',parentsWrap);
			// console.log('event',event);
			// console.info('parent Key = ', key);

// that.animation({'wrap':$wrap,'content':$content,'paging':$paging},true);
// return;


			console.warn('COUNTER',that.eventCounter);
			console.warn('COUNTER prev',that.eventCounterPrev);


			// если action == getData и у родителя пагинация == scroll
			// то нужно именить offset у родителя, чтобы мы знали на какое место возвращаться
			// когда нажали кнопку back.
			if ( action =='getData' && typeof $obj.currentTarget !=='undefined'){
				var keyReal = key;
				if ( parentsWrap.length ){
					var $parent = $(parentsWrap.eq(0)),
							// parentObject = this.getCurrent('object', $parent),
							// parentConfig = this.getCurrent('config', $parent),
							pagination = this.getCurrent('pagination', $parent),
							keyReal = this.getCurrent('key', $parent)
					;
				}

				var curOffset = $target.data('aw-idx');
				// config['offset'] = 777;
				if ( pagination == 'scroll' && curOffset ){
					// нужно заменить текущее состояни
					var hashConfig,
							hash = that.hash.getState();
					// var hashConfig = hash.data[keyReal].config;

					try {
						hashConfig = hash.data[keyReal].config;
					} catch(e) {
						hashConfig = $.extend(config);
					}
					// var hashConfig = History.getLastStoredState().data[keyReal].config;

					console.warn('keyReal',keyReal);
					console.warn('hash',History.getLastStoredState());
					console.warn('hashConfig',hashConfig);
					hashConfig['offset'] = curOffset-1;
					// hashConfig['fff'] = true;
					// hash.data['updateState'] = true;
					// hash.saveState();
					// that.hash.set( hashConfig, hash.data, that.eventCounter, true );
					that.hashes[hash.id] = hash.data;
					console.warn('запомнили данные в hash = ' + hash.id, that.hashes);
					// alert('запомнили данные в hash = '+curOffset);
					// History.replaceState({state:'replace'}, "State 3", that.eventCounter);
					// alert ('есть = '+curOffset);
					// temp[key]['config']
					// params = $.extend(params, {'offset':curOffset});
					// config = $.extend(config, {'offset':curOffset});
				}

			}

			// var sendData = $.extend(config, params, {'action':action}),
			var sendData = $.extend(config, params, {'action':action}),
					eventCounter = that.eventCounter++,
					sendParam = {
						// 'wrap' : $wrap,
						// 'content' : $content,
						// 'paging' : $paging,
						// 'key' : key,
						'action' : action,
						'config' : config,
						// 'sendData' : sendData,
						'eventCounter' : eventCounter,

					}, temp;
			temp[key]=sendParam;
			// temp['eventCounter']=eventCounter;



			// else{
				// не фиксируем history у вложенных элементов
				if (
							bindHistory && !parentsWrap.length && (
							       action =='getData'
								|| ( action =='showPage' )
								|| ( action =='showMore' && config.pagination =='scroll' )
					)){
					// записываем данные в history
					// alert ('hash');
					that.hash.set( params, temp, title );
				}else{
					// alert ('интересно когда это сработает?');
					that._reloadDataByKeys(temp,bindHistory);
				}
			// }

		},


		sendData: function (key, prop, curEventCounter) {
			curEventCounter = curEventCounter || 0; // текущие данные счетчика

			var that = this, newConfig = {},
					// key = prop.key,
					action = prop.action,

					$wrap = $(that.options.wrapper+'[data-aw-key='+key+']'),

					$content = $wrap.find('> '+that.options.contentSelector),

					// $content = ( action =='getData' )
					// 	? $wrap.find('> '+that.options.contentSelector)
					// 	: $wrap.find(that.options.contentSelector).filter(':first'),

					$paging = $wrap.find('> .paging'),

					config = prop.config,

					curEventCounter = prop.eventCounter || 0,

					backButtonClicked = (that.eventCounterPrev > curEventCounter) ? true : false,

					scrollTop = $(window).scrollTop()
					// $wrap = prop.wrap,
					// $content = prop.content,
					// $paging = prop.paging
			;

			that.backButtonClicked = backButtonClicked;

			if ( typeof config['action']=='undefined' ) config['action'] = action;

// alert (config['id']);

			// if ( backButtonClicked && action=='showMore' ){
			if ( backButtonClicked ){
				// console.log ('XXXX', $wrap);
				switch (config.pagination){
					case "scroll":
						config['offset'] = (config['offset']==1)?0:config['offset']--;
						// config['page'] = config['page']--;
						// alert ('scroll');
						// $content = $wrap.find('> '+that.options.contentSelector);
					break;
					default:
						config['offset'] = (config['offset']==1)?0:config['offset'];
					break;
				}
				// console.log ('YYYY', $content);
				// config['offset'] = config['offset']-config['limit'];	// так правильно, но не совусем, всегда открывается более ранний элемент
				// alert ('ooook2='+config['offset']);
				console.log ('нажали backButton', config);
			}else{
				// двигаемся вперед или проходим по ссылке
				if (config.pagination == 'scroll'){
					// alert (777);
					// config['offset']=config['offset']-1;
					// config['page'] = parseInt(config['offset'])+1;
				}
			}

			// delete (prop.sendData['key']);
			// delete (prop.sendData['balance']);



			console.log ('сработал SendData', prop);
			console.warn ('отправили на сервер', config);
			console.warn (curEventCounter,' = текущий СЧЕТЧИК СОБЫТИЙ последний= ', that.eventCounter);
			console.warn ('ПРЕДЫДУЩИЙ = ', that.eventCounterPrev);
			awesomeVideos.eventCounterPrev = curEventCounter;


// http://copy.sportsreda.ru/testvideo?part=playlist&id=7&setOfProperties=aw_playlist&limit=1&parentIds=7&action=getData
// http://copy.sportsreda.ru/testvideo?log_status=0&limit=2&offset=2&total=4&balance=2&part=video&where=&key=d3a96f2d3cdd0b893d0341bd57cdeb64&setOfProperties=aw_videos&pagination=button&action=showMore

			$.ajax({
				url: "[[+actionUrl]]",
				cache: false,
				type: "POST",
				data: config,
				dataType:'json',
				beforeSend: function(html){
					// включаем анимацию
					// и добавляем атрибут чтоб не было повторного вызова.
					that.animation({'wrap':$wrap,'content':$content,'paging':$paging},true);
				},
				complete: function(html){
					// выключаем анимацию
					that.animation({'wrap':$wrap,'content':$content,'paging':$paging},false);
				},
				success: function(responce){
					console.log('success');
					if (responce.success==true){

						responce.log = responce.log || '';
						$(that.options.log).html( responce.log );

						switch (action){
							// case 'getFirstData':
							case 'getData':
								// добавляем content
								$content.html( responce.data );
								$paging.html( responce.paging );

								// т.к. мы меняем только content то нам нужно изменить параметры обертки
								if (typeof responce.pagination !=='undefined') $wrap.attr('data-aw-pagination',responce.pagination)
								newConfig = $.extend(config, {'setOfProperties':responce.setOfProperties,'pagination':responce.pagination,'balance':responce.balance,'limit':responce.limit,'offset':responce.offset});

								break;
							case 'showMore':
								// newConfig = $.extend(config, {'balance':parseInt(responce.balance),'limit':parseInt(responce.limit),'offset':parseInt(responce.offset)});
								newConfig = $.extend(config, {'total':parseInt(responce.total),'balance':parseInt(responce.balance),'limit':parseInt(responce.limit),'offset':parseInt(responce.offset)});


							case 'showPage':
								// var newConfig = $.extend(config, {'pagination':responce.pagination,'balance':responce.balance,'limit':responce.limit,'offset':responce.offset});
								newConfig = $.extend(config, {'total':parseInt(responce.total),'balance':parseInt(responce.balance),'limit':parseInt(responce.limit),'offset':parseInt(responce.offset)});
								console.info('newConfig',config);

								// добавляем content
								// this.wrap[key].find('.content').append(responce.data);

								// console.info(responce.log);

								// выясняем тип пагинации и в зависимости от этого изменем ее.
								// that.setPaging();
								// var $tempData=$('<div>').attr({
								// 	class: 'aw-temp'
								// }).html(responce.data);


								switch (config.pagination){
									case 'carousel':
										console.info($content);
										$content.html(responce.data);
										break;
									case 'snippet':
										$paging.html( responce.paging );
										$content.html(responce.data);

										if (backButtonClicked){
											// некоторые браузеры сбрасывают позицию скролинга при нажатии на кнопку
											// назад, этот фикс решает этот трабл, но если он срабатывает заметно перемещение на экране
											setTimeout(function() {
												$(window).scrollTop(scrollTop);
											}, 0)
										}
										// $('> .paging', $wrap).html(responce.paging);
										break;
									case 'scroll':
										if (backButtonClicked){
											$content.html(responce.data);
										}else{
											$content.append(responce.data);
										}
										$paging.html( responce.paging );
										break;
									case 'button':
									default:
										$content.append(responce.data);
										// $newData = $tempData.html().appendTo($content);
										// $newData = $content.append(responce.data);
										// $wrap.find('.content').filter(':first').append(responce.log + responce.data);
										$('> .paging', $wrap).html(responce.paging);
										break;
								}

							break;
						}

						// замещаем конфигурацию
						that.setConfig($wrap, newConfig);

						setTimeout(function() {
							console.info('ща отработаем');
							$content.waitForImages(function() {
								if (backButtonClicked){
									that.timer = that._dateNow() + that.options.pausedTimer;
									that._scrollTo($content);
								// that._bindScroll();
							// }else{
								    // All descendant images have loaded, now slide up.
										// пагинация скролингом, прежде нужно дождаться загрузки всех изображений!
								}
								that._bindScroll();
							});
						}, 0)


						// включаем карусель
						// var $carousels=$content.find('.aw_wrap');
						var $carousels=$content
							.find('.aw_wrap[data-aw-pagination="carousel"]')
							.each(function() {
								that.createCarousel( $( this ).find(that.options.contentSelector).filter(':first') );
						});

					}
					else {
						alert (responce.message);
					}
				},
				error: function(e) {
					console.log(e.message);
				}
			});

		},

		events: {
			showPage: function(event) {
				this.preventDefault(event);
				console.log('showPage',arguments);
				// выясняем есть ли параметр page и если есть вытаскиваем его и отправляем в метод
				// var page = url('?page', event.target.href) || 1,
				return this._loadData(event,'showPage');
			},

			getData: function(event, params) {
				this.preventDefault(event);
				console.warn('event getData',event);
				// alert (999);
				return this._loadData(event,'getData');
			},

			showMore: function(event, params) {
				this.preventDefault(event);
				console.log('showMore',arguments);
				return this._loadData(event,'showMore');
			},

		},
		preventDefault: function(e) {
			if (e.preventDefault) {
				e.preventDefault();
			} else {
				e.returnValue = false;
			}
		},

		rewriteTitle: function (title) {
			if (typeof title == 'undefined' || !String(title).length ) return '';
			var that = this,
					newTitle = that.baseTitle.replace( new RegExp(title,'g'), "" ) + ' | ' +title
			;
			return newTitle;
		},

		hash: {
			hashIsReplaceNow: false,
			initialize: function() {
				// Bind to StateChange Event
		    // this.clear();

				var that = this,
						bindHistory = true,
						res={},
						History = window.History;
				if ( !History.enabled ) {
				    return false;
				}
				this.history = History;

				var hash = this.get();

				awesomeVideos.mainParents.each(function (index, obj) {
					var $obj=$(obj),
							// config = {},
							// config = awesomeVideos.getCurrent('configstart', $obj),
							config = awesomeVideos.getCurrent('config', $obj),
							key = awesomeVideos.getCurrent('key', $obj) || false
					;


					// if (config['offset']==2) config['offset'] = 0;

					if (key){
						bindHistory = (typeof config['bindHistory'] ==='undefined') || ( config['bindHistory'] ) ? true : false;
						config['key'] = key;
						res[key]={
							'firstInit' : true,
							'action' : 'getData',
							'config' : config
							// 'sendData' : $.extend(config, { 'action' : 'getData' })
						};
					}
				});

				console.log('initialize history: ', res);



				if (res && bindHistory){
					that.set(hash, res);
					// that.set(null, res);
					// that.eventCounter = 5;
					// that.set(hash, res, awesomeVideos.eventCounter);
				}

				History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
				    var firstInit = false,
				    		State = History.getState(); // Note: We are using History.getState() instead of event.state
				    console.log ('history binded', State, arguments);
				    // if ( State.title == 'initialize' ){
				    if ( State.title == 0 ){
				    	// попали на титульную страницу
				    	// если оффсет ==1 то нам нужно сбрасывать его на 0 иначе будем видеть записи с 1-й
				    	// State.data.sendData.offset = 0;
				    	// firstInit = true;
				    	// alert ('нулевое состояние');
				    }else{
				    	// awesomeVideos.eventCounter = State.title;
				    	// awesomeVideos.eventCounterPrev = awesomeVideos.eventCounter;
				    }

				    // if ( awesomeVideos.eventCounter && awesomeVideos.eventCounter == State.title ){
				    // if ( (typeof (State.data.updateState) !=='undefined') && State.data.updateState ){
				    if ( that.hashIsReplaceNow ){
				    	// alert ('сработала замена хеша');
				    // 	delete State.data.updateState;
				    // 	History.saveState(State);
				    // 	alert ('заменили - 3');
				    }else{
				    	awesomeVideos._reloadDataByKeys(State.data);
				    }

				    	// awesomeVideos._reloadPage(State.data);
				    	// awesomeVideos.sendData(State.data);
				    // console.log ('xxx', );
				});


        // var State = History.getState(); // Note: We are using History.getState() instead of event.state
        // console.log ('history00', this);
        // console.log ('history00-1=', History.getCurrentIndex() );
        // console.log ('history00-2=', History.getHash());
        // console.log ('history00-2=', this.get());


		    // History.Adapter.onDomLoad(function (window) {
		    //     console.log ('on DOM LOAD', arguments);
		    // })

			},
			getState: function() {
				if ( !History.enabled ) return {};
				return this.history.getState();
			},
			get: function() {
				var vars = {}, hash, splitter, hashes;
				if (!this.oldbrowser()) {
					var pos = window.location.href.indexOf('?');
					hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
					splitter = '&';
				}
				else {
					hashes = decodeURIComponent(window.location.hash.substr(1));
					splitter = '/';
				}

				if (hashes.length == 0) {return vars;}
				else {hashes = hashes.split(splitter);}

				for (var i in hashes) {
					if (hashes.hasOwnProperty(i)) {
						hash = hashes[i].split('=');
						if (typeof hash[1] == 'undefined') {
							vars['anchor'] = hash[0];
						}
						else {
							vars[hash[0]] = hash[1];
						}
					}
				}
				return vars;
			}

			,set: function(vars,props,title,replace) {
				console.info ('MS2', arguments);
				// alert ('stop');
				title = title || '';
				props = props || null;
				replace = replace || false;

				var that=this,
						hash = '',
						result = {};

				for (var i in vars) {
					if (vars.hasOwnProperty(i)) {
						// if (typeof result[i] == 'undefined') result[i] = vars[i];
						hash += '&' + i + '=' + vars[i];
					}
				}

				if (!this.oldbrowser()) {
					if (hash.length != 0) {
						hash = '?' + hash.substr(1);
					}
					// console.log('MS2: PPPUSH', result);
					console.log('MS2: PPPUSH', document.location.pathname + hash);
					title = awesomeVideos.rewriteTitle(title);
					if (replace){
						console.log('заменяем hash = replace',props, title);
						that.hashIsReplaceNow = true;
						this.history.replaceState(props, title, document.location.pathname + hash);
						that.hashIsReplaceNow = false;
					}else{
						that.hashIsReplaceNow = false;
						console.log('добавляем hash',props, title);
						this.history.pushState(props, title, document.location.pathname + hash);
					}
				}
				else {
					window.location.hash = hash.substr(1);
				}
			}
			,add: function(key, val) {
				var hash = this.get();
				hash[key] = val;
				this.set(hash);
			}
			,remove: function(key) {
				var hash = this.get();
				delete hash[key];
				this.set(hash);
			}
			,clear: function() {
				this.set({});
			}
			,oldbrowser: function() {
				return !(window.history && history.pushState);
			}
		}


	}
	awesomeVideos.initialize();
	window.awesomeVideos = awesomeVideos;
});