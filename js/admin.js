( function ( $ ) {

	$( document ).ready( function () {

		var itwButtonImageSelector = '';

		imageTextWidgetFileUpload = {
			frame: function () {
				if ( this._frameImageTextWidget )
					return this._frameImageTextWidget;

				this._frameImageTextWidget = wp.media( {
					title: itwArgs.title,
					frame: itwArgs.frame,
					button: itwArgs.button,
					multiple: itwArgs.multiple,
					library: {
						type: 'image'
					}
				} );

				this._frameImageTextWidget.on( 'open', this.updateFrame ).state( 'library' ).on( 'select', this.select );
				return this._frameImageTextWidget;
			},
			select: function () {
				var attachment = this.frame.state().get( 'selection' ).first();
				var img = new Image();
				var parent = itwButtonImageSelector.parent();
				img.src = attachment.attributes.sizes.thumbnail.url;

				parent.find( '.itw-spinner' ).fadeIn( 300 );
				parent.find( '.itw_turn_off_image_button' ).attr( 'disabled', false );
				parent.find( '.itw_upload_image_id' ).val( attachment.attributes.id );
				parent.parent().find( '.itw-image-preview img' ).attr( 'src', attachment.attributes.sizes.thumbnail.url ).fadeIn( 300 );

				img.onload = function () {
					parent.find( '.itw-spinner' ).fadeOut( 300 );
				}
			},
			init: function () {
				$( document ).on( 'click', 'input.itw_upload_image_button', function ( e ) {
					itwButtonImageSelector = $( this );
					e.preventDefault();
					imageTextWidgetFileUpload.frame().open();
				} );
			}
		};

		imageTextWidgetFileUpload.init();

		$( document ).on( 'click', '.itw_turn_off_image_button', function ( event ) {
			$( this ).attr( 'disabled', true );
			$( this ).parent().find( '.itw_upload_image_id' ).val( 0 );
			var parentImg = $( this ).parent().parent().find( '.itw-image-preview img' );

			parentImg.fadeOut( 300, function () {
				parentImg.attr( 'src', '' );
			} );
		} );

		$( document ).on( 'change', '.itw-link-type', function ( event ) {
			var parent = $( this ).parent();

			if ( $( this ).val() === 'none' ) {
				parent.find( '.itw-link-custom' ).fadeOut( 300 );
				parent.find( '.itw-link-pages' ).fadeOut( 300 );
			} else if ( $( this ).val() === 'page' ) {
				parent.find( '.itw-link-custom' ).fadeOut( 300, function () {
					parent.find( '.itw-link-pages' ).fadeIn( 300 );
				} );
			} else {
				parent.find( '.itw-link-pages' ).fadeOut( 300, function () {
					parent.find( '.itw-link-custom' ).fadeIn( 300 );
				} );
			}
		} );

		$( document ).on( 'change', '.itw-size-type', function ( event ) {
			var parent = $( this ).parent();

			if ( $( this ).val() === 'custom' ) {
				parent.find( '.itw-custom-size' ).fadeIn( 300 );
			} else {
				parent.find( '.itw-custom-size' ).fadeOut( 300 );
			}
		} );

	} );

} )( jQuery );