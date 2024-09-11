<?php 

namespace PNS\Admin\Extensions\Helpers\Presets;

class EcommercePreset {

    const STUBS = [
        'product' => [
            'required' => true,
            'models' => [
                'ProductCategory',
                'Product',
            ],
            'controllers' => [
                'ProductCategoryController',
                'ProductController',
            ],
            'migrations' => [
                'create_product_categories_table',
                'create_products_table',
            ],
            'seeds' => [
                // 'ProductSeeder',
            ],
            'routes' => [
                "\$router->resource('products', 'ProductController')->names('admin.products');",
            ]
        ],
        'customer' => [
            'required' => true,
            'models' => [
                'Customer',
            ],
            'controllers' => [
                'CustomerController',
            ],
            'migrations' => [
                'create_customers_table',
            ],
            'seeds' => [
                // 'CustomerSeeder',
            ],
            'routes' => [
                "\$router->resource('customers', 'CustomerController')->names('admin.customers');",
            ]
        ],
        'order' => [
            'required' => true,
            'models' => [
                'Order',
                'OrderItem',
            ],
            'controllers' => [
                'OrderController',
                'OrderItemController',
            ],
            'migrations' => [
                'create_orders_table',
                'create_order_items_table',
            ],
            'seeds' => [
                // 'OrderSeeder',
            ],
            'routes' => [
                "\$router->resource('orders', 'OrderController')->names('admin.orders');",
            ]
        ],
    ];

    public static function install($presets)
    {
        foreach (static::STUBS as $key => $stub) {
            
        }
    }

}