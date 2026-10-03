<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

/** Registra na auditoria a criação, alteração e exclusão do model. */
trait Auditavel
{
    protected static function bootAuditavel(): void
    {
        static::created(fn (Model $modelo) => Auditoria::registrarModelo($modelo, 'criado'));
        static::updated(fn (Model $modelo) => Auditoria::registrarModelo($modelo, 'alterado'));
        static::deleted(fn (Model $modelo) => Auditoria::registrarModelo($modelo, 'excluido'));
    }
}