<div class="row playlist">
	<h4 class="red title">[[+playlist]] - [[+id]]</h4>
	<div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`2`
			&pagination = `snippet`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
			&DIS_where=`awesomeVideosItem.playlist=[[+id]]`
		]]
	</div>
</div>