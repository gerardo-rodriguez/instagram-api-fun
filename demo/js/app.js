
// #pdxybb2012


// Let's load photos, once the DOM has loaded.
$(function(){
	// DEMO: LET'S LOAD THE PHOTOS!! ;)
	/*
	var instagramController = new InstagramViewController(); // Create a new instance of our controller.
	instagramController.loadPhotos(); // Call the loadPhotos method to load some photos! :D
	*/
});











/**
 * @class InstagramViewController The controller for the Instagram Demo.
 *
 * @author Gerardo Rodriguez - ger.rod34@gmail.com
 * @created 04/27/2012
 */
function InstagramViewController() {
	//-------------------------------------------------
	// Properties
	//-------------------------------------------------
	this.clientID = '8f2d5174acd2443aa8f6bb55f64c10fc';
	this.photoTag = ''; // DEMO: LET'S DROP IN THE  '#pdxybb2012' TAG TO FIND THE PHOTOS ON INSTAGRAM!
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
		// Hide the instagram photo container and button at first.
		$('.instagramContainer, #more').hide();
		
		// Setup our colorbox gallery
		$('a.gallery').live('click', function(e){
			// prevent the default behavior of a link
			e.preventDefault();

			// Setup the Colorbox plugin
			$.colorbox({
				href: $(e.currentTarget).attr('href'),
				title: $(e.currentTarget).attr('title'),
				scrolling: false
			});
		});
		
		// Bind to the 'click' event on our more button.
		var self = this;
		$('#more').on('click', function(e) {
			// prevent the default behavior of a link
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
	 * @public loadPhotos() Will load photos from the Instagram API.
	 */
	loadPhotos: function() {
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
			complete: function(jqXHR, textStatus) {
				// hide loader
				$('.loadingAnimation').fadeOut('slow', function(){
					$('.instagramContainer').fadeIn('slow', function() {
						(self.nextURL === undefined) ? $('#more').fadeOut() : $('#more').fadeIn();
					});
				});
			},
			success: function(data, textStatus, jqXHR) {
				// DEMO: LET'S WRITE THE CODE TO RENDER OUR VIEW IF THE AJAX REQUEST WAS SUCCESSFUL! :)
				/*
				self.renderView(data);
				*/
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
	 * @param {Object} dataObj The data object from the Instagram API.
	 */
	renderView: function(dataObj) {
		if( dataObj.data ) {
			// Update our next url with the one that's handed to us by the Instagram API
			this.nextURL = (dataObj.pagination) ? dataObj.pagination.next_url : null;
		
			// If, upon getting the data back from the Instagram API we have an undefined, then hide the more button so we can't ask for more.
			// (this.nextURL === undefined) ? $('#more').fadeOut() : $('#more').fadeIn();

			// Grab the photo data that contains the image data.
			var data = dataObj.data;

			// Start creating our string of HTML markup.
			var markup = '';
			// Loop through the data and create the HTML markup for our images.
			var total = data.length;
			for(var i = 0; i < total; i++) {
				var caption = (data[i].caption) ? data[i].caption.text : '';
				var thumbImageURL = data[i].images.low_resolution.url;
				var fullImageURL = data[i].images.standard_resolution.url;
			
				markup += "<li>";
				markup += "<a class='gallery' href='" + fullImageURL + "' title='" + caption + "'>";
				markup += "<img src='" + thumbImageURL + "' alt='" + caption + "'/>";
				markup += "</a>";
				markup += "</li>";
			}
			// Write our markup to the HTML page.
			$('.instagramContainer').append(markup);
		} else {
			alert("Error: " + dataObj.meta.error_type + "\n" + dataObj.meta.error_message);
		}
	}
	//-------------------------------------------------
	// Getters/Setters
	//-------------------------------------------------
};