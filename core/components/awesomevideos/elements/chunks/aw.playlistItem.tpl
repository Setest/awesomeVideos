<div class="row playlist catalog">
	<h4 class="red title">[[+playlist]]  <small>Id: [[+id]]</small></h4>
  <small><a class="link green title" href="[[+uri]]" data-aw-idx='[[+id]]'>Cсылка на текущего родителя</a></small>

	<div class="col-md-12">
		<!-- список видео -->
		[[!getAwesomeVideos?
			&log_status=`0`
			&limit=`2`
			&pagination = `button`
			&pagination = `snippet`
			&parentIds=`[[+id]]`
			&setOfProperties=`aw_videos`
			&DIS_where=`awesomeVideosItem.playlist=[[+id]]`
		]]
		[[!+aw.log]]
	</div>
</div>