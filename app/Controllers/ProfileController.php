<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        $userId = $this->requireAuth();
        $user = User::find($userId);
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');

        $this->view('profile.index', compact('user', 'error', 'success'));
    }

    public function update(): void
    {
        $userId = $this->requireAuth();

        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/profile');
        }

        $user = User::find($userId);
        $user->username   = trim($this->post('username', $user->username));
        $user->bio        = trim($this->post('bio', ''));
        $user->avatar_url = trim($this->post('avatar_url', ''));

        // Password change (optional)
        $newPass = $this->post('new_password', '');
        if (!empty($newPass)) {
            if (strlen($newPass) < 6) {
                Session::flash('error', 'Password must be at least 6 characters.');
                $this->redirect('/profile');
            }
            $user->password_hash = password_hash($newPass, PASSWORD_BCRYPT);
        }

        $user->save();
        Session::set('user_name', $user->username);
        Session::flash('success', 'Profile updated!');
        $this->redirect('/profile');
    }
}
