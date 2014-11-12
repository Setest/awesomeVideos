$(function() {
	'use strict';
	var awesomeVideos = {
		options: {
			wrapper: '.aw_wrap',
			results: '#mse2_results',
			total: '#mse2_total',
			pagination: '#mse2_pagination',
			// sort: '#mse2_sort',
			// limit: '#mse2_limit',
			// slider: '.mse2_number_slider',
			// pagination_link: '#mse2_pagination a',
			// sort_link: '#mse2_sort a',
			// tpl_link: '#mse2_tpl a',
			// selected_tpl: '<a href="#" data-id="[[now]] [[*id]]" class="mse2_selected_link"><em>[[+title]]</em><sup>x</sup></a>',
			active_class: 'active',
			disabled_class: 'disabled',
			disabled_class_fieldsets: 'disabled_fieldsets',
			prefix: 'mse2_'
		},
		// sliders: {},
		wrap : {}, // массив ссылок на объекты, ключами которых являются ключи
		initialize: function(selector) {
			var that=this;
			console.log('awesomeVideos initialize"', this);
			$(document).on("click", ".btn.showMore", that.events.showMore.bind(this));
			// $(document).on("click", ".paging a[href*='page=']", that.events.showPage.bind(this));
			$(document).on("click", ".paging .pagination a", that.events.showPage.bind(this));
			$(document).on("click", "a.link", that.events.getData.bind(this));
			this.createCarousel($('.aw_wrap_video > .content'));
		},

		// <li class="control"><a[[+classes]][[+title]] href="[[+href]]?[[+pageVarKey]]=1">First</a></li>

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
					$wrap = $(awesomeVideos.options.wrapper+':first', $body),
					$content = $wrap.find('> .content'),
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
					data: $.extend(urlParams,{'log_status':1,'action':'getData','return_type':'json'}),
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
							$('.log').html( responce.log );
							$content.html( responce.data);
							$paging.html(responce.paging);
							// console.info(responce.log);
							// console.info(config);
							var $carousels=$content
								.find('.aw_wrap[data-aw-pagination="carousel"]')
								.each(function() {
									// $( this ).addClass( "foo" );
									that.createCarousel( $( this ).find('.content').filter(':first') );
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
					data: $.extend(config,params,{'log_status':1,'action':'showMore','return_type':'json'}),
					dataType:'json',
					beforeSend: function(html){
						/* console.log('beforesend'); */
						// $('body').addClass("[[+clsGalRecursiveSeeMoreLoading]]");
						// see_more_albShowFull_waiting=true;
					},
					complete: function(html){
						// see_more_albShowFull_waiting=false;
						console.log('compl');
						// $('body').removeClass("[[+clsGalRecursiveSeeMoreLoading]]");	// не сувать в complete
					},
					success: function(responce){
						console.log('success');
						if (responce.success==true){

							var newConfig = $.extend(config, {'limit':responce.limit,'offset':responce.offset}),
									$firstContent = $wrap.find('.content').filter(':first');

							// замещаем конфигурацию
							that.setConfig($wrap, newConfig);

							// добавляем content
							// this.wrap[key].find('.content').append(responce.data);
							responce.log = responce.log || '';
							// console.info(responce.log);
							console.info(config);

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
									that.createCarousel( $( this ).find('.content').filter(':first') );
									// console.log ('www',this );
							});
							// console.log ('ZZZ',$carousels );

							// console.log ('vvv',$tempData );
							// console.log ('XXX',$firstContent );
							// console.log ('qqq',$newData );
							// that.createCarousel($firstContent);


/*							var message = jQuery.parseJSON(json.message);
							if ($see_more==true){
								$("[[+parentCls]]").append(message.html);
							}
							else {
								$("[[+parentCls]]").html(message.html);
							}
							console.log('offset: '+message.offset);
							console.log('Query: '+message.query);
							console.log('Total: '+message.total);
							console.log('Leftover: '+message.leftover);
							// вставляем в плейсхолдер кнопки наши данные
							if (message.leftover==0) {
								// console.log('hidddd');
								// скрываем кнопку так как показали все данные
								$(".[[+clsGalRecursiveSeeMoreButton]]").addClass("hide");
								see_more_albShowFull=true;
							}
							else{
								see_more_albShowFull=false;
								if (typeof GetInfinityScroll == 'function'){
									// так как при открытии нового альбома мы не знаем
									// заполняют ли изображения весь экран, нужно запустить функцию проверки
									// которая в случае необходимо догрузит оставшиеся изображения
									GetInfinityScroll();
								}

								$(".[[+clsGalRecursiveSeeMoreButton]]").removeClass("hide");
								$(".[[+clsGalRecursiveSeeMoreButton]] .leftover").html(message.leftover);
								if ($counttype=='balance') {
									$(".[[+clsGalRecursiveSeeMoreButton]] .total").html(message.balance);
								}
								else {
									$(".[[+clsGalRecursiveSeeMoreButton]] .total").html(message.total);
								}
							}
							recreate_lightbox();

							// запускаем lazyload если он у нас определен
							if (typeof recreate_lazyload == 'function'){
								recreate_lazyload();
							}*/

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