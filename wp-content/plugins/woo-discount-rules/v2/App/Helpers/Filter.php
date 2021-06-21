<?php

namespace Wdr\App\Helpers;

use Wdr\App\Controllers\Configuration;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Filter
{
    /**
     * Match rule filters against product
     * @param $product
     * @param $type
     * @param $method
     * @param $values
     * @param object $options
     * @param array $cart_item
     * @return bool
     */
    function match($product, $type, $method, $values, $options, $cart_item = array())
    {
        $config = new Configuration();
        if (is_a($product, 'WC_Product')) {
            $method = !empty($method) ? $method : 'in_list';
            $product_id = Woocommerce::getProductId($product);
            if ('all_products' === $type) {
                return true;
            } else if ('products' === $type) {
                $apply_discount_to_child = apply_filters('advanced_woo_discount_rules_apply_discount_to_child', true, $product);
                if ($apply_discount_to_child) {
                    if (isset($options->product_variants) && !empty($options->product_variants) && is_array($options->product_variants)) {
                        $values = Helper::combineProductArrays($values, $options->product_variants);
                    }
                }
                return $this->compareWithProducts($values, $method, $product_id, $product);
            } elseif ('product_category' === $type) {
                return $this->compareWithCategories($product, $values, $method);
            } elseif ('product_tags' === $type) {
                $product = Woocommerce::getParentProduct($product);
                return $this->compareWithTags($product, $values, $method);
            } elseif ('product_attributes' === $type) {
                return $this->compareWithAttributes($product, $values, $method, $cart_item);
            } elseif ('product_sku' === $type) {
                return $this->compareWithSku($product, $values, $method);
            } elseif ('product_on_sale' === $type) {
                return $this->compareWithOnSale($product, $method);
            } elseif (in_array($type, array_keys(Woocommerce::getCustomProductTaxonomies()))) {
                return $this->compareWithCustomTaxonomy($product_id, $values, $method, $type);
            }
        }
        return false;
    }

    /**
     * Match rule filters against product
     * @param $product
     * @param $filters array
     * @param $sale_badge
     * @return bool
     */
    function matchFilters($product, $filters, $sale_badge, $product_table = false)
    {
        $rule = new Rule();
        $status = false;
        if (is_a($product, 'WC_Product')) {
            $product_id = Woocommerce::getProductId($product);
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    $type = $rule->getFilterType($filter);
                    $method = $rule->getFilterMethod($filter);
                    $values = $rule->getFilterOptionValue($filter);
                    $options = $filter;
                    $cart_item = array();
                    $method = !empty($method) ? $method : 'in_list';
                    $processing_result = false;
                    if ('all_products' === $type) {
                        $status = true;
                    } else if ('products' === $type) {
                        $apply_discount_to_child = apply_filters('advanced_woo_discount_rules_apply_discount_to_variants', true);
                        if ($apply_discount_to_child) {
                            if (isset($options->product_variants) && !empty($options->product_variants) && is_array($options->product_variants)) {
                                $values = Helper::combineProductArrays($values, $options->product_variants);
                            }
                            if($sale_badge && isset($options->product_variants_for_sale_badge) && !empty($options->product_variants_for_sale_badge) && is_array($options->product_variants_for_sale_badge)){
                                $values = Helper::combineProductArrays($values, $options->product_variants_for_sale_badge);
                            }
                        }
                        $processing_result = $this->compareWithProducts($values, $method, $product_id, $product);
                    } elseif ('product_category' === $type) {
                        $product = Woocommerce::getParentProduct($product);
                        $processing_result = $this->compareWithCategories($product, $values, $method);
                    } elseif ('product_tags' === $type) {
                        $product = Woocommerce::getParentProduct($product);
                        $processing_result = $this->compareWithTags($product, $values, $method);
                    } elseif ('product_attributes' === $type) {
                        //$product = Woocommerce::getParentProduct($product);
                        if($product_table === true && Woocommerce::productTypeIs($product, array('variable', 'variable-subscription'))){
                            $processing_result = false;
                        } else {
                            $processing_result = $this->compareWithAttributes($product, $values, $method, $cart_item);
                        }
                    } elseif ('product_sku' === $type) {
                        $processing_result = $this->compareWithSku($product, $values, $method, $sale_badge);
                    } elseif ('product_on_sale' === $type) {
                        $processing_result = $this->compareWithOnSale($product, $method);
                    } elseif (in_array($type, array_keys(Woocommerce::getCustomProductTaxonomies()))) {
                        $parant_product_id = Woocommerce::getProductParentId($product);
                        if(!empty($parant_product_id)){
                            $product_id = $parant_product_id;
                        }
                        $processing_result = $this->compareWithCustomTaxonomy($product_id, $values, $method, $type);
                    }
                    if ($method === 'not_in_list') {
                        if (!$processing_result) {
                            $status = false;
                            break;
                        }
                    } else if ($processing_result) {
                        $status = true;
                    }
                }
            }
        }

        return $status;
    }

    protected function processInNotInList()
    {
    }

    /**
     * Compare product against Custom taxonomy Filter
     * @param $product_id
     * @param $operation_values
     * @param $operation_method
     * @param $taxonomy
     * @return bool
     */
    protected function compareWithCustomTaxonomy($product_id, $operation_values, $operation_method, $taxonomy)
    {
        $product_parent = Woocommerce::getProductParentId($product_id);
        $product_id = !empty($product_parent) ? $product_parent : $product_id;
        $term_ids = wp_get_post_terms($product_id, $taxonomy, array("fields" => "ids"));
        $is_product_has_term = count(array_intersect($term_ids, $operation_values)) > 0;
        if ('in_list' === $operation_method) {
            return $is_product_has_term;
        } elseif ('not_in_list' === $operation_method) {
            return !$is_product_has_term;
        }
        return false;
    }

    /**
     * Compare product against SKU Filter
     * @param $product
     * @param $operation_values
     * @param $operation_method
     * @param $sale_badge
     * @return bool
     */
    protected function compareWithSku($product, $operation_values, $operation_method, $sale_badge=false)
    {
        $result = false;
        $product_sku = apply_filters('advanced_woo_discount_rules_check_sku_filter', Woocommerce::getProductSku($product), $product, $operation_values, $operation_method, $sale_badge);
        if ('in_list' === $operation_method) {
            $result = (in_array($product_sku, $operation_values));
        } elseif ('not_in_list' === $operation_method) {
            $result = !(in_array($product_sku, $operation_values));
        } elseif ('any' === $operation_method) {
            $result = true;
        }
        return $result;
    }

    /**
     * Compare product's tags against attribute filters
     * @param $product
     * @param $operation_values
     * @param $operation_method
     * @param $cart_item
     * @return bool
     */
    protected function compareWithAttributes($product, $operation_values, $operation_method, $cart_item)
    {
        $attrs = Woocommerce::getProductAttributes($product);
        $attr_ids = array();
        if (Woocommerce::productTypeIs($product, 'variation')) {
            if (count(array_filter($attrs)) < count($attrs)) {
                if (isset($cart_item['variation'])) {
                    $attrs = array();
                    foreach ($cart_item['variation'] as $attribute_name => $value) {
                        $attrs[str_replace('attribute_', '', $attribute_name)] = $value;
                    }
                }
            }
            $product_variation = Woocommerce::getProduct(Woocommerce::getProductParentId($product));
            foreach ($attrs as $taxonomy => $value) {
                if ($value) {
                    $taxonomy = apply_filters('advanced_woo_discount_rules_attribute_slug', urldecode($taxonomy), $taxonomy, $value);
                    $term_obj = get_term_by('slug', $value, $taxonomy);
                    if (!is_wp_error($term_obj) && $term_obj && $term_obj->name) {
                        $attr_ids = array_merge($attr_ids, (array)($term_obj->term_id));
                    }
                } else {
                    $attrs_variation = Woocommerce::getProductAttributes($product_variation);
                    foreach ($attrs_variation as $attr) {
                        if ($taxonomy == Woocommerce::getAttributeName($attr))
                            $attr_ids = array_merge($attr_ids, Woocommerce::getAttributeOption($attr));
                    }
                }
                $attr_ids = apply_filters('advanced_woo_discount_rules_get_attribute_id_from_taxonomy_name', $attr_ids, $taxonomy, $product, $cart_item, $operation_values);
            }
            if(!empty($product_variation)){
                $attributes_parent = Woocommerce::getProductAttributes($product_variation);
                foreach ($attributes_parent as $attributes){
                    if(!empty($attributes) && is_object($attributes)){
                        $variation = Woocommerce::getAttributeVariation($attributes);
                        if(!(int)$variation){
                            $options = Woocommerce::getAttributeOption($attributes);

                            if(!empty($options) && is_array($options)){
                                $attr_ids = array_merge($attr_ids, $options);
                            }
                        }
                    } else {
                        $options = Woocommerce::getAttributeOption($attributes);
                        if(!empty($options) && is_array($options)){
                            $attr_ids = array_merge($attr_ids, $options);
                        }
                    }
                }
            }
        } else {
            foreach ($attrs as $attr) {
                $attr_ids = array_merge($attr_ids, Woocommerce::getAttributeOption($attr));
            }
        }
        $attr_ids = array_unique($attr_ids);
        $is_product_has_attrs = count(array_intersect($attr_ids, $operation_values)) > 0;
        if ('in_list' === $operation_method) {
            return $is_product_has_attrs;
        } elseif ('not_in_list' === $operation_method) {
            return !$is_product_has_attrs;
        }
        return false;
    }

    /**
     * Compare product's tags against tag filters
     * @param $product
     * @param $operation_values
     * @param $operation_method
     * @return bool
     */
    protected function compareWithTags($product, $operation_values, $operation_method)
    {
        $tag_ids = Woocommerce::getProductTags($product);
        $is_product_has_tag = count(array_intersect($tag_ids, $operation_values)) > 0;
        if ('in_list' === $operation_method) {
            return $is_product_has_tag;
        } elseif ('not_in_list' === $operation_method) {
            return !$is_product_has_tag;
        }
        return false;
    }

    /**
     * Compare product's categories against category filters
     * @param $product
     * @param $operation_values
     * @param $operation_method
     * @return bool
     */
    protected function compareWithCategories($product, $operation_values, $operation_method)
    {
        $categories = Woocommerce::getProductCategories($product);
        $is_product_in_category = count(array_intersect($categories, $operation_values)) > 0;
        if ('in_list' === $operation_method) {
            return $is_product_in_category;
        } elseif ('not_in_list' === $operation_method) {
            return !$is_product_in_category;
        }
        return false;
    }

    /**
     * Compare products against product filter values
     * @param $operation_values
     * @param $operation_method
     * @param $product_id
     * @param $product
     * @return bool
     */
    protected function compareWithProducts($operation_values, $operation_method, $product_id, $product)
    {
        $result = $this->checkInList($product_id, $operation_method, $operation_values);
        if($operation_method == 'not_in_list'){
            if (!$result) {
                return false;
            }
        } else {
            if ($result) {
                return true;
            }
        }
        $apply_discount_to_child = apply_filters('advanced_woo_discount_rules_apply_discount_to_variants', true);
        $parent_id = Woocommerce::getProductParentId($product);
        if (!empty($apply_discount_to_child) && !empty($parent_id)) {
            $product_id = $parent_id;
        }
        $result = $this->checkInList($product_id, $operation_method, $operation_values);
        return $result;
    }

    /**
     * Compare products against product is on sale values
     * @param $product
     * @param $operation_method
     * @return bool
     */
    protected function compareWithOnSale($product, $operation_method)
    {
        if ('in_list' === $operation_method) {
            return (Woocommerce::isProductInSale($product)) ? true : false;
        } elseif ('not_in_list' === $operation_method) {
            return (Woocommerce::isProductInSale($product)) ? false : true;
        } elseif ('any' === $operation_method) {
            return false;
        }

    }

    /**
     * Check product in list
     * @param $product_id
     * @param $operation_method
     * @param $operation_values
     * @return bool
     */
    function checkInList($product_id, $operation_method, $operation_values)
    {
        $result = false;
        if ('in_list' === $operation_method) {
            $result = (in_array($product_id, $operation_values));
        } elseif ('not_in_list' === $operation_method) {
            $result = !(in_array($product_id, $operation_values));
        } elseif ('any' === $operation_method) {
            $result = true;
        }
        return $result;
    }
}