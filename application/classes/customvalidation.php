<?php
class CustomValidation {
	public static function positive($value) {
		return $value > 0;
	}
	
	public static function minValue($value, $min) {
		return $value >= $min;
	}
}