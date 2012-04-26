// #PortlandYouthBridgeBuilders
// #PortlandYBB
// #pdxybb2012
/*
$(function() {
  $(".instagram").instagram({
      hash: 'pdx' // 
    , clientId: '8f2d5174acd2443aa8f6bb55f64c10fc'
  });
});
*/

$(function(){
/*
	var insta_container = $(".instagram");
	var insta_next_url;

	insta_container.instagram({
		hash: 'togo', 
		clientId : '8f2d5174acd2443aa8f6bb55f64c10fc',
		show : 10,
		onLoad: function() {
			console.log('onLoad');
		},
		onComplete : function (photos, data) {
			console.log(photos);
			console.log(data);
			insta_next_url = data.pagination.next_url
		}
	});

	$('button').on('click', function(){
		var button = $(this);
		var text = button.text();

		if (button.text() != 'Loading…'){
			button.text('Loading…');
			insta_container.instagram({
				next_url : insta_next_url,
				show : 18,
				onComplete : function(photos, data) {
					insta_next_url = data.pagination.next_url
					button.text(text)
				}
			});
		}
	}); 
*/

	loadPhotos();

});

function loadPhotos( nextURL ) {
	var url = !nextURL ? 'https://api.instagram.com/v1/tags/pdxputtputt/media/recent' : nextURL;
	$.ajax({
		type: 'GET',
		dataType: 'jsonp',
		cache: false,
		url: url,
		data: {
			'client_id': '8f2d5174acd2443aa8f6bb55f64c10fc'
		},
		beforeSend: function(jqXHR, settings) {
			// show loader
		},
		complete: function(jqXHR, textStatus) {
			// hide loader
		},
		success: function(data, textStatus, jqXHR) {
			renderView(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert("Error " + textStatus + " " + errorThrown);
		}
	});
}

function renderView(dataObj) {
	var nextURL = dataObj.pagination.next_url;
	var data = dataObj.data;
	var markup = '';
	
	markup += '<ul>';
	console.log(data.length);
	var total = data.length;
	for(var i = 0; i < total; i++) {
		console.log(data[i].caption.text);
		markup += "<li>";
		markup += "<a href='" + data[i].link + "' target='_blank'><img src='" + data[i].images.thumbnail.url + "' alt='" + data[i].caption.text + "'/></a>";
		markup += "</li>";
	}
	markup += "</ul>"
	
	$('.instagramContainer').append(markup);
	
	$('#more').on('click', function(e) {
		e.preventDefault();
		loadPhotos(nextURL);
	});
}