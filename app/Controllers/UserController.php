<?php

namespace App\Controllers;
use App\Libraries\EmailService;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class UserController extends BaseController
{
    
    public function __construct()
    {
        helper(['form', 'url']);
        $this->dbUser = new UserModel(); 
    }
    public function login()
    {
        $rule =[
            'email' => 'required|valid_email',
            'password' => 'required'
        ];
        if(!$this->validate($rule)){
            return view('auth/login',['error', $this->validator->getErrors()]);
        }
        $data =[
            'email' => $this->request->getPost('email'),
           'password' =>  $this->request->getPost('password'),
         ];
         $isLogin = $this->dbUser()->login($data);

        if(!$isLogin){
            return view('auth/login',['error', 'Login failed. Invalid email or Password.']);
        }
        $session = session();
        $session->set('auth', $isLogin);
        return redirect()->to('/user')->with('success', 'Login successful.');

    }
    public function reg()
    {
        $rule =[
            'username'=> 'required|min_length[8]',
            'email' => 'required|valid_email|is_unique[user.email]',
            'password' => 'required|min_length[6]',
        ];
        if(!$this->validate($rule)){
            return view('auth/register',['error', $this->validator->getErrors()]);
        }
        $data =[
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' =>  $this->request->getPost('password'),
         ];
         $this->dbUser()->reg($data);
         return redirect()->to('/login')->with('success', 'Registration successful. Please login.');
    }
    public function forgottenPas()
    {
        $rule =[
            'email' => 'required|valid_email'
        ];
        if(!$this->validate($rule)){
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }
        $data =[
            'email' => $this->request->getPost('email'),
         ];
         $isExist = $this->dbUser()->where('email', $data['email']);

        if(!$isExist){
         return redirect()->back()->withInput()->with('error', 'email not registered');
        }
        $token = md5(time().rand(1000,9999));
        $this->dbUser()->insertToken($isExist['email'], $token);
       $sendMail = new EmailService();
       $sub = 'Password reset token';
       $msg = 'Your password reset token is: '.$token;
       if($sendMail($isExist['email'], $sub, $msg)){
        return redirect()->back()->with('success', 'Password reset token has been sent to your email.');
       }
       return redirect()->back()->with('error', 'Failed to send password reset token.');
    }
    public function veriyT(){
       $token = $this->request->getGet('token');
       $email = $this->request->getGet('email');
       $isExist = $this->dbUser()->where(['email' => $email, 'token' => $token]);
       if(!$isExist){
        return  redirect()->back()->with('error', 'Invalid token.');
       }
       return  redirect()->back()->with('success', 'Enter New Password Below');
    }
    public function resetPas(){
     $rule =[
         'password' => 'required|min_length[6]',
         'confirm_password' => 'required|matches[password]'
     ];
     if(!$this->validate($rule)){
         return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
     }
     $data =[
         'password' => $this->request->getPost('password'),
         'email' => $this->request->getGet('email')
     ];
     if($this->dbUser()->resetPassword($data)){
     return redirect()->to('/login')->with('success', 'Password has been reset successfully.');
     }
     return redirect()->back()->with('error', 'Failed to reset password.');

    }
}
