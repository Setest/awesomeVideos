awesomeVideos.grid.Items = function(config) {
    config = config || {};
    if (!config.id) {
        config.id = 'awesomevideos-grid-items';
    }

    var editor = new Ext.ux.grid.RowEditor({
        saveText: 'Update'
    });

    // console.warn("z5",config);
    Ext.applyIf(config, {
        id: 'awesomevideos-grid-items',
        loadMask: true,
        ddGroup: 'mygridDD',
        enableDragDrop: true // enable drag and drop of grid rows
        ,
        autosave: true // will automatically fire the 'updateFromGrid' processor
        ,
        preventSaveRefresh: 0
        // ,saveParams: {"zzz":555} // доп параметры при сохранении
        ,
        trackMouseOver: true // will highlight rows on hover
        ,
        url: awesomeVideos.config.connectorUrl,
        baseParams: {
            // action: 'mgr/video/getlist'
            action: 'mgr/items/getlist'

        },
        sm: new Ext.grid.CheckboxSelectionModel(),
        sortBy:'rank',
        sortDir:'DESC',
        save_action: 'mgr/items/updatefromgrid',
        viewConfig: {
            emptyText: 'No pages found',
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            // getRowClass: function (rec, ri, p) {
            //     return !rec.data.active
            //         ? 'awesomevideos-row-disabled'
            //         : '';
            // }
            listeners : {

             }
        },
        tbar: [{
            text: _('awesomeVideos_import'),
            handler: this.doImport
        }, {
            text: _('awesomeVideos_item_new'),
            handler: this.addVideo
        }, {
            xtype: 'tbfill'
        }, {
            xtype: 'textfield',
            name: 'query',
            width: 200,
            id: config.id + '-search-field',
            emptyText: _('awesomevideos_grid_search'),
            listeners: {
                render: {
                    fn: function(tf) {
                        // console.warn("z3", this.config);
                        // console.warn("z4", config);
                        tf.getEl().addKeyListener(Ext.EventObject.ENTER, function() {
                            this._doSearch(tf);
                        }, this);
                    },
                    scope: this
                }
            }
        }, {
            xtype: 'button',
            id: config.id + '-search-clear',
            text: '<i class="fa fa-times"></i>',
            listeners: {
                click: {
                    fn: this._clearSearch,
                    scope: this
                }
            }
        }],
        // plugins: [editor],   // прикольное inline редактирование правда пришлось от него пока отказаться
        // http://cpansearch.perl.org/src/VANSTYN/JavaScript-ExtJS-V3-3.4.11/share/ext-3.4.1/examples/grid/row-editor.html
        //
        // dnd - с помощью стандартного метода
        // ,plugins: [new Ext.ux.dd.GridDragDropRowOrder({
        //     copy: false
        //     ,scrollable: true
        //     ,targetCfg: {}
        //     ,listeners: {
        //         // 'afterrowmove': {fn:this.onAfterRowMove,scope:this}
        //         'beforerowmove': {fn:this._onBeforeRowMove,scope:this}
        //     }
        // })]

        listeners: {
            celldblclick : function(grid, rowIndex, columnIndex, e) {
                var row = grid.getStore().getAt(rowIndex),
                    fieldName = grid.getColumnModel().getDataIndex(columnIndex),
                    config = grid.getColumnModel().getColumnAt(columnIndex),
                    data = row.get(fieldName),
                    ids = this._getSelectedIds()
                ;

                if (ids.length > 1) return false;
                if (!config.editor){
                    this.updateVideo(grid, e, row);
                    console.log('cellClick-OOOOK', grid);
                }
                // console.log('cellClick-grid', grid);
                // console.log('cellClick-rec', record);
                // console.log('cellClick-fieldName', fieldName);
                // console.log('cellClick-config', config);
                // console.log('cellClick-data', data);
            },
            // rowDblClick: function (grid, rowIndex, e) {
                // console.log("DBL click",arguments);
                // var row = grid.store.getAt(rowIndex);
                // this.updateVideo(grid, e, row);
            // },

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
                            // NOTE:
                            // you may need to make an ajax call here
                            // to send the new order
                            // and then reload the store
                            // alternatively, you can handle the changes
                            // in the order of the row as demonstrated below
                            // ***************************************
                            var sm = grid.getSelectionModel();
                            var rows = sm.getSelections();

                            console.log("ds", ds);
                            console.log("sm", sm);
                            console.log("rows", rows);
                            console.log("dd", dd);
                            console.log("this", this);
                            console.log("data", data);
                            console.log("data-sel", data.selections);
                            // console.log("cindex", cindex);
                            // console.log("dd.getDragData", dd.getDragData(e));
                            //
                            // data.rowIndex - предыдущий индекс, тот откуда перетащили
                            // cindex - тот индекс который стал
                            // rows - тот что перетащили
                            // data.selection[0].data.id или data.selection[0].id - id документа который таскали
                            // как это увяжеться с сортировкой, может ее отключить???
                            // dd.getDragData - та строка куда бросили, dd.getDragData.selection[0].data.id - id элемента который сместился вниз
                            // ************************************
                            // load the grid store
                            //  after the grid has been rendered
                            // ds.save({"a":123});
                            // ds.updateRecord({"a":123});
                            var target = dd.getDragData(e);
                            var target2 = sm.getSelected();
                            console.log("TARGET", target2);
                            // console.log("TARGET2", target.selections.get('id'));
                            MODx.Ajax.request({
                                url: awesomeVideos.config.connectorUrl,
                                params: {
                                    action: 'mgr/items/drag',
                                    register: 'mgr',
                                    id: rows[0].id,
                                    targetId: target2.get('id')
                                },
                                listeners: {
                                    'success': {
                                        fn: function() {
                                            // grid.refresh();
                                            // return;
// console.log ("DND77",target);
//  чтоб не перегружать лишний раз таблицу, просто переставим местами
                                            if (target) {
                                                var cindex = target.rowIndex;
                                                cindex = cindex==0?1:cindex;
console.log ("cindex",cindex);
                                                if (typeof(cindex) != "undefined") {
                                                    for (i = 0; i < rows.length; i++) {
                                                        ds.remove(ds.getById(rows[i].id));
                                                    }
                                                    ds.insert(cindex, data.selections);
                                                    sm.clearSelections();
                                                }
                                                // MODx.fireResourceFormChange();  // кнопка сохранить
                                            }

                                            console.log('completeOOOO');
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
        // ,fields: ['id', 'active','special','chosen','image', 'source','source_detail', 'videoId', 'name', 'description', 'keywords','topic', 'author', 'duration', 'created', 'jsondata']
        ,
        fields: ['id', 'rank', 'active', 'special', 'chosen', 'image', 'source', 'source_detail', 'videoId', 'name', 'description', 'keywords', 'topic', 'author', 'duration', 'created', 'jsondata'],
        paging: true,
        border: true,
        frame: false,
        remoteSort: true,
        anchor: '97%',
        autoExpandColumn: 'name',
        columns: [{
            header: _('id'),
            dataIndex: 'id',
            sortable: true,
            width: 1
        }, {
            header: _('rank'),
            dataIndex: 'rank',
            sortable: true,
            hidden: true, // скрывает колонку,
            width: 1
        }, {
            header: _('awesomeVideos_item_active'),
            dataIndex: 'active',
            width: 2
            /*,renderer: function(value) {
                return "<input disabled='disabled' type='checkbox'" + (value ? "checked='checked'" : "") + " />";
            }*/
            ,
            editor: {
                xtype: 'combo-boolean',
                renderer: 'boolean',
                store: new Ext.data.SimpleStore({
                    fields: ['d', 'v'],
                    data: [
                        [_('yes'), 1],
                        [_('no'), 0]
                    ] // было true и false
                })
            }
        }, {
            header: _('awesomeVideos_item_special'),
            dataIndex: 'special',
            width: 2
            /*,renderer: function(value) {
                return "<input disabled='disabled' type='checkbox'" + (value ? "checked='checked'" : "") + " />";
            }*/
            ,
            editor: {
                xtype: 'combo-boolean',
                renderer: 'boolean',
                store: new Ext.data.SimpleStore({
                    fields: ['d', 'v'],
                    data: [
                        [_('yes'), 1],
                        [_('no'), 0]
                    ] // было true и false
                })
            }
        }, {
            header: _('awesomeVideos_item_chosen'),
            dataIndex: 'chosen',
            sortable: true,
            editor: {
                xtype: 'combo-boolean',
                renderer: 'boolean',
                store: new Ext.data.SimpleStore({
                    fields: ['d', 'v'],
                    data: [
                        [_('yes'), 1],
                        [_('no'), 0]
                    ] // было true и false
                })
            },
            width: 2
        }, {
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
        }, {
            header: _('awesomeVideos_item_name'),
            dataIndex: 'name',
            sortable: true,
            width: 10
        }, {
            header: _('awesomeVideos_item_source'),
            dataIndex: 'source',
            sortable: true,
            width: 4,
            hidden: true // скрывает колонку,
            ,
            hideable: true // снимает галочку в выпадающем списке видимых полей грида
        }, {
            header: _('awesomeVideos_item_source_detail'),
            dataIndex: 'source_detail',
            sortable: true,
            width: 4,
            hidden: true,
            hideable: true
        }, {
            header: _('awesomeVideos_item_videoId'),
            dataIndex: 'videoId',
            sortable: true,
            width: 4,
            hidden: true,
            hideable: true
        }, {
            header: _('awesomeVideos_item_topic'),
            id: 'topic',
            dataIndex: 'topic',
            sortable: true,
            width: 4,
            renderer: function(value, obj, curRow, x, y, jsonStore) {
                // срабатывает при построении грида, то что будет возвращено через return отобразиться на экране
                // но не попадет в value данного combobox-a !!!
                // return curRow.json.topic_val;
                return curRow.json.topic_val;
            },
            editor: {
                xtype: 'modx-combo',
                fieldLabel: _('awesomeVideos_item_topic')
                // ,html:  '<div id="image-preview2" style="">777</div>'
                ,
                name: 'topic',
                width: '100%',
                anchor: '100%' // ширина элемента в окне
                ,
                url: awesomeVideos.config.connectorUrl,
                fields: ['id', 'topic'],
                triggerAction: 'all',
                mode: 'remote',
                valueField: 'id',
                displayField: 'topic',

                // ,hiddenName : 'topic_val' // название поля в которое будет отправленно значение valueField
                // если оно не равно значению displayField, то значение выпадающего списка при первом открытии будет пустовать до тех пор пока не тычнем на него
                // ,hiddenValue: 'id'  // если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение
                // ,inputValue: 'id'    // если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение

                // store: {
                //     constructor: function() {
                //         this.superclass().constructor.call(this);
                //         // No need to call this, you're not adding any events
                //         // this.addEvents('load','beforeload');
                //         this.on('load', function(store,records,options) {
                //             alert (111);
                //             this.loaded = true;
                //         }, this);
                //     },
                // },

                // listeners: {
                //     scope: this,
                //     'select': function(combo,store,index) {
                //         console.log("ZZZ",arguments);
                //             // alert (123);
                //         return 123;
                //     }
                // },
                baseParams: {
                    action: 'mgr/items/gettopic'
                },
                allowBlank: true, // значит: можно оставлять пустым? (да,нет)
                emptyText: _('awesomeVideos_item_topic_empty'), //надпись в поле если ничего не указано
                valueNotFoundText: _('awesomeVideos_item_topic_notfound')   // если в store не нашел
            }
        }, {
            header: _('awesomeVideos_item_author'),
            dataIndex: 'author',
            sortable: true,
            width: 4
        }, {
            header: _('awesomeVideos_item_duration'),
            dataIndex: 'duration',
            sortable: true,
            width: 2,
            renderer: function(value) {
                if (typeof(value) === "undefined") return;
                var parsedTime = new Date(null, null, null, null, null, value).toTimeString().match(/\d{2}:\d{2}:\d{2}/)[0];
                return parsedTime;
            }
        }, {
            header: _('awesomeVideos_item_created'),
            dataIndex: 'created',
            sortable: true,
            width: 4
        }]
    });
    awesomeVideos.grid.Items.superclass.constructor.call(this, config);


    // если у нас используется локальное хранилище, то перегрузим грид изменив его store
    var editor = this.getColumnModel().getColumnById('topic').editor;
    console.log('EDITOR',editor);
    if (awesomeVideos.config.topicSource){
        // var res=Ext.applyIf(editor, {
        var res=Ext.override(editor, {
            mode: 'local',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: [
                    'id',
                    'topic'
                ],
                data: function(data){
                    var data = Ext.decode(data),
                        result = new Array;
                    for(var i=0 ; i < data.length; i++){
                        result.push( new Array ( data[i].id, data[i].topic) )
                    }
                    return result;
                }(awesomeVideos.config.topicSource)
            })
        })
    }
    editor.store.on('loadexception', function(proxy,options, response) {
        // отлавливаем ошибки в разделе topic
        // console.log(arguments);
        if (response.status==200 && Ext.decode(response.responseText).success==false){
            MODx.msg.status({
                title: _('awesomeVideos_err_ajax'),
                message: Ext.decode(response.responseText).message,
                delay: 3
            });
        }
    }, editor.store);


    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);

};
Ext.extend(awesomeVideos.grid.Items, MODx.grid.Grid, {
    windows: {},
    _onBeforeRowMove: function(dt, sri, ri, sels) {
        // вариант пересортировки при перетаскивании
        // не очень хороший так как требует прописывания и внесения доп проверок в метод save_callback от grid
        console.warn("DND-1", arguments);
        console.warn("DND-2", this);
        row = this.view.getRow(0);
        console.warn("DND-3", row);
        // var record = this.grid.store.getAt(row.rowIndex);
        var s = this.getStore();
        var sourceRec = s.getAt(sri);
        var belowRec = s.getAt(ri);
        var total = s.getTotalCount();
        sourceRec.set('rank', 666);
        sourceRec.commit();
        /* get all rows below ri, and up their rank by 1 */
        var brec;
        for (var x = (ri - 1); x < total; x++) {
            brec = s.getAt(x);
            if (brec) {
                brec.set('rank', x);
                brec.commit();
            }
        }
        console.warn("DND-4", sourceRec);
        // this.fireEvent('afteredit',sourceRec);
        var res = this.saveRecord({
            'record': sourceRec
        });
        console.warn("DND-5", res);
        // this.fireEvent('afterReorderGroup');
        return true;
    },
    _doSearch: function(tf, nv, ov) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
    _clearSearch: function(btn, e) {
        var searchBar = Ext.getCmp(this.config.id + '-search-field');
        Ext.getCmp('awesomevideos-grid-items-search-field');
        // console.warn("z0",config);
        console.warn("z1", this.config);
        console.warn("z2", searchBar);
        this.getStore().baseParams.query = '';
        searchBar.setValue('');
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },
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
            text: _('awesomeVideos_item_update'),
            handler: this.updateVideo
        }, {
            text: _('awesomeVideos_item_remove'),
            handler: this.removeVideo
        }];

        var ids = this._getSelectedIds();
        if (ids.length>1){
            m.shift();
        }
        this.addContextMenuItem(m);

        // var row = grid.getStore().getAt(rowIndex);
        // var menu = awesomeVideos.utils.getMenu(row.data['actions'], this, ids);

        // this.addContextMenuItem(menu);

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
                action: 'mgr/items/import',
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
    addVideo: function(btn, e) {
        if (typeof(this.VideoWindow) !== "undefined") {
            // MODx.unloadTVRTE();
            this.VideoWindow.close();
            this.VideoWindow.destroy();
            delete this.VideoWindow;
        }
        this.VideoWindow = this.VideoWindow || MODx.load({
            xtype: 'awesomevideos-item-window-create',
            listeners: {
                'success': {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
        this.VideoWindow.new_scripts = false;
        this.VideoWindow.already_loaded = false;
        this.VideoWindow.show(e.target);
        this.VideoWindow.setTitle(_('awesomeVideos_item_new'));
        this.VideoWindow.reset();
        MODx.loadRTE('description'); // запускаем WYSIWYG
        this.getTVs(this.VideoWindow, e, true);
    },
    updateVideo: function(btn, e, row) {
        var record = this.menu.record || row.data;
        if (typeof row !=='undefined'){
            record=row.data;
        }else if(typeof this.menu !=='undefined'){
            record=this.menu.record ;
        }else{ return; }
        // console.log("THIS",this);
        // console.log("THIS MENU",this.menu);
        // console.log("THIS MENU record",this.menu.record);
        // console.warn("THIS MENU record233",record);
        // console.log(typeof(this.VideoWindow));
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
            xtype: 'awesomevideos-item-window-create',
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
        this.VideoWindow.setTitle(_('awesomeVideos_item_update'));
        this.VideoWindow.setValues(record);
        MODx.loadRTE('description'); // запускаем WYSIWYG
        // console.log("target",e);
        // console.log("VideoWindow",this.VideoWindow);
        this.getTVs(this.VideoWindow, e);
    },
    removeVideo: function() {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }

        MODx.msg.confirm({
            title: ids.length > 1
                ? _('awesomeVideos_items_remove')
                : _('awesomeVideos_item_remove'),
            text: ids.length > 1
                ? _('awesomeVideos_items_remove_confirm')
                : _('awesomeVideos_item_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/items/remove',
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
    getTVs: function(VideoWindow, e) {
        var selectedRecordsArray = this.getSelectionModel().getSelections();
        selected = [];
        Ext.each(selectedRecordsArray, function(item) {
            selected.push(item.data.id);
        });
        console.log("grid target:", selected);
        // return;
        MODx.Ajax.request({
            url: awesomeVideos.config.connectorUrl,
            params: {
                action: 'mgr/items/gettvs',
                register: 'mgr',
                "selected[]": selected
            },
            listeners: {
                'success': {
                    fn: function(responce, options, status) {
                        // console.fireEvent('complete');
                        // console.log("TVS:",responce);
                        console.log("INSIDE:", VideoWindow);
                        // console.log("testing:",VideoWindow.el.__proto__.down('tabTvList'));
                        // console.log("testing:",VideoWindow.items[0].getForm());
                        // var clickedElement = Ext.getCmp('id').el.child('>');
                        // VideoWindow.el.setHTML("hello");
                        // VideoWindow.update('hello');
                        // VideoWindow.el.get('tvslist').update(responce.output);
                        // VideoWindow.show(e.target);
                        var insertedCode = responce.output.replace(/<script(.*?)>/, '').replace(/<\/script(.*?)>/ig, '');
                        Ext.getCmp('panelTvList').update(insertedCode);
                        VideoWindow.doLayout(); // по идее обновляет форму.
                        // Ext.get('panelTvList').update(insertedCode);
                        // Ext.select('panelTvList').update(insertedCode);
                        // Ext.getCmp('panelTvList').setHTML(insertedCode);
                        // нужно прибить все предыдущие загруженные скрипты
                        // var panel = Ext.select('modx-content');
                        var oldScripts = Ext.select("script[tv_already='1']");
                        if (oldScripts.elements.length > 0) {
                            // oldScripts.remove();
                            oldScripts.elements.forEach(function(elem) {
                                elem.remove();
                                console.warn("OLD SCRIPTS:", elem);
                            })
                        }
                        VideoWindow["new_scripts"] = responce.scripts;
                        // VideoWindow.addField();
                        return;
                        // MODx.sleep(14); /* delay load event to allow FC rules to move before loading RTE */
                        setTimeout(function() {
                            // append all inline scripts to the body to execute these
                            responce.scripts.forEach(function(content) {
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
                        }, 3000)
                        return;
                        // Ext.getCmp('modx-panel-resource-tv').refreshTVs();
                        // Ext.get('awesomeVideos-temp-tv-container').update(responce.output);
                        MODx.refreshTVs();
                        MODx.fireEvent('load');
                        MODx.fireEvent('ready');
                        MODx.afterTVLoad();
                        // Ext.get('awesomeVideos-temp-tv-container').el.html(responce.output);
                        //
                        // Ext.select('awesomeVideos-temp-tv-container').update(777);
                        // VideoWindow.addField(responce.output);
                        // Ext.onReady();
                        // Ext.getCmp('tvslist').setHTML(responce.output);
                    },
                    scope: this
                }
            }
        });
    }
});
Ext.reg('awesomevideos-grid-items', awesomeVideos.grid.Items);