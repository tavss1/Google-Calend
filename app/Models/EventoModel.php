<?php

namespace App\Models;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table         = 'eventos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'titulo', 'descricao', 'tipo', 'local', 'inicio', 'fim', 'dia_inteiro',
        'google_event_id', 'sync_status', 'sync_erro', 'criado_por',
    ];
}
