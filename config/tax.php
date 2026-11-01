<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Tax Rate
    |--------------------------------------------------------------------------
    |
    | This value represents the default VAT/tax rate percentage used when a
    | product or variant does not specify a tax rate. It can be overridden
    | per-product or per-variant via the `tax_rate` column.
    |
    */

    'default_rate' => (float) env('TAX_RATE', 21),
];
