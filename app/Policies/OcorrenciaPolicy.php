<?php

namespace App\Policies;

use App\Support\Perfis;

class OcorrenciaPolicy extends BasePolicy
{
    protected array $editores = [Perfis::DAE_CENTRAL, Perfis::PSICOSSOCIAL];

    protected array $leitores = [];
}