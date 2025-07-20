<?php
/**
 * PizzaLayer Template : Orderboard
 * This file returns structured metadata for use in admin listing, sorting,
 * display in customizer, and potential REST API exposure.
 */
 
 
$pizzalayer_template_help_info = "Usage Instructions

1. **Install the Template:**
   - Place this template folder inside your `wp-content/uploads/pizzalayer-templates` directory.
   - The template will automatically appear in your PizzaLayer template selector.

2. **Assign the Template:**
   - Create or edit a PizzaLayer product in WooCommerce.
   - Under the 'Pizza Details' tab, select 'Classic Pepperoni Pizza' as your template.

3. **Customize Layers:**
   - You can modify crust type, sauce type, cheese layer, and pepperoni distribution in the layer editor.
   - Prices and fractions are automatically calculated using your active price grid.

4. **Preview:**
   - Use the live preview on the front end to test different combinations before publishing.

5. **Support:**
   - For help customizing this template or advanced hook integration, visit the support URL provided above.
"; // close $pizzalayer_template_help_info
 
 
 $pizzalayer_template_info_array = [
    'name' => 'Order Board',
    'author' => 'Ryan Bishop',
    'author_url' => 'https://yourwebsite.com',
    'description' => 'An app-style layout designed for mobile or smaller spaces',
    'support_url' => 'https://yourwebsite.com/support',
    'license' => 'GPL-3.0-or-later',
    'version' => '1.0.0',
    'tags' => ['classic', 'pepperoni', 'cheese', 'customizable'],
    'instructions' => $pizzalayer_template_help_info
];