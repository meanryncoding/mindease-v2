<?php
declare(strict_types=1);

namespace App\Controller;

class DashboardController extends AppController
{
    public function student()
    {
        $this->viewBuilder()->setLayout('student');

        return $this->guard('student');
    }

    public function counselor()
    {
        return $this->guard('counselor');
    }

    public function admin()
    {
        return $this->guard('admin');
    }

    /**
     * Send the user back to the front page when their role does not match the page.
     */
    private function guard(string $role)
    {
        if ($this->Authentication->getIdentity()->get('role') !== $role) {
            return $this->redirect('/');
        }

        return null;
    }
}