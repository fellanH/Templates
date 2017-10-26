/*--------------------------------------------------------------------------------------------------

 File: app.js

 Description: This is the main javascript file for the Zion Builder's frontend scripts

 --------------------------------------------------------------------------------------------------*/

(function ($) {

	$.ZnbFrontendJs = function () {
		this.scope = $(document);
		this.zinit();
	};

	$.ZnbFrontendJs.prototype = {
		zinit : function() {
			var fw = this;

			fw.addActions();
			fw.initHelpers( $(document) );
			// EVENTS THAT CAN BE REFRESHED
			fw.refresh_events( $(document) );

			$(document).trigger( 'ZnbFrontendJsReady', this );
		},

		refresh_events : function( content ) {

			var fw = this;

			fw.contact_forms( content );
			fw.doParallax(content);
			fw.doObjectParallax(content);
			fw.background_video(content);
			fw.entryAnimations(content);
			fw.objectFitCover(content);

		},

		RefreshOnWidthChange : function(content) {},

		addActions : function() {
			var fw = this;

			// Refresh events on new content
			fw.scope.on('ZnWidthChanged',function(e){
				fw.RefreshOnWidthChange(e.content);
				$(window).trigger('resize');
			});

			// Refresh events on new content
			fw.scope.on('ZnNewContent',function(e){
				fw.refresh_events( e.content );
			});

		},

		unbind_events : function( scope ){},

		initHelpers: function( scope ){

			/**
			 * Helper Functions
			 */
			var fw = this;
			this.helpers = {};

			this.helpers.IsJsonString = function (a) {
				try {
					JSON.parse(a);
				} catch (e) {
					return false;
				}
				return true;
			};

			this.helpers.is_null = function (a) {
				return (a === null);
			};
			this.helpers.is_undefined = function (a) {
				return (typeof a == 'undefined' || a === null || a === '' || a === 'undefined');
			};
			this.helpers.is_number = function (a) {
				return ((a instanceof Number || typeof a == 'number') && !isNaN(a));
			};
			this.helpers.is_true = function (a) {
				return (a === true || a === 'true');
			};
			this.helpers.is_false = function (a) {
				return (a === false || a === 'false');
			};
			this.helpers.throttle = function(func, wait, options) {
				var timeout, context, args, result;
				var previous = 0;
				if (!options) options = {};

				var later = function() {
					previous = options.leading === false ? 0 : fw.helpers.date_now;
					timeout = null;
					result = func.apply(context, args);
					if (!timeout) context = args = null;
				};

				var throttled = function() {
					var now = fw.helpers.date_now;
					if (!previous && options.leading === false) previous = now;
					var remaining = wait - (now - previous);
					context = this;
					args = arguments;
					if (remaining <= 0 || remaining > wait) {
						if (timeout) {
							clearTimeout(timeout);
							timeout = null;
						}
						previous = now;
						result = func.apply(context, args);
						if (!timeout) context = args = null;
					} else if (!timeout && options.trailing !== false) {
						timeout = setTimeout(later, remaining);
					}
					return result;
				};

				throttled.cancel = function() {
					clearTimeout(timeout);
					previous = 0;
					timeout = context = args = null;
				};

				return throttled;
			};

			// Returns a function, that, as long as it continues to be invoked, will not
			// be triggered. The function will be called after it stops being called for
			// N milliseconds. If `immediate` is passed, trigger the function on the
			// leading edge, instead of the trailing.
			this.helpers.debounce = function(func, wait, immediate) {
				var timeout;
				return function() {
					var context = this, args = arguments;
					var later = function() {
						timeout = null;
						if (!immediate) func.apply(context, args);
					};
					var callNow = immediate && !timeout;
					clearTimeout(timeout);
					timeout = setTimeout(later, wait);
					if (callNow) func.apply(context, args);
				};
			};

			this.helpers.isInViewport = function(element) {
				var rect = element.getBoundingClientRect();
				var html = document.documentElement;
				var tolerance = rect.height * 0.75; // 3/4 of itself
				return (
					rect.top >= -tolerance
					&& rect.bottom <= (window.innerHeight || html.clientHeight) + tolerance
					//  && rect.left >= -( rect.width / 2 )
					//  && rect.right <= (window.innerWidth || html.clientWidth)
				);
			};

			this.helpers.date_now = Date.now || function() {
				return new Date().getTime();
			};

			this.helpers.hasTouch 			= ( typeof Modernizr == 'object' && Modernizr.touchevents) || false;
			this.helpers.hasTouchMobile 	= this.helpers.hasTouch && window.matchMedia( "(max-width: 1024px)" ).matches;
			this.helpers.ua 				= navigator.userAgent || '';
			this.helpers.is_mobile_ie 		= -1 !== this.helpers.ua.indexOf("IEMobile");
			this.helpers.is_firefox 		= -1 !== this.helpers.ua.indexOf("Firefox");
			this.helpers.isAtLeastIE11 		= !!(this.helpers.ua.match(/Trident/) && !this.helpers.ua.match(/MSIE/));
			this.helpers.isIE11 			= !!(this.helpers.ua.match(/Trident/) && this.helpers.ua.match(/rv[ :]11/));
			this.helpers.isMac 				= /^Mac/.test(navigator.platform);
			this.helpers.is_safari 			= /^((?!chrome|android).)*safari/i.test(this.helpers.ua);
			this.helpers.isIE10 			= navigator.userAgent.match("MSIE 10");
			this.helpers.isIE9 				= navigator.userAgent.match("MSIE 9");
			this.helpers.is_EDGE 			= /Edge\/12./i.test(this.helpers.ua);
			this.helpers.is_pb 				= !this.helpers.is_undefined($.ZnPbFactory);

			var $body = $('body');
			if (this.helpers.is_EDGE) 		$body.addClass('is-edge');
			if (this.helpers.isIE11) 		$body.addClass('is-ie11');
			if (this.helpers.is_safari) 	$body.addClass('is-safari');

		},

		contact_forms : function ( scope ){
			var fw = this,
				element = (scope) ? scope.find('.zn-contactForm') : $('.zn-contactForm');

			element.each(function(index, el) {

				var $el = $(el),
					time_picker = $el.find('.zn-formItem-field--timepicker'),
					date_picker = $el.find('.zn-formItem-field--datepicker'),
					datepicker_lang = date_picker.is('[data-datepickerlang]') ? date_picker.attr('data-datepickerlang') : '',
					date_format = date_picker.is('[data-dateformat]') ? date_picker.attr('data-dateformat') : 'yy-mm-dd',
					timeformat = time_picker.is('[data-timeformat]') ? time_picker.attr('data-timeformat') : 'h:i A';

				if(time_picker.length > 0){
					time_picker.timepicker({
						'timeFormat': timeformat,
						'className': 'cf-elm-tp'
					});
				}

				if(date_picker.length > 0){
					date_picker.datepicker({
						dateFormat: date_format,
						showOtherMonths: true
					}).datepicker('widget').wrap('<div class="ll-skin-melon"/>');

					if(datepicker_lang !== ''){
						$.datepicker.setDefaults( $.datepicker.regional[ datepicker_lang ] );
					}
				}

				// SUBMIT
				$el.on( 'submit', function(e){

					e.preventDefault();

					if ( fw.form_submitting === true ) { return false; }

					fw.form_submitting = true;

					var form = $(this),
						response_container = form.find('.zn_contact_ajax_response:eq(0)'),
						has_error   = false,
						inputs =
						{
							fields : form.find('textarea, select, input[type="text"], input[type="checkbox"], input[type="hidden"]')
						},
						form_id = response_container.attr('id'),
						submit_button = form.find('.zn-formSubmit');

					// Some IE Fix
					if((fw.helpers.isIE11 || fw.helpers.isIE10 || fw.helpers.isIE9) && form.is('[action="#"]') ){
						form.attr('action','');
					}

					// FADE THE BUTTON
					submit_button.addClass('zn-contactForm--loading');

					// PERFORM A CHECK ON ELEMENTS :
					inputs.fields.each(function()
					{
						var field       = $(this),
							p_container = field.parent();

						// Set the proper value for checkboxes
						if(field.is(':checkbox'))
						{
							if(field.is(':checked')) { field.val(true); } else { field.val(''); }
						}

						p_container.removeClass('zn-formItem--invalid');

						// Check fields that needs to be filled
						if ( field.hasClass('zn_validate_not_empty') ) {
							if( field.is(':checkbox') ){
								if( ! field.is(':checked') ){
									p_container.addClass('zn-formItem--invalid');
									has_error = true;
								}
							}
							else {
								if ( field.val() === '' ){
									p_container.addClass('zn-formItem--invalid');
									has_error = true;
								}
							}
						}
						else if ( field.hasClass('zn_validate_is_email') ) {
							if ( !field.val().match(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/) )
							{
								p_container.addClass('zn-formItem--invalid');
								has_error = true;
							}
						}
						else if ( field.hasClass('zn_validate_is_numeric') ) {
							if ( isNaN(field.val() ) )
							{
								p_container.addClass('zn-formItem--invalid');
								has_error = true;
							}
						}
					});

					if ( has_error )
					{
						submit_button.removeClass('zn-contactForm--loading');
						fw.form_submitting = false;
						return false;
					}

					var data = form.serialize();
					$.post(form.attr('action'), data).success(function(result){

						// DO SOMETHING
						fw.form_submitting = false;
						submit_button.removeClass('zn-contactForm--loading');

						// Perform the redirect if the form was valid
						var response = $(result).find('#'+form_id +' > .zn_cf_response'),
							responseContainer = $('#'+form_id),
							redirect_uri = form.data( 'redirect' );

						responseContainer.html( response );

						// If the form was successfull
						if( response.hasClass('alert-success') ){
							inputs.fields.val('');
							if( redirect_uri ){
								window.location.replace(redirect_uri);
							}
						}
					})
					.error(function(){
						console.log('Error loading page');
					});

					return false;
				});
			});

		},


		/**
		 * Easy Video Background
		 * Based on easy background video plugin
		 * Example data setup attribute:
		 * data-video-setup='{ "position": absolute, "loop": true , "autoplay": true, "muted": true, "mp4":"", "webm":"", "ogg":""  }'
		 */
		background_video : function( scope ){
			var fw = this,
			elements = scope.find('.zn-videoBg:not(.zn-videoBg--no-init)');

			if(!elements.length) return;

			elements.each(function(index, el) {
				var $video = $(el),
					$options = $video.is("[data-video-setup]") && fw.helpers.IsJsonString( $video.attr("data-video-setup") ) ? JSON.parse( $video.attr("data-video-setup") ) : {};

				// TODO: Exclude
				// if( $options  )

				if(typeof video_background != 'undefined') {
					var Video_back = new video_background( $video, $options);
				}
			});
		},

		doParallax: function(scope){

			var fw = this,
				$el = $(".js-znParallax", $(scope) );

			if( $el.length > 0 && !fw.helpers.hasTouchMobile && typeof($.fn.znParallax) != 'undefined' ){

				$el.znParallax();
			}
		},

		doObjectParallax: function(scope){

			var fw = this,
				$el = $(".js-doObjParallax", $(scope) );

			if( $el.length > 0 && !fw.helpers.hasTouchMobile && !fw.helpers.is_mobile_ie && typeof Rellax != 'undefined' ){
				var rellax = new Rellax( '.js-doObjParallax' );
			}
		},

		entryAnimations: function(scope){

			var fw = this,
				elements = $(scope).find('.zn-animateInViewport'),
				is = [];

			if( elements.length > 0) {
				elements.each(function(i, el) {
					var $el = $(el);
					// animation-delay
					$el.css('animation-delay', $el.attr('data-anim-delay'));

					function animateEntrance(){
						if( $(el).parent().hasClass('eluida7543286') ){
							console.log( fw.helpers.isInViewport( el ) );
						}

						if ( !is[i] && fw.helpers.isInViewport( el ) ) {
							$el.removeClass('zn-animateInViewport').addClass('is-animating');
							is[i] = true;
						}
					}
					animateEntrance();

					$(window).on('scroll', animateEntrance );
				});
			}
		},

		objectFitCover: function(scope){

			var fw = this;

			// switch between height:100% and width:100% based on comparison of obj and container aspect ratios
			function coverFillSwitch(container, obj, invert) {
				if (!container || !obj) return false;

				var objHeight = obj.naturalHeight || obj.videoHeight;
				var objWidth = obj.naturalWidth || obj.videoWidth;
				var containerRatio = container.offsetWidth / container.offsetHeight;
				var objRatio = objWidth / objHeight;

				var ratioComparison = false;
				if (objRatio >= containerRatio) ratioComparison = true;
				if (invert) ratioComparison = !ratioComparison; // flip the bool

				if (ratioComparison) {
					obj.style.height = '100%';
					obj.style.width = 'auto';
				} else {
					obj.style.height = 'auto';
					obj.style.width = '100%';
				}
			}

			// add absolute center object css properties
			function applyStandardProperties(container, obj) {
				var containerStyle = window.getComputedStyle(container);
				if (containerStyle.overflow !== 'hidden') container.style.overflow = 'hidden';
				if (containerStyle.position !== 'relative' &&
					containerStyle.position !== 'absolute' &&
					containerStyle.position !== 'fixed') container.style.position = 'relative';
				obj.style.position = 'absolute';
				obj.style.top = '50%';
				obj.style.left = '50%';
				obj.style.transform = 'translate(-50%,-50%)';
			}

			function objectFitInt(el) {

				var objs = document.getElementsByClassName(el);
				for (var i = 0; i < objs.length; i++) {

					var obj = objs[i];
					var container = obj.parentElement;

					coverFillSwitch(container, obj);
					applyStandardProperties(container, obj);

				}
			}

			/**
			 * Fallback for object-fit__cover;
			 */
			if (!Modernizr.objectfit) {
				window.addEventListener('load', objectFitInt('object-fit__cover'), false);
				window.addEventListener('resize', fw.helpers.throttle(
					function () {
						var i, obj, container;
						var objsCover = document.getElementsByClassName('object-fit__cover');
						for (i = 0; i < objsCover.length; i++) {
							obj = objsCover[i];
							container = obj.parentElement;
							coverFillSwitch(container, obj);
						}
					}, 100), false);
			}

			/**
			 * JS native "object-fit:cover" behaviour
			 * use class .js-object-fit-cover
			 */
			if( $('.js-object-fit-cover', scope).length !== 0 ) {

				// Object Fit Cover as JS Solution (eg: for iframes)
				window.addEventListener('load', objectFitInt('js-object-fit-cover'), false);
				window.addEventListener('resize', fw.helpers.throttle(
					function () {
						var i, obj, container;
						var objsCover = document.getElementsByClassName('js-object-fit-cover');
						for (i = 0; i < objsCover.length; i++) {
							obj = objsCover[i];
							container = obj.parentElement;
							coverFillSwitch(container, obj);
						}
					}, 100), false);
			}
		}

	};

	$(document).ready(function () {

		// Call this on document ready
		$.znb_frontend_js = new $.ZnbFrontendJs();

	});

})(jQuery);


var znCaptchaOnloadCallback = function() {
	jQuery('.zn-recaptcha').each(function(i, el){
		var $el = jQuery(el);
		grecaptcha.render( $el.attr('id'), {
			'sitekey' 	: $el.data('sitekey'),
			'theme' 	: $el.data('colorscheme')
		});
	});
};

