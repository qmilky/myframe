<?php
class AllCommonController
{
    public function yuanToFen($price)
    {
        return $prices = 100 * $price;
    }

    public function fenToYuan($price)
    {
        return $prices =$price/100;
    }
}