<?php
/*
Plugin Name: Wp Cron Job Plugin for Daily WooCommerce Commission Transfer
Description: The Cron Job Plugin for Daily WooCommerce Commission Transfer is designed to automatically transfer funds to vendors daily using the Stripe payment gateway. It schedules a cron job that runs daily to process commissions marked as 'due' and transfer the corresponding amounts to the vendors' Stripe accounts.
Version: 1.0
Author: Umar Khtab
Author URI: https://www.linkedin.com/in/umar-khtab-a6b96317b?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app
*/
// Function to transfer funds to vendors
function transfer_funds_to_vendors()
{
	$to = 'umarkhtab.te@gmail.com';
	$subject = 'Test email for trasferring funds to vendors';
	$message = 'This is a test email sent from plugin to tell you that cron is run and funds is tranfer to vendor account';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail($to, $subject, $message, $headers);
	global $wpdb;
	// Get the commission table name
	$table_name = $wpdb->prefix . 'pv_commission';
	// Retrieve records with status 'due'
	$query = $wpdb->prepare(
		"SELECT * FROM $table_name WHERE status = %s ORDER BY id ",
		'due'
	);

	$commissions = $wpdb->get_results($query);
	foreach ($commissions as $commission) {
		$commission_id = $commission->id;
		$orderId = $commission->order_id;
		$vendor_id = $commission->vendor_id;
		// $vendor_id = 1852;
		$total_amount = $commission->total_due;
		if ($vendor_id != 1) {
			$vendor_stripe_account_id = get_user_meta($vendor_id, 'vendor_stripe_account_id', true);
			if ($vendor_stripe_account_id != "") {
				// var_dump($vendor_stripe_account_id);die;
				$query = $wpdb->prepare("
					SELECT order_item_id
					FROM {$wpdb->prefix}woocommerce_order_items
					WHERE order_id = %d
					", $orderId);

				$order_item_ids = $wpdb->get_col($query);
				$meta_key = "FROM";
				foreach ($order_item_ids as $order_item_id) {
					// echo $order_item_id;

					$query = $wpdb->prepare("
							SELECT meta_value
							FROM {$wpdb->prefix}woocommerce_order_itemmeta
							WHERE meta_key = %s
							AND order_item_id = %d
						", $meta_key, $order_item_id);

					$meta_value = $wpdb->get_var($query);
					// echo $meta_value;
					if ($meta_value != "") {
						$givenDate = new DateTime($meta_value);
						// Format the dates as strings in the same format for comparison
						$givenDateString = $givenDate->format('Y/m/d');
						$previousDate = (new DateTime())->modify('-1 day');
						$previousDateFormatted = $previousDate->format('Y/m/d');
						// Compare the dates
						if ($givenDateString <= $previousDateFormatted) {
							// echo $givenDateString . '<br>';

							require_once $_SERVER['DOCUMENT_ROOT'] . '/stripe-php-master/init.php';
							// Check the user's IP address
							$user_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

							\Stripe\Stripe::setApiKey('You Stripe API key');

							try {
								// Create a Stripe transfer
								$transfer = \Stripe\Transfer::create([
									"amount" => $total_amount * 100,
									"currency" => "aud",
									"destination" => $vendor_stripe_account_id,
								]);

								echo 'Funds transferred to vendor: ' . $vendor_id . ' - Amount: ' . $transfer->amount . ' ' . $transfer->currency . '<br>';

							} catch (\Stripe\Exception\ApiErrorException $stripeException) {
								$errorMessage = 'Stripe API Error: ' . $stripeException->getMessage();
								// Log the Stripe API error to the WordPress default log file
								error_log($errorMessage);
							}
							if ($transfer->amount) {
								global $wpdb;

								// Get the commission table name
								$table_name = $wpdb->prefix . 'pv_commission';

								// Update the commission status in the database
								$wpdb->update(
									$table_name,
									array('status' => "Paid"),
									array('id' => $commission_id)
								);
							}

						}
					} else {
						echo 'Order item date does not match today\'s date for vendor: ' . $vendor_id . '<br>';

					}

				}
			} else {
				echo "No Available Payments ";
			}
		}

	}
}

// Function to schedule the daily transfer of funds
function schedule_daily_transfer()
{
	// Run the transfer function daily basis once in a day
	if (!wp_next_scheduled('transfer_funds_event')) {
		wp_schedule_event(time(), 'daily', 'transfer_funds_event');
	}
}

// Function to cancel the scheduled daily transfer
function cancel_daily_transfer()
{
	wp_clear_scheduled_hook('transfer_funds_event');
}

// Register activation hook to schedule daily transfer on plugin activation
register_activation_hook(__FILE__, 'schedule_daily_transfer');

// Register deactivation hook to cancel the scheduled daily transfer on plugin deactivation
register_deactivation_hook(__FILE__, 'cancel_daily_transfer');

// Callback function to perform the transfer when the scheduled event is triggered
function transfer_funds_event_callback()
{
	transfer_funds_to_vendors();
}

// Hook the callback function to the scheduled event
add_action('transfer_funds_event', 'transfer_funds_event_callback');
