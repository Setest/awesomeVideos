awesomeVideos.window.CreatePlaylist = function(config) {
	config = config || {};
	this.ident = config.ident || Ext.id();
	Ext.applyIf(config, {

		autoHeight: false,
    width:Ext.getBody().getViewSize().width*0.8, //80%

		title: _('awesomeVideos_playlist_update'),
		url: awesomeVideos.config.connectorUrl,
		baseParams: {
			action: 'mgr/playlists/update'
		},
		closeAction: 'close',
		// new_scripts: false, // храним список вновь загруженных скриптов
		already_loaded: false,
		keys: [{
			key: Ext.EventObject.ENTER,
			shift: true,
			fn: function() {
				this.submit()
			},
			scope: this
		}],
		// listeners: {},
		fields: [{
			xtype: 'modx-tabs',
			listeners: {
				'tabchange': function(curTab) {
					this.syncSize();
					// alert ("111");
					// console.log("TAB change", curTab.activeTab.id);
				},
				scope: this
			},
			deferredRender: false,
			// ,autoScroll: true
			forceLayout: true,

			autoHeight: false,
			autoWidth: false,
			height:Ext.getBody().getViewSize().height*0.7,
	    // width:Ext.getBody().getViewSize().width*0.5, //80%
			bodyStyle: {
				// background: 'red',
				maxHeight: '700px',
				padding: '30px',
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
							xtype: 'checkboxgroup',
							fieldLabel: '',
							items: [{
								xtype: 'xcheckbox',
								name: 'active',
								inputValue: 1,
								fieldLabel: _('awesomeVideos_item_active'),
								// hideLabel: true,
								// boxLabel: _('awesomeVideos_item_active'),
								// labelAlign: 'right'
								labelStyle: 'color: green; float: left; margin-right: 10px;',
							}, {
								xtype: 'spacer'	// для того чтобы прижать чекбоксы влево
							}, ]
						}, {
							xtype: 'hidden',
							name: 'id'
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
										console.log('DDDDATA', data);
										// обновляем превью, это событие срабатывает и при открытии окна и при выборе картинки
										this.constructor.prototype.setValue.apply(this, arguments);
										var d = Ext.get('image-preview');
										if (!d) return;
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
						},{
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_channel'),
							name: 'channel',
							anchor: '90%',
							allowBlank: true
						},{
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_channelId'),
							name: 'channelId',
							anchor: '90%',
							allowBlank: true
						},{
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_playlist_user'),
							name: 'user',
							anchor: '90%',
							allowBlank: true
						},{
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_playlist'),
							name: 'playlist',
							anchor: '90%',
							allowBlank: false
						}, {
							xtype: 'textfield',
							fieldLabel: _('awesomeVideos_item_playlistId'),
							name: 'playlistId',
							anchor: '90%',
							allowBlank: true
						}, {
							xtype: 'textarea',
							// xtype: 'richtext'
							fieldLabel: _('awesomeVideos_item_description'),
							id: 'description',
							cls: 'modx-richtext',
							name: 'description',
							anchor: '90%',
							height: 200,
							allowBlank: true
						}
					]
				}
			]
		}]
	});
	awesomeVideos.window.CreatePlaylist.superclass.constructor.call(this, config);
	this.on('beforeClose', function() {
		// alert ('beforeClose');
		MODx.unloadTVRTE();
	}, this);
};
Ext.extend(awesomeVideos.window.CreatePlaylist, MODx.Window, {});
Ext.reg('awesomevideos-playlist-window-create', awesomeVideos.window.CreatePlaylist);