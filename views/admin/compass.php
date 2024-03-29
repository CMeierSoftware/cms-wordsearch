<?php
declare(strict_types=1);
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="compass">

    <input type="checkbox" <?php checked(in_array('northwest', $value, true)); ?> id="northwest"
        name="<?php echo esc_attr($name); ?>[]" value="northwest">
    <label for="northwest" class="dashicons dashicons-arrow-up-alt" style="transform:rotate(-45deg);"></label>

    <input type="checkbox" <?php checked(in_array('north', $value, true)); ?> id="north" name="<?php echo esc_attr($name); ?>[]"
        value="north">
    <label for="north" class="dashicons dashicons-arrow-up-alt"></label>

    <input type="checkbox" <?php checked(in_array('northeast', $value, true)); ?> id="northeast"
        name="<?php echo esc_attr($name); ?>[]" value="southeast">
    <label for="northeast" class="dashicons dashicons-arrow-up-alt" style="transform:rotate(45deg);"></label>

    <input type="checkbox" <?php checked(in_array('west', $value, true)); ?> id="west" name="<?php echo esc_attr($name); ?>[]"
        value="west">
    <label for="west" class="dashicons dashicons-arrow-left-alt"></label>

    <input type="checkbox" id="middle" name="" value="middle">
    <label for="middle" class=""></label>

    <input type="checkbox" <?php checked(in_array('east', $value, true)); ?> id="east" name="<?php echo esc_attr($name); ?>[]"
        value="east">
    <label for="east" class="dashicons dashicons-arrow-right-alt"></label>

    <input type="checkbox" <?php checked(in_array('southwest', $value, true)); ?> id="southwest"
        name="<?php echo esc_attr($name); ?>[]" value="southwest">
    <label for="southwest" class="dashicons dashicons-arrow-down-alt" style="transform:rotate(45deg);"></label>

    <input type="checkbox" <?php checked(in_array('south', $value, true)); ?> id="south" name="<?php echo esc_attr($name); ?>[]"
        value="south">
    <label for="south" class="dashicons dashicons-arrow-down-alt"></label>

    <input type="checkbox" <?php checked(in_array('southeast', $value, true)); ?> id="southeast"
        name="<?php echo esc_attr($name); ?>[]" value="northeast">
    <label for="southeast" class="dashicons dashicons-arrow-down-alt" style="transform:rotate(-45deg);"></label>
</div>

<style>
    .compass {
        display: grid;
        grid-template-columns: repeat(3, 25px);
        grid-gap: 3px;
        text-align: center;
        font-size: 24px;
    }

    .compass input[type="checkbox"] {
        display: none;
    }

    .compass input[type="checkbox"]+label {
        color: lightgrey;
    }

    .compass input[type="checkbox"]:checked+label {
        color: black;
    }
</style>