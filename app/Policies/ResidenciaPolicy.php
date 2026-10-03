<?php

namespace App\Policies;

use App\Support\Perfis;

class ResidenciaPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [];
}