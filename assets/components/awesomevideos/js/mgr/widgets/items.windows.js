awesomeVideos.window.CreateItem = function(config) {
	config = config || {};
	this.ident = config.ident || Ext.id();
	Ext.applyIf(config, {
		title: _('awesomeVideos_item_update'),
		url: awesomeVideos.config.connectorUrl,
		autoHeight: true,
		baseParams: {
			action: 'mgr/video/update'
		},
		width: 900,
		closeAction: 'close',
		new_scripts: false, // храним список вновь загруженных скриптов
		already_loaded: false, // храним список вновь загруженных скриптов
		// ,closeAction : 'hide'
		// ,buttons : [{
		//     text    : 'Close',
		//     scope   : this,
		//     handler : function() {
		//         this.hide(); // not destroy !!! that comes later !!!
		//     }
		// }]
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}],
		listeners: {
			'close': {
				fn: function(obj, newval, prevval) {
					alert(1);
				},
				scope: this
			},
			'beforeclose': {
				fn: function(obj, newval, prevval) {
					alert(2);
				},
				scope: this
			},
			'beforehide': {
				fn: function(obj, newval, prevval) {
					alert(3);
				},
				scope: this
			},
			'destroy': {
				fn: function(obj, newval, prevval) {
					alert(5);
				},
				scope: this
			},
			'hide': {
				fn: function(obj, newval, prevval) {
					alert(4);
				},
				scope: this
			}
			/*            'beforehide' : function(window) {
	            // if (some_condition == true) {
	                Ext.Msg.confirm( 'Confirm close of window', 'You really wanna close this window ?', function( answer ) {
	                    if( answer == "yes" ) {
	                        window.destroy();
	                    }
	                });
	                return false;
	            // }

	        },
	        'destroy' : function(window) {
	              // do something after the window destruction //
	              alert('Another window smashed');
	        },
	        'beforeclose': function(window) {
	            Ext.Msg.confirm("Hey", "Are you sure you want to close?", function(answer) {
	                if (answer == "yes") {
	                    window.events.beforeclose.clearListeners();
	                    window.close();
	                }
	            });
	            return false;
	        }*/
		},
		loadScripts: function(code) {
			code = code || this.new_scripts;
			if (typeof(code) === "undefined" || code == "" || code == false) return;
			console.log("Загружаю скрипты");
			if (this.already_loaded == true) return;
			this.already_loaded = true;
			setTimeout(function() {
				// append all inline scripts to the body to execute these
				code.forEach(function(content) {
					var script = document.createElement('script');
					script.setAttribute('tv_already', '1')
					// в идеале нужно запоминать TV id каждого элемента в атрибутах
					// чтоб не удалять его .... хотя нет как он запуститься то иначе, удалять надо
					// НО что делать с RTE???
					script.innerHTML = content.replace(/<script(.*?)>/, '').replace(/<\/script(.*?)>/ig, '');
					document.body.appendChild(script);
					// console.log("SCRIPTS LOADING:",script.innerHTML);
				});
				// alert('0.5 секунды')
			}, 500)
		},
		addField: function(html) {
			html = html || "its empty";
			// Если версия ExtJS < 4, то
			// var frm = this.find('xtype', 'form')[0];
			// Если версия ExtJS >= 4, то
			// var frm = Ext.getCmp('modx-tabs');
			// var frm = this.fields["modx-tabs"];
			// var frm = this;
			var frm = Ext.getCmp('tabTvList');
			// frm.update('<div id="modx-panel-resource-tv"></div>');
			// frm.update(html);
			// var childPanel = Ext.getCmp('parentPanel').getComponent('childPanel09');
			console.log("ADDfield2", frm);
			// return;
			// this.doLayout();
			frm.add({
				xtype: 'modx-panel',
				html: 123
				// ,title: 'Panel TVS list'
				// ,items: [{
				// xtype: 'modx-panel'
				// xtype: 'modx-panel-resource-tv'
				// ,id: 'modx-panel-resource-tv'
				// ,html: html
				// ,autoEl  : {
				// html  : '<input type="submit" class="custom_loginbtn" value="Login" id="login"/>'
				// }
				// }]
				// ,id: 'modx-panel-resource-tv'
			});
			this.doLayout();
		},
		fields: [{
			xtype: 'modx-tabs',
			listeners: {
				'tabchange': function(curTab) {
					this.syncSize();
					// alert ("111");
					console.log("TAB change", curTab.activeTab.id);
					if (curTab.activeTab.id == "tabTvList") this.loadScripts();
				},
				scope: this
			}
			// ,autoHeight: true
			// ,autoWidth: true
			,
			deferredRender: false
			// ,autoScroll: true
			,
			forceLayout: true
			// ,width: 800
			// ,height: '70%'
			,
			bodyStyle: {
				maxHeight: '700px'
			},
			borderStyle: 'padding: 10px 10px 10px 10px;',
			border: true,
			defaults: {
				border: false,
				labelWidth: 100,
				autoHeight: true,
				bodyStyle: 'padding: 5px 8px 5px 5px;',
				layout: 'form',
				deferredRender: false,
				forceLayout: true
			},
			items: [{
					title: _('awesomeVideos_item_form_tab_main'),
					items: [{
							xtype: 'hidden',
							name: 'id'
						}, {
							xtype: 'xcheckbox',
							fieldLabel: _('awesomeVideos_item_active'),
							name: 'active',
							inputValue: 1
						}, {
							xtype: 'xcheckbox',
							fieldLabel: _('awesomeVideos_item_special'),
							name: 'special',
							inputValue: 1
						}, {
							xtype: 'modx-panel-tv-image',
							fieldLabel: _('awesomeVideos_item_image'),
							html: '<div id="image-preview" style=""></div>',
							name: 'image_container',
							width: '97%'
							// ,allowBlank: {if $params.allowBlank == 1 || $params.allowBlank == 'true'}true{else}false{/if}
							,
							msgTarget: 'under',
							items: [
								/*{
	                        xtype: 'hidden'
	                        ,name: 'tv'+this.tv
	                        ,id: 'tv'+this.tv
	                        ,value: this.value
	                    },*/
								{
									xtype: 'modx-combo-browser',
									browserEl: 'image',
									name: 'image',
									id: 'image',
									hideFiles: true,
									wctx: awesomeVideos.config.ctx,
									source: awesomeVideos.config.imageSourceId,
									hideSourceCombo: true,
									setValue: function(data) {
										// обновляем превью, это собятие срабатывает и при открытии окна и при выборе картинки
										this.constructor.prototype.setValue.apply(this, arguments);
										var d = Ext.get('image-preview');
										if (Ext.isEmpty(this.value)) {
											d.update('');
										} else {
											d.update('<img src="' + MODx.config.connectors_url + 'system/phpthumb.php?h=150&w=150&src=' + this.value + '&wctx=' + awesomeVideos.config.ctx + '&source=' + awesomeVideos.config.imageSourceId + '" alt="" />');
										}
									},
									listeners: {
										'select': {
											fn: function(data) {
												/*                                var d = Ext.get('image-preview');
	                                if (Ext.isEmpty(data.url)) {
	                                    d.update('');
	                                } else {
	                                    d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?h=150&w=150&src='+data.url+'&wctx='+awesomeVideos.config.ctx+'&source='+awesomeVideos.config.imageSourceId+'" alt="" />');
	                                    // d.update('<img src="'+MODx.config.connectors_url+'system/phpthumb.php?h=150&w=150&src='+data.url+'&wctx={$ctx}&source={$source}" alt="" />');
	                                }
	*/
												this.fireEvent('select', data);
											},
											scope: this
										},
										'change': {
											fn: function(obj, newval, prevval) {
												// срабатывает при вводе вручную
												obj.setValue(newval);
												this.fireEvent('change', obj);
											},
											scope: this
										}
									}
								}
							]
						}, {
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_id'),
							name: 'videoId',
							width: '98%',
							allowBlank: false
						}, {
							xtype: 'modx-combo',
							fieldLabel: _('awesomeVideos_item_topic'),
							// ,html:  '<div id="image-preview2" style="">777</div>'
							name: 'topic',
							width: '100%',
							anchor: '100%', // ширина элемента в окне
							url: awesomeVideos.config.connectorUrl,
							fields: ['id', 'topic'],
							triggerAction: 'all',
							mode: 'remote',
							displayField: 'topic',
							valueField: 'id',
							hiddenName: 'topic', // название поля в которое будет опроавленно значение valueField
							// если оно не равно значению displayField, то значение выпадающего списка при первом открытии будет пустовать до тех пор пока не тычнем на него
							hiddenValue: 'id', // если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение
							// ,inputValue: 'id'	// если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение
							baseParams: {
								action: 'mgr/video/gettopic'
							},
							allowBlank: true, // значит: можно оставлять пустым? (да,нет)
							// ,blankText: '5555444'
							emptyText: 'не выбрана', //надпись в поле если ничего не указано
							listeners: {
								'render': {
									fn: function(obj) {
										console.log("выввв", obj);
										// obj.setValue('666');
										console.log("выввв", obj.hiddenField);
										// this.value="77777";
										// this.fireEvent('render',obj);
										// obj.hiddenField.value('new value');
										// obj.hiddenField.value="test";
										// Ext.get(obj.hiddenField).setAttribute('value','1111');
										// Ext.select(obj.hiddenField).setValue('1111');
										// myform.getForm().findField('title').setValue('new value');
										// return 777;
									},
									scope: this
								},
								'change': {
									fn: function(obj, newval, prevval) {
										// срабатывает при вводе вручную
										// obj.setValue(newval);
										console.log("aaaa", arguments);
										this.fireEvent('change', obj);
									},
									scope: this
								}
							}
						}, {
							xtype: 'modx-combo',
							fieldLabel: _('awesomeVideos_item_source'),
							name: 'source',
							width: '100%',
							anchor: '100%', // ширина элемента в окне
							url: awesomeVideos.config.connectorUrl,
							fields: ['source'],
							displayField: 'source',
							valueField: 'source',
							baseParams: {
								action: 'mgr/video/getsources'
							},
							allowBlank: false,
							editable: true,
							forceSelection: false,
							typeAhead: true // you can also input text, not only choise from list
						}, {
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_author'),
							name: 'author',
							width: '98%',
							allowBlank: false
						}, {
							// xtype: 'numberfield'
							xtype: 'hidden',
							fieldLabel: _('awesomeVideos_item_duration'),
							name: 'duration',
							width: '98%',
							allowBlank: false
						}, {
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_name'),
							name: 'name',
							width: '98%',
							allowBlank: false
						}, {
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_keywords'),
							name: 'keywords',
							width: '98%',
							allowBlank: true
						}, {
							xtype: 'textarea',
							// xtype: 'richtext'
							fieldLabel: _('awesomeVideos_item_description'),
							id: 'description',
							cls: 'modx-richtext',
							name: 'description',
							width: '98%',
							height: 200,
							allowBlank: true
						}
						/*,{
	                    id: 'modx-content-below'
	                    ,border: false
	                }*/
					]
				}, {
					title: _('awesomeVideos_item_form_tab_tv'),
					id: 'tabTvList',
					items: [{
						xtype: 'modx-panel',
						title: 'Panel TVS list',
						id: 'panelTvList',
						width: 'auto',
						bodyStyle: 'padding: 5px 8px 5px 5px;',
						forceLayout: false,
						deferredRender: true
					}]
				}
				// прячем раздел "продвинутые" - нах не нужен
				/*,{
	            title: _('awesomeVideos_item_advanced')
	            ,items: [{
	                xtype: 'textarea'
	                ,fieldLabel: _('awesomeVideos_item_jsondata')
	                ,name: 'jsondata'
	                ,width: '98%'
	                ,height: 300
	                ,allowBlank: true
	            }]
	        }*/
			]
		}]
	});
	awesomeVideos.window.CreateItem.superclass.constructor.call(this, config);
	this.on('beforeClose', function() {
		// alert ('beforeClose');
		// MODx.unloadTVRTE();
	}, this);
};
Ext.extend(awesomeVideos.window.CreateItem, MODx.Window, {});
Ext.reg('awesomevideos-item-window-create', awesomeVideos.window.CreateItem);

