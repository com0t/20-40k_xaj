(function ($) {
	('use strict');

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function () {
		var welcomePanel = $('#welcome-panel');
		var updateWelcomePanel;

		updateWelcomePanel = function (visible) {
			$.post(ajaxurl, {
				action: 'catch_instagram_feed_gallery_widget_update_welcome_panel',
				visible: visible,
				welcomenonce: $(
					'#catch-instagram-feed-gallery-widget-welcome-nonce'
				).val(),
			});
		};

		$('a.welcome-panel-close', welcomePanel).click(function (event) {
			event.preventDefault();
			welcomePanel.addClass('hidden');
			updateWelcomePanel(0);
		});

		// Tabs
		$('.catchp_widget_settings .nav-tab-wrapper a').on(
			'click',
			function (e) {
				e.preventDefault();

				if (!$(this).hasClass('ui-state-active')) {
					$('.nav-tab').removeClass('nav-tab-active');
					$('.wpcatchtab').removeClass('active').fadeOut(0);

					$(this).addClass('nav-tab-active');

					var anchorAttr = $(this).attr('href');

					$(anchorAttr).addClass('active').fadeOut(0).fadeIn(500);
				}
			}
		);
	});

	// jQuery Match Height init for sidebar spots
	$(document).ready(function () {
		$(
			'.catchp-sidebar-spot .sidebar-spot-inner, .col-2 .catchp-lists li, .col-3 .catchp-lists li'
		).matchHeight();
	});

	$(function () {
		/* CPT switch */
		$('.ctp-switch').on('click', function () {
			console.log('asdf');
			var loader = $(this).parent().next();

			loader.show();

			var main_control = $(this);
			var data = {
				action: 'ctp_switch',
				value: this.checked,
				option_name: main_control.attr('rel'),
				security: $('#ctp_tabs_nonce').val(),
			};

			$.post(ajaxurl, data, function (response) {
				response = $.trim(response);

				if ('1' == response) {
					main_control.parent().parent().addClass('active');
					main_control.parent().parent().removeClass('inactive');
				} else if ('0' == response) {
					main_control.parent().parent().addClass('inactive');
					main_control.parent().parent().removeClass('active');
				} else {
					alert(response);
				}
				loader.hide();
			});
		});
		/* CPT switch End */
	});

	$(function () {
		// Instagram logout
		$('.cifgw-instagram-logout').on('click', function (e) {
			e.preventDefault();
			/* console.log('clicked');
                            return false; */

			var check = confirm('Are you sure, you want to logout?');

			// console.log(check);
			if (check === false) {
				return false;
			}

			var data = {
				action: 'catch_instagram_reset',
				reset: 'instagram',
				instagram_security: $('#catch-instagram-reset-nonce').val(),
			};

			$.post(ajaxurl, data, function (response) {
				// alert(response);
				var res = JSON.parse(response);
				alert(res.message);

				if (true === res.status) {
					window.location.href = cifgw_object.page_url;
				}
			});
		});
	});

	// Show/hide access token
	$(function () {
		$('.button.show-secure').on('click', function () {
			var $this = $(this);
			$(this).hasClass('visible')
				? $this.html($this.attr('data-show'))
				: $this.html($this.attr('data-hide'));
			$this.toggleClass('visible');
			$this.parents('p').next('p').find('strong').toggleClass('secure');
		});
	});
})(jQuery);
