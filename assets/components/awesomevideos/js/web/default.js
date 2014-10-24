$(function() {'use strict'

var awesomeVideos = {
	options: {
		wrapper: '.mse2_mfilter'
		,filters: '#mse2_filters'
		,results: '#mse2_results'
		,total: '#mse2_total'
		,pagination: '#mse2_pagination'
		,sort: '#mse2_sort'
		,limit: '#mse2_limit'
		,slider: '.mse2_number_slider'
		,selected: '#mse2_selected'

		,pagination_link: '#mse2_pagination a'
		,sort_link: '#mse2_sort a'
		,tpl_link: '#mse2_tpl a'
		,selected_tpl: '<a href="#" data-id="[[+id]]" class="mse2_selected_link"><em>[[+title]]</em><sup>x</sup></a>'

		,active_class: 'active'
		,disabled_class: 'disabled'
		,disabled_class_fieldsets: 'disabled_fieldsets'
		,prefix: 'mse2_'
		,suggestion: 'sup' // inside filter item, e.g. #mse2_filters
	}
	,sliders: {}
	,initialize: function(selector) {
		console.log('awesomeVideos initialize"', this );
	}
}
awesomeVideos.initialize();

});