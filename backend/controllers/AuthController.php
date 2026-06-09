<?php
// ============================================================
//  DCRS — Auth Controller
//  File: backend/controllers/AuthController.php
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

class AuthController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // ── Handle Login ─────────────────────────────────────────
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/login.php');
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate
        if (empty($email) || empty($password)) {
            Session::flash('error', 'Email and password are required.');
            redirect(APP_URL . '/login.php');
        }

        if (!isValidEmail($email)) {
            Session::flash('error', 'Invalid email format.');
            redirect(APP_URL . '/login.php');
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            Session::flash('error', 'Invalid email or password.');
            redirect(APP_URL . '/login.php');
        }

        // Login
        Session::login($user);

        // Redirect based on role
        $this->redirectByRole($user['role']);
    }

    // ── Handle Register ──────────────────────────────────────
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/register.php');
        }

        $name       = trim($_POST['name']       ?? '');
        $email      = trim($_POST['email']      ?? '');
        $password   = trim($_POST['password']   ?? '');
        $confirm    = trim($_POST['confirm']    ?? '');
        $department = trim($_POST['department'] ?? '');
        $phone      = trim($_POST['phone']      ?? '');

        // Validations
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Name, email, and password are required.');
            redirect(APP_URL . '/register.php');
        }

        if (!isValidEmail($email)) {
            Session::flash('error', 'Invalid email format.');
            redirect(APP_URL . '/register.php');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            redirect(APP_URL . '/register.php');
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            redirect(APP_URL . '/register.php');
        }

        if ($this->userModel->emailExists($email)) {
            Session::flash('error', 'An account with this email already exists.');
            redirect(APP_URL . '/register.php');
        }

        // Create user (students only via self-registration)
        $userId = $this->userModel->create([
            'name'       => $name,
            'email'      => $email,
            'password'   => $password,
            'role'       => 'student',
            'department' => $department,
            'phone'      => $phone,
        ]);

        if ($userId) {
            Session::flash('success', 'Account created! Please login.');
            redirect(APP_URL . '/login.php');
        } else {
            Session::flash('error', 'Registration failed. Try again.');
            redirect(APP_URL . '/register.php');
        }
    }

    // ── Handle Logout ─────────────────────────────────────────
    public function logout(): void {
        Session::logout();
        redirect(APP_URL . '/login.php');
    }

    // ── Role-based redirect ───────────────────────────────────
    private function redirectByRole(string $role): void {
        $map = [
            'admin'    => APP_URL . '/pages/admin/dashboard.php',
            'resolver' => APP_URL . '/pages/resolver/dashboard.php',
            'student'  => APP_URL . '/pages/student/dashboard.php',
        ];
        redirect($map[$role] ?? APP_URL . '/login.php');
    }
}

// ── Route ─────────────────────────────────────────────────────
$ctrl   = new AuthController();
$action = $_GET['action'] ?? 'login';

match ($action) {
    'login'    => $ctrl->login(),
    'register' => $ctrl->register(),
    'logout'   => $ctrl->logout(),
    default    => redirect(APP_URL . '/login.php'),
};
