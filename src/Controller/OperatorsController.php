<?php
namespace App\Controller;

use App\Controller\AppController;

class OperatorsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Usuário ou senha inválidos');
        }
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['action' => 'login']);
        }
    }
}
