# Cron Job Plugin for Daily WooCommerce Commission Transfer

**Version:** 1.0

**Author:** Umar Khtab

## Description
The Cron Job Plugin for Daily WooCommerce Commission Transfer is designed to automatically transfer funds to vendors daily using the Stripe payment gateway. It schedules a cron job that runs daily to process commissions marked as 'due' and transfer the corresponding amounts to the vendors' Stripe accounts.

## Features
- Automatically transfers funds to vendors daily.
- Sends a test email to verify cron job execution.
- Retrieves commissions with status 'due' and processes them.
- Transfers funds to vendors using Stripe.
- Updates commission status to 'Paid' upon successful transfer.

## Installation

1. **Upload Plugin Files:**
   - Upload the plugin files to the `/wp-content/plugins/` directory.

2. **Activate the Plugin:**
   - Activate the plugin through the 'Plugins' menu in WordPress.

3. **Install Stripe Gate Library the Plugin:**
   - Install or download the stripe payment gateway library in plugin root directory and set the path according to your location in plugin file.

## Usage

### Functionality

1. **Send Test Email:**
   - A test email is sent to `umarkhtab.te@gmail.com` to verify that the cron job is running correctly.

2. **Retrieve Commissions:**
   - The plugin retrieves commissions from the `pv_commission` table that have a status of 'due'.

3. **Process Each Commission:**
   - For each commission:
     - Retrieve the vendor's Stripe account ID from user meta.
     - Get the order item IDs associated with the commission.
     - Check if the order date is before the current date.
     - If conditions are met, transfer funds to the vendor's Stripe account.
     - Update the commission status to 'Paid' in the database.

4. **Stripe Integration:**
   - Uses the Stripe API to handle fund transfers.
   - Switches between test and live API keys based on the user's IP address.

### Scheduling

1. **Schedule Daily Transfer:**
   - The plugin schedules a daily cron job to run the `transfer_funds_to_vendors` function.

2. **Cancel Scheduled Transfer:**
   - If the plugin is deactivated, the scheduled event is canceled.

### Activation and Deactivation

- **Activation Hook:**
  - Schedules the daily transfer when the plugin is activated.

- **Deactivation Hook:**
  - Cancels the scheduled transfer when the plugin is deactivated.

