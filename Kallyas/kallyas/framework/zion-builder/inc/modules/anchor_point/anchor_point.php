<?php if(! defined('ABSPATH')){ return; }

class ZNB_AnchorPoint extends ZionElement
{

	function options() {

		$uid = $this->data['uid'];

		$options = array(
			'has_tabs'  => true,
			'general' => array(
				'title' => 'General options',
				'options' => array(

					array (
						'id'          => 'id',
						'name'        => 'ID',
						'description' => 'Please enter an id for this anchor point. You can use this #id for an anchor href.',
						'std'         => $this->data['uid'],
						'type'        => 'text'
					),

				),
			),

			'help' => znpb_get_helptab( array(
				// 'video'   => 'https://my.hogash.com/video_category/kallyas-wordpress-theme/#GAiAelvoOg4',
				// 'docs'    => 'https://my.hogash.com/documentation/anchor-point-element/',
				'copy'    => $uid,
				'general' => true,
				// 'custom_id' => true,
			)),

		);

		return $options;

	}

	function element(){
		$options = $this->data['options'];
		$classes = $attributes = array();
		$uid = $this->data['uid'];

		$classes[] = $uid;
		$classes[] = zn_get_element_classes($options);
		$classes[] = 'zn-achPoint clearfix';

		$id = $this->opt('id', $uid);
		$attributes[] = zn_get_element_attributes($options, $id);
		$attributes[] = 'class="'.zn_join_spaces($classes).'"';

		echo '<div '. zn_join_spaces($attributes ) .'>';

		if( ZNB()->utility->isActiveEditor() ){
			echo '<div class="zn-achPointEdit">'.$id.'</div>';
		}

		echo '</div>';
	}

}

ZNB()->elements_manager->registerElement( new ZNB_AnchorPoint(array(
	'id' => 'ZnAnchorPoint',
	'name' => __('Anchor Point', 'zn_framework'),
	'description' => __('This element will generate an empty element with an unique ID that can be used as an achor point.', 'zn_framework'),
	'level' => 3,
	'category' => 'Content',
	'legacy' => false,
	'keywords' => array('scroll'),
)));
