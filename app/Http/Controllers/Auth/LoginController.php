<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class LoginController extends Controller {
    public function showLoginForm() { return view('auth.login'); }
    public function login(Request $req, ActivityLogger $logger) {
        $credentials = $req->validate(['email' => 'required|email', 'password' => 'required']);
        if (auth()->attempt($credentials, $req->boolean('remember'))) {
            $req->session()->regenerate();
            $logger->login();
            return redirect()->intended(route('dashboard'));
        }
        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }
    public function logout(Request $req, ActivityLogger $logger) {
        $logger->logout();
        auth()->logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        return redirect()->route('login');
    }
}
