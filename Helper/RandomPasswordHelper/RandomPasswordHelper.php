<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 20/05/18
 * Time: 18:00
 */

class RandomPasswordHelper
{

    /**
     * RandomPasswordHelperHelper constructor.
     */
    public function __construct()
    {
    }

    //Generatore automatico di password random
    function generatePassword($length){
        return str_shuffle(bin2hex(openssl_random_pseudo_bytes($length)));
    }

}