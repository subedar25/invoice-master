(function( factory ){
	if ( typeof define === 'function' && define.amd ) {
		// AMD
		define( ['jquery', 'datatables.net', 'datatables.net-buttons'], function ( $ ) {
			return factory( $, window, document );
		} );
	}
	else if ( typeof exports === 'object' ) {
		// CommonJS
		module.exports = function (root, $) {
			if ( ! root ) {
				root = window;
			}

			if ( ! $ || ! $.fn.dataTable ) {
				$ = require('datatables.net')(root, $).$;
			}

			if ( ! $.fn.dataTable.Buttons ) {
				require('datatables.net-buttons')(root, $);
			}

			return factory( $, root, root.document );
		};
	}
	else {
		// Browser
		factory( jQuery, window, document );
	}
}(function( $, window, document, undefined ) {
'use strict';
var DataTable = $.fn.dataTable;


var _link = document.createElement( 'a' );

/**
 * Clone link and style tags, taking into account the need to change the source
 * path.
 *
 * @param  {node}     el Element to convert
 */
var _styleToAbs = function( el ) {
	var url;
	var clone = $(el).clone()[0];
	var linkHost;

	if ( clone.nodeName.toLowerCase() === 'link' ) {
		clone.href = _relToAbs( clone.href );
	}

	return clone.outerHTML;
};

/**
 * Convert a URL from a relative to an absolute address so it will work
 * correctly in the popup window which has no base URL.
 *
 * @param  {string} href URL
 */
var _relToAbs = function( href ) {
	// Assign to a link on the original page so the browser will do all the
	// hard work of figuring out where the file actually is
	_link.href = href;
	var linkHost = _link.host;

	// IE doesn't have a trailing slash on the host
	// Chrome has it on the pathname
	if ( linkHost.indexOf('/') === -1 && _link.pathname.indexOf('/') !== 0) {
		linkHost += '/';
	}

	return _link.protocol+"//"+linkHost+_link.pathname+_link.search;
};


DataTable.ext.buttons.print = {
	className: 'buttons-print',

	text: function ( dt ) {
		return dt.i18n( 'buttons.print', 'Print' );
	},

	action: function ( e, dt, button, config ) {
		var data = dt.buttons.exportData(
			$.extend( {decodeEntities: false}, config.exportOptions ) // XSS protection
		);
		var exportInfo = dt.buttons.exportInfo( config );
		var columnClasses = dt
			.columns( config.exportOptions.columns )
			.flatten()
			.map( function (idx) {
				return dt.settings()[0].aoColumns[dt.column(idx).index()].sClass;
			} )
			.toArray();

		var addRow = function ( d, tag ) {
			var str = '<tr>';

			for ( var i=0, ien=d.length ; i<ien ; i++ ) {
				// null and undefined aren't useful in the print output
				var dataOut = d[i] === null || d[i] === undefined ?
					'' :
					d[i];
				var classAttr = columnClasses[i] ?
					'class="'+columnClasses[i]+'"' :
					'';

				str += '<'+tag+' '+classAttr+'>'+dataOut+'</'+tag+'>';
			}

			return str + '</tr>';
		};

		// Construct a table for printing
		var html = '<table class="'+dt.table().node().className+'">';

		if ( config.header ) {
			html += '<thead>'+ addRow( data.header, 'th' ) +'</thead>';
		}

		html += '<tbody>';
		for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
			html += addRow( data.body[i], 'td' );
		}
		html += '</tbody>';

		if ( config.footer && data.footer ) {
			html += '<tfoot>'+ addRow( data.footer, 'th' ) +'</tfoot>';
		}
		html += '</table>';

		// In-place print: render in the same window and call window.print().
		// This avoids a popup window, so the search box and page stay usable after print.
		var wrapper = document.createElement( 'div' );
		wrapper.className = 'dt-print-view';
		wrapper.setAttribute( 'id', 'DataTables_Print_Wrapper' );
		wrapper.style.cssText = 'position:absolute;left:-9999px;top:0;width:1px;height:1px;overflow:hidden;';

		var headDiv = document.createElement( 'div' );
		headDiv.setAttribute( 'data-dt-print-head', '1' );
		$('style, link').each( function () {
			var el = this;
			var tag = el.nodeName.toLowerCase();
			var clone = document.createElement( tag );
			if ( tag === 'link' ) {
				clone.href = _relToAbs( el.href );
				clone.rel = el.rel || 'stylesheet';
				if ( el.type ) clone.type = el.type;
			} else {
				clone.textContent = el.textContent || el.innerText || '';
			}
			headDiv.appendChild( clone );
		} );

		var bodyDiv = document.createElement( 'div' );
		bodyDiv.innerHTML =
			'<h1>'+exportInfo.title+'</h1>'+
			'<div>'+(exportInfo.messageTop || '')+'</div>'+
			html+
			'<div>'+(exportInfo.messageBottom || '')+'</div>';
		bodyDiv.className = 'dt-print-view';

		$('img', bodyDiv).each( function ( i, img ) {
			var src = img.getAttribute( 'src' );
			if ( src ) img.setAttribute( 'src', _relToAbs( src ) );
		} );

		wrapper.appendChild( headDiv );
		wrapper.appendChild( bodyDiv );
		document.body.appendChild( wrapper );

		var printStyleId = 'DataTables_Print_Media_Style';
		var printStyle = document.createElement( 'style' );
		printStyle.setAttribute( 'id', printStyleId );
		printStyle.textContent =
			'@media print {' +
			'body * { visibility: hidden; }' +
			'#DataTables_Print_Wrapper, #DataTables_Print_Wrapper * { visibility: visible; }' +
			'#DataTables_Print_Wrapper { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; height: auto !important; overflow: visible !important; }' +
			'}' +
			'@media screen {' +
			'#DataTables_Print_Wrapper { position: absolute !important; left: -9999px !important; overflow: hidden !important; }' +
			'}';
		document.head.appendChild( printStyle );

		var removePrintView = function () {
			try {
				if ( wrapper.parentNode ) wrapper.parentNode.removeChild( wrapper );
				var el = document.getElementById( printStyleId );
				if ( el && el.parentNode ) el.parentNode.removeChild( el );
			} catch ( err ) {}
		};

		var fakeWin = {
			document: {
				head: headDiv,
				body: bodyDiv,
				documentElement: wrapper
			},
			print: function () { window.print(); },
			close: removePrintView
		};

		if ( config.customize ) {
			config.customize( fakeWin, config, dt );
		}

		var doPrint = function () {
			if ( config.autoPrint ) {
				window.print();
			}
			removePrintView();
		};

		if ( navigator.userAgent.match(/Trident\/\d.\d/) ) {
			doPrint();
		} else {
			setTimeout( doPrint, 250 );
		}
	},

	title: '*',

	messageTop: '*',

	messageBottom: '*',

	exportOptions: {},

	header: true,

	footer: false,

	autoPrint: true,

	customize: null
};


return DataTable.Buttons;
}));
