<?php

namespace App\Controllers;

use App\Libraries\GoogleCalendarSync;
use App\Models\EventoConvidadoModel;
use App\Models\EventoModel;
use DateTimeImmutable;

class Calendario extends BaseController
{
    private const CORES = ['interno' => '#0d6efd', 'externo' => '#198754', 'aniversario' => '#fd7e14'];

    private function ehAdm(): bool
    {
        return session()->get('perfil') === 'ADM'; // AJUSTE conforme o seu login
    }

    public function index()
    {
        return view('calendario/index', [
            'isAdm' => $this->ehAdm(),
            'nome'  => (string) session()->get('nome'),
        ]);
    }

    /** GET calendario/feed?start=...&end=...  (usado pelo FullCalendar; todos os usuários logados) */
    public function feed()
    {
        $start = $this->dataGet('start');
        $end   = $this->dataGet('end');
        if (! $start || ! $end) {
            return $this->response->setStatusCode(400)->setJSON(['erro' => 'Período inválido.']);
        }

        $isAdm   = $this->ehAdm();
        $eventos = model(EventoModel::class)
            ->where('tipo !=', 'aniversario')
            ->where('inicio <', $end)
            ->where('COALESCE(fim, inicio) >=', $start, false)
            ->findAll();

        // Lista de convidados só é enviada ao ADM (privacidade/LGPD).
        $convidados = [];
        if ($isAdm && $eventos) {
            $rows = model(EventoConvidadoModel::class)->whereIn('evento_id', array_column($eventos, 'id'))->findAll();
            foreach ($rows as $c) {
                $convidados[$c['evento_id']][] = [
                    'email'    => $c['email'],
                    'nome'     => $c['nome'],
                    'externo'  => (bool) $c['externo'],
                    'resposta' => $c['resposta'],
                ];
            }
        }

        $out = [];
        foreach ($eventos as $e) {
            $diaInteiro = (bool) $e['dia_inteiro'];
            if ($diaInteiro) {
                $ini = substr($e['inicio'], 0, 10);
                // FullCalendar também usa "end" exclusivo em dia inteiro
                $fim = (new DateTimeImmutable($e['fim'] ?? $e['inicio']))->modify('+1 day')->format('Y-m-d');
            } else {
                $ini = str_replace(' ', 'T', $e['inicio']);
                $fim = $e['fim'] ? str_replace(' ', 'T', $e['fim']) : null;
            }

            $out[] = [
                'id'            => 'e' . $e['id'],
                'title'         => $e['titulo'],
                'start'         => $ini,
                'end'           => $fim,
                'allDay'        => $diaInteiro,
                'color'         => self::CORES[$e['tipo']] ?? '#6c757d',
                'extendedProps' => [
                    'eventoId'   => (int) $e['id'],
                    'titulo'     => $e['titulo'],
                    'tipo'       => $e['tipo'],
                    'descricao'  => $e['descricao'],
                    'local'      => $e['local'],
                    'inicio'     => $e['inicio'],
                    'fim'        => $e['fim'],
                    'dia_inteiro'=> $diaInteiro,
                    'sync'       => $isAdm ? $e['sync_status'] : null,
                    'convidados' => $convidados[$e['id']] ?? [],
                ],
            ];
        }

        return $this->response->setJSON(array_merge($out, $this->aniversarios($start, $end)));
    }

