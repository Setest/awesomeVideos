awesomeVideos.grid.Playlists = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'awesomevideos-grid-playlists';
    }

    Ext.applyIf(config,{
        id: 'awesomevideos-grid-playlists'

        ,loadMask: true
        ,ddGroup:'mygridDD'
        ,enableDragDrop: true // enable drag and drop of grid rows

        ,autosave: true // will automatically fire the 'updateFromGrid' processor
        ,save_action: 'mgr/playlists/updateFromGrid'
        ,preventSaveRefresh: false  // при false сбрасывает грид в случае успешного ответа от сервера
        ,save_callback: function(){
            // console.warn('save_callback',arguments);
        }
        ,save_failure_callback: function(responce, recordData){
            console.warn('save_failure_callback',arguments);
            recordData.record.set(recordData.field, recordData.originalValue);
        }

        ,trackMouseOver:true  // will highlight rows on hover

        ,url: awesomeVideos.config.connectorUrl
        ,baseParams: {
            action: 'mgr/playlists/getlist'
        }

        ,viewConfig: {
            emptyText: 'No pages found',
            forceFit: true
        }
        ,sm: new Ext.grid.CheckboxSelectionModel()
        ,sortBy:'rank'
        ,sortDir:'DESC'
        ,fields: ['id','rank','image','active','channel','channelId','user','playlist','playlistId','description','created']
        ,paging: true
        ,border: true
        ,frame: false
        ,remoteSort: true
        ,anchor: '100%'
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
            hidden: true, // скрывает колонку,
            width: 1
        },{
            header: _('awesomeVideos_item_active')
            ,dataIndex: 'active'
            ,width: 1
            ,editor: {
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
                ,store: new Ext.data.SimpleStore({
                    fields: ['d','v']
                    ,data: [[_('yes'),1],[_('no'),0]]   // было true и false
                })
            }
        },{
            header: _('awesomeVideos_item_image'),
            dataIndex: 'image',
            sortable: false,
            width: 2,
            renderer: function(value) {
                value = value || awesomeVideos.config.imageNoPhoto
                var source = '&source=' + awesomeVideos.config.imageSourceId;
                var testPath = value.toLowerCase().indexOf(awesomeVideos.config['sitePath'].toLowerCase());
                if (testPath !== -1) {
                    source = "";
                    // value.replace(awesomeVideos.config['sitePath'],"")
                }
                var params = "h=60&src=" + value + '&wctx=' + awesomeVideos.config.ctx + source;
                /*if (awesomeVideos.config.imageSourceId==""){
                    awesomeVideos.config.imageSourceId=0;
                }else{
                    // на всякий случай убираем из строки мусор
                    // value
                }
                */
                var phpthumb = MODx.config.connectors_url + 'system/phpthumb.php?' + params;
                var phpthumbimg = '<img src="' + phpthumb + '" alt="" />';
                return phpthumbimg;
            }
        },{
            header: _('awesomeVideos_item_channel')
            ,dataIndex: 'channel'
            ,sortable: true
            ,width: 2
            ,editor: {
                xtype: 'textfield',
            }
        },{
            header: _('awesomeVideos_item_channelId')
            ,dataIndex: 'channelId'
            ,sortable: true
            ,width: 2
            ,editor: {
                xtype: 'textfield',
            }
        },{
            header: _('awesomeVideos_item_user')
            ,dataIndex: 'user'
            ,sortable: true
            ,hidden: false // скрывает колонку,
            ,hideable: false // снимает галочку в выпадающем списке видимых полей грида
            ,width: 2
            ,editor: {
                xtype: 'textfield',
            }
        },{
            header: _('awesomeVideos_item_playlist')
            ,dataIndex: 'playlist'
            ,sortable: true
            ,width: 2
            ,editor: {
                xtype: 'textfield',
            }
            // ,hidden: true
            // ,hideable: true
        },{
            header: _('awesomeVideos_item_playlistId')
            ,id: 'playlistId'
            ,dataIndex: 'playlistId'
            ,sortable: true
            ,width: 2
            ,editor: {
                xtype: 'textfield'
            }
            // ,hidden: true
            // ,hideable: true
        },{
            header: _('awesomeVideos_item_description')
            ,dataIndex: 'description'
            ,sortable: true
            ,width: 2
            ,editor: {
                xtype: 'textarea',
                cls: 'modx-richtext',
            }
            // ,hidden: true
            // ,hideable: true
        },{
            header: _('awesomeVideos_item_created'),
            dataIndex: 'created',
            sortable: true,
            width: 2
        }
        ]
        ,tbar: [
            {
                text: _('awesomeVideos_playlists_import')
                ,handler: this.doImport
            },{
                text: _('awesomeVideos_playlists_synchronize')
                ,handler: this.relatedVideos
            },{
                text: _('awesomeVideos_playlist_new'),
                handler: this.createPlaylist
            }
        ]
        ,listeners: {
            // afteredit : function(data) {
            //     console.log('afteredit', arguments);
            // },
            // afterAutoSave : function() {
            //     console.log('afterAutoSave', arguments);
            // },
            celldblclick : function(grid, rowIndex, columnIndex, e) {
                var row = grid.getStore().getAt(rowIndex),
                    fieldName = grid.getColumnModel().getDataIndex(columnIndex),
                    config = grid.getColumnModel().getColumnAt(columnIndex),
                    data = row.get(fieldName),
                    ids = this._getSelectedIds()
                ;

                if (ids.length > 1) return false;
                if (!config.editor){
                    this.updatePlaylist(grid, e, row);
                    console.log('cellClick-OOOOK', grid);
                }
            },
            render: {
                scope: this,
                fn: function(grid) {
                    // Enable sorting Rows via Drag & Drop
                    // this drop target listens for a row drop
                    //  and handles rearranging the rows
                    var ddrow = new Ext.dd.DropTarget(grid.container, {
                        ddGroup: 'mygridDD',
                        copy: false,
                        notifyDrop: function(dd, e, data) {
                            var ds = grid.store;
                            var sm = grid.getSelectionModel();
                            var rows = sm.getSelections();

                            var target = dd.getDragData(e);
                            var target2 = sm.getSelected();
                            console.log("TARGET", target2);
                            // console.log("TARGET2", target.selections.get('id'));
                            MODx.Ajax.request({
                                url: awesomeVideos.config.connectorUrl,
                                params: {
                                    action: 'mgr/playlists/drag',
                                    register: 'mgr',
                                    id: rows[0].id,
                                    targetId: target2.get('id')
                                },
                                listeners: {
                                    'success': {
                                        fn: function() {
                                            //  чтоб не перегружать лишний раз таблицу, просто переставим местами
                                            if (target) {
                                                var cindex = target.rowIndex;
                                                cindex = cindex==0?1:cindex;
                                                // console.log ("cindex",cindex);
                                                if (typeof(cindex) != "undefined") {
                                                    for (i = 0; i < rows.length; i++) {
                                                        ds.remove(ds.getById(rows[i].id));
                                                    }
                                                    ds.insert(cindex, data.selections);
                                                    sm.clearSelections();
                                                }
                                                // MODx.fireResourceFormChange();  // кнопка сохранить
                                            }
                                        },
                                        scope: this
                                    }
                                }
                            });
                            // store.load();
                        }
                    })
                }
            }
        }
    });
	awesomeVideos.grid.Playlists.superclass.constructor.call(this, config);

    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);

};




