<?php



class PayClassArr
{

    public function paymethodClassState()
    {
        return [
            'self' => 'App\HttpController\Channel\SelfChannel',
            'cust' => 'App\HttpController\Channel\CustChannel',
        ];
    }

}