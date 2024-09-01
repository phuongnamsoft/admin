<?php

namespace PNS\Admin\Traits;
use PragmaRX\Google2FA\Google2FA;

trait HasGoogle2Fa
{

    protected $google2fa_secret;
    
    public function generate2FaSecret()
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

}