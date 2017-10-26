<?php if(! defined('ABSPATH')){ return; }

/**
 * Class Znb_Polylang_Integration
 */
class Znb_Polylang_Integration extends Znb_Integration
{
    /**
     * Check if we can load this integration or not
     * @return [type] [description]
     */
    static public function can_load(){
         return class_exists( 'Polylang' );
    }


    function initialize()
    {
        //#! Add the smart area post type to polylang settings page
        add_filter( 'pll_get_post_types', array( $this, 'filter_post_types' ), 200 );

        add_action( 'activated_plugin', array( $this, 'update_polylang_options' ), 200 );
    }

    function filter_post_types( $post_types ) {
        $post_types[] = 'znpb_template_mngr';
        return $post_types;
    }

    function update_polylang_options() {
        //#! Check the options and set Smart Areas to be translatable
        $options = get_option( 'polylang' );
        if( ! empty($options) ){
            if( isset($options['post_types']) && ! in_array( 'znpb_template_mngr', $options['post_types'])){
                array_push( $options['post_types'], 'znpb_template_mngr' );
            }
        }
        update_option( 'polylang', $options );
    }
}
