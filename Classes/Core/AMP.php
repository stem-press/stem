<?php

namespace ILab\Stem\Core;

/**
 * Class AMP
 *
 * This class represents the manager for handling Google's AMP pages
 *
 * @package ILab\Stem\Core
 */
class AMP {
	/**
	 * Current context
	 * @var Context
	 */
	private $context;

	/**
	 * Determines if this is a request for an AMP page
	 * @var bool
	 */
	private $active = false;

	/**
	 * Constructor
	 *
	 * @param $context Context The current context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
		$ampWasEnabled = get_site_option('stem-amp-enabled', false);
		if ($this->context->setting('amp/enabled', false)) {
			add_action('init',function() use ($ampWasEnabled) {
				add_rewrite_endpoint('amp',EP_PERMALINK);

				if (!$ampWasEnabled) {
					update_site_option('stem-amp-enabled', true);
					flush_rewrite_rules();
				}

				foreach($this->context->setting('amp/post-types',[]) as $postType)
					add_post_type_support($postType, 'amp');

				add_action('wp',function(){
					if ( ! is_singular() || is_feed() ) {
						return;
					}

					global $wp_query;
					$is_amp = isset($wp_query->query_vars['amp']);
					$post = $wp_query->post;
					$supports = post_type_supports($post->post_type, 'amp');

					if (!$supports) {
						if ($is_amp) {
							wp_safe_redirect( get_permalink( $post->ID ) );
							exit;
						}

						return;
					}

					$this->active = $is_amp;

					if ($is_amp) {
						add_filter('embed_oembed_html', function($cache, $url, $attr, $post_id){
							if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
								$tubeId = $match[1];
								return "<amp-youtube layout='responsive' data-videoid='$tubeId' width='480' height='270'></amp-youtube>";
							}

							if (preg_match('#twitter.com\/(?:.*)\/status/([aA-zZ0-9]+)#',$url, $matches)) {
								$tweetId = $matches[1];
								return "<amp-twitter width=486 height=657 layout='responsive' data-tweetid='$tweetId' data-cards='visible'></amp-twitter>";
							}

							if (strpos($url, 'soundcloud')>0)
								return '';

							return $cache;
						}, 10, 4);

						add_filter('the_content',function($content) {
							$content = str_replace('<img ','<amp-img layout="responsive" ',$content);
							$content = preg_replace('/<p>(<amp-youtube(?:.*)><\/amp-youtube>)<\/p>/', "$1", $content);
							return $content;
						}, 100000);
					}
				});
			});
		} else if ($ampWasEnabled) {
			update_site_option('stem-amp-enabled', false);
			flush_rewrite_rules();
		}
	}

	/**
	 * Indicates that this context is serving a request for an AMP page
	 * @return bool
	 */
	public function active() {
		return $this->active;
	}
}