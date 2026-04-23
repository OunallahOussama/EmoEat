<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\GamificationService;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $this->view('auth.login', compact('error', 'success'));
    }

    public function doLogin(): void
    {
        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/login');
        }

        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in all fields.');
            $this->redirect('/login');
        }

        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        // Set session
        Session::set('user_id', (int)$user->id);
        Session::set('user_name', $user->username);
        Session::set('is_admin', (bool)$user->is_admin);

        // Update streak & gamification
        $user->updateStreak();
        $gamification = new GamificationService();
        $gamification->checkBadges((int)$user->id);

        $this->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $error = Session::getFlash('error');
        $this->view('auth.register', compact('error'));
    }

    public function doRegister(): void
    {
        if (!Session::verifyCsrf()) {
            Session::flash('error', 'Invalid form submission.');
            $this->redirect('/register');
        }

        $username = trim($this->post('username', ''));
        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');
        $confirm  = $this->post('password_confirm', '');

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            Session::flash('error', 'All fields are required.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email address.');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            $this->redirect('/register');
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            $this->redirect('/register');
        }

        if (User::findByEmail($email)) {
            Session::flash('error', 'Email already registered.');
            $this->redirect('/register');
        }

        if (User::findByUsername($username)) {
            Session::flash('error', 'Username already taken.');
            $this->redirect('/register');
        }

        $user = new User([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'xp'            => 0,
            'level'         => 1,
            'streak_days'   => 0,
        ]);
        $user->save();

        Session::flash('success', 'Account created! Please log in.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Session::destroy();
        session_start();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }
}