// VidLister={};
// VidLister.window.Video = function(config) {}
// Ext.extend(VidLister.window.Video,MODx.Window,{});
// Ext.reg('vidlister-window-video',VidLister.window.Video);

// awesomeVideos.window.UpdateItem = function (config) {
// 	config = config || {};
// 	if (!config.id) {
// 		config.id = 'awesomevideos-item-window-update';
// 	}
// 	Ext.applyIf(config, {
// 		title: _('awesomevideos_item_update'),
// 		width: 550,
// 		autoHeight: true,
// 		url: awesomeVideos.config.connector_url,
// 		action: 'mgr/item/update',
// 		fields: this.getFields(config),
// 		keys: [{
// 			key: Ext.EventObject.ENTER, shift: true, fn: function () {
// 				this.submit()
// 			}, scope: this
// 		}]
// 	});
// 	awesomeVideos.window.UpdateItem.superclass.constructor.call(this, config);
// };
// Ext.extend(awesomeVideos.window.UpdateItem, MODx.Window, {
// 	getFields: function (config) {
// 		return [{
// 			xtype: 'hidden',
// 			name: 'id',
// 			id: config.id + '-id',
// 		}, {
// 			xtype: 'textfield',
// 			fieldLabel: _('awesomevideos_item_name'),
// 			name: 'name',
// 			id: config.id + '-name',
// 			anchor: '99%',
// 			allowBlank: false,
// 		}, {
// 			xtype: 'textarea',
// 			fieldLabel: _('awesomevideos_item_description'),
// 			name: 'description',
// 			id: config.id + '-description',
// 			anchor: '99%',
// 			height: 150,
// 		}, {
// 			xtype: 'xcheckbox',
// 			boxLabel: _('awesomevideos_item_active'),
// 			name: 'active',
// 			id: config.id + '-active',
// 		}];
// 	}
// });
// Ext.reg('awesomevideos-item-window-update', awesomeVideos.window.UpdateItem);