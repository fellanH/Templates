(function ($) {
	/**
	 * Adds pagebuilder data to Yoast analysis
	 */
	$(window).on('YoastSEO:ready',function(){

		// YOAST CONTENT FILTER
		var ZnbYoast = function() {
			YoastSEO.app.registerPlugin( 'ZnbYoast', {status: 'ready'} );
			YoastSEO.app.registerModification( 'content', this.ModifyContent, 'ZnbYoast', 5 );
		};

		// Add the pagebuilder data
		// Yoast already loads all the custom fields... we just need to use them
		ZnbYoast.prototype.ModifyContent = function(data) {
			return data + $('#wpseo_zn_page_builder_els__wrapper').text()
		};

		new ZnbYoast();

	});
})(jQuery);
