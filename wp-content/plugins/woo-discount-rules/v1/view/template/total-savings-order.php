<?php
/**
 * List matched Rules in Table format
 *
 * This template can be overridden by copying it to yourtheme/plugin-folder-name/total-savings-cart.php
 *
 * @param int/float $total_discount
 * @param string $total_discounted_price
 * @param string $subtotal_additional_text
 * @param object $order
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<style>
    .wdr_order_discount-total{
        color: green;
        margin: 10px 0;
    }
</style>
<div class="wdr_order_discount-total">
    <?php echo $subtotal_additional_text; ?>
</div>
