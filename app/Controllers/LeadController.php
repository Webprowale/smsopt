<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LeadModel; 
use CodeIgniter\HTTP\ResponseInterface;

class LeadController extends BaseController
{

    public function store()
    {
        $rules = [
            'category_id' => 'required|integer',
            'csv_file'    => 'uploaded[csv_file]|mime_in[csv_file,text/csv,text/plain]|max_size[csv_file,2048]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $categoryId = $this->request->getPost('category_id'); 
        $file = $this->request->getFile('csv_file'); 
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $leadModel = new LeadModel();
            try {
                $csvFile = fopen($file->getTempName(), 'r');
                fgetcsv($csvFile);
                while (($row = fgetcsv($csvFile, 1000, ",")) !== false) {
                    $leadData = [
                        'name'        => filter_var($row[0], FILTER_SANITIZE_STRING),
                        'email'       => filter_var($row[1], FILTER_SANITIZE_EMAIL),   
                        'phone'       => filter_var($row[2], FILTER_SANITIZE_STRING),  
                        'category_id' => $categoryId,
                    ];
                    $leadModel->store($leadData);
                }

                fclose($csvFile);
                return redirect()->to('/user')->with('message', 'CSV uploaded and leads added successfully.');

            } catch (\Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'An error occurred while processing the CSV file.');
            }
        }
        return redirect()->back()->with('error', 'There was an issue with the file upload.');
    }
}
