# Blocks - Dynamic content areas for WordPress 3.5 +


In your page-template where you want blocks add this (example: Left column) in page.php:

```php
<?php // Block Areas: left ?>

<?php get_blocks('left'); ?>

```
