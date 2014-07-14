<?php
class GlobalFunction {
	
	public static function displayNumber($number) {
		return number_format($number, 2);
	}
	
	public static function displayJPYNumber($number) {
		return number_format($number);
	}
	
	/**
	 * RMB -> JPY
	 * @param unknown_type $price
	 * @param unknown_type $rmb_to_jpy_rate
	 */
	public static function convertRMB2JPY($price, $rmb_to_jpy_rate) {
		return round($price * $rmb_to_jpy_rate);
	}
	
	/**
	 * JPY -> RMB
	 * @param unknown_type $price
	 * @param unknown_type $rmb_to_jpy_rate
	 */
	public static function convertJPY2RMB($price, $rmb_to_jpy_rate) {
		return round($price * 1.0 / $rmb_to_jpy_rate, 2);
	}
	
	/**
	 * RMB -> USD
	 * @param unknown_type $price
	 * @param unknown_type $rmb_to_usd_rate
	 */
	public static function convertRMB2USD($price, $rmb_to_usd_rate) {
		return round($price * 1.0 * $rmb_to_usd_rate, 2);
	}
	
	public static function roundJPY($price) {
		return round($price);
	}
	
	public static function roundRMB($price) {
		return round($price, 2);
	}
	
	public static function roundUpTo($value, $precision) {
		return ceil($value * pow(10, $precision)) / pow(10, $precision);
	}

	public static function hasPrivilege($page, $permission=NULL) {
		return Auth::instance()->get_user()->hasPrivilege($page, $permission);
	}
	
	public static function orderProductPictureAnchor($orderId, $picturePath) {
		if ($picturePath != NULL) {
			return HTML::anchor(ORDER_IMAGE_PATH.$orderId.'/'.$picturePath, HTML::image(ORDER_IMAGE_PATH.$orderId.'/s_'.$picturePath), array('target'=>'_blank'));
		} else {
			return NULL;
		}
	}
	
	public static function giftPictureAnchor($giftId, $picturePath) {
		if ($picturePath != NULL) {
			return HTML::anchor(GIFT_IMAGE_PATH.$giftId.'/'.$picturePath, HTML::image(GIFT_IMAGE_PATH.$giftId.'/s_'.$picturePath), array('target'=>'_blank'));
		} else {
			return NULL;
		}
	}
}