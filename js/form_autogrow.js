jQuery(function() {
    jQuery( document ).on('widget-updated widget-added', () => autosize(jQuery('.widefat')));
	jQuery("document").ready(() => autosize(jQuery('.widefat')));
});
