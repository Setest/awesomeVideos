awesomeVideos.grid.Items = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'awesomevideos-grid-items'

        ,loadMask: true
        ,ddGroup:'mygridDD'
        ,enableDragDrop: true // enable drag and drop of grid rows
        ,autosave: true // will automatically fire the 'updateFromGrid' processor
        ,preventSaveRefresh: 0
        // ,saveParams: {"zzz":555} // доп параметры при сохранении

        ,trackMouseOver:true  // will highlight rows on hover

        ,url: awesomeVideos.config.connectorUrl
        ,baseParams: {
            action: 'mgr/video/getlist'
        }
        ,save_action: 'mgr/video/updateFromGrid'

        ,viewConfig: {
            emptyText: 'No pages found',
            sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            forceFit: true
        }

        ,listeners: {
            // 'rowclick': {fn:function() {
            // 'afterRemoveRow': {fn:function() {
            // 'afterAutoSave': {fn:function() {
                // alert (777);
                // that.refresh();
            // },scope:this}
            "render": {
              scope: this,
              fn: function(grid) {

                  // Enable sorting Rows via Drag & Drop
                  // this drop target listens for a row drop
                  //  and handles rearranging the rows

                          var ddrow = new Ext.dd.DropTarget(grid.container, {
                              ddGroup : 'mygridDD',
                              copy:false,
                              notifyDrop : function(dd, e, data){

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
                                    if(dd.getDragData(e)) {
                                        var cindex=dd.getDragData(e).rowIndex;
                                        if(typeof(cindex) != "undefined") {
                                            for(i = 0; i <  rows.length; i++) {
                                                ds.remove(ds.getById(rows[i].id));
                                            }
                                            ds.insert(cindex,data.selections);
                                            sm.clearSelections();
                                         }
                                         // MODx.fireResourceFormChange();  // кнопка сохранить
                                     }
                                     console.log ("ds",ds);
                                     console.log ("sm",sm);
                                     console.log ("rows",rows);
                                     console.log ("dd",dd);
                                     console.log ("this",this);
                                     console.log ("data-sel",data.selections);
                                     console.log ("data",data);
                                     console.log ("cindex",cindex);
                                     console.log ("dd.getDragData",dd.getDragData(e));

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
                    MODx.Ajax.request({
                        url: awesomeVideos.config.connectorUrl
                        ,params: {
                            action: 'mgr/video/dnd'
                            ,register: 'mgr'
                        }
                        ,listeners: {
                            'success':{fn:function() {
                                console.log('completeOOOO');
                            },scope:this}
                        }
                    });
                              // store.load();
                                  }
                               })
                   }
               }
        }

        // ,fields: ['id', 'active','special','chosen','image', 'source','source_detail', 'videoId', 'name', 'description', 'keywords','topic', 'author', 'duration', 'created', 'jsondata']
        ,fields: ['id', 'active','special','chosen','image', 'source','source_detail', 'videoId', 'name', 'description', 'keywords','topic', 'author', 'duration', 'created', 'jsondata']
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
            /*,renderer: function(value) {
                return "<input disabled='disabled' type='checkbox'" + (value ? "checked='checked'" : "") + " />";
            }*/
            ,editor: {
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
                ,store: new Ext.data.SimpleStore({
                    fields: ['d','v']
                    ,data: [[_('yes'),1],[_('no'),0]]   // было true и false
                })
            }
        },{
            header: _('awesomeVideos_item_special')
            ,dataIndex: 'special'
            ,width: 2
            /*,renderer: function(value) {
                return "<input disabled='disabled' type='checkbox'" + (value ? "checked='checked'" : "") + " />";
            }*/
            ,editor: {
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
                ,store: new Ext.data.SimpleStore({
                    fields: ['d','v']
                    ,data: [[_('yes'),1],[_('no'),0]]   // было true и false
                })
            }
        },{
            header: _('awesomeVideos_item_chosen')
            ,dataIndex: 'chosen'
            ,sortable: true
            ,editor: {
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
                ,store: new Ext.data.SimpleStore({
                    fields: ['d','v']
                    ,data: [[_('yes'),1],[_('no'),0]]   // было true и false
                })
            }
            ,width: 2

        },{
            header: _('awesomeVideos_item_image')
            ,dataIndex: 'image'
            ,sortable: false
            ,width: 2
            ,renderer: function(value) {
                value=value||awesomeVideosConfig.imageNoPhoto
                var source='&source='+awesomeVideosConfig.imageSourceId;
                var testPath=value.toLowerCase().indexOf(awesomeVideosConfig['sitePath'].toLowerCase());
                if (testPath!==-1){
                    source="";
                    // value.replace(awesomeVideosConfig['sitePath'],"")
                }
                var params="h=60&src="+value+'&wctx='+awesomeVideosConfig.ctx+source;

                /*if (awesomeVideosConfig.imageSourceId==""){
                    awesomeVideosConfig.imageSourceId=0;
                }else{
                    // на всякий случай убираем из строки мусор
                    // value
                }
                */

                var phpthumb = MODx.config.connectors_url+'system/phpthumb.php?'+params;
                var phpthumbimg = '<img src="'+phpthumb+'" alt="" />';
                return phpthumbimg;
            }
        },{
            header: _('awesomeVideos_item_name')
            ,dataIndex: 'name'
            ,sortable: true
            ,width: 10
        },{
            header: _('awesomeVideos_item_source')
            ,dataIndex: 'source'
            ,sortable: true
            ,width: 4
            ,hidden: true // скрывает колонку,
            ,hideable: true // снимает галочку в выпадающем списке видимых полей грида
        },{
            header: _('awesomeVideos_item_source_detail')
            ,dataIndex: 'source_detail'
            ,sortable: true
            ,width: 4
            ,hidden: true
            ,hideable: true
        },{
            header: _('awesomeVideos_item_videoId')
            ,dataIndex: 'videoId'
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

                        // ,hiddenName : 'topic_val' // название поля в которое будет опроавленно значение valueField
                            // если оно не равно значению displayField, то значение выпадающего списка при первом открытии будет пустовать до тех пор пока не тычнем на него
                        // ,hiddenValue: 'topic_val'  // если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение
                        // ,inputValue: 'topic_val'    // если ниче не выбрали то по-умолчанию отправляется с полем hiddenName это значение

                        ,baseParams: { action: 'mgr/video/gettopic' }
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
        },{
			header: _('awesomeVideos_item_author')
            ,dataIndex: 'author'
            ,sortable: true
            ,width: 4
        },{
            header: _('awesomeVideos_item_duration')
            ,dataIndex: 'duration'
            ,sortable: true
            ,width: 2
            ,renderer: function(value) {
                if (typeof (value)==="undefined") return;
                var parsedTime=new Date(null, null, null, null, null, value).toTimeString().match(/\d{2}:\d{2}:\d{2}/)[0];
                return parsedTime;
            }
        },{
            header: _('awesomeVideos_item_created')
            ,dataIndex: 'created'
            ,sortable: true
            ,width: 4
        }]
        ,tbar: [{
                text: _('awesomeVideos_import')
                ,handler: this.doImport
            },{
                text: _('awesomeVideos_item_new')
                ,handler: this.addVideo
        }]
    });

	awesomeVideos.grid.Items.superclass.constructor.call(this, config);
};




