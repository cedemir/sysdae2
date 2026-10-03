<?php

namespace App\Policies;

use App\Support\Perfis;

class AtaPolicy extends BasePolicy
{
    protected array $editores = [Perfis::DAE_CENTRAL];

    protected array $leitores = [];
}