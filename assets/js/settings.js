( function ( $ ) {
	var AAL = {
		$wrapper: {},
		$container: {},
		conter: 0,

		init: function () {
			var _this = this;

			this.$wrapper = $( ".aal-notifier-settings" );
			this.$container = $( "ul", this.$wrapper );

			this.counter = this.$container.children().size();

			// when the "add" button is clicked
			this.$container.on( 'click', '.aal-new-rule', function ( e ) {
				e.preventDefault();
				_this.addRule( $( this ).closest( 'li' ) );
			});

			// handle change on action category selectbox
			this.$container.on( 'change', '.aal-category', function ( e ) {
				e.preventDefault();

				var $select = $( this ),
					$siblings = $select.siblings( "select" );

				// disable all selectboxes to prevent multiple calls
				$siblings.filter( "select" ).prop( 'disabled', true );

				// grab live data via AJAX
				var data = _this.getData( $select.val(), function ( d ) {
					var $target = $siblings.filter( '.aal-value' );
					$target.empty(); // clear so we can insert fresh data

					$.each( d.data, function ( k, v ) {
						$target.append( $( "<option/>", {
							text: v,
							value: k
						} ) );
						console.log( 'AAL appending', v, k );
					});

					// restore disabled selectboxes
					$siblings.filter( "select" ).prop( 'disabled', false );
				});
			});

		},
		addRule: function ( $el ) {
			this.counter++;
			var $copy = $el.clone(),
				curID = parseInt( $el.data( 'id' ) ),
				newID = this.counter;

			$copy.find( '[name]' ).each( function() {
				$( this ).attr( 'name', $( this ).attr( 'name' ).replace( curID, newID ) );
				// $( this ).attr( 'id', $( this ).attr( 'id' ).replace( curID, newID ) );
			});

			$copy.attr( 'data-id', newID );
			$el.after( $copy );
		},
		getData: function ( type, cb ) {
			var payload = {
				action: 'aal_get_properties',
				action_category: type
			};
			$.getJSON( window.ajaxurl, payload, cb );
		}
	};

	$( function () {
		AAL.init();
	});

	window.AAL = AAL;


	/**
	 * Form serialization helper
	 */
	$.fn.AALSerializeObject = function() {
	    var o = {};
	    var a = this.serializeArray();
	    $.each(a, function() {
	        if (o[this.name] !== undefined) {
	            if (!o[this.name].push) {
	                o[this.name] = [o[this.name]];
	            }
	            o[this.name].push(this.value || '');
	        } else {
	            o[this.name] = this.value || '';
	        }
	    });
	    return o;
	};
})( jQuery );