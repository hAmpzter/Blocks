# Blocks - Dynamic content areas for WordPress 3.5 +


In your page-template where you want blocks add this (example: Left column) in page.php:

```html
<?php // Block Areas: left ?>

<?php get_blocks('left'); ?>

```

The plugin can be included with versions of jQuery as old as 1.6.4 as a migration tool to identify potential upgrade issues. However, the plugin is only required for version 1.9 or higher to restore deprecated and removed functionality.

## Development vs. Production versions


