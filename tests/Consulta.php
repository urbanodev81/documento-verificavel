<?php

namespace UrbanoDev\DocumentoVerificavel\Tests;

use Illuminate\Database\Eloquent\Model;

/** Um dono qualquer do sistema, para o vínculo polimórfico. */
class Consulta extends Model
{
    protected $table = 'consultas';

    public $timestamps = false;

    protected $guarded = [];
}