Ext.extend(awesomeVideos.grid.Items, MODx.grid.Grid, {
	windows: {},

    getMenu: function() {
        var m = [{
                text: _('awesomeVideos_item_update')
                ,handler: this.updateVideo
            },{
                text: _('awesomeVideos_item_remove')
                ,handler: this.removeVideo
            }
        ];
        this.addContextMenuItem(m);
        return true;
    }
    ,doImport: function(btn,e) {
        var that=this,
        		topic= '/awesomeVideosimport/'
        if (this.console == null || this.console == undefined) {
        		// открываем консоль и сообщаем через topic где отслеживать события
            this.console = MODx.load({
                xtype: 'modx-console'
                ,title: _('awesomeVideos_import')
                ,register: 'mgr'
                ,topic: topic
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

        // отправляем запрос на импорт
        MODx.Ajax.request({
            url: awesomeVideos.config.connectorUrl
            ,disableCaching: true
            ,params: {
                action: 'mgr/items/import'
                ,register: 'mgr'
                ,topic: topic // сообщаем в каком топике будем размещать логи
                ,cacheKey: awesomeVideos.config.cacheKey || false
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
        if (typeof(this.VideoWindow)!=="undefined"){
            // MODx.unloadTVRTE();
            this.VideoWindow.close();
            this.VideoWindow.destroy();
            delete this.VideoWindow;
        }
        this.VideoWindow = this.VideoWindow || MODx.load({
            xtype: 'awesomevideos-item-window-create'
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
        this.VideoWindow.new_scripts=false;
        this.VideoWindow.already_loaded=false;

        this.VideoWindow.show(e.target);
        this.VideoWindow.setTitle(_('awesomeVideos_item_new'));
        this.VideoWindow.reset();

        MODx.loadRTE('description');    // запускаем WYSIWYG
        this.getTVs(this.VideoWindow,e,true);
    }
    ,updateVideo: function(btn,e) {
        if (typeof(this.VideoWindow)!=="undefined"){
            // нужно уничтожить окно
            // console.log("нужно уничтожить окно");
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
            xtype: 'awesomevideos-item-window-create'
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
        // console.log("DESTROY",this.VideoWindow);
        this.VideoWindow.new_scripts=false;
        this.VideoWindow.already_loaded=false;

        this.VideoWindow.show(e.target);
        this.VideoWindow.setTitle(_('awesomeVideos_item_update'));

        this.VideoWindow.setValues(this.menu.record);
        MODx.loadRTE('description');    // запускаем WYSIWYG
        // console.log("THIS",this);
        // console.log("target",e);
        // console.log("VideoWindow",this.VideoWindow);
        this.getTVs(this.VideoWindow,e);
    }
    ,removeVideo: function() {
        MODx.msg.confirm({
            title: _('awesomeVideos_item_remove')
            ,text: _('awesomeVideos_item_remove.confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/video/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,getTVs: function(VideoWindow,e) {
        var selectedRecordsArray = this.getSelectionModel().getSelections();
        selected = [];
        Ext.each(selectedRecordsArray, function (item) {
          selected.push(item.data.id);
        });
        console.log("grid target:",selected);
        // return;

        MODx.Ajax.request({
            url: awesomeVideos.config.connectorUrl
            ,params: {
                action: 'mgr/video/gettvs2'
                ,register: 'mgr'
                ,"selected[]": selected
            }
            ,listeners: {
                'success':{fn:function(responce, options ,status) {
                    // console.fireEvent('complete');
                    // console.log("TVS:",responce);
                    console.log("INSIDE:",VideoWindow);
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
if (oldScripts.elements.length>0){
    // oldScripts.remove();
    oldScripts.elements.forEach(function(elem){
        elem.remove();
        console.warn("OLD SCRIPTS:",elem);
    })
}

VideoWindow["new_scripts"]=responce.scripts;
// VideoWindow.addField();
return;
// MODx.sleep(14); /* delay load event to allow FC rules to move before loading RTE */
setTimeout(function() {
    // append all inline scripts to the body to execute these
    responce.scripts.forEach(function(content){
        var script = document.createElement('script');
        script.setAttribute('tv_already','1')
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
                },scope:this}
            }
        });
    }
});

Ext.reg('awesomevideos-grid-items', awesomeVideos.grid.Items);