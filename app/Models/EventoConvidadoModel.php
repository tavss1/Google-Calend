<?php

namespace App\Models;

use CodeIgniter\Model;

class EventoConvidadoModel extends Model
{
    protected $table         = 'evento_convidados';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['evento_id', 'email', 'nome', 'externo', 'resposta'];
}