// Ext.extend(awesomeVideos.grid.Playlists, MODx.grid.Grid, {
Ext.extend(awesomeVideos.grid.Playlists, MODx.grid.Grid, {
	windows: {},
    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();
        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }
        return ids;
    },
    getMenu: function() {
        var m = [{
            text: _('awesomeVideos_playlist_update'),
            handler: this.updatePlaylist
        }, {
            text: _('awesomeVideos_playlist_remove'),
            handler: this.removePlaylists
        }];

        var ids = this._getSelectedIds();
        // console.log('hhhh',ids);
        if (ids.length>1){
            m.shift();
        }
        this.addContextMenuItem(m);
        return true;
    },
    doImport: function(btn, e) {
        var that = this,
            topic = '/awesomeVideosimportPlaylists/'
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
    },
    relatedVideos: function(btn,e) {
        // синхронизация
        var that = this,
            topic = '/awesomeVideossynchronizePlaylists/'
        if (this.console == null || this.console == undefined) {
            // открываем консоль и сообщаем через topic где отслеживать события
            this.console = MODx.load({
                xtype: 'modx-console',
                title: _('awesomeVideos_synchronize'),
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
                action: 'mgr/playlists/synchronize',
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
    },
    removePlaylists: function() {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }

        MODx.msg.confirm({
            title: ids.length > 1
                ? _('awesomeVideos_playlists_remove')
                : _('awesomeVideos_playlist_remove'),
            text: ids.length > 1
                ? _('awesomeVideos_playlists_remove_confirm')
                : _('awesomeVideos_playlist_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/playlists/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    },
    createPlaylist: function(btn, e) {
        if (typeof(this.VideoWindow) !== "undefined") {
            // MODx.unloadTVRTE();
            this.VideoWindow.close();
            this.VideoWindow.destroy();
            delete this.VideoWindow;
        }
        this.VideoWindow = this.VideoWindow || MODx.load({
            xtype: 'awesomevideos-playlist-window-create',
            baseParams: {
                action: 'mgr/playlists/create'
            },
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        // console.log('zxzx',this.VideoWindow);
        // this.VideoWindow.new_scripts = false;
        this.VideoWindow.already_loaded = false;
        this.VideoWindow.show(e.target);
        this.VideoWindow.setTitle(_('awesomeVideos_playlist_new'));
        this.VideoWindow.reset();
        MODx.loadRTE('description'); // запускаем WYSIWYG
    },
    updatePlaylist: function(btn, e, row) {
        var record = this.menu.record || row.data;
        if (typeof row !=='undefined'){
            record=row.data;
        }else if(typeof this.menu !=='undefined'){
            record=this.menu.record ;
        }else{ return; }
        // console.log("THIS",this);
        if (typeof(this.VideoWindow) !== "undefined") {
            // нужно уничтожить окно
            console.log("нужно уничтожить окно");
            // MODx.unloadTVRTE();
            this.VideoWindow.close();
            this.VideoWindow.destroy();
            // Ext.get('description').remove();
            // this.VideoWindow.unbind();
            this.VideoWindow.remove();
            // this.VideoWindow.removeAll();
            // delete this.VideoWindow;
            delete this.VideoWindow;
        }
        this.VideoWindow = this.VideoWindow || MODx.load({
            // this.VideoWindow = MODx.load({
            xtype: 'awesomevideos-playlist-window-create',
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        // return;
        // console.log("DESTROY",this.VideoWindow);

        this.VideoWindow.new_scripts = false;
        this.VideoWindow.already_loaded = false;
        // this.VideoWindow.reset();    // обнуляет окно - фактически активирует его
        this.VideoWindow.show(e.target);
        this.VideoWindow.setTitle(_('awesomeVideos_playlist_update'));
        this.VideoWindow.setValues(record);
        MODx.loadRTE('description'); // запускаем WYSIWYG
        // console.log("target",e);
        // console.log("VideoWindow",this.VideoWindow);
    }
    ,saveRecord: function(e) {
        console.log('saveRecord11');
        e.record.data.menu = null;
        var p = this.config.saveParams || {};
        Ext.apply(e.record.data,p);
        var d = Ext.util.JSON.encode(e.record.data);
        var url = this.config.saveUrl || (this.config.url || this.config.connector);
        MODx.Ajax.request({
            url: url
            ,params: {
                action: this.config.save_action || 'updateFromGrid'
                ,data: d
            }
            ,listeners: {
                'success': {fn:function(r) {
                    if (this.config.save_callback) {
                        Ext.callback(this.config.save_callback,this.config.scope || this,[r]);
                    }
                    e.record.commit();
                    if (!this.config.preventSaveRefresh) {
                        this.refresh();
                    }
                    this.fireEvent('afterAutoSave',r);
                },scope:this}
               ,'failure': {fn:function(r) {
                    if (this.config.save_failure_callback) {
                        Ext.callback(this.config.save_failure_callback,this.config.scope || this, [r,e]);
                    }
                    e.record.commit();
                    // if (!this.config.preventSaveRefresh) {
                    //     this.refresh();
                    // }
                },scope:this}
            }
        });
    }
});

Ext.reg('awesomevideos-grid-playlists', awesomeVideos.grid.Playlists);