    /** Aniversários: dos usuários (data_nascimento) + os cadastrados manualmente. Repetem todo ano. */
    private function aniversarios(string $start, string $end): array
    {
        $fontes = [];

        // AJUSTE: tabela/colunas dos usuários do seu sistema
        $usuarios = db_connect()->table('usuarios')
            ->select('id, nome, data_nascimento')
            ->where('data_nascimento IS NOT NULL', null, false)
            ->get()->getResultArray();
        foreach ($usuarios as $u) {
            $fontes[] = ['id' => 'u' . $u['id'], 'titulo' => $u['nome'], 'md' => substr($u['data_nascimento'], 5, 5), 'ev' => null];
        }

        foreach (model(EventoModel::class)->where('tipo', 'aniversario')->findAll() as $e) {
            $fontes[] = ['id' => 'a' . $e['id'], 'titulo' => $e['titulo'], 'md' => substr($e['inicio'], 5, 5), 'ev' => $e];
        }

        $anoIni = (int) substr($start, 0, 4);
        $anoFim = (int) (new DateTimeImmutable($end))->modify('-1 day')->format('Y');
        $out    = [];

        foreach ($fontes as $f) {
            for ($ano = $anoIni; $ano <= $anoFim; $ano++) {
                $md = ($f['md'] === '02-29' && ! checkdate(2, 29, $ano)) ? '02-28' : $f['md'];
                $d  = "$ano-$md";
                if ($d < $start || $d >= $end) {
                    continue;
                }
                $e = $f['ev'];
                $out[] = [
                    'id'            => $f['id'] . '-' . $ano,
                    'title'         => '🎂 ' . $f['titulo'],
                    'start'         => $d,
                    'allDay'        => true,
                    'color'         => self::CORES['aniversario'],
                    'extendedProps' => [
                        'eventoId'    => $e ? (int) $e['id'] : null, // null = vem do cadastro de usuários (não editável aqui)
                        'titulo'      => $f['titulo'],
                        'tipo'        => 'aniversario',
                        'descricao'   => $e['descricao'] ?? null,
                        'local'       => null,
                        'inicio'      => $e['inicio'] ?? null,
                        'fim'         => null,
                        'dia_inteiro' => true,
                        'convidados'  => [],
                    ],
                ];
            }
        }
        return $out;
    }

