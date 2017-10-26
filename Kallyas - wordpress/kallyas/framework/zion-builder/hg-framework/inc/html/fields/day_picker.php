<?php

class ZnHgFw_Html_Day_Picker extends ZnHgFw_BaseFieldType{

	var $type = 'day_picker';

	function render( $value ){
		if ( empty( $value['options'] ) ) {
			$value['options'] = array(
					__('Sunday', 'hg-framework') => __('Sunday', 'hg-framework'),
					__('Monday', 'hg-framework') => __('Monday', 'hg-framework'),
					__('Tuesday', 'hg-framework') => __('Tuesday', 'hg-framework'),
					__('Wednesday', 'hg-framework') => __('Wednesday', 'hg-framework'),
					__('Thursday', 'hg-framework') => __('Thursday', 'hg-framework'),
					__('Friday', 'hg-framework') => __('Friday', 'hg-framework'),
					__('Saturday', 'hg-framework') => __('Saturday', 'hg-framework'),
			);
		}

		if(empty($value['std'])){
			$value['std'] = __('Sunday', 'hg-framework');
		}

		$out = '<select class="select zn_input zn_input_select" name="'.$value['id'].'" id="'. $value['id'] .'">';
		if(! empty($value['options'])) {
			foreach ( $value['options'] as $select_ID => $option ) {
				$out .= '<option  value="' . $select_ID . '" ' . selected( $value['std'], $select_ID, false ) . '>' . $option . '</option>';
			}
		}
		$out .= '</select>';

		return $out;
	}

}
