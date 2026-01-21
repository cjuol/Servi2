// config/services.php - Añade esta configuración para Stripe

return [
    // ... otras configuraciones

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];
