<?php

use Gacela\Field as F;

class Kohana_Kacela_Field_Bool extends F\Bool
{

public static function transform($meta, $value, $in = true)
{
	if($in && is_bool($value)) {
		$value === true ? $value = 1 : $value = 0;
	} elseif(!$in) {
		$value = (bool) $value;
	}

	return $value;
}

}
