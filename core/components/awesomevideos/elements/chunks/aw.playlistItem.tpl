<div class="row playlist catalog">
	<h4 class="red title">[[+playlist]]</h4>

	<div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`8`
			&pagination = `button`
			&pagination = `snippet`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
			&DIS_where=`awesomeVideosItem.playlist=[[+id]]`
		]]
	</div>
</div>