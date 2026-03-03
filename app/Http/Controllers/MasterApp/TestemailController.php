<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Core\Roles\Services\RolesService;
use App\Http\Requests\MasterApp\Roles\RolesStoreRequest;
use App\Http\Requests\MasterApp\Roles\RolesUpdateRequest;
use App\Core\Email\Services\EmailService;

class TestemailController extends Controller
{
    private $EmailService;

    public function __construct(EmailService $emailServices){
        $this->EmailService = $emailServices;
    }
    

    public function index()
    {
        
 


    // generic email
    // $useremail = "subedary@futurismtechnologies.com";
    //  $subject = "Welcome to " . config('app.name') . "!";
    //     $view = 'masterapp.emails.generic'; // A specific welcome email template

    //     $data = [
    //         'user' => "Subedar Y",
    //         'appName' => config('app.name'),
    //     ];

    //    $options = array("send_smtp_id"=>3);
    
    $useremail = "subedary@futurismtechnologies.com";
     $subject = "Welcome to " . config('app.name') . "!";
        $view = 'masterapp.emails.welcome'; // A specific welcome email template

        $data = [
            'userName' => "Subedar Y",
            'appName' => config('app.name'),
        ];

       $options = [];

        // $options = [
        //     'cc' => 'admin@example.com',
        //     'attachments' => [
        //         storage_path('app/public/welcome-guide.pdf'),
        //     ],
        // ];

        // $success = $this->EmailService->send($useremail, $subject, $view, $data, $options);

        // if ($success) {
        //    // return back()->with('success', 'Welcome email sent!');
        //    echo "Welcome email sent!";
        // }
        // else{
        //     echo "fail";
        // }

        // $success = $this->EmailService->send($useremail, $subject, $view, $data, $options);



        //// welcome mail

     $to = "subedary@futurismtechnologies.com";
            $subject = "Welcome to " . config('app.name') . "!";
            $view = 'masterapp.emails.generic'; 
            $data = [
                'userName' => "Subedar",
                'appName' => config('app.name'),
                'headerTitle' => 'Test Email',
                'headerSubtitle' => 'Template preview',
                'footerText' => 'Thanks for checking our email layout.'
            ];
            $options = [];

        

             $success = $this->EmailService->send($to, $subject, $view, $data, $options);

    //  $to = "subedary@futurismtechnologies.com";
    //         $subject = "Welcome to " . config('app.name') . "!";
    //         $view = 'masterapp.emails.generic'; 
    //         $data = [
    //             'userName' => "Subedar",
    //             'appName' => config('app.name'),
    //             'headerTitle' => 'Test Email',
    //             'headerSubtitle' => 'Template preview',
    //             'footerText' => 'Thanks for checking our email layout.'
    //         ];
    //         $options = array("send_smtp_id"=>3);

        

    //          $success = $this->EmailService->send($to, $subject, $view, $data, $options);



                if ($success) {
                    
                   echo "success";

                }
                else{
                      echo "fail";
                }
        die;
    }

}
