<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class UserModel extends Model
{
    protected $table            = 'user';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'username',
        'email',
        'token',
        'password',
        'created_at',
        'updated_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true; 
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function hashPassword(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $data; 
    }

    public function checkUser($name, $email)
    {
        return $this->where('username', $name) 
                    ->orWhere('email', $email)
                    ->first();
    }

    public function reg(array $data)
    {
        if ($this->checkUser($data['username'], $data['email'])) { 
            return false;
        }
        return $this->insert($data) ? true : false;
    }

    public function login(array $data)
    {
        $user = $this->where('email', $data['email'])->first();

        if ($user && password_verify($data['password'], $user['password'])) {
            return $user;
        } 
        return false;
    }

    public function insertToken($email, $token)
    {
        return $this->where('email', $email)
            ->set('token', $token)
            ->update();
    }
 /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function resetPassword($data)
    {
        $user = $this->where('email', $data['email'])->first();
        if (!$user) {
            return false;
        }
        
        $updatetime = Time::parse($user['updated_at']);
        $currenttime = Time::now();
        $diff = $currenttime->difference($updatetime)->getSeconds();
        if ($diff <= 300) {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT); 
            return $this->where('email', $data['email'])
                        ->set('password', $hashedPassword) 
                        ->update();
        }
        return false;
    }
}
