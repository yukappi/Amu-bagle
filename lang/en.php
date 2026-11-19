<?php
return [
    // header
    'nav.top'   => 'Home',
    'nav.about' => 'About',
    'nav.menu'  => 'Menu',
    'nav.cart'  => 'Cart',
    'nav.admin' => 'Shop admin',

    // menu page
    'menu.title'    => 'Menu',
    'menu.closed'   => 'Orders for this day are closed.',
    'menu.none'     => 'No bagels match your choice.',
    'filter.day'    => 'Open day',
    'filter.cat'    => 'Type',
    'filter.all'    => 'All',
    'filter.button' => 'Filter',
    'soldout'       => 'Sold out',

    // product page
    'product.back'              => '← Back to menu',
    'product.notfound'          => 'Bagel not found',
    'product.allergens'         => 'Allergens',
    'product.allergens_unknown' => 'Not confirmed yet. Please ask the shop.',
    'product.days'              => 'Available on',
    'product.no_days'           => 'Not on sale for the next open days yet.',
    'product.pickup_day'        => 'Pickup day',
    'product.qty'               => 'Quantity',
    'product.add'               => 'Add to cart',

    // top page
    'home.title'    => 'Bagel reservations',
    'home.next'     => 'Next open day',
    'home.left'     => '%d days %d hours left to order',
    'home.see_menu' => 'See the menu',
    'home.no_next'  => 'The next open day is not decided yet.',
    'home.picks'    => 'Our picks',
    'home.small'    => 'A small bagel shop in Nakama',
    'home.catch1'   => 'Chewy and soft,',
    'home.catch2'   => 'happy bagels.',
    'home.lead'     => 'Order our chewy bagels on this site and pick them up at the shop. (4 or more, please.)',
    'home.cta'      => 'See the menu and order',



    // about page
    'about.intro'       => 'A small bagel shop in Nakama, Fukuoka. We open on irregular days, about once a week.',
    'about.address'     => 'Address: ',
    'about.hours'       => 'Hours: ',
    'about.our_address' => '2-14 Nabeyama-machi, Nakama, Fukuoka',
    'about.our_hours'   => '11:00-16:00 (open days only)',
    'about.shops'       => 'Shops that sell our bagels',
    'about.unknown'     => 'Checking',
    'about.shops_lead'  => 'You can also buy our bagels here on other days.',

    // order (complete page, cart, checkout)
    'order.day'      => 'Pickup day',
    'order.time'     => 'Pickup time',
    'order.name'     => 'Name',
    'order.bagel'    => 'Bagel',
    'order.price'    => 'Price',
    'order.subtotal' => 'Subtotal',
    'order.total'    => 'Total: %d items',

    // complete page
    'done.title' => 'Thank you for your order!',
    'done.code'  => 'Order number',
    'done.tell'  => 'Please tell us your order number at the shop.',
    'done.back'  => 'Back to home',

    // cart page
    'cart.title'     => 'Cart',
    'cart.added'     => 'Added to your cart.',
    'cart.other_day' => 'Your cart has bagels for a different pickup day. Please empty your cart first.',
    'cart.empty'     => 'Your cart is empty.',
    'cart.until'     => '(order by %s)',
    'cart.change'    => 'Update',
    'cart.remove'    => 'Remove',
    'cart.min4'      => 'Please order 4 or more bagels.',
    'cart.continue'  => '← Keep shopping',
    'cart.clear'     => 'Empty cart',
    'cart.checkout'  => 'Go to order',

    // checkout page
    'checkout.title'      => 'Your order',
    'checkout.confirm'    => 'Check your order',
    'checkout.required'   => ' (required)',
    'checkout.optional'   => ' (optional)',
    'checkout.choose'     => 'Please choose',
    'checkout.back_cart'  => '← Back to cart',
    'checkout.next'       => 'Next',
    'checkout.none'       => 'None',
    'checkout.fix'        => '← Edit',
    'checkout.place'      => 'Place order',
    'order.phone'         => 'Phone',
    'order.email'         => 'Email',
    'order.note'          => 'Requests',
    'error.name'          => 'Please enter your name.',
    'error.name_long'     => 'Your name can be up to 100 characters.',
    'error.phone'         => 'Please use numbers and hyphens for your phone number. (e.g. 090-1234-5678)',
    'error.email'         => 'This email address does not look right.',
    'error.time'          => 'Please choose a pickup time.',
    'error.note'          => 'Requests can be up to 500 characters.',

    // reservation rules
    'rule.no_day'   => 'Pickup day not found.',
    'rule.closed'   => 'Orders for this day are closed.',
    'rule.not_sold' => 'Your cart has a bagel that is no longer sold.',
    'rule.not_day'  => '%s is not sold on this day.',
    'rule.soldout'  => '%s is sold out.',
    'rule.max'      => '%s: up to %d per order.',
    'rule.left'     => 'Only %d more bagels can be ordered for this day.',
];
