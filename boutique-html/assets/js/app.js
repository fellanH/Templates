var DECENTTHEMES = DECENTTHEMES || {};

(function($){

  // Beautiful functions stack by Aminul Islam <aminul@decentthemes.com>
  // Lead Web Developer @DECENTTHEMES

  // USE STRICT
  "use strict";

  DECENTTHEMES.initialize = {

    init: function(){
      DECENTTHEMES.initialize.defaults();
      DECENTTHEMES.initialize.header();
      DECENTTHEMES.initialize.stickyHeader();
      DECENTTHEMES.initialize.swiperSlider();
      DECENTTHEMES.initialize.photoGallery();
      DECENTTHEMES.initialize.sectionCustomize();
      DECENTTHEMES.initialize.isotopeInit();
      DECENTTHEMES.initialize.mailChimp();
    },
    defaults: function() {

      // Preloader
      $(window).on('load', function () {
        $("#preloader").delay(350).fadeOut("slow");
      });

      // Toggle
      var $toggle = $('[data-dt-toggle]');

      $toggle.each(function() {
        var $this       = $(this),
            $class      = $this.data('dt-toggle'),
            $altClass   = $this.data('dt-toggle-false');

        $this.on('click', function(e) {
          e.preventDefault();
          $this.toggleClass('active');
          $('body').toggleClass($class).removeClass($altClass);
        })
      })

      // Checkbox fix
      $('.type-checkbox input[type="checkbox"]').on('change', function() {
        $('.type-checkbox input[name="' + this.name + '"]').not(this).prop('checked', false);
      });

      // Check If WOW.js Exists
      if (typeof WOW === 'function') {
        var wow = new WOW({
          boxClass:     'isAnimated',
          animateClass: 'animated', // default
          offset:       100,
          mobile:       false,
        })
        wow.init();
      }
    },
    header: function() {
      // Tweak the overlay menu position
      var $headerH = $('#masthead').outerHeight(),
          $headerMH = $('#masthead .header-middle-area').outerHeight(),
          $wWidth = window.innerWidth,
          $scrolling  = $(window).scrollTop();
      $('.menu-overlay-full').css("top", $headerMH);

      if ( $('body').hasClass('header-style-3') && $wWidth < 769) {
        $('#primary-menu-container').css('top', $headerH + 'px');
      }
    },
    stickyHeader: function() {
      var $mainHeader = $('#masthead');
      var $maskHeader = $('#header-fake-mask');
      var $siteHeader = $mainHeader.outerHeight();

      //set scrolling variables
      var scrolling   = false,
        previousTop   = 0,
        currentTop    = 0,
        scrollDelta   = 10,
        scrollOffset  = 150;

      $(window).on('scroll', function(){
        if( !scrolling ) {
          scrolling = true;
          (!window.requestAnimationFrame)
            ? setTimeout(autoHideHeader, 250)
            : requestAnimationFrame(autoHideHeader);
        }
      });

      function autoHideHeader() {
        var currentTop = $(window).scrollTop();

          checkStickyNavigation(currentTop);

          previousTop  = currentTop;
        scrolling = false;
      }

      function checkStickyNavigation(currentTop) {
      //secondary nav below intro section - sticky secondary nav

      if (previousTop >= currentTop ) {
          //if scrolling up...
          if( currentTop < $siteHeader ) {
            //secondary nav is not fixed
            $('body').removeClass('fix-header is-scrolled');
            $maskHeader.css('height', '0px');
          } else if( previousTop - currentTop > scrollDelta ) {
            //secondary nav is fixed
            $('body').addClass('is-scrolled');
          }

        } else {
          //if scrolling down...
          if( currentTop > $siteHeader + scrollOffset ) {
            //hide primary nav
            $('body').addClass('fix-header').removeClass('is-scrolled');
            $maskHeader.css('height', $siteHeader + 10 +'px');
          } else if( currentTop > $siteHeader ) {
            //once the secondary nav is fixed, do not hide primary nav if you haven't scrolled more than scrollOffset
            $('body').removeClass('is-scrolled');
          }

        }
      }
    },
    swiperSlider: function() {
      $('[data-carousel="swiper"]').each( function() {

        var $this        = $(this),
            $container   = $this.find('[data-swiper="container"]'),
            $asControl   = $this.find('[data-swiper="ascontrol"]');

        // Configuration
        var conf = function(element) {
          var obj = {
            slidesPerView: element.data('items'),
            centeredSlides: element.data('center'),
            loop: element.data('loop'),
            initialSlide: element.data('initial'),
            effect: element.data('effect'),
            spaceBetween: element.data('space'),
            autoplay: element.data('autoplay'),
            direction: element.data('direction'),
            paginationClickable: true,
            breakpoints: element.data('breakpoints'),
            slideToClickedSlide: element.data('click-to-slide'),
            loopedSlides: element.data('looped'),
            fade: {
              crossFade: element.data('crossfade')
            },
            paginationBulletRender: function(swiper, index, className) {
              return '<li class="' + className + '"><span class="count">0' + (index + 1) + '</span></li>';
            }
          };
          return obj;
        }

        // Primary Configuration
        var $primaryConf = conf($container);
        // Pagination and Nav Settings
        $primaryConf.prevButton = $this.find('[data-swiper="prev"]');
        $primaryConf.nextButton = $this.find('[data-swiper="next"]');
        $primaryConf.pagination = $this.find('[data-swiper="pagination"]');

        // As Control Configuration\
        var $ctrlConf = conf($asControl);

        // Animate Function
        function animateSwiper(selector, slider) {
          var makeAnimated = function animated() {
            selector.find('.swiper-slide-active [data-animate]').each(function(){
              var anim      = $(this).data('animate'),
                  delay     = $(this).data('delay'),
                  duration  = $(this).data('duration');

              $(this).addClass(anim + ' animated')
              .css({
                webkitAnimationDelay: delay,
                animationDelay: delay,
                webkitAnimationDuration: duration,
                animationDuration: duration
              })
              .one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $(this).removeClass(anim + ' animated');
              });
            });
          };
          makeAnimated();
          // Make animated when slide change
          slider.on('SlideChangeStart', function() {
            selector.find('[data-animate]').each(function(){
              var anim = $(this).data('animate');
              $(this).removeClass(anim + ' animated');
            });
          });
          slider.on('SlideChangeEnd', makeAnimated);
        };

        if ($container.length) {
          // Run Swiper
          var $swiper = new Swiper( $container, $primaryConf);
          // Make Animated Layer
          animateSwiper($this, $swiper);

          if ($asControl.length) {
            var $control = new Swiper( $asControl, $ctrlConf);
            $swiper.params.control = $control;
            $control.params.control = $swiper;
          }

        } else {
          console.log('Swiper container is not defined!');
        };

      });
    },
    photoGallery: function() {
      var $gallery = $('[data-gallery="lightbox"]');
      $gallery.each(function() {
        $(this).magnificPopup({
          delegate: 'a',
          type: 'image',
          closeOnContentClick: false,
          closeBtnInside: false,
          mainClass: 'mfp-with-zoom mfp-img-mobile',
          gallery: {
            enabled: true
          },
          zoom: {
            enabled: true,
            duration: 300, // don't foget to change the duration also in CSS
            opener: function(element) {
              return element.find('img');
            }
          }
        });
      });
    },
    sectionCustomize: function() {
      // Section Background Color
      $('[data-bg-color]').each(function() {

        var value = $(this).data('bg-color');

        $(this).css({
          backgroundColor: value,
        });
      });

      // Section Background Image
      $('[data-bg-image]').each(function() {

        var img = $(this).data('bg-image');

        $(this).css({
          backgroundImage: 'url(' + img + ')',
        });
      });


      // Elements Padding
      function elementPadding(attr) {

        $(attr).each(function() {

          if ( attr === '[data-padding]' ) {
            var data = {
              padding: $(this).data('padding')
            }
          } else if ( attr === '[data-padding-top]' ) {
            var data = {
              paddingTop: $(this).data('padding-top')
            }
          } else if ( attr === '[data-padding-right]' ) {
            var data = {
              paddingRight: $(this).data('padding-right')
            }
          } else if ( attr === '[data-padding-bottom]' ) {
            var data = {
              paddingBottom: $(this).data('padding-bottom')
            }
          } else if ( attr === '[data-padding-left]' ) {
            var data = {
              paddingLeft: $(this).data('padding-left')
            }
          }

          $(this).css(data);
        });
      }
      elementPadding('[data-padding]');
      elementPadding('[data-padding-top]');
      elementPadding('[data-padding-right]');
      elementPadding('[data-padding-bottom]');
      elementPadding('[data-padding-left]');

      // Elements margin
      function elementMargin(attr) {

        $(attr).each(function() {

          if ( attr === '[data-margin]' ) {
            var data = {
              margin: $(this).data('margin')
            }
          } else if ( attr === '[data-margin-top]' ) {
            var data = {
              marginTop: $(this).data('margin-top')
            }
          } else if ( attr === '[data-margin-right]' ) {
            var data = {
              marginRight: $(this).data('margin-right')
            }
          } else if ( attr === '[data-margin-bottom]' ) {
            var data = {
              marginBottom: $(this).data('margin-bottom')
            }
          } else if ( attr === '[data-margin-left]' ) {
            var data = {
              marginLeft: $(this).data('margin-left')
            }
          }

          $(this).css(data);
        });
      }
      elementMargin('[data-margin]');
      elementMargin('[data-margin-top]');
      elementMargin('[data-margin-right]');
      elementMargin('[data-margin-bottom]');
      elementMargin('[data-margin-left]');
    },

    isotopeInit: function() {
      $('[data-area="isotope"]').each( function() {

        var container = $(this).find('[data-area="isotope-container"]');
        var filters   = $(this).find('[data-area="isotope-filters"]');

        // Isotope Container
        var $portfolio =  $(container).isotope({
          itemSelector: '.grid-item',
          masonry: {
            columnWidth: 2
          }
        });

        // Filtering items
        $(filters).on( 'click', 'a', function() {
          var filterValue = $( this ).attr('data-filter');
          $portfolio.isotope({ filter: filterValue });
        });

      });

      // Filter Buttons
      $('.filter-items').each( function( i, buttonGroup ) {
        var $buttonGroup = $( buttonGroup );
        $buttonGroup.on( 'click', 'a', function(e) {
          $buttonGroup.find('.active').removeClass('active');
          $( this ).addClass('active');
          e.preventDefault();
        });
      });
    },
    mailChimp: function() {
      $('.newsletter-form').each(function () {
        var $this = $(this);
        $('.form-result', $this).css('display', 'none');

        $this.submit(function () {

          $('button[type="submit"]', $this).addClass('clicked').attr('disabled', true);

          $.ajax({
            // url: ajaxurl, // <- Uncomment it on wp theme
            type: 'POST',
            data: {
              action: $('input[name="action"]', $this).val(),
              email: $('input[name="email"]', $this).val()
            },
            success: function success(data) {
              if (data.err == true) {
                $('.form-result', $this).addClass('alert-warning').removeClass('alert-success alert-danger').css('display', 'block');
              } else {
                $('.form-result', $this).addClass('alert-success').removeClass('alert-warning alert-danger').css('display', 'block');
              }
              $('.form-result', $this).html(data.msg);
              $('button[type="submit"]', $this).removeClass('clicked').removeAttr('disabled');
            },
            error: function error() {
              $('.form-result', $this).addClass('alert-danger').removeClass('alert-warning alert-success').css('display', 'block');
              $('.form-result', $this).html('Sorry, an error occurred.');
              $('button[type="submit"]', $this).removeClass('clicked').removeAttr('disabled');
            }
          });
          return false;
        });

      });
    }
  };

  DECENTTHEMES.documentOnReady = {
    init: function(){
      DECENTTHEMES.initialize.init();
    },

  };

  DECENTTHEMES.documentOnResize = {
    init: function(){
      DECENTTHEMES.initialize.header();
    },

  };
  DECENTTHEMES.documentOnLoad = {
    init: function(){
      DECENTTHEMES.initialize.isotopeInit();
    },

  };

  DECENTTHEMES.documentOnScroll = {
    init: function(){
      DECENTTHEMES.initialize.header();
    },

  };

  // Initialize Functions
  $(document).ready( DECENTTHEMES.documentOnReady.init );
  $(window).on( 'resize', DECENTTHEMES.documentOnResize.init );
  $(window).on( 'load', DECENTTHEMES.documentOnLoad.init );
  $(document).on( 'scroll', DECENTTHEMES.documentOnScroll.init );

})(jQuery);
