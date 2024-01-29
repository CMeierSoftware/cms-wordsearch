<?php 
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

foreach ($values as $key => $label) {
    $dd_options[] = sprintf(
        '<option %s value="%s">%s</option>',
        selected($value, $key, false),
        esc_attr($key),
        esc_html($label)
    );
}
?>
<select id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>">
    <?php echo implode('', $dd_options); ?>
</select>