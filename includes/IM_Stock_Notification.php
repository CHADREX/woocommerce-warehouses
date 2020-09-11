<?php
namespace Hellodev\InventoryManager;

if (! defined('ABSPATH')) {
	exit();
}

class IM_Stock_Notification
{
	public function __construct() {}

	/**
	 * [notify_low_stock notify the store owner according to the woocommerce settings]
	 * @param  [WC_Product] $product   [product object]
	 * @param  [IM_Warehouse] $warehouse [warehouse object]
	 */
	public function notify_low_stock( $product, $warehouse, $stock ) {
		$blog_title = get_bloginfo();

		if($stock >= 0){
			$subject = sprintf( '[%s] %s', $blog_title, __( 'Product in warehouse low in stock', 'woocommerce-inventorymanager' ) );
			$message = sprintf(
				__( '%s is low in stock.', 'woocommerce-inventorymanager' ),
				html_entity_decode(
					strip_tags( $product->get_formatted_name() ),
					ENT_QUOTES,
					get_bloginfo( 'charset' ) ) )
			. ' ' .
				sprintf(
					__( 'There are %d left in the Warehouse %s.', 'woocommerce-inventorymanager' ),
					strip_tags( $stock ),
		 			strip_tags( $warehouse->IM_Warehouse_name )
				);
		}
		else{
			$subject = sprintf( '[%s] %s', $blog_title, __( 'Product in warehouse needs stock', 'woocommerce-inventorymanager' ) );
			$message = sprintf(
				__( '%s is low in stock.', 'woocommerce-inventorymanager' ),
				html_entity_decode(
					strip_tags( $product->get_formatted_name() ),
					ENT_QUOTES,
					get_bloginfo( 'charset' ) ) )
			. ' ' .
				sprintf(
					__( 'Warehouse %s lacks stock (%s) to fulfill an order.', 'woocommerce-inventorymanager' ),
		 			strip_tags( $warehouse->IM_Warehouse_name ),
					strip_tags( $stock)
				);
		}

		wp_mail(
			apply_filters( 'woocommerce_email_recipient_low_stock', get_option( 'woocommerce_stock_email_recipient' ), $product ),
			apply_filters( 'woocommerce_email_subject_low_stock', $subject, $product ),
			apply_filters( 'woocommerce_email_content_low_stock', $message, $product ),
			apply_filters( 'woocommerce_email_headers', '', 'low_stock', $product ),
			apply_filters( 'woocommerce_email_attachments', array(), 'low_stock', $product )
		);
	}

	public function notify_warehouse( $warehouse_id, $order ) {
		global $woocommerce;
		$mailer = $woocommerce->mailer();
		// Email customer with order-processing receipt
		$warehouse_repository = new IM_Warehouse_Repository();
		$warehouse = $warehouse_repository->getByID($warehouse_id);

		$email = $mailer->emails['WC_Email_New_Order'];

		$old_recipient = $email->recipient;
		$email->recipient = $warehouse->IM_Warehouse_email;
		$old_heading = $email->heading;
		$email->heading .= " on $warehouse->IM_Warehouse_name";

		$email->trigger( $order->id );

		$email->recipient = $old_recipient;
		$email->heading = $old_heading;

		// Add order note
		$note = sprintf( __( 'Warehouse %s received an email for this order.', 'woocommerce-inventorymanager' ), $warehouse->IM_Warehouse_name);
		$order->add_order_note( $note );
	}

	// Format email to HTML
	function set_email_to_html() {
		return 'text/html';
	}

}
