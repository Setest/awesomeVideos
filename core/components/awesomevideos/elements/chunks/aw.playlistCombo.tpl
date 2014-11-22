<div class="row playlist">
  <div class="col-md-12">
		<!-- информация о плейлисте -->
    <a class="link red title" href="[[+uri]]" data-aw-idx='[[+idx]]' title='[[+playlist]]'>[[+playlist]]</a><small>(offset:[[+idx]])</small>
  </div>

  <div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`1`
			&pagination = `carousel`
			&pagination = `button`
			&pagination = `snippet`
			&pagination = `scroll`
			&pagination = `0`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
			&DIS_where=`awesomeVideosItem.playlist=[[+id]]`
		]]
  </div>
</div>