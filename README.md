# Blocks - Dynamic content areas for WordPress 3.5 +

This project can be used to detect and restore APIs, features or functionality that have been deprecated in jQuery and removed as of version 1.9. They include:

* `jQuery.browser` [docs](http://api.jquery.com/jquery.browser)
* `jQuery.fn.andSelf()` [docs](http://api.jquery.com/andSelf)
* `jQuery.sub()` [docs](http://api.jquery.com/jquery.sub)
* `jQuery.fn.toggle()` [docs](http://api.jquery.com/toggle-event/) (_event click signature only_)
* `"hover"` pseudo-event name [docs](http://api.jquery.com/on/)
* `jQuery.fn.error()` [docs](http://api.jquery.com/error/)
* `ajaxStart, ajaxSend, ajaxSuccess, ajaxError, ajaxComplete, ajaxStop` global events on non-`document` targets [docs](http://api.jquery.com/category/ajax/global-ajax-event-handlers/)
* Use of `attrChange`, `attrName`, `relatedNode`, `srcElement` on the `Event` object (use `Event.originalEvent.attrChange` etc. instead)
* `jQuery.fn.attr()` using the `pass` argument (undocumented)
* `jQuery.attrFn` object (undocumented)
* `jQuery.fn.data()` data events (undocumented)
* `jQuery.fn.data("events")` to retrieve event-related data (undocumented)

See the [warnings](https://github.com/jquery/jquery-migrate/blob/master/warnings.md) page for more information regarding messages the plugin generates.

In your web page, make sure to load this plugin *after* the script for jQuery:

```html
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="http://code.jquery.com/jquery-migrate-1.0.0b1.js"></script>
```

The plugin can be included with versions of jQuery as old as 1.6.4 as a migration tool to identify potential upgrade issues. However, the plugin is only required for version 1.9 or higher to restore deprecated and removed functionality.

## Development vs. Production versions


