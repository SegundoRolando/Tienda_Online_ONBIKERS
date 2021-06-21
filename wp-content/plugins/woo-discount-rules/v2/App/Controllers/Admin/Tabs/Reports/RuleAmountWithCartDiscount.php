<?php
namespace Wdr\App\Controllers\Admin\Tabs\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class RuleAmountWithCartDiscount extends RuleAmount {

    public function get_subtitle() {
        return __( 'Amount shown in default store currency', 'woo-discount-rules' );
    }

	protected function prepare_params( $params ) {
        $params['limit'] = ( isset($params['limit']) && !empty($params['limit']) ) ? $params['limit'] : 5;
		$params['include_cart_discount'] = true;
		return $params;
	}
}