    /** GET calendario/usuarios?q=  (autocomplete de convidados internos; só ADM) */
    public function usuarios()
    {
        $q = trim((string) $this->request->getGet('q'));
        if (mb_strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $rows = db_connect()->table('usuarios') // AJUSTE
            ->select('nome, email')
            ->where('email IS NOT NULL', null, false)
            ->groupStart()->like('nome', $q)->orLike('email', $q)->groupEnd()
            ->orderBy('nome')->limit(8)
            ->get()->getResultArray();

        return $this->response->setJSON($rows);
    }

    /** POST calendario/salvar  (JSON; cria se não vier id, senão atualiza; só ADM) */
    public function salvar()
    {
        $in = $this->request->getJSON(true) ?? [];
        $id = (int) ($in['id'] ?? 0);

        if (! $this->validateData($in, [
            'titulo'    => 'required|max_length[200]',
            'tipo'      => 'required|in_list[interno,externo,aniversario]',
            'local'     => 'permit_empty|max_length[255]',
            'descricao' => 'permit_empty|max_length[5000]',
        ])) {
            return $this->resposta(['erros' => $this->validator->getErrors()], 422);
        }

        $tipo       = $in['tipo'];
        $diaInteiro = ! empty($in['dia_inteiro']) || $tipo === 'aniversario';

        // Datas
        try {
            if (empty($in['inicio'])) {
                return $this->resposta(['erro' => 'Informe a data de início.'], 422);
            }
            $ini = new DateTimeImmutable($in['inicio']);
            $fim = (! empty($in['fim']) && $tipo !== 'aniversario') ? new DateTimeImmutable($in['fim']) : null;
        } catch (\Exception $e) {
            return $this->resposta(['erro' => 'Data inválida.'], 422);
        }
        if ($diaInteiro) {
            $ini = $ini->setTime(0, 0);
            $fim = $fim?->setTime(0, 0);
        }
        if ($fim && ($fim < $ini || (! $diaInteiro && $fim == $ini))) {
            return $this->resposta(['erro' => 'O fim deve ser depois do início.'], 422);
        }

        // Convidados (não se aplica a aniversário)
        $emails = [];
        if ($tipo !== 'aniversario') {
            foreach (($in['convidados'] ?? []) as $c) {
                $email = mb_strtolower(trim(is_array($c) ? ($c['email'] ?? '') : ''));
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->resposta(['erro' => 'E-mail de convidado inválido.'], 422);
                }
                $emails[$email] = mb_substr(trim($c['nome'] ?? ''), 0, 150);
            }
            if (count($emails) > 100) {
                return $this->resposta(['erro' => 'Máximo de 100 convidados por evento.'], 422);
            }
        }

        // Quem já tem cadastro no sistema é "interno"; os demais são externos.
        $internos = [];
        if ($emails) {
            $rows     = db_connect()->table('usuarios')->select('email')->whereIn('email', array_keys($emails))->get()->getResultArray();
            $internos = array_map('mb_strtolower', array_column($rows, 'email'));
        }

        $eventos = model(EventoModel::class);
        $convMdl = model(EventoConvidadoModel::class);

        if ($id && ! $eventos->find($id)) {
            return $this->resposta(['erro' => 'Evento não encontrado.'], 404);
        }

        $dados = [
            'titulo'      => trim($in['titulo']),
            'descricao'   => $in['descricao'] ?? null,
            'tipo'        => $tipo,
            'local'       => $in['local'] ?? null,
            'inicio'      => $ini->format('Y-m-d H:i:s'),
            'fim'         => $fim?->format('Y-m-d H:i:s'),
            'dia_inteiro' => $diaInteiro ? 1 : 0,
            'sync_status' => 'pendente',
            'sync_erro'   => null,
        ];

        // 1) Salva SEMPRE primeiro no MySQL
        $db = db_connect();
        $db->transStart();
        if ($id) {
            $respostas = array_column($convMdl->where('evento_id', $id)->findAll(), 'resposta', 'email');
            $eventos->update($id, $dados);
            $convMdl->where('evento_id', $id)->delete();
        } else {
            $respostas = [];
            $dados['criado_por'] = (int) session()->get('id'); // AJUSTE
            $id = (int) $eventos->insert($dados);
        }
        foreach ($emails as $email => $nome) {
            $convMdl->insert([
                'evento_id' => $id,
                'email'     => $email,
                'nome'      => $nome ?: null,
                'externo'   => in_array($email, $internos, true) ? 0 : 1,
                'resposta'  => $respostas[$email] ?? 'pendente',
            ]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->resposta(['erro' => 'Não foi possível salvar o evento.'], 500);
        }

        // 2) Depois tenta sincronizar; falha aqui não desfaz o salvamento.
        $aviso = GoogleCalendarSync::sincronizarSeguro($id) !== null
            ? 'Evento salvo, mas a sincronização com o Google falhou e será reprocessada.'
            : null;

        return $this->resposta(['id' => $id, 'aviso' => $aviso]);
    }

    /** POST calendario/excluir/{id}  (só ADM) */
    public function excluir(int $id)
    {
        $eventos = model(EventoModel::class);
        $e       = $eventos->find($id);
        if (! $e) {
            return $this->resposta(['erro' => 'Evento não encontrado.'], 404);
        }

        // Se existe no Google, remove lá primeiro (avisa os convidados). Se falhar, não apaga aqui,
        // para não deixar um evento "fantasma" com convidados.
        if ($e['google_event_id'] && GoogleCalendarSync::habilitado()) {
            try {
                (new GoogleCalendarSync())->remover($e['google_event_id']);
            } catch (\Throwable $t) {
                log_message('error', 'Remoção Google (evento {id}): {msg}', ['id' => $id, 'msg' => $t->getMessage()]);
                return $this->resposta(['erro' => 'Não foi possível remover o evento no Google. Tente novamente.'], 502);
            }
        }

        $eventos->delete($id);
        return $this->resposta(['ok' => true]);
    }

    // ---- helpers ----

    private function resposta(array $dados, int $status = 200)
    {
        $dados['csrf'] = csrf_hash(); // novo token (o CI4 regenera a cada POST)
        return $this->response->setStatusCode($status)->setJSON($dados);
    }

    private function dataGet(string $campo): ?string
    {
        $v = substr((string) $this->request->getGet($campo), 0, 10);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    }
}
