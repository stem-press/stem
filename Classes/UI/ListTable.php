<?php

namespace Stem\UI;

/**
 * Extends \WP_List_Table and fixes an issue when embedded in Metaboxes
 *
 * @package Stem\UI
 */
abstract class ListTable extends \WP_List_Table {
	protected function display_tablenav( $which ) {
//		if ( 'top' === $which ) {
//			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
//		}
		?>
		<div class="tablenav <?php echo esc_attr($which); ?>">

			<?php if($this->has_items()): ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions($which); ?>
				</div>
			<?php endif;
			$this->extra_tablenav($which);
			$this->pagination($which);
			?>

			<br class="clear"/>
		</div>
		<?php
	}
}