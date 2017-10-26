<?php if(! defined('ABSPATH')){ return; }
/**
 * Template layout for single entries
 * @package  Kallyas
 * @author   Team Hogash
 */
get_header();

/*** USE THE NEW HEADER FUNCTION **/
WpkPageHelper::zn_get_subheader();

/*--------------------------------------------------------------------------------------------------
	CONTENT AREA
--------------------------------------------------------------------------------------------------*/
?>
<section id="content" class="site-content" >
	<div class="container">
		<div id="mainbody" <?php echo WpkPageHelper::zn_schema_markup('main'); ?>>
			<div class="row">
				<div class="col-sm-12">
				<?php
					while ( have_posts() ) : the_post();
						get_template_part( 'inc/page', 'content-view-portfolio.inc' );
					endwhile;
				?>
				</div>
			</div><!--// End .row -->
		</div><!--// End #mainbody -->
	</div><!--// End .container -->
</section><!--// #content -->
<?php get_footer(); ?>
