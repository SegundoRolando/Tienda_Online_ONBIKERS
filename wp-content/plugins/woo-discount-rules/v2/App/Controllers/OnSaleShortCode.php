<?php

namespace Wdr\App\Controllers;

use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Rule;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class OnSaleShortCode extends ManageDiscount
{
    protected $on_sale_list = array();
    protected static $on_sale_list_key = 'wdr_on_sale_list';
    protected static $required_rebuild_key = 'wdr_on_sale_list_attention_required';
    function __construct()
    {
        parent::__construct();
    }

    public function getAdditionalFilters($rule, $discount_type){
        $additional_filter = $products = $category = array();
        if($discount_type == 'wdr_buy_x_get_y_discount'){
            $bxgy_adjustment = json_decode($rule->buy_x_get_y_adjustments);
            if($bxgy_adjustment->type == 'bxgy_product'){
                $ranges = $bxgy_adjustment->ranges;
                foreach ($ranges as $range){
                    if(!empty($products) && !empty($range->products)){
                        $products = array_merge($products, $range->products);
                    } else {
                        $products = $range->products;
                    }
                    $parent_products = isset($range->product_variants_for_sale_badge)? $range->product_variants_for_sale_badge: array();
                    if(!empty($products) && !empty($parent_products)){
                        $products = array_merge($products, $parent_products);
                    }
                }
                if(!empty($products)){
                    $products = array_unique($products);
                }
                $additional_filter['product'] = $products;
            } else if($bxgy_adjustment->type == 'bxgy_category'){
                $ranges = $bxgy_adjustment->ranges;
                foreach ($ranges as $range){
                    if(!empty($category) && !empty($range->categories)){
                        $category = array_merge($category, $range->categories);
                    } else {
                        $category = $range->categories;
                    }
                }
                if(!empty($category)){
                    $category = array_unique($category);
                }
                $additional_filter['category'] = $category;
            }
        }

        return $additional_filter;
    }

    public static function getOnPageReBuildOption($id){
        $option['available'] = false;
        $option['required_rebuild'] = false;
        $is_pro = Helper::hasPro();
        if($is_pro){
            $rules = self::getReBuildOnSaleRules();
            if(!empty($rules)){
                if(is_array($rules) && (in_array("all", $rules) || in_array($id, $rules))){
                    $option['available'] = true;
                    $option['rule_depend_on_sale_page'] = true;
                    $is_required = self::isRequiredRebuild();
                    if(!empty($is_required) && $is_required == 1){
                        $option['required_rebuild'] = true;
                    }
                }
            }
        }

        return $option;
    }
    public static function updateOnsaleRebuildPageStatus($id){
        $is_pro = Helper::hasPro();
        if($is_pro){
            $rules = self::getReBuildOnSaleRules();
            if(!empty($rules)){
                if(is_array($rules) && (in_array("all", $rules) || in_array($id, $rules))){
                    self::setRequiredRebuild();
                }
            }
        }
    }

    protected function getSelectedRules($rules_ids){
        $rule_helper = new Rule();
        $this->updateRebuildRulesInSettings($rules_ids);
        return $rule_helper->getAvailableRules($this->getAvailableConditions(), $rules_ids);
    }

    /**
     * Update rebuild rules with settings
     * */
    protected function updateRebuildRulesInSettings($awdr_rebuild_on_sale_rules){
        $config = get_option(Configuration::DEFAULT_OPTION);
        $config['awdr_rebuild_on_sale_rules'] = $awdr_rebuild_on_sale_rules;
        update_option(Configuration::DEFAULT_OPTION, $config);
    }

    /**
     * get rebuild rules from settings
     * */
    public static function getReBuildOnSaleRules(){
        $config = new Configuration();
        return $config->getConfig("awdr_rebuild_on_sale_rules", null);
    }

    /**
     * get rebuild rules from settings
     * */
    public static function isRequiredRebuild(){
        return get_option(self::$required_rebuild_key);
    }

    /**
     * get rebuild rules from settings
     * */
    public static function setRequiredRebuild($val = 1){
        return update_option(self::$required_rebuild_key, $val);
    }

    public function rebuildOnSaleList($rules_ids){
        $this->on_sale_list = array();
        if(!empty($rules_ids) && is_array($rules_ids)){
            if(!in_array("all", $rules_ids)){
                self::$available_rules = $this->getSelectedRules($rules_ids);
            }
        } else {
            self::getReBuildOnSaleRules();
            $rules_ids = self::getReBuildOnSaleRules();
            if(!empty($rules_ids)){
                if(!in_array("all", $rules_ids)){
                    self::$available_rules = $this->getSelectedRules($rules_ids);
                }
            }
        }
        if (!empty(self::$available_rules)) {
            foreach (self::$available_rules as $rule) {
                if($rule->rule->enabled == 1){
                    $discount_type = $rule->getRuleDiscountType();
                    if($discount_type != 'wdr_free_shipping'){
                        $filters = $rule->getFilter();
                        $additional_filter = $this->getAdditionalFilters($rule->rule, $discount_type);
                        if(!empty($additional_filter)){
                            if(isset($additional_filter['product']) && !empty($additional_filter['product'])){
                                if(empty($filters)){
                                    $filters = new \stdClass();
                                }
                                $filters->bogo = new \stdClass();
                                $filters->bogo->type = 'products';
                                $filters->bogo->method = 'in_list';
                                $filters->bogo->value = $additional_filter['product'];
                                $filters->bogo->product_variants = array();
                                $filters->bogo->product_variants_for_sale_badge = array();
                            }
                            if(isset($additional_filter['category']) && !empty($additional_filter['category'])){
                                if(empty($filters)){
                                    $filters = new \stdClass();
                                }
                                $filters->bogo = new \stdClass();
                                $filters->bogo->type = 'product_category';
                                $filters->bogo->method = 'in_list';
                                $filters->bogo->value = $additional_filter['category'];
                            }
                        }
                        $this->rebuildOnSaleListForARule($rule, $filters, $additional_filter);
                    }
                }
            }
            $this->mergeAllRebuildRules();
            self::setRequiredRebuild(0);
        }
    }
    
    protected function mergeAllRebuildRules(){
        $final_on_sale_list = array();
        $exclude_list = $include_list = array();
        if(!empty($this->on_sale_list)){
            if(isset($this->on_sale_list['has_store_wide']) && $this->on_sale_list['has_store_wide'] == true){
                $final_on_sale_list['has_store_wide'] = true;
                if(!empty($this->on_sale_list['items'])){
                    foreach ($this->on_sale_list['items'] as $rule_id => $items){
                        if($items['has_store_wide']){
                            if(!empty($exclude_list)){
                                if(!empty($items['list'])){
                                    $exclude_list = array_merge($exclude_list, $items['list']);
                                }
                            } else {
                                $exclude_list = $items['list'];
                            }
                        } else {
                            if(!empty($include_list)){
                                if(!empty($items['list'])){
                                    $include_list = array_merge($include_list, $items['list']);
                                }
                            } else {
                                $include_list = $items['list'];
                            }
                        }
                    }
                }
                if(!empty($exclude_list)){
                    $exclude_list = array_unique($exclude_list);
                    if(!empty($include_list)){
                        $include_list = array_unique($include_list);
                        $exclude_list = array_diff($exclude_list, $include_list);
                    }

                    $final_on_sale_list['list'] = $exclude_list;
                }
            } else {
                $final_on_sale_list['has_store_wide'] = false;
                if(!empty($this->on_sale_list['items'])){
                    foreach ($this->on_sale_list['items'] as $rule_id => $items){
                        if(!empty($include_list)){
                            if(!empty($items['list'])){
                                $include_list = array_merge($include_list, $items['list']);
                            }
                        } else {
                            $include_list = $items['list'];
                        }
                    }
                }
                if(!empty($include_list)){
                    $include_list = array_unique($include_list);
                    $final_on_sale_list['list'] = $include_list;
                }
            }
        }
        update_option(self::$on_sale_list_key, $final_on_sale_list);
    }

    public static function getOnSaleList(){
        return get_option(self::$on_sale_list_key, array());
    }

    public function rebuildOnSaleListForARule($rule, $filters, $additional_filters = array()){
        $this->processFiltersForRebuildOnSaleList($rule, $filters, $additional_filters);
    }

    protected function processFiltersForRebuildOnSaleList($rule, $filters, $additional_filters){
        $rule_id = $rule->rule->id;
        $has_store_wide = $this->hasStoreWideDiscount($rule, $filters);
        if($has_store_wide === true){
            $this->on_sale_list['has_store_wide'] = true;
        }
        $generated_filters = $this->generateFilters($rule, $filters, $has_store_wide, $additional_filters);
        $query_args = $this->generateQueryArguments($generated_filters, $has_store_wide);
        $this->on_sale_list['items'][$rule_id]['has_store_wide'] =  $has_store_wide;
        if(!empty($query_args)){
            $exclude_ids = $include_id = array();
            if(isset($query_args['post__in'])){
                $include_id = $query_args['post__in'];
                unset($query_args['post__in']);
            }
            if(isset($query_args['post__not_in'])){
                $exclude_ids = $query_args['post__not_in'];
                unset($query_args['post__not_in']);
            }
            if(!empty($query_args)){
                $post_ids = array();
                $total_page = $total_post_count = 0;
                $query_args['post_type'] = 'product';
                $query_args['post_status'] = 'publish';
                $query_args['posts_per_page'] = 1000;
                $query_args['paged'] = 1;
                $query_args['offset'] = 0;
                $products = new \WP_Query($query_args);
                $total_page = isset($products->max_num_pages) ? $products->max_num_pages : $total_page;
                $total_post_count = isset( $products->found_posts) ? $products->found_posts : $total_post_count ;
                if($total_page > 1 && $total_post_count > 1000){
                    $process_of_post_ids = wp_list_pluck( $products->posts, 'ID' );
                    $post_ids = !empty($process_of_post_ids) ? array_merge($post_ids,$process_of_post_ids) : $post_ids;
                    for($i=2; $i <= $total_page; $i++ ){
                        $query_args['post_type'] = 'product';
                        $query_args['post_status'] = 'publish';
                        $query_args['posts_per_page'] = 1000;
                        $query_args['paged'] = $i;
                        $query_args['offset'] = $i * 1000;
                        $large_products = new \WP_Query($query_args);
                        $process_of_post_ids = wp_list_pluck( $large_products->posts, 'ID' );
                        $post_ids = !empty($process_of_post_ids) ? array_merge($post_ids,$process_of_post_ids) : $post_ids;
                    }
                }else{
                    $post_ids = wp_list_pluck( $products->posts, 'ID' );
                }
            } else {
                $post_ids = array();
            }

            if(!empty($include_id)){
                if(!empty($post_ids) && is_array($post_ids)){
                    $post_ids = array_merge($post_ids, $include_id);
                } else {
                    $post_ids = $include_id;
                }
            }
            if(!empty($exclude_ids)){
                if(!empty($post_ids) && is_array($post_ids)){
                    $post_ids = array_diff($post_ids, $exclude_ids);
                }
            }
            $this->on_sale_list['items'][$rule_id]['list'] = $post_ids;
        } else {
            $this->on_sale_list['items'][$rule_id]['list'] = array();
        }

    }

    protected function generateQueryArguments($generated_filters, $has_store_wide){
        $query_arguments = array();
        foreach ($generated_filters as $type => $generated_filter){
            switch ($type) {
                case "product_on_sale":
                    if (isset($generated_filter['in_list'])) {
                        $this->setOnSaleProductQueryArguments($query_arguments, 'include');
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setOnSaleProductQueryArguments($query_arguments, 'exclude');
                    }
                    break;
                case "product_category":
                    if (isset($generated_filter['in_list'])) {
                        $this->setCategoriesQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setCategoriesQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
                case "products":
                    if (isset($generated_filter['in_list'])) {
                        $this->setProductsQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setProductsQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
                case "product_tags":
                    if (isset($generated_filter['in_list'])) {
                        $this->setTagsQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setTagsQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
                case "product_attributes":
                    if (isset($generated_filter['in_list'])) {
                        $this->setAttributesQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setAttributesQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
                case "product_sku":
                    if (isset($generated_filter['in_list'])) {
                        $this->setSkuQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setSkuQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
                default:
                    if (isset($generated_filter['in_list'])) {
                        $this->setCustomTaxonomyQueryArguments($query_arguments, 'include', $generated_filter['in_list']);
                    }
                    if (isset($generated_filter['not_in_list'])) {
                        $this->setCustomTaxonomyQueryArguments($query_arguments, 'exclude', $generated_filter['not_in_list']);
                    }
                    break;
            }
        }
        $this->setQueryRelationship($query_arguments);
        return $query_arguments;

    }

    function setCustomTaxonomyQueryArguments(&$query_arguments, $query_type, $taxonomies)
    {
        $values = array_map('absint', $taxonomies);
        if($query_type == 'include'){
            $operator = 'IN';
        } else {
            $operator = 'NOT IN';
        }
        foreach ($values as $taxonomy_id) {
            $data = get_term($taxonomy_id);
            if (!empty($data)) {
                $taxonomy = $data->taxonomy;
                $query_arguments['tax_query'][$query_type][$taxonomy] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'operator' => $operator
                );
                $query_arguments['tax_query'][$query_type][$taxonomy]['terms'][] = $taxonomy_id;
            }
        }
    }

    function setSkuQueryArguments(&$query_arguments, $query_type, $values)
    {
        /* It might has string values so we can't convert it to integer */
        /*$values = array_map('absint', $values);*/
        if($query_type == 'include'){
            $operator = 'IN';
        } else {
            $operator = 'NOT IN';
        }
        $query_arguments['meta_query'][] = array(
            'key' => '_sku',
            'value' => $values,
            'compare' => $operator,
        );
    }

    function setOnSaleProductQueryArguments(&$query_arguments, $query_type)
    {
        if($query_type == 'include'){
            $operator = '>';
            $values = 0;
        } else {
            $operator = '<=';
            $values = 0;
        }
        $query_arguments['meta_query'][] = array(
            'key' => '_sale_price',
            'value' => $values,
            'compare' => $operator,
        );
    }

    protected function setAttributesQueryArguments(&$query_arguments, $query_type, $values)
    {
        $values = array_map('absint', $values);
        if($query_type == 'include'){
            $operator = 'IN';
        } else {
            $operator = 'NOT IN';
        }
        foreach ($values as $attribute) {
            $data = get_term($attribute);
            if (!empty($data)) {
                $taxonomy = $data->taxonomy;
                $query_arguments['tax_query'][$query_type][$taxonomy] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'operator' => $operator
                );
                $query_arguments['tax_query'][$query_type][$taxonomy]['terms'][] = $attribute;
            }
        }
    }

    function setTagsQueryArguments(&$query_arguments, $query_type, $values)
    {
        $values = array_map('absint', $values);
        if($query_type == 'include'){
            $operator = 'IN';
        } else {
            $operator = 'NOT IN';
        }
        $query_arguments['tax_query'][$query_type][] = array(
            'taxonomy' => 'product_tag',
            'terms' => $values,
            'field' => 'term_id',
            'operator' => $operator
        );
    }

    function setProductsQueryArguments(&$query_arguments, $query_type, $values)
    {
        /*
         * As per https://www.billerickson.net/code/wp_query-arguments/
         * you cannot combine 'post__in' and 'post__not_in' in the same query
         */
        //TODO: you cannot combine 'post__in' and 'post__not_in' in the same query
        if($query_type == 'include'){
            $query_arguments['post__in'] = $values;
        } else {
            $query_arguments['post__not_in'] = $values;
        }
    }

    function setCategoriesQueryArguments(&$query_arguments, $query_type, $values)
    {
        $values = array_map('absint', $values);
        if($query_type == 'include'){
            $operator = 'IN';
        } else {
            $operator = 'NOT IN';
        }
        $query_arguments['tax_query'][$query_type][] = array(
            'taxonomy' => 'product_cat',
            'terms' => $values,
            'field' => 'term_id',
            'operator' => $operator,
            'include_children' => false
        );
    }

    protected function generateFilters($rule, $filters, $has_store_wide, $additional_filters){
        $generated_filters = array();
        foreach ($filters as $filter) {
            $type = $rule->getFilterType($filter);
            $values = (array)$rule->getFilterOptionValue($filter);
            $parent_product_ids = (array)$rule->getFilterOptionParentValue($filter);
            $method = $rule->getFilterMethod($filter);
            if($type == "products"){
                if ($method == "in_list") {
                    if(!empty($parent_product_ids) && is_array($parent_product_ids)){
                        $values = array_merge($values, $parent_product_ids);
                    }
                }
            }
            switch ($type) {
                case "all_products":
                    break;
                case "product_on_sale":
                    if($has_store_wide){
                        if($method == 'not_in_list'){
                            $generated_filters[$type][$method] = true;
                        }
                    } else {
                        $generated_filters[$type][$method] = true;
                    }
                    break;
                default:
                    if($has_store_wide){
                        if($method == 'not_in_list' && !empty($values) && is_array($values)){
                            $method = 'in_list';
                            $generated_filters[$type][$method] = $this->mergeValues($generated_filters, $type, $method, $values);
                        }
                    } else {
                        if(!empty($values) && is_array($values)){
                            $generated_filters[$type][$method] = $this->mergeValues($generated_filters, $type, $method, $values);
                        }
                    }
                    break;
            }
        }

        return $generated_filters;
    }

    protected function mergeValues($generated_filters, $type, $method, $values){
        if(isset($generated_filters[$type])){
            if(isset($generated_filters[$type][$method]) && !empty($generated_filters[$type][$method])){
                $values = array_merge($generated_filters[$type][$method], $values);
                $values = array_unique($values);
            }
        }
        return $values;
    }

    protected function hasStoreWideDiscount($rule, $filters){
        foreach ($filters as $filter) {
            $type = $rule->getFilterType($filter);
            if($type == "all_products"){
                return true;
            }
        }
        return false;
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
        if (!empty($query_arguments['meta_query'])) {
            $query_arguments['meta_query']['relation'] = 'or';
        }
    }
}