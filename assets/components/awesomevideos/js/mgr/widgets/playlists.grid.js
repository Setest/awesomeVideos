awesomeVideos.grid.Playlists = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'awesomevideos-grid-playlists'

        ,loadMask: true
        ,ddGroup:'mygridDD'
        ,enableDragDrop: true // enable drag and drop of grid rows
        ,autosave: true // will automatically fire the 'updateFromGrid' processor
        ,preventSaveRefresh: 0
        // ,saveParams: {"zzz":555} // доп параметры при сохранении

        ,trackMouseOver:true  // will highlight rows on hover

        ,url: awesomeVideos.config.connectorUrl
        ,baseParams: {
            action: 'mgr/playlists/getlist'
        }
        ,save_action: 'mgr/playlists/updateFromGrid'

        ,viewConfig: {
            emptyText: 'No pages found',
            forceFit: true
        }
        ,sm: new Ext.grid.CheckboxSelectionModel()
        ,sortBy:'rank'
        ,sortDir:'DESC'
        ,fields: ['id','rank','active','channel','channelId','user','playlist','playlistId']
        ,paging: true
        ,border: true
        ,frame: false
        ,remoteSort: true
        ,anchor: '97%'
        ,autoExpandColumn: 'name'
        ,columns: [{
            header: _('id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 1
        }, {
            header: _('rank'),
            dataIndex: 'rank',
            sortable: true,
            // hidden: true, // скрывает колонку,
            width: 1
        },{
            header: _('awesomeVideos_item_active')
            ,dataIndex: 'active'
            ,width: 2
            ,editor: {
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
                ,store: new Ext.data.SimpleStore({
                    fields: ['d','v']
                    ,data: [[_('yes'),1],[_('no'),0]]   // было true и false
                })
            }
        },{
            header: _('awesomeVideos_item_channel')
            ,dataIndex: 'channel'
            ,sortable: true
            ,width: 10
        },{
            header: _('awesomeVideos_item_channelId')
            ,dataIndex: 'channelId'
            ,sortable: true
            ,width: 10
        },{
            header: _('awesomeVideos_item_user')
            ,dataIndex: 'user'
            ,sortable: true
            ,width: 4
            ,hidden: false // скрывает колонку,
            ,hideable: false // снимает галочку в выпадающем списке видимых полей грида
        },{
            header: _('awesomeVideos_item_playlist')
            ,dataIndex: 'playlist'
            ,sortable: true
            ,width: 4
            // ,hidden: true
            // ,hideable: true
        },{
            header: _('awesomeVideos_item_playlistId')
            ,dataIndex: 'playlistId'
            ,sortable: true
            ,width: 4
            // ,hidden: true
            // ,hideable: true
        }
        ]
        ,tbar: [
            {
                text: _('awesomeVideos_playlists_import')
                ,handler: this.doImport
            },{
                text: _('awesomeVideos_playlists_autofill')
                ,handler: this.relatedVideos
            },{
                text: _('awesomeVideos_playlist_new'),
                handler: this.addVideo
            }
        ]
    });
	awesomeVideos.grid.Playlists.superclass.constructor.call(this, config);
};




Ext.extend(awesomeVideos.grid.Playlists, MODx.grid.Grid, {
	windows: {},

    getMenu: function() {
        var m = [{
                text: _('awesomeVideos_item_remove')
                ,handler: this.removeVideo
            }
        ];
        this.addContextMenuItem(m);
        return true;
    },
    doImport: function(btn, e) {
        var that = this,
            topic = '/awesomeVideosimport/'
        if (this.console == null || this.console == undefined) {
            // открываем консоль и сообщаем через topic где отслеживать события
            this.console = MODx.load({
                xtype: 'modx-console',
                title: _('awesomeVideos_import'),
                register: 'mgr',
                topic: topic,
                show_filename: 0,
                listeners: {
                    'shutdown': {
                        fn: function() {
                            // когда нажали на закрытие окна, происходит перезагрузка страницы
                            // по идее можно просто обновить таблицу, так будет быстрее.
                            // window.location.reload();
                            that.refresh();
                        },
                        scope: this
                    }
                }
            });
        };
        this.console.show(Ext.getBody());
        // отправляем запрос на импорт
        MODx.Ajax.request({
            url: awesomeVideos.config.connectorUrl,
            disableCaching: true,
            params: {
                action: 'mgr/playlists/import',
                register: 'mgr',
                topic: topic // сообщаем в каком топике будем размещать логи
                ,
                cacheKey: awesomeVideos.config.cacheKey || false
            },
            listeners: {
                'success': {
                    fn: function() {
                        this.console.fireEvent('complete');
                    },
                    scope: this
                }
            }
        });
        return true;
    }
    ,relatedVideos: function(btn,e) {
    }
    ,removeVideo: function() {
        MODx.msg.confirm({
            title: _('awesomeVideos_item_remove')
            ,text: _('awesomeVideos_item_remove.confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/playlists/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
});

Ext.reg('awesomevideos-grid-playlists', awesomeVideos.grid.Playlists);