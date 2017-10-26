<?php if(! defined('ABSPATH')){ exit; }
/**
 * Admin View: Notice - Update
 * @see class-zn-about.php
 */
?>
<div id="message" class="notice notice-success">
	<h3><?php _e( 'Successfully updated!', 'zn_framework' ); ?></h3>
	<p><?php printf( _e( 'Thanks! You\'ve just updated <strong>%s options</strong> to the latest version!', 'zn_framework' ), ZNHGTFW()->getThemeName() ); ?></p>
</div>
