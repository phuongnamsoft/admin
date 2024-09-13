<?php 

namespace PNS\Admin\Extensions\Helpers\Presets;

class EcommercePreset extends BasePreset {

    protected $selectedModules = [
        'product' => true,
        'customer' => true,
        'order' => true,
    ];

    protected $options = [
        'run_migrations' => true,
        'run_seeds' => false,
    ];

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



    public function install()
    {
        foreach (static::STUBS as $key => $stub) {
            if ($this->options[$key]) {
                $this->moveModelFiles($key);
                $this->moveControllerFiles($key);
                $this->moveMigrationFiles($key);
                $this->moveSeedFiles($key);
                // $this->moveRouteFiles($key);

            }

        }

        if ($this->options['run_migrations']) {
            $this->runMigrations();
        }

        if ($this->options['run_seeds']) {
            $this->runSeeds();
        }
    }

}