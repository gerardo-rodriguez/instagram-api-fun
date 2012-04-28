
// #pdxybb2012


// Let's load photos, once the DOM has loaded.
$(function(){
	var instagramController = new InstagramViewController(); // Create a new instance of our controller.
	instagramController.loadPhotos(); // Call the loadPhotos method to load some photos! :D
});











/**
 * @class InstagramViewController The controller for the Instagram Demo.
 *
 * @author Gerardo Rodriguez - gerardo.rodriguez@dachisgroup.com
 * @created 04/27/2012
 */
function InstagramViewController() {
	//-------------------------------------------------
	// Properties
	//-------------------------------------------------
	this.clientID = '8f2d5174acd2443aa8f6bb55f64c10fc';
	this.photoTag = 'pdxputtputt';
	this.nextURL;
	//-------------------------------------------------
	// Faux Constructor Init
	//-------------------------------------------------
	this.init(); // We need to call our faux constructor, since it won't actually run by itself.
}

InstagramViewController.prototype = {
	/**
	 * @private init() Our constructor method.
	 */
	init: function() {
		$('.instagramContainer, #more').hide();
		
		// Setup our colorbox
		$('a.gallery').live('click', function(e){
			e.preventDefault();

			$.colorbox({
				href: $(e.currentTarget).attr('href'),
				title: $(e.currentTarget).attr('title'),
				scrolling: false
			});
		});
		
		// Bind to the 'click' event on our more button.
		var self = this;
		$('#more').on('click', function(e) {
			e.preventDefault();
			// show loader
			$(this).fadeOut('slow', function(){
				$('.loadingAnimation').fadeIn('slow', function(){
					self.loadPhotos(this.nextURL);
				});
			});
		});
	},
	//-------------------------------------------------
	// Public Methods
	//-------------------------------------------------
	/**
	 * @private loadPhotos() Will load photos from the Instagram API.
	 */
	loadPhotos: function() {
		console.log('loadPhotos');
		
		var self = this;
		
		var url = !this.nextURL ? 'https://api.instagram.com/v1/tags/' + this.photoTag + '/media/recent' : this.nextURL;
		$.ajax({
			type: 'GET',
			dataType: 'jsonp',
			cache: false,
			url: url,
			data: {
				'client_id': this.clientID
			},
			// beforeSend: function(jqXHR, settings) {
				// show loader
				// $('#more').fadeOut('slow', function(){
				// 	$('.loadingAnimation').fadeIn('slow');
				// });
			// },
			complete: function(jqXHR, textStatus) {
				// hide loader
				$('.loadingAnimation').fadeOut('slow', function(){
					$('.instagramContainer').fadeIn('slow', function() {
						(self.nextURL === undefined) ? $('#more').fadeOut() : $('#more').fadeIn();
					});
				});
			},
			success: function(data, textStatus, jqXHR) {
				self.renderView(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error " + textStatus + " " + errorThrown);
			}
		});
	},
	//-------------------------------------------------
	// Private Methods
	//-------------------------------------------------
	/**
	 * @private renderView() Will render the data to the view.
	 */
	renderView: function(dataObj) {
		// Update our next url with the one that's handed to us by the Instagram API
		this.nextURL = dataObj.pagination.next_url;
		console.log( this.nextURL );
		
		// If, upon getting the data back from the Instagram API we have an undefined, then hide the more button so we can't ask for more.
		// (this.nextURL === undefined) ? $('#more').fadeOut() : $('#more').fadeIn();

		// Grab the photo data that contains the image data.
		var data = dataObj.data;

		// Start creating our string of HTML markup.
		var markup = '';
	
		console.log(data.length);

		var total = data.length;
		for(var i = 0; i < total; i++) {
			markup += "<li>";
			markup += "<a class='gallery' href='" + data[i].images.standard_resolution.url + "' title='" + data[i].caption.text + "'>";
			markup += "<img src='" + data[i].images.low_resolution.url + "' alt='" + data[i].caption.text + "'/>";
			markup += "</a>";
			markup += "</li>";
		}
	
		// Write our markup to the HTML page.
		$('.instagramContainer').append(markup);
	}
	//-------------------------------------------------
	// Getters/Setters
	//-------------------------------------------------
};