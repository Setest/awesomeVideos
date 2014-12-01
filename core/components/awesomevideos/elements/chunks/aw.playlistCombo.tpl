<div class="row playlist">
  <div class="col-md-12">
		<!-- информация о плейлисте -->
    <a class="link red title" href="[[+uri]]" data-aw-idx='[[+idx]]' title='[[+playlist]]'>[[+playlist]]</a>
  </div>

  <div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`0`
			&pagination = `button`
			&pagination = `scroll`
			&pagination = `snippet`
			&pagination = `0`
			&pagination = `carousel`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
	    &topic=`[[+topic]]`
			&Dwhere=`awesomeVideosItem.playlist=[[+id]]`
			&Dwhere=`{"awesomeVideosItem.topic":"[[+id]]"}`
		]]
  </div>
</div>