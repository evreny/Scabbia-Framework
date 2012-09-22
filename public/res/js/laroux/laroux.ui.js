(function(laroux) {
	// "use strict";

	// ui
	laroux.ui = {
		floatContainer: null,

		createFloatContainer: function() {
			if(!laroux.ui.floatContainer) {
				laroux.dom.loadImage(
					laroux.baseLocation + '/res/css/images/box/corners.gif',
					laroux.baseLocation + '/res/css/images/box/l.gif',
					laroux.baseLocation + '/res/css/images/box/tb.gif',
					laroux.baseLocation + '/res/css/images/box/r.gif'
				);

				laroux.ui.floatContainer = laroux.dom.createElement('DIV', { id: 'floatDiv' }, '');
				document.body.insertBefore(laroux.ui.floatContainer, document.body.firstChild);
			}
		},

		defaultTexts: function() {
			var focusFunc = function() {
				var elem = $(this);
				
				if(elem.val() == elem.attr('data-defaulttext')) {
					elem.val('');
				}
			};
			
			var blurFunc = function() {
				var elem = $(this);
				
				if(elem.val() == '') {
					elem.val(elem.attr('data-defaulttext'));
				}
			};
			
			$('*[data-defaulttext]').each(function() {
				var elem = $(this);
				
				if(elem.val() == '') {
					elem.val(elem.attr('data-defaulttext'));
				}
				
				elem.focus(focusFunc);
				elem.blur(blurFunc);
			});
		},

		createBox: function(id, xclass, message) {
			return laroux.dom.createElement('DIV', { id: id, class: xclass },
				'<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>' +
				'<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">' + message + '</div></div></div>' +
				'<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>'
			);
		},

		msgbox: function(timeout, message) {
			var id = laroux.helpers.getUniqueId();
			laroux.ui.floatContainer.appendChild(laroux.ui.createBox(id, 'x-box msgbox', message));
			var obj = $('#' + id);
			$(obj).fadeIn('slow');
			laroux.timers.set(timeout, function(x) { $(x).fadeOut('slow'); }, obj);
		},
		
		autocomplete: function(obj, settings) {
			if(typeof $.ui.autocomplete !== 'undefined') {
				$.widget('custom.catcomplete', $.ui.autocomplete, {
					_renderMenu: function(ul, items) {
						var self = this,
						currentCategory = '';
						
						$.each(items, function(index, item) {
							if(self.options.categories === true && item.category != currentCategory) {
								ul.append('<li class="ui-autocomplete-category">' + item.category + '</li>');
								currentCategory = item.category;
							}
							
							var text = '<a><span class="label">' + item.label + '</span>';
							if(self.options.descriptions === true) {
								text += '<span class="desc">' + item.desc + '</span></a>';
							}
							
							ul.append($('<li></li>').data('item.autocomplete', item).append(text));
							// self._renderItem(ul, item);
						});
					},
					dropdown: function() {
						this.element.focus();
						this.search('', null);
					}
				});
			}
			
			settings.select = function(event, ui) {
				if(typeof settings.hiddenfield !== 'undefined') {
					settings.hiddenfield.val(ui.item.hiddenvalue);
				}
				
				if(typeof settings.onselect !== 'undefined') {
					settings.onselect(event, ui);
				}
			};
			
			obj.click(function() {
				var catcomplete = obj.data('catcomplete');
				catcomplete.search((catcomplete.options.alllist === true) ? '' : null, null);
			});

			obj.addClass('catcomplete');
			obj.catcomplete(settings);

			if(typeof settings.hiddenfield !== 'undefined') {
				for(x in settings.source) {
					if(settings.source[x].hiddenvalue == settings.hiddenfield.val()) {
						obj.val(settings.source[x].label);
						break;
					}
				}
			}
		},
		
		datepicker: function(obj, settings) {
			settings.changeMonth = true;
			settings.changeYear = true;
			settings.dateFormat = 'dd/mm/yy';
			
			obj.datepicker(settings);
		}
	};

	laroux.ready(laroux.ui.createFloatContainer);
	laroux.ready(laroux.ui.defaultTexts);

	laroux.popupFunc = function(message) {
		laroux.ui.msgbox(5, message);
	};

})(window.laroux);