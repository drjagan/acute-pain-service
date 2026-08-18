<?php
namespace Routing;

use Helpers\CSRF;
use Helpers\Flash;
use Helpers\Session;

/**
 * Enforces route-level request policy before controller construction.
 */
class ActionPolicy
{
    /**
     * Enforce authentication, role, guest, and CSRF policy for a matched route.
     */
    public function enforce(array $policy): void
    {
        if (!empty($policy['guest']) && Session::has('user_id')) {
            redirect('/dashboard');
        }

        if (!empty($policy['roles'])) {
            $this->requireRole($policy['roles']);
        } elseif (!empty($policy['auth'])) {
            $this->requireAuth();
        }

        if (!empty($policy['csrf'])) {
            CSRF::check();
        }
    }

    /**
     * Require an authenticated session.
     */
    private function requireAuth(): void
    {
        if (!Session::has('user_id')) {
            Flash::error('Please login to continue');
            redirect('/auth/login');
        }
    }

    /**
     * Require the current user to hold one of the supplied roles.
     */
    private function requireRole($roles): void
    {
        $this->requireAuth();

        $userRole = Session::get('role');
        $roles = (array)$roles;

        if (!in_array($userRole, $roles, true) && $userRole !== 'admin') {
            Flash::error('You do not have permission to access this page');
            redirect('/dashboard');
        }
    }
}
