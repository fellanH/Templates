window.znhg = window.znhg || {};
if( typeof(window.ZnHgShManager) =='undefined') {
	throw new Error('Error: ZnHgShManager was not found.');
}
znhgShortcodesManagerData = {};

znhgShortcodesManagerData.sections = ZnHgShManager.sections;
znhgShortcodesManagerData.shortcodes = ZnHgShManager.shortcodes;

(function ($) {
	var App = function(){},
		ModalView = require('./views/modal'),
		ShortcodesCollection = require('./models/shortcodesCollection');

	/**
	 * Starts the main shortcode manager class
	 */
	App.prototype.start = function(){
		// Bind the click event
		$(document).on('click', '#znhgtfw-shortcode-modal-open', function(e){
			e.preventDefault();
			this.openModal();
		}.bind(this));

		this.shortcodesCollection = new ShortcodesCollection(znhgShortcodesManagerData.shortcodes);

		// Allow chaining
		return this;
	};

	/**
	 * Opens the modal window
	 */
	App.prototype.openModal = function(){
		// Only allow an instance of the modalView
		if( this.modalView === undefined ){
			this.modalView = new ModalView({collection: this.shortcodesCollection, app : this});
		}
	};

	/**
	 * Opens the modal window
	 */
	App.prototype.closeModal = function(){
		this.modalView = undefined;
	};

	znhg.shortcodesManager = new App().start();

})(jQuery);
