<div id="wpbody">
	<div id="wpbody-content">

		<div class="about-wrap">

			<h1>
				Welcome to Runway
				<span class="version"><?php
$framework = wp_get_theme( 'runway-framework' );
if ( $framework->exists() )
	echo 'Version '. $framework->Version;
?></span>
			</h1>

			<div class="about-text">A better way to create WordPress themes. Runway is a powerful development environment for making awesome themes.</div>

			<div class="runway-badge"><br></div>

			<div class="clear"></div>

			<?php // include_once 'views/introduction.php'; ?>

			<h2 class="nav-tab-wrapper tab-controlls">
				<a data-tabrel="#getting-started" href="#getting-started" class="nav-tab nav-tab-active">Getting Started</a><a data-tabrel="#support" href="#support" class="nav-tab">Help &amp; Support</a><a data-tabrel="#release-notes" href="#release-notes" class="nav-tab">Release Notes</a><a data-tabrel="#contribute" href="#contribute" class="nav-tab">Contribute</a>
			</h2>

			<div id="getting-started" class="tab tab-active">
				<?php include_once 'views/getting-started.php'; ?>
			</div>
			<div id="support" class="tab">
				<?php include_once 'views/support.php'; ?>
			</div>
			<div id="release-notes" class="tab">
				<?php include_once 'views/release-notes.php'; ?>
			</div>
			<div id="contribute" class="tab">
				<?php include_once 'views/contribute.php'; ?>
			</div>

			<div class="clear"></div>
		</div><!-- about-wrap -->


		<div class="clear"></div>
	</div><!-- wpbody-content -->

	<div class="clear"></div>
</div> <!-- id="wpbody" -->




<script type="text/javascript">
	jQuery(function () {

		var $ = jQuery;

		$('.tab-controlls a').click(function () {

			if(!$(this).hasClass('nav-tab-active')) {
				$('.tab-controlls a').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				$('.tab-active').removeClass('tab-active');
				$($(this).data('tabrel')).addClass('tab-active');
			}

			return false;
		});

	});
</script>
