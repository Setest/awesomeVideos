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
            sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            forceFit: true
        }

        ,fields: ['id','active', 'channel','user','playlist','playlistId']
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
            ,hidden: true
            ,hideable: true
        },{
            header: _('awesomeVideos_item_playlistId')
            ,dataIndex: 'playlistId'
            ,sortable: true
            ,width: 4
            ,hidden: true
            ,hideable: true
        },{
            header: _('awesomeVideos_item_topic')
            ,dataIndex: 'topic'
            ,sortable: true
            ,width: 4
            ,renderer: function(value,obj,curRow,x,y,jsonStore) {
                // срабатывает при построении грида, то что будет возвращено через return? отобразиться на экране
                // но не попадет в value данного combobox-a
                // console.log("ZZZ",arguments);
                // alert (123);
                return curRow.json.topic_val;
            }
            ,editor:{
                    xtype: 'modx-combo'
                        ,fieldLabel: _('awesomeVideos_item_topic')
                        // ,html:  '<div id="image-preview2" style="">777</div>'
                        ,name: 'topic'
                        ,width: '100%'
                        ,anchor:'100%'  // ширина элемента в окне
                        ,url: awesomeVideos.config.connectorUrl
                        ,fields: ['id','topic']
                        ,triggerAction: 'all'
                        ,mode: 'remote'

                        ,valueField: 'id'
                        ,displayField: 'topic'

                        ,baseParams: { action: 'mgr/playlists/gettopic' }
                        ,allowBlank: true   // значит: можно оставлять пустым? (да,нет)
                        ,emptyText: 'не выбрана' //надпись в поле если ничего не указано
                        /*,listeners: {
                            "afterAutoSave": {
                                  scope: this,
                                  fn: function(grid) {
                                    alert (123);
                                    return 777;
                                }
                            }
                        }*/

                    }
        }]
        ,tbar: [{
                text: _('awesomeVideos_playlists_import')
                ,handler: this.doImport
            },{
                text: _('awesomeVideos_playlists_autofill')
                ,handler: this.addVideo
        }]
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
    }
    ,doImport: function(btn,e) {
        var that=this;
        if (this.console == null || this.console == undefined) {
            this.console = MODx.load({
                xtype: 'modx-console'
                ,title: _('awesomeVideos_import')
                ,register: 'mgr'
                ,topic: '/awesomeVideosimport/'
                ,show_filename: 0
                ,listeners: {
                    'shutdown': {fn:function() {
                        // когда нажали на закрытие окна, происходит перезагрузка страницы
                        // по идее можно просто обновить таблицу, так будет быстрее.
                        // window.location.reload();
                        that.refresh();
                    },scope:this}
               }
            });
        };
        this.console.show(Ext.getBody());
        // var rrr=this.console;
        // var rrr=this.console.VideoWindow.el.get('tvslist').update(responce.output);;
        // var eee=this.console.el.select( 'x-form-text');
        // var eee=Ext.select('panel[cls=x-form-text modx-console-text]');
        // var eee=Ext.select('x-form-text');
        // var eee=rrr.query("panel[cls~=x-form-text]")
        // var eee=rrr.down(".x-form-text")
        // var eee=rrr.down('#body');
        // var eee=Ext.ComponentQuery.query('panel[cls=x-form-text modx-console-text]');
        // console.log("CONS",eee);

        MODx.Ajax.request({
            url: awesomeVideos.config.connectorUrl
            ,disableCaching: true
            ,params: {
                // action: 'mgr/playlists/import'
                action: 'mgr/playlists/import_new'
                ,register: 'mgr'
                ,topic: '/awesomeVideosimport/'
            }
            ,listeners: {
                'success':{fn:function() {
                    this.console.fireEvent('complete');
                },scope:this}
            }
        });

        return true;

    }
    ,addVideo: function(btn,e) {
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