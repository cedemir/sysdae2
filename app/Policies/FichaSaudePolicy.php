<?php

namespace App\Policies;

use App\Support\Perfis;

class FichaSaudePolicy extends BasePolicy
{
    protected array $editores = [Perfis::PSICOSSOCIAL];

    protected array $leitores = [];
}