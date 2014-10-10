var awesomeVideos = function (config) {
	config = config || {};
	awesomeVideos.superclass.constructor.call(this, config);
};
Ext.extend(awesomeVideos, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('awesomevideos', awesomeVideos);

awesomeVideos = new awesomeVideos();