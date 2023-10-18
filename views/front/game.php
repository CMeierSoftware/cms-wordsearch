<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<h2>Hallo</h2>
<?php
    if (!empty($instructions)) {
        ?>
        <p class="instructions"><?php echo wp_kses($instructions, 'post'); ?></p>
        <?php
    }
?>
<div class="row wha-wordsearch-row">
    <div class="wha-wordsearch-container">
<div class="wha-wordsearch" id="wha-wordsearch"></div>