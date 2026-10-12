<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Libera somente o perfil ADM.
 * AJUSTE: a chave de sessão 'perfil' e o valor 'ADM' devem refletir o seu login.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('perfil') !== 'ADM') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['erro' => 'Ação restrita a administradores.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
