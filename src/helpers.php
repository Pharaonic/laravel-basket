<?php

if (!function_exists('basket')) {
    /**
     * Basket Object
     *
     * @return \Pharaonic\Laravel\Basket\BasketManager
     */
    function basket()
    {
        return app('basket');
    }
}
