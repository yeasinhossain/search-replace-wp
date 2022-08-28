(function( $ ) {
	'use strict';

	/**
	 * Initializes our event handlers.
	 */
	function srwp_init() {
		srwp_backup_database();
		srwp_import_database();
		srwp_search_replace();
		srwp_update_sliders();
		srwp_save_profile();
	}

	/**
	 * Recursive function for performing batch operations.
	 */
	function srwp_process_step( action, step, page, data ) {

		$.ajax({
			type: 'POST',
			url: srwp_object_vars.endpoint + action,
			data: {
				srwp_ajax_nonce : srwp_object_vars.ajax_nonce,
				action: action,
				srwp_step: step,
				srwp_page: page,
				srwp_data: data
			},
			dataType: 'json',
			success: function( response ) {

				// Maybe display more details.
				if ( typeof response.message != 'undefined' ) {
					$('.srwp-description').remove();
					$('.srwp-progress-wrap').append( '<p class="description srwp-description">' + response.message + '</p>' );
				}

				if ( 'done' == response.step ) {

					srwp_update_progress_bar( '100%' );

					// Maybe run another action.
					if ( typeof response.next_action != 'undefined' ) {
						srwp_update_progress_bar( '0%', 0 );
						srwp_process_step( response.next_action, 0, 0, response.srwp_data );
					} else {
						$('.srwp-processing-wrap').remove();
						$('.srwp-disabled').removeClass('srwp-disabled button-disabled' );
						window.location = response.url;
					}

				} else {
					srwp_update_progress_bar( response.percentage );
					srwp_process_step( action, response.step, response.page, response.srwp_data );
				}

			}
		}).fail(function (response) {
			$('.srwp-processing-wrap').remove();
			$('.srwp-disabled').removeClass('srwp-disabled button-disabled' );
			$('#srwp-error-wrap').html( '<div class="error"><p>' + srwp_object_vars.unknown + '</p></div>' );
			if ( window.console && window.console.log ) {
				console.log(response);
			}
		});

	}

	/**
	 * Initializes a database backup.
	 */
	function srwp_backup_database() {

		var backup_submit = $( '#srwp-backup-submit' );
		backup_submit.click( function( e ) {

			e.preventDefault();

			if ( ! backup_submit.hasClass( 'button-disabled' ) ) {

				var data = $( '.srwp-action-form' ).serialize();

				backup_submit.addClass( 'srwp-disabled button-disabled' );
				$('#srwp-backup-form').append('<div class="srwp-processing-wrap"><div class="spinner is-active srwp-spinner"></div><div class="srwp-progress-wrap"><div class="srwp-progress"></div></div></div>');
				$('.srwp-progress-wrap').append( '<p class="description srwp-description">' + srwp_object_vars.processing + '</p>' );
				srwp_process_step( 'process_backup', 0, 0, data );

			}

		});

	}

	/**
	 * Initializes a database import.
	 */
	function srwp_import_database() {
		var import_submit = $( '#srwp-import-submit' );
		import_submit.click( function( e ) {

			e.preventDefault();

			var file_data 	= $('#srwp-file-import').prop('files')[0];
			var profile 	= $('#srwp_import_profile').val();
			var form_data 	= new FormData();

			form_data.append( 'srwp_import_file', file_data);
			form_data.append( 'action', 'upload_import' );
			form_data.append( 'profile', profile );
			form_data.append( 'srwp_ajax_nonce', srwp_object_vars.ajax_nonce );

			$.ajax({
				url: srwp_object_vars.endpoint + 'upload_import',
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function( response ) {

					if ( response.upload_method === 'manual' ) {

						if ( confirm( 'No upload was detected, but an existing backup file was detected at ' + response.file + '. Do you want to import it?' ) ) {

							if ( ! import_submit.hasClass( 'button-disabled' ) ) {
								import_submit.addClass( 'srwp-disabled button-disabled' );
								$('#srwp-import-form').append('<div class="srwp-processing-wrap"><div class="spinner is-active srwp-spinner"></div><div class="srwp-progress-wrap"><div class="srwp-progress"></div></div></div>');
								$('.srwp-progress-wrap').append( '<p class="description srwp-description">Importing database...</p>' );
								srwp_process_step( 'process_import', 0, 0, response );
							}

						}

					} else if ( response.upload_method == 'ajax' ) {

						if ( ! import_submit.hasClass( 'button-disabled' ) ) {

							import_submit.addClass( 'srwp-disabled button-disabled' );
							$('#srwp-import-form').append('<div class="srwp-processing-wrap"><div class="spinner is-active srwp-spinner"></div><div class="srwp-progress-wrap"><div class="srwp-progress"></div></div></div>');
							$('.srwp-progress-wrap').append( '<p class="description srwp-description">Importing database...</p>' );
							srwp_process_step( 'process_import', 0, 0, response );

						}

					} else {
						alert( 'Please upload a valid backup file, or try increasing the max upload size.' );
					}



				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});

		});
	}

	/**
	 * Initializes a search/replace.
	 */
	function srwp_search_replace() {

		var search_replace_submit = $( '#srwp-submit' );
		var srwp_error_wrap = $( '#srwp-error-wrap' );
		search_replace_submit.click( function( e ) {

			e.preventDefault();

			if ( ! search_replace_submit.hasClass( 'button-disabled' ) ) {

				if ( ! $( '#search_for' ).val() ) {
					srwp_error_wrap.html( '<div class="error"><p>' + srwp_object_vars.no_search + '</p></div>' );
				} else if ( ! $( '#srwp-table-select' ).val() ) {
					srwp_error_wrap.html( '<div class="error"><p>' + srwp_object_vars.no_tables + '</p></div>' );
				} else {
					var str 	= $( '.srwp-action-form' ).serialize();
					var data 	= str.replace(/%5C/g, "#SRWP_BACKSLASH#" );

					srwp_error_wrap.html('');
					search_replace_submit.addClass( 'srwp-disabled button-disabled' );
					$( '#srwp-submit-wrap' ).append('<div class="srwp-processing-wrap"><div class="spinner is-active srwp-spinner"></div><div class="srwp-progress-wrap"><div class="srwp-progress"></div></div></div>');
					$('.srwp-progress-wrap').append( '<p class="description srwp-description">' + srwp_object_vars.processing + '</p>' );
					srwp_process_step( 'process_search_replace', 0, 0, data );
				}

			}

		});

	}

	/**
	 * Updates the progress bar for AJAX bulk actions.
	 */
	function srwp_update_progress_bar( percentage, speed ) {
		if ( typeof speed == 'undefined' ) {
			speed = 150;
		}
		$( '.srwp-progress' ).animate({
			width: percentage
		}, speed );
	}

	/**
	 * Updates the "Max Page Size" slider.
	 */
	function srwp_update_sliders( percentage ) {
		$('#srwp-page-size-slider').slider({
			value: srwp_object_vars.page_size,
			range: "min",
			min: 1000,
			max: 50000,
			step: 1000,
			slide: function( event, ui ) {
				$('#srwp-page-size-value').text( ui.value );
				$('#srwp_page_size').val( ui.value );
			}

		});
		$('#srwp-max-results-slider').slider({
			value: srwp_object_vars.max_results,
			range: "min",
			min: 20,
			max: 1000,
			step: 20,
			slide: function( event, ui ) {
				$('#srwp-max-results-value').text( ui.value );
				$('#srwp_max_results').val( ui.value );
			}
		});
	}

	/**
	 * Displays the "Profile Name" field.
	 */
	function srwp_save_profile() {
		$('#save_profile').change( function() {
			if ( this.checked ) {
				$(this).closest('tr').next('tr').fadeIn('fast');
			} else {
				$(this).closest('tr').next('tr').fadeOut('fast');
			}
		});
	}

	srwp_init();

})( jQuery );
