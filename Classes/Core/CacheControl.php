<?php

namespace ILab\Stem\Core;

use Symfony\Component\HttpFoundation\Response;

class CacheControl {
	private $context = null;
	private $cacheControl = null;
	private $maxAge = null;
	private $s_maxAge = null;

	public function __construct(Context $context) {
		$this->context = $context;

		$this->configureCacheControl();
	}

	private function configureCacheControl() {
		if (!is_admin())
			return;

		if ($this->context->setting('options/cache-control/enabled')) {
			if ($this->context->setting('options/cache-control/metabox')) {
				add_action('add_meta_boxes', function () {
					add_meta_box('cache-headers','Cache Headers', function($page){
						$this->renderCacheMetabox($page);
					}, 'page', 'side', 'low');
				});

				add_action('save_post', function($page_id) {
					$is_autosave = wp_is_post_autosave( $page_id );
					$is_revision = wp_is_post_revision( $page_id );
					$is_valid_nonce = ( isset( $_POST[ 'ilab_cache_nonce' ] ) && wp_verify_nonce( $_POST[ 'ilab_cache_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

					if ($is_autosave || $is_revision || !$is_valid_nonce) {
						return;
					}

					if (isset($_POST['stem-cache-control'])) {
						update_post_meta($page_id, 'stem-cache-control', sanitize_text_field($_POST['stem-cache-control']));
					}

					if (isset($_POST['stem-cache-max-age'])) {
						update_post_meta($page_id, 'stem-cache-max-age', sanitize_text_field($_POST['stem-cache-max-age']));
					}

					if (isset($_POST['stem-cache-s-maxage'])) {
						update_post_meta($page_id, 'stem-cache-s-maxage', sanitize_text_field($_POST['stem-cache-s-maxage']));
					}
				});

				add_action('admin_print_styles', function(){
					global $typenow;
					if($typenow == 'page') {
						wp_enqueue_style('stem_meta_box_styles', ILAB_STEM_PUB_CSS_URL . '/metabox.css' );
					}
				});
			}
		}
	}

	private function renderCacheMetabox($page) {
		wp_nonce_field( basename( __FILE__ ), 'ilab_cache_nonce' );
		$hasDefault = is_array($this->context->setting('options/cache-control/default'));

		$cc = get_post_meta( $page->ID, 'stem-cache-control', true);
		$ma = get_post_meta( $page->ID, 'stem-cache-max-age', true);
		$sma = get_post_meta( $page->ID, 'stem-cache-s-maxage', true);

		$showMaxAge = ($cc && (($cc == 'public') || ($cc == 'private') || ($cc == 'none')));
		?>
		<p><strong>Cache Control</strong></p>
		<label for="stem-cache-control" class="screen-reader-text">Cache Control</label>
		<select name="stem-cache-control" id="stem-cache-control">
			<?php if ($hasDefault): ?>
				<option value="default" <?php if ($cc) selected($cc, 'default'); ?>>Default</option>';
			<?php endif; ?>
			<option value="none" <?php if ($cc) selected($cc, 'none'); ?>>None</option>';
			<option value="no-store" <?php if ($cc) selected($cc, 'no-store'); ?>>No Store</option>';
			<option value="no-cache" <?php if ($cc) selected($cc, 'no-cache'); ?>>No Cache</option>';
			<option value="no-store, no-cache" <?php if ($cc) selected($cc, 'no-store, no-cache'); ?>>No Store, No Cache</option>';
			<option value="private" <?php if ($cc) selected($cc, 'private'); ?>>Private</option>';
			<option value="public" <?php if ($cc) selected($cc, 'public'); ?>>Public</option>';
		</select>
		<p id="stem-max-age-label" style="display:<?php echo ($showMaxAge) ? 'block' : 'none' ?>"><strong>Max Age</strong></p>
		<p id="stem-max-age-input" style="display:<?php echo ($showMaxAge) ? 'block' : 'none' ?>">
			<label class="screen-reader-text" for="stem-cache-max-age">Max Age</label>
			<input name="stem-cache-max-age" type="text" size="10" id="stem-cache-max-age" value="<?php echo ($ma != '') ? $ma : ''; ?>">&nbsp;seconds
		</p>
		<p id="stem-s-maxage-label" style="display:<?php echo ($showMaxAge) ? 'block' : 'none' ?>"><strong>CDN/Proxy Max Age</strong></p>
		<p id="stem-s-maxage-input" style="display:<?php echo ($showMaxAge) ? 'block' : 'none' ?>">
			<label class="screen-reader-text" for="stem-cache-s-maxage">CDN/Proxy Max Age</label>
			<input name="stem-cache-s-maxage" type="text" size="10" id="stem-cache-s-maxage" value="<?php echo ($sma != '') ? $sma : ''; ?>">&nbsp;seconds
		</p>
		<script>
			(function($){
				$('#stem-cache-control').on('change',function(e){
					var val = $(this).val();
					if ((val != 'private') && (val != 'public') && (val != 'none')) {
						$('#stem-max-age-label').css({display: 'none'});
						$('#stem-max-age-input').css({display: 'none'});
						$('#stem-s-maxage-label').css({display: 'none'});
						$('#stem-s-maxage-input').css({display: 'none'});
					} else {
						$('#stem-max-age-label').css({display: 'block'});
						$('#stem-max-age-input').css({display: 'block'});
						$('#stem-s-maxage-label').css({display: 'block'});
						$('#stem-s-maxage-input').css({display: 'block'});
					}
				});
			})(jQuery);
		</script>
		<?php
	}

	private function setDefaultCacheControlHeaders() {
		$this->cacheControl = null;
		$this->maxAge = null;
		$this->s_maxAge = null;

		if (is_array($this->context->setting('options/cache-control/default'))) {
			$this->cacheControl = $this->context->setting('options/cache-control/default/cache-control', null);
			$this->maxAge = $this->context->setting('options/cache-control/default/max-age', null);
			$this->s_maxAge = $this->context->setting('options/cache-control/default/s-maxage', null);
		}
	}

	public function setCacheControlHeadersForPage($page_id = null) {
		if ($page_id == null) {
			$this->setDefaultCacheControlHeaders();
			return;
		}

		$cacheControl = get_post_meta($page_id, 'stem-cache-control', true);
		if ($cacheControl == 'default') {
			$this->setDefaultCacheControlHeaders();
			return;
		}

		$maxAge = get_post_meta($page_id, 'stem-cache-max-age', true);
		$s_maxAge = get_post_meta($page_id, 'stem-cache-s-maxage', true);

		if ($cacheControl != 'none') {
			$this->cacheControl = $cacheControl;
			$this->maxAge = $maxAge;
			$this->s_maxAge = $s_maxAge;
		}
	}

	public function setCacheControlHeaders($cacheControl = null, $maxAge = null, $s_maxAge=null) {
		$this->cacheControl = $cacheControl;
		$this->maxAge = $maxAge;
		$this->s_maxAge = $s_maxAge;
	}

	public function sendHTTPHeaders() {
		if (!$this->cacheControl || ($this->cacheControl == 'default'))
			$this->setDefaultCacheControlHeaders();

		$parts = [];

		if ($this->cacheControl)
			$parts[] = $this->cacheControl;

		if ($this->maxAge)
			$parts[] = "max-age: {$this->maxAge}";

		if ($this->s_maxAge)
			$parts[] = "s-maxage: {$this->s_maxAge}";

		if (count($parts)>0) {
			header("Cache-Control: ".implode(", ", $parts));
		}
	}

	public function addResponseHeaders(Response $response) {
		if (!$this->cacheControl || ($this->cacheControl == 'default'))
			$this->setDefaultCacheControlHeaders();

		if ($this->cacheControl) {
			if ($this->cacheControl == 'public') {
				$response->headers->addCacheControlDirective('public', true);

				if (is_numeric($this->maxAge))
					$response->setMaxAge($this->maxAge);

				if (is_numeric($this->s_maxAge))
					$response->setSharedMaxAge($this->s_maxAge);
			}
			else if ($this->cacheControl == 'private') {
				$response->headers->addCacheControlDirective('private', true);

				if (is_numeric($this->maxAge))
					$response->setMaxAge($this->maxAge);
			}
			else if ($this->cacheControl == 'no-cache') {
				$response->headers->addCacheControlDirective('no-cache', true);
			}
			else if ($this->cacheControl == 'no-store') {
				$response->headers->addCacheControlDirective('no-store', true);
			}
			else if ($this->cacheControl == 'no-store, no-cache') {
				$response->headers->addCacheControlDirective('no-store', true);
				$response->headers->addCacheControlDirective('no-cache', true);
			}
		}
	}
}
