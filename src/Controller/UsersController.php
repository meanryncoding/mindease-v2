<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login']);
    }

    public function login()
    {
        $this->viewBuilder()->setLayout('auth');
        $result = $this->Authentication->getResult();

        if ($result !== null && $result->isValid()) {
            $home = $this->homeFor((string)$this->Authentication->getIdentity()->get('role'));
            if ($home === null) {
                $this->Authentication->logout();

                return $this->redirect('/');
            }

            return $this->redirect($home);
        }

        $this->set('loginFailed', $this->request->is('post'));
    }

    public function logout()
    {
        $this->Authentication->logout();

        return $this->redirect('/');
    }

    private function homeFor(string $role): ?string
    {
        return match ($role) {
            'student' => '/student',
            'counselor' => '/counselor',
            'admin' => '/admin',
            default => null,
        };
    }
}