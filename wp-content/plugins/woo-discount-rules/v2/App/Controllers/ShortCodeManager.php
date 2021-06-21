<?php

namespace Wdr\App\Controllers;

use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ShortCodeManager extends ManageDiscount
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Create SKU query arguments
     * @param $query_arguments
     * @param $skus
     * @param $exclude_skus
     * @param $all_products
     */
    function setSkuQueryArguments(&$query_arguments, $skus, $exclude_skus, $all_products)
    {
        $this->removeDuplicateValuesInArray($skus, $exclude_skus);
        if (!empty($skus) && empty($all_products)) {
            $query_arguments['meta_query'][] = array(
                'key' => '_sku',
                'value' => $skus,
                'compare' => 'IN',
            );
        }
        if (!empty($exclude_skus)) {
            $query_arguments['meta_query'][] = array(
                'key' => '_sku',
                'value' => $exclude_skus,
                'compare' => 'NOT IN',
            );
        }
    }

    /**
     * Create on sale query arguments
     * @param $query_arguments
     * @param $onsale_product_id
     * @param $exclude_product_id
     * @param $all_products
     */
    function setOnSaleQueryArguments(&$query_arguments, $on_sale_products, $all_products)
    {
        if (function_exists('wc_get_product_ids_on_sale')) {
            $product_ids_on_sale = wc_get_product_ids_on_sale();
        }
        if (!empty($on_sale_products) && $on_sale_products == 'in_list' && empty($all_products)) {
            /*$query_arguments['meta_query'][] = array( // Simple products type
                'key'           => '_sale_price',
                'value'         => 0,
                'compare'       => '>',
                'type'          => 'numeric'
            );
            $query_arguments['meta_query'][] =  array( // Variable products type
                'key'           => '_min_variation_sale_price',
                'value'         => 0,
                'compare'       => '>',
                'type'          => 'numeric'
            );*/

            if(isset($query_arguments['post__in']) && !empty($query_arguments['post__in'])){
                    $query_arguments['post__in'] = array_merge($query_arguments['post__in'],$product_ids_on_sale);
                    $query_arguments['post__in'] = array_unique($query_arguments['post__in']);
            }else{
                    $query_arguments['post__in'] = $product_ids_on_sale;
            }
        }
        if (!empty($on_sale_products) && $on_sale_products == 'not_in_list' ) {
            if(isset($query_arguments['post__not_in']) && !empty($query_arguments['post__not_in'])){
               $query_arguments['post__not_in'] = array_merge($query_arguments['post__not_in'],$product_ids_on_sale);
                $query_arguments['post__not_in'] = array_unique($query_arguments['post__not_in']);
            }else{
                $query_arguments['post__not_in'] = $product_ids_on_sale;
            }
        }
    }

    /**
     * Create SKU query arguments
     * @param $query_arguments
     * @param $products
     * @param $exclude_products
     * @param $all_products
     */
    function setProductsQueryArguments(&$query_arguments, $products, $exclude_products, $all_products)
    {
        /*
         * As per https://www.billerickson.net/code/wp_query-arguments/
         * you cannot combine 'post__in' and 'post__not_in' in the same query
         */
        //TODO: you cannot combine 'post__in' and 'post__not_in' in the same query
        $this->removeDuplicateValuesInArray($products, $exclude_products);
        if (!empty($products) && empty($all_products)) {
            $query_arguments['post__in'] = $products;
        }
        if (!empty($exclude_products)) {
            $query_arguments['post__not_in'] = $exclude_products;
        }
    }

    /**
     * Set the query relations
     * @param $query_arguments
     */
    function setQueryRelationship(&$query_arguments)
    {
        if (!empty($query_arguments['tax_query'])) {
            if (!empty($query_arguments['tax_query']['include'])) {
                $query_arguments['tax_query']['include']['relation'] = 'or';
            }
            if (!empty($query_arguments['tax_query']['exclude'])) {
                $query_arguments['tax_query']['exclude']['relation'] = 'or';
            }
            if (!empty($query_arguments['tax_query'])) {
                $query_arguments['tax_query']['relation'] = 'and';
            }
        }
    }

    /**
     * Create Tags query arguments
     * @param $query_arguments
     * @param $tags
     * @param $exclude_tags
     * @param $all_products
     */
    function setTagsQueryArguments(&$query_arguments, $tags, $exclude_tags, $all_products)
    {
        $this->removeDuplicateValuesInArray($tags, $exclude_tags);
        if (!empty($tags) && empty($all_products)) {
            $tags = array_map('absint', $tags);
            $query_arguments['tax_query']['include'][] = array(
                'taxonomy' => 'product_tag',
                'terms' => $tags,
                'field' => 'term_id',
                'operator' => 'IN',
            );
        }
        if (!empty($exclude_tags)) {
            $exclude_tags = array_map('absint', $exclude_tags);
            $query_arguments['tax_query']['exclude'][] = array(
                'taxonomy' => 'product_tag',
                'terms' => $exclude_tags,
                'field' => 'term_id',
                'operator' => 'NOT IN',
            );
        }
    }

    /**
     * Create Tags query arguments
     * @param $query_arguments
     * @param $taxonomies
     * @param $exclude_taxonomies
     * @param $all_products
     */
    function setCustomTaxonomyQueryArguments(&$query_arguments, $taxonomies, $exclude_taxonomies, $all_products)
    {
        $this->removeDuplicateValuesInArray($taxonomies, $exclude_taxonomies);
        if (!empty($taxonomies) && empty($all_products)) {
            foreach ($taxonomies as $taxonomy => $values) {
                $values = array_map('absint', $values);
                $query_arguments['tax_query']['include'][] = array(
                    'taxonomy' => $taxonomy,
                    'terms' => $values,
                    'field' => 'term_id',
                    'operator' => 'IN',
                );
            }
        }
        if (!empty($exclude_taxonomies)) {
            foreach ($exclude_taxonomies as $taxonomy => $values) {
                $values = array_map('absint', $values);
                $query_arguments['tax_query']['include'][] = array(
                    'taxonomy' => $taxonomy,
                    'terms' => $values,
                    'field' => 'term_id',
                    'operator' => 'NOT IN',
                );
            }
        }
    }

    /**
     * Create Category query arguments
     * @param $query_arguments
     * @param $categories
     * @param $exclude_categories
     * @param $all_products
     */
    function setCategoriesQueryArguments(&$query_arguments, $categories, $exclude_categories, $all_products)
    {
        $this->removeDuplicateValuesInArray($categories, $exclude_categories);
        if (!empty($categories) && empty($all_products)) {
            $categories = array_map('absint', $categories);
            $query_arguments['tax_query']['include'][] = array(
                'taxonomy' => 'product_cat',
                'terms' => $categories,
                'field' => 'term_id',
                'operator' => 'IN',
                'include_children' => true
            );
        }
        if (!empty($exclude_categories)) {
            $exclude_categories = array_map('absint', $exclude_categories);
            $query_arguments['tax_query']['exclude'][] = array(
                'taxonomy' => 'product_cat',
                'terms' => $exclude_categories,
                'field' => 'term_id',
                'operator' => 'NOT IN',
                'include_children' => true
            );
        }
    }

    /**
     * Remove duplicate value by comparing 2 different array
     * @param $array_1
     * @param $array_2
     * @param int $remove_from
     */
    function removeDuplicateValuesInArray(&$array_1, &$array_2, $remove_from = 2)
    {
        $duplicate_values = array_intersect($array_1, $array_2);
        if ($remove_from == 2) {
            $array_2 = array_diff($array_2, $duplicate_values);
        } else {
            $array_1 = array_diff($array_1, $duplicate_values);
        }
    }

    /**
     * Set attributes query args.
     * @param $query_arguments
     * @param $attributes
     * @param $exclude_attributes
     * @param $all_products
     */
    protected function setAttributesQueryArguments(&$query_arguments, $attributes, $exclude_attributes, $all_products)
    {
        if (!empty($attributes) && empty($all_products)) {
            $attributes = array_map('absint', $attributes);
            foreach ($attributes as $attribute) {
                $data = get_term($attribute);
                if (!empty($data)) {
                    $taxonomy = $data->taxonomy;
                    $query_arguments['tax_query']['include'][$taxonomy] = array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'operator' => 'IN'
                    );
                    $query_arguments['tax_query']['include'][$taxonomy]['terms'][] = $attribute;
                }
            }
        }
        if (!empty($exclude_attributes)) {
            $exclude_attributes = array_map('absint', $exclude_attributes);
            foreach ($exclude_attributes as $attribute) {
                $data = get_term($attribute);
                if (!empty($data)) {
                    $taxonomy = $data->taxonomy;
                    $query_arguments['tax_query']['exclude'][$taxonomy] = array(
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'operator' => 'NOT IN'
                    );
                    $query_arguments['tax_query']['exclude'][$taxonomy]['terms'][] = $attribute;
                }
            }
        }
    }

    function modifyFilterArguments(&$query_arguments, $order_by)
    {
        switch ($order_by) {
            default:
                $meta_key = NULL;
                $order = 'asc';
                $order_by = 'title';
                break;
            case 'popularity':
                $meta_key = '';
                $order = 'asc';
                $order_by = 'post_views';
                break;
            case 'price':
            case 'low_to_high':
                $meta_key = '_price';
                $order = 'asc';
                $order_by = 'meta_value_num';
                break;
            case 'price-desc':
            case 'high_to_low':
                $meta_key = '_price';
                $order = 'desc';
                $order_by = 'meta_value_num';
                break;
            case 'date':
            case 'newness':
                $meta_key = NULL;
                $order = 'desc';
                $order_by = 'date';
                break;
            case 'rating':
                $meta_key = NULL;
                $order = 'desc';
                $order_by = 'rating';
                break;
        }
        $query_arguments['orderby'] = $order_by;
        $query_arguments['order'] = $order;
        if (!empty($meta_key)) {
            $query_arguments['meta_key'] = $meta_key;
        }
    }

    /**
     * Show sale items by short code
     * @param $short_code_attributes
     * @return string
     */
    function saleItemsList($short_code_attributes)
    {
        if (!empty(self::$available_rules)) {
            global $woocommerce_loop;
            $short_code_attributes = shortcode_atts(array(
                'per_page' => 12,
                'columns' => 3,
                'orderby' => 'title',
                'do_pagination' => 1,
                'order' => 'asc'
            ), $short_code_attributes);
            $paged = $this->input->get('product-page', 1);
            $order_by = $this->input->get('orderby', 'title');

            ob_start();
            $onsale_list = OnSaleShortCode::getOnSaleList();
            if(!empty($onsale_list)){
                $query_arguments = array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'paged' => $paged,
                    'posts_per_page' => $short_code_attributes['per_page'],
                    'orderby' => $short_code_attributes['orderby'],
                    'order' => $short_code_attributes['order'],
                );
                if($onsale_list['has_store_wide']){
                    if (!empty($onsale_list['list'])) {
                        $query_arguments['post__not_in'] = $onsale_list['list'];
                    }
                } else {
                    if (!empty($onsale_list['list'])) {
                        $query_arguments['post__in'] = $onsale_list['list'];
                    }
                }
                if(apply_filters('advanced_woo_discount_rules_exclude_out_of_stock_product_on_sale_page', false)){
                    $exclude_out_of_stock = array('meta_query' => array(
                        array(
                            'key' => '_stock_status',
                            'value' => 'instock'
                        ),
                        array(
                            'key' => '_backorders',
                            'value' => 'no'
                        ),
                    ));
                    $query_arguments = array_merge($query_arguments, $exclude_out_of_stock);
                }
            } else {
                $query_arguments = array();
            }
            $products = new \WP_Query($query_arguments);
            $columns = absint($short_code_attributes['columns']);
            $woocommerce_loop['columns'] = $columns;
            if ($products->have_posts()) {
                self::$woocommerce_helper->setLoopProperties('is_shortcode', true);
                self::$woocommerce_helper->setLoopProperties('is_paginated', (!empty($short_code_attributes['do_pagination'])));
                self::$woocommerce_helper->setLoopProperties('per_page', $short_code_attributes['per_page']);
                self::$woocommerce_helper->setLoopProperties('current_page', $paged);
                $total = $products->found_posts;
                self::$woocommerce_helper->setLoopProperties('total', $total);
                $total_pages = ceil($total / $short_code_attributes['per_page']);
                self::$woocommerce_helper->setLoopProperties('total_pages', $total_pages);
                do_action('woocommerce_before_shop_loop');
                self::$woocommerce_helper->productLoopStart();
                while ($products->have_posts()) {
                    $products->the_post();
                    wc_get_template_part('content', 'product');
                }
                self::$woocommerce_helper->productLoopEnd();
                do_action('woocommerce_after_shop_loop');
                // woocommerce_pagination();
                wp_reset_postdata();
            } else {
                do_action('woocommerce_no_products_found');
            }
            return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
        }
        return NULL;
    }

    /**
     * Show sale items by short code
     * @param $short_code_attributes
     * @return string
     */
    function saleItemsList_old($short_code_attributes)
    {
        if (!empty(self::$available_rules)) {
            global $woocommerce_loop;
            $short_code_attributes = shortcode_atts(array(
                'per_page' => 12,
                'columns' => 3,
                'orderby' => 'title',
                'do_pagination' => 1,
                'order' => 'asc'
            ), $short_code_attributes);
            $paged = $this->input->get('product-page', 1);
            $order_by = $this->input->get('orderby', 'title');
            $exclude_categories = $exclude_products = $exclude_tags = $exclude_attributes = $exclude_skus = $exclude_custom_taxonomies = array();
            $categories = $products = $tags = $attributes = $skus = $custom_taxonomies = array();
            $all_products = $on_sale_products = '';
            $query_arguments = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'paged' => $paged,
                'posts_per_page' => $short_code_attributes['per_page'],
                'orderby' => $short_code_attributes['orderby'],
                'order' => $short_code_attributes['order'],
            );
            $this->modifyFilterArguments($query_arguments, $order_by);
            foreach (self::$available_rules as $rule) {
                $discount_type = $rule->getRuleDiscountType();
                if($discount_type == 'wdr_buy_x_get_y_discount'){
                    $get_y = $rule->getBuyXGetYAdjustment();
                    $type = (isset($get_y->type) && !empty($get_y->type)) ? $get_y->type : '';
                    $parent_id = array();
                    foreach ($get_y->ranges as $range){
                        $get_y_products = isset($range->products) ? $range->products : array();
                        $get_y_parent_ids = isset($range->product_variants_for_sale_badge) ? $range->product_variants_for_sale_badge : array();
                        foreach ($get_y_parent_ids as $get_y_parent_id){
                            $get_y_parent_id_array = isset($get_y_parent_id) ? $get_y_parent_id : array();
                            $parent_id = array_merge($parent_id, $get_y_parent_id_array);
                        }
                        if(!empty($type)){
                            if($type == 'bxgy_product' && isset($range->products) && !empty($range->products)){
                                if(!empty($parent_id)){
                                    $get_y_products = array_merge($get_y_products, $parent_id);
                                }
                                $products = array_merge($products, $get_y_products);
                                $products = array_unique($products);
                            }
                            if($type == 'bxgy_category' &&  isset($range->categories) && !empty($range->categories)){
                                $categories = array_merge($categories, $range->categories);
                            }
                        }
                    }
                }
                $filters = $rule->getFilter();
                foreach ($filters as $filter) {
                    $type = $rule->getFilterType($filter);
                    $values = (array)$rule->getFilterOptionValue($filter);
                    $parent_product_id = (array)$rule->getFilterOptionParentValue($filter);
                    $method = $rule->getFilterMethod($filter);
                    switch ($type) {
                        case "all_products":
                            $all_products = 'yes';
                            break;
                        case "product_on_sale":
                            $on_sale_products = $method;
                            break;
                        case "product_category":
                            if ($method == "in_list") {
                                $categories = array_merge($categories, $values);
                                $categories = array_unique($categories);
                            } else {
                                $exclude_categories = array_merge($exclude_categories, $values);
                            }
                            break;
                        case "products":
                            if ($method == "in_list") {
                                if(!empty($parent_product_id)){
                                    $values = array_merge($values, $parent_product_id);
                                }
                                $products = array_merge($products, $values);
                                $products = array_unique($products);
                            } else {
                                $exclude_products = array_merge($exclude_products, $values);
                            }
                            break;
                        case "product_tags":
                            if ($method == "in_list") {
                                $tags = array_merge($tags, $values);
                            } else {
                                $exclude_tags = array_merge($exclude_tags, $values);
                            }
                            break;
                        case "product_attributes":
                            if ($method == "in_list") {
                                $attributes = array_merge($attributes, $values);
                            } else {
                                $exclude_attributes = array_merge($exclude_attributes, $values);
                            }
                            break;
                        case "product_sku":
                            if ($method == "in_list") {
                                $skus = array_merge($skus, $values);
                            } else {
                                $exclude_skus = array_merge($exclude_skus, $values);
                            }
                            break;
                        default:
                            if ($method == "in_list") {
                                $custom_taxonomies[$type] = isset($custom_taxonomies[$type]) ? array_merge($custom_taxonomies[$type], $values) : $values;
                            } else {
                                $exclude_custom_taxonomies[$type] = isset($exclude_custom_taxonomies[$type]) ? array_merge($exclude_custom_taxonomies[$type], $values) : $values;
                            }
                            break;
                    }
                }
            }

            $this->setCategoriesQueryArguments($query_arguments, $categories, $exclude_categories, $all_products);
            $this->setTagsQueryArguments($query_arguments, $tags, $exclude_tags, $all_products);
            $this->setAttributesQueryArguments($query_arguments, $attributes, $exclude_attributes, $all_products);
            $this->setSkuQueryArguments($query_arguments, $skus, $exclude_skus, $all_products);
            $this->setOnSaleQueryArguments($query_arguments, $on_sale_products, $all_products);
            $this->setCustomTaxonomyQueryArguments($query_arguments, $custom_taxonomies, $exclude_custom_taxonomies, $all_products);
            $this->setProductsQueryArguments($query_arguments, $products, $exclude_products, $all_products);
            $this->setQueryRelationship($query_arguments);
            ob_start();
            $products = new \WP_Query($query_arguments);
            $columns = absint($short_code_attributes['columns']);
            $woocommerce_loop['columns'] = $columns;
            if ($products->have_posts()) {
                self::$woocommerce_helper->setLoopProperties('is_shortcode', true);
                self::$woocommerce_helper->setLoopProperties('is_paginated', (!empty($short_code_attributes['do_pagination'])));
                self::$woocommerce_helper->setLoopProperties('per_page', $short_code_attributes['per_page']);
                self::$woocommerce_helper->setLoopProperties('current_page', $paged);
                $total = $products->found_posts;
                self::$woocommerce_helper->setLoopProperties('total', $total);
                $total_pages = ceil($total / $short_code_attributes['per_page']);
                self::$woocommerce_helper->setLoopProperties('total_pages', $total_pages);
                do_action('woocommerce_before_shop_loop');
                self::$woocommerce_helper->productLoopStart();
                while ($products->have_posts()) {
                    $products->the_post();
                    wc_get_template_part('content', 'product');
                }
                self::$woocommerce_helper->productLoopEnd();
                do_action('woocommerce_after_shop_loop');
                // woocommerce_pagination();
                wp_reset_postdata();
            } else {
                do_action('woocommerce_no_products_found');
            }
            return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
        }
        return NULL;
    }

    function bannerContent(){
        echo "";
        /*$awdr_banner_editer = self::$config->getConfig('awdr_banner_editor', '');
        if(!empty($awdr_banner_editer) && $awdr_banner_editer != ''){
            $awdr_banner_editer = $this->getCleanHtml($awdr_banner_editer);
            echo "<div class='awdr_banner_content'>".$awdr_banner_editer."</div>";
        }else{
            echo "<div class='awdr_banner_content'>"._e('No Banner Content', 'woo-discount-rules');".</div>";
        }*/
    }
}