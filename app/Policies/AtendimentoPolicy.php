<?php

namespace App\Policies;

use App\Support\Perfis;

class AtendimentoPolicy extends BasePolicy
{
    protected array $editores = [Perfis::PSICOSSOCIAL];

    protected array $leitores = [];
}