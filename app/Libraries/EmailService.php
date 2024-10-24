<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

class EmailService
{
    protected $email;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        // Load the email service using static access (Suppressed warning)
        $this->email = \Config\Services::email();
    }

    /**
     * Sends an email using the provided parameters.
     *
     * @param string $to Recipient email address.
     * @param string $subject Subject of the email.
     * @param string $message The content of the email.
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public function sendEmail($to, $subject, $message)
    {
        // Set default email sender information
        $this->email->setFrom('seemlesspay1@gmail.com', 'SeemLess Pay');
        $this->email->setTo($to);
        $this->email->setSubject($subject);
        $this->email->setMessage($message);

        // Try sending the email and handle errors
        if ($this->email->send()) {
            return true; // Email sent successfully
        }

        // Log error if email failed to send
        log_message('error', $this->email->printDebugger(['headers']));
        return false; // Email failed to send
    }
}
