<?php

namespace App\Commands;

use App\Libraries\GoogleCalendarSync;
use App\Models\EventoModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** Uso: php spark calendario:sync   (reprocessa eventos com sync pendente ou com erro) */
class SyncEventos extends BaseCommand
{
    protected $group       = 'Calendario';
    protected $name        = 'calendario:sync';
    protected $description = 'Reenvia ao Google Agenda os eventos pendentes ou com erro.';

    public function run(array $params)
    {
        if (! GoogleCalendarSync::habilitado()) {
            CLI::write('GOOGLE_SYNC_ENABLED está desligado. Nada a fazer.', 'yellow');
            return;
        }

        $pendentes = model(EventoModel::class)
            ->whereIn('sync_status', ['pendente', 'erro'])
            ->where('tipo !=', 'aniversario')
            ->orderBy('id')
            ->findAll(50);

        foreach ($pendentes as $e) {
            $erro = GoogleCalendarSync::sincronizarSeguro((int) $e['id']);
            $erro === null
                ? CLI::write("#{$e['id']} {$e['titulo']}: ok", 'green')
                : CLI::write("#{$e['id']} {$e['titulo']}: ERRO - {$erro}", 'red');
        }

        CLI::write(count($pendentes) . ' evento(s) processado(s).');
    }
}
