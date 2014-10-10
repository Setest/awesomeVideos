awesomeVideos.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'awesomevideos-panel-home', renderTo: 'awesomevideos-panel-home-div'
		}]
	});
	awesomeVideos.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(awesomeVideos.page.Home, MODx.Component);
Ext.reg('awesomevideos-page-home', awesomeVideos.page.Home);