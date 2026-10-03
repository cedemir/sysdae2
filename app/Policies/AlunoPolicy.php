<?php

namespace App\Policies;

use App\Support\Perfis;

class AlunoPolicy extends BasePolicy
{
    protected array $editores = [Perfis::RESIDENCIA];

    protected array $leitores = [Perfis::DAE_CENTRAL, Perfis::PSICOSSOCIAL];
}