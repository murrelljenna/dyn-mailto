/*
The MIT License

Copyright (c) 2020 Jenna Murrell
Copyright (c) 2012 Amjad Masad

Permission is hereby granted, free of charge, 
to any person obtaining a copy of this software and 
associated documentation files (the "Software"), to 
deal in the Software without restriction, including 
without limitation the rights to use, copy, modify, 
merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom 
the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice and this permission notice 
shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR 
ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE 
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

'use strict';

(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {
	'use strict';
	var caretClass	= 'textarea-helper-caret'
	, dataKey		= 'textarea-helper'

	// Styles that could influence size of the mirrored element.
	, mirrorStyles = [ 
		// Box Styles.
	, 'box-sizing', 'height', 'width', 'padding-bottom'
	, 'padding-left', 'padding-right', 'padding-top'

		// Font stuff.
	, 'font-family', 'font-size', 'font-style' 
	, 'font-variant', 'font-weight'

		// Spacing etc.
	, 'word-spacing', 'letter-spacing', 'line-height'
	, 'text-decoration', 'text-indent', 'text-transform' 
	 
		// The direction.
	, 'direction'
	];

	var TextareaHelper = function (elem) {
		if (elem.nodeName.toLowerCase() !== 'textarea') return;
		this.$text = $(elem);
		this.$mirror = $('<div/>').css({
			'position' : 'absolute', 
			'overflow' : 'auto',
			'white-space' : 'pre-wrap',
			'word-wrap' : 'break-word',
			'top' : 0,
			'left' : -9999
		}).insertAfter(this.$text);
	};

	(function () {
	this.update = function () {

		// Copy styles.
		var styles = {};
		for (var i = 0, style; style = mirrorStyles[i]; i++) {
			styles[style] = this.$text.css(style);
		}

		this.$mirror.css(styles).empty();
		
		// Update content and insert caret.
		var caretPos = this.getOriginalCaretPos()
		, str		= this.$text.val()
		, pre		= document.createTextNode(str.substring(0, caretPos))
		, post	 = document.createTextNode(str.substring(caretPos))
		, $car	 = $('<span/>').addClass(caretClass).css('position', 'absolute').html('&nbsp;');
		this.$mirror.append(pre, $car, post)
					.scrollTop(this.$text.scrollTop());
	};

	this.destroy = function () {
		this.$mirror.remove();
		this.$text.removeData(dataKey);
		return null;
	};

	this.caretPos = function () {
		this.update();
		var $caret = this.$mirror.find('.' + caretClass)
		, pos	= $caret.position();
		if (this.$text.css('direction') === 'rtl') {
		pos.right = this.$mirror.innerWidth() - pos.left - $caret.width();
		pos.left = 'auto';
		}

		return pos;
	};

	this.height = function () {
		this.update();
		this.$mirror.css('height', '');
		return this.$mirror.height();
	};

	// XBrowser caret position
	// Adapted from http://stackoverflow.com/questions/263743/how-to-get-caret-position-in-textarea
	this.getOriginalCaretPos = function () {
		var text = this.$text[0];
		if (text.selectionStart) {
		return text.selectionStart;
		} else if (document.selection) {
		text.focus();
		var r = document.selection.createRange();
		if (r == null) {
			return 0;
		}
		var re = text.createTextRange()
			, rc = re.duplicate();
		re.moveToBookmark(r.getBookmark());
		rc.setEndPoint('EndToStart', re);
		return rc.text.length;
		} 
		return 0;
	};

	}).call(TextareaHelper.prototype);
	
	$.fn.textareaHelper = function (method) {
	this.each(function () {
		var $this	= $(this)
		, instance = $this.data(dataKey);
		if (!instance) {
		instance = new TextareaHelper(this);
		$this.data(dataKey, instance);
		}
	});
	if (method) {
		var instance = this.first().data(dataKey);
		return instance[method]();
	} else {
		return this;
	}
	};

}));

jQuery(function() {
	jQuery("document").ready(function() {
		// The tags we will be looking for
		var field_names = textcomplete_ajax_params;
		// State variable to keep track of which category we are in
		var tagState = field_names;

		// Helper functions
		function split(val) {
			return val.split( /(?={{\s*)/ );
		}

		function extractLast(term) {
			return split(term).pop().substring(2).trim();
		}


		jQuery(".widefat")

		// Create the autocomplete box
		.autocomplete({
			minLength : 0,
			autoFocus : true,
			source : function(request, response) {
				// Use only the last entry from the textarea (exclude previous matches)
				var lastEntry = extractLast(request.term);

				var filteredArray = jQuery.map(tagState, function(item) {
					if (item.indexOf(lastEntry) === 0) {
						return item;
					} else {
						return null;
					}
				});
				
				// delegate back to autocomplete, but extract the last term
				response(jQuery.ui.autocomplete.filter(filteredArray, lastEntry));
			},
			focus : function() {
				// prevent value inserted on focus
				return false;
			},
			select : function(event, ui) {
				var terms = split(this.value);
				terms.pop();

				// add the selected item
				terms.push(`{{ ${ui.item.value} }}`);
				// add placeholder to get the comma-and-space at the end
				terms.push("");
				this.value = terms.join("");
				return false;
			}
		}).on("keydown", function(event) {
			// don't navigate away from the field on tab when selecting an item
			if (event.keyCode === jQuery.ui.keyCode.TAB /** && jQuery(this).data("ui-autocomplete").menu.active **/) {
				event.preventDefault();
				return;
			}
			// Check that cursor is in open bracket

			const { value } = event.target;

			const openBraces = value.replace(/{{[ a-zA-Z0-9_]+}}/g, match => match.replace(/./g, '*')).indexOf("{{")

			if (openBraces >= 0) {
				jQuery(this).autocomplete( "option", "disabled", false );
			} else {
				jQuery(this).autocomplete( "option", "disabled", true );
			}

			// Code to position and move the selection box as the user types
			var newY = jQuery(this).textareaHelper('caretPos').top + (parseInt(jQuery(this).css('font-size'), 10) * 1.5);
			var newX = jQuery(this).textareaHelper('caretPos').left;
			var posString = "left+" + newX + "px top+" + newY + "px";
			jQuery(this).autocomplete("option", "position", {
				my : "left top",
				at : posString
			});
		});
	});
});
