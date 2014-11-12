<div class="row playlist">
  <div class="col-md-12">
		<!-- информация о плейлисте -->
    <a class="link red title" href="[[+uri]]">[[+playlist]]</a>
  </div>

  <div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`0`
			&pagination = `carousel`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
			&DIS_where=`awesomeVideosItem.playlist=[[+id]]`
		]]
  </div>
</div>