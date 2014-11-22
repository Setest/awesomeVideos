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
			wrapper: '.aw_wrap',
			contentSelector: ' .content',
			firstContentSelector: '> .content:eq(0)',
			log: '.aw_log',
			defScrollSize: 0,
			paddingBottom: 30,	// величина пикселей не доходя до низа которых у нас начнется загрузка, к примеру скролинг не докурутили до 100px до низа, а загрузка уже началась

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
		// sliders: {},
		wrap : {}, // массив ссылок на объекты, ключами которых являются ключи
		initialize: function(selector) {
			var that=this;
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

		// <li class="control"><a[[+classes]][[+title]] href="[[+href]]?[[+pageVarKey]]=1">First</a></li>
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

		_checkHeightLimit: function($obj) {
			var height = false,
					scrollCanSeen = false
			;

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
				$obj.css({
					// 'height' : height,
					'max-height' : height,
				});
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


			if (_$aw_wrap.length ){

				_$window.unbind( '.pageScrolling').bind('scroll.pageScrolling', that._checkScroll.bind(this) );
				_$aw_wrap.unbind( '.pageScrolling').bind('scroll.pageScrolling', that._checkScroll.bind(this) );

				_$aw_wrap.each(function (index, obj) {
					var $obj = $ ( obj ),
				      maxHeight = $obj.css('max-height'),
							objProp = $obj.data('aw-config'),
							heightLimit = that._checkHeightLimit($obj),	// хранит маинимальную высоту и данные о том есть ли скролинг у элемента
							aw_scrollType = $obj.data('aw-scrolltype')
					;
					// проходимся по каждому элементу и выясняем есть ли у него ограничения по высоте
					// если они есть, но скролинга не видно, и balance > 0, то запускаем ajax.
					// если ограничения нет и нет среди родителей элемента aw_wrap
					// то делаем ограничение по body
					// если у body нет скролинга и balance > 0, запускаем ajax, если скролинг не появился зацикливаем ajax.
					//
					// в ходе проверки каждый элемент обозначаем как проверенный и к какому типу скролинга относиться, body или content
					console.log ("Текущий wrap", heightLimit ,$obj);

					// делаем проверку на принадлежность к BODY.
					if ( !aw_scrollType ){
						aw_scrollType = ($obj.parents(that.options.wrapper).length) ? 'element' : 'body';
						// $.data($obj,'aw-scrolltype',aw_scrollType);
						if ( maxHeight && maxHeight !== 'none' ) aw_scrollType = 'element';
						$obj.attr('data-aw-scrolltype',aw_scrollType);

						if (!objProp.balance) return; // значит все показали не будем лишний раз ничего вытаскивать

						if (( !_bodyScroll && aw_scrollType=='body' ) || ( !heightLimit.scrollCanSeen && aw_scrollType=='element' )){
							console.log ("Скролинга нет - запускаем AJAX");
							// console.log ("Мало данных запускаем получение через ajax", balance);
							// that._checkScroll({'currentTarget':$wrap});
						}else{
							console.log ("Скролинг есть ниче не делаем");
						}
					}


				});
			}




			// т.к. этот метод запускается один раз, то нам нужно
			// пробежаться по всем элементам .content или просто вызывать у них событие scroll
			// _$body.trigger( "scroll" );


			// console.log('bindScroll',this);
		},

		_scrollObserve: function($obj, _isWindow) {
			console.log ('Вычисляем размер скрола и выполянем загрузку данных по необходимости', $obj);
			_isWindow = _isWindow || false;
			var that = this,
					_$window = $(window),
					_$scroll = _isWindow ? _$window : $obj,
					paddingHor = parseInt($obj.css('padding-top')) + parseInt($obj.css('padding-bottom')),
					borderHor = parseInt($obj.css('border-top')) + parseInt($obj.css('border-bottom')),

					$inner = $obj.find(this.options.firstContentSelector),
					outerBottom = $obj.offset().top+$obj.outerHeight(),
					innerBottom = $inner.offset().top+$inner.outerHeight()-this.options.paddingBottom
          // data = $obj.data('jscroll'),
          // borderTopWidth = parseInt($obj.css('borderTopWidth')),
          // borderTopWidthInt = isNaN(borderTopWidth) ? 0 : borderTopWidth,
          // iContainerTop = parseInt($obj.css('paddingTop')) + borderTopWidthInt,
          // iTopHeight = _isWindow ? _$scroll.scrollTop() : $obj.offset().top,
          // innerTop = $inner.length ? $inner.offset().top : 0,
          // iTotalHeight = Math.ceil(iTopHeight - innerTop + _$scroll.height() + iContainerTop)
			;

			if (
					(_isWindow && _$window.scrollTop() >= $obj.offset().top + $obj.outerHeight() - window.innerHeight- (paddingHor+borderHor))
					||
					( !_isWindow &&  outerBottom >= innerBottom )
				)
			{
				// console.log('GLOBAL');
				console.log('LOADING',outerBottom,innerBottom);

			}
		},

		_checkScroll: function($obj) {
			var that=this,
					$target = $($obj.currentTarget)
			;
			console.log ('СРАБОТАЛО СОБЫТИЕ!!!', $target);

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
					break;
				}
			}

			// console.log('wrap',$wrap);
			return result;
		},
		events: {
			showPage: function(event) {
				this.preventDefault(event);
				console.log('showPage',arguments);
				// выясняем есть ли параметр page и если есть вытаскиваем его и отправляем в метод
				// var page = url('?page', event.target.href) || 1,

				var targetUrl = URI.parse(event.target.href),
						urlParams = URI.parseQuery(targetUrl.query) || {},
						page = ( typeof urlParams['page']!=='undefined' && urlParams['page'] ) ? urlParams['page'] : 1,
					  params={'page':page};
				// console.log('bbb',page);

				return this.events.showMore.apply(this,[event,params]);
			},

			getData: function(event, params) {
				params = params || {};
				this.preventDefault(event);

				// нужно найти родителя самого первого уровня
				var that = this,
					$body=$('body'),
					$target = $(event.currentTarget),
					$wrap = $(that.options.wrapper+':first', $body),
					$content = $wrap.find('> '+that.options.contentSelector),
					$paging = $wrap.find('> .paging'),
					href = $target.attr('href') || '',
					targetUrl = URI.parse(href),
					urlParams = URI.parseQuery(targetUrl.query) || {}
				;

				console.log(targetUrl);
				console.log(urlParams);

				// $content.html(123);
				// $paging.html(555);
				// return;

				$.ajax({
					url: "[[+actionUrl]]",
					cache: false,
					type: "POST",
					// data: $.extend(urlParams,{'log_status':1,'action':'getData','return_type':'json'}),
					data: $.extend(urlParams,{'action':'getData','return_type':'json'}),
					dataType:'json',
					success: function(responce){
						console.log('success');
						if (responce.success==true){

							// var newConfig = $.extend(config, {'limit':responce.limit,'offset':responce.offset}),
							// 		$firstContent = $wrap.find('.content').filter(':first');

							// замещаем конфигурацию
							// that.setConfig($wrap, newConfig);

							// добавляем content
							responce.log = responce.log || '';
							$(that.options.log).html( responce.log );
							$content.html( responce.data);
							$paging.html(responce.paging);
							// console.info(responce.log);
							// console.info(config);
							var $carousels=$content
								.find('.aw_wrap[data-aw-pagination="carousel"]')
								.each(function() {
									// $( this ).addClass( "foo" );
									that.createCarousel( $( this ).find(that.options.contentSelector).filter(':first') );
									// console.log ('www',this );
							});


						}
						else {
							alert (json.message);
						}
					},
					error: function(e) {
						console.log(e.message);
					}
				});
			},
			showMore: function(event, params) {
				params = params || {};
				// console.log('showMore',arguments);
				// console.log('showMore2',this);
				this.preventDefault(event);

				var that = this,
						action='showMore',
						$target = $(event.currentTarget),
						$wrap=this.getCurrent('object', $target),
						key=this.getCurrent('key', $target),
						config=this.getCurrent('config', $target)
						;

				console.log('ShowMore',this);
				console.log('event',event);
				console.info('parent Key = ', key);

				console.log('connectorUrl = [[+actionUrl]]');


				// внедряем полученные данные
				$.ajax({
					url: "[[+actionUrl]]",
					cache: false,
					type: "POST",
					data: $.extend(config,params,{'action':'showMore','return_type':'json'}),
					dataType:'json',
					beforeSend: function(html){
						/* console.log('beforesend'); */
						// $('body').addClass("[[+clsGalRecursiveSeeMoreLoading]]");
						// see_more_albShowFull_waiting=true;
					},
					complete: function(html){
						// выключаем анимацию

						console.log('compl');
						// $('body').removeClass("[[+clsGalRecursiveSeeMoreLoading]]");	// не сувать в complete
					},
					success: function(responce){
						console.log('success');
						if (responce.success==true){

							var newConfig = $.extend(config, {'limit':responce.limit,'offset':responce.offset}),
									$firstContent = $wrap.find(that.options.contentSelector).filter(':first');

							// замещаем конфигурацию
							that.setConfig($wrap, newConfig);

							// добавляем content
							// this.wrap[key].find('.content').append(responce.data);
							responce.log = responce.log || '';
							$(that.options.log).html( responce.log );

							// console.info(responce.log);
							console.info(444,config);

							// выясняем тип пагинации и в зависимости от этого изменем ее.
							// that.setPaging();
							// var $tempData=$('<div>').attr({
							// 	class: 'aw-temp'
							// }).html(responce.data);


							switch (config.pagination){
								case 'carousel':
									console.info($firstContent);
									$firstContent.html(responce.data);
									break;
								case 'snippet':
									$firstContent.html(responce.data);
									$('> .paging', $wrap).html(responce.paging);
									break;
								case 'scroll':
									$firstContent.append(responce.data);
								case 'button':
								default:
									$firstContent.append(responce.data);
									// $newData = $tempData.html().appendTo($firstContent);
									// $newData = $firstContent.append(responce.data);
									// $wrap.find('.content').filter(':first').append(responce.log + responce.data);
									$('> .paging', $wrap).html(responce.paging);
									break;
							}


							// проверяем есть ли у нас карусель в добавленном содержимом
							// var $carousels=$firstContent.find('.aw_wrap');
							var $carousels=$firstContent
								.find('.aw_wrap[data-aw-pagination="carousel"]')
								.each(function() {
									// $( this ).addClass( "foo" );
									that.createCarousel( $( this ).find(that.options.contentSelector).filter(':first') );
									// console.log ('www',this );
							});
							// console.log ('ZZZ',$carousels );

							// console.log ('vvv',$tempData );
							// console.log ('XXX',$firstContent );
							// console.log ('qqq',$newData );
							// that.createCarousel($firstContent);


						}
						else {
							alert (json.message);
						}
					},
					error: function(e) {
						console.log(e.message);
					}
				});






			}
		},
		preventDefault: function(e) {
			if (e.preventDefault) {
				e.preventDefault();
			} else {
				e.returnValue = false;
			}
		}
	}
	awesomeVideos.initialize();
});