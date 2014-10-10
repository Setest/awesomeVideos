awesomeVideos.panel.Home = function(config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		/*
		 stateful: true,
		 stateId: 'awesomevideos-panel-home',
		 stateEvents: ['tabchange'],
		 getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
		 */
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('awesomeVideos') + '</h2>',
			cls: 'modx-page-header',
			style: {
				margin: '15px 0'
			}
		}, {
			xtype: 'modx-tabs',
			defaults: {
				border: false,
				autoHeight: true
			},
			border: true,
			hideMode: 'offsets',
			items: [{
					title: _('awesomeVideos_items'),
					layout: 'anchor',
					// ,border: false
					// ,defaults: { autoHeight: true, border: false }
					items: [{
						html: _('awesomeVideos_intro_msg'),
						cls: 'panel-desc',
					}, {
						xtype: 'awesomevideos-grid-items',
						cls: 'main-wrapper',
					}]
				}
				, {
                title: "Плейлисты",
                border: false,
                defaults: {
                    autoHeight: true,
                    border: false
                },
                items: [{
                    xtype: 'awesomevideos-grid-playlists',
                    preventRender: true
                }]
            }
			]
		}]
	});
	awesomeVideos.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(awesomeVideos.panel.Home, MODx.Panel);
Ext.reg('awesomevideos-panel-home', awesomeVideos.panel.Home);