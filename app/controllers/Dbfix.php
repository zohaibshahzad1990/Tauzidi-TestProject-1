<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dbfix extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dbfix_m');
    }
    

    public function index()
    {
        $this->dbfix_m->add_column('users', [
            'otp_code' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'otp_expiry_time' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'refferal_code' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'is_active' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'access_token' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'email_verification_code' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'email_verification_expire_time' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('groups', [
            'slug' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'is_validated' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'is_complete_setup' => [
                'type' => 'int',
            ],
        ]);
        
        $this->dbfix_m->add_column('groups', [
            'is_core_role' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('groups', [
            'permissions' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('groups', [
            'active' => [
                'type' => 'int',
            ],
        ]);

        

        $this->dbfix_m->add_column('notifications', [
            'resource_id' => [
                'type' => 'int',
            ],
        ]);

        
        $this->dbfix_m->modify_column('emails_queue',array(
            'message'=>array(
                'name'=>'message',
                'type'=>'text',
            ))
        );

        $this->dbfix_m->add_column('countries', [
            'currency' => [
                'type' => 'varchar(200)',
            ],
        ]);
        
        $this->dbfix_m->add_column('countries', [
            'currency_code' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'account_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'description' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'statement_type' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'old_statement_id' => [
                'type' => 'int',
            ],
        ]);
        $this->dbfix_m->add_column('statements', [
            'deposit_id' => [
                'type' => 'int',
            ],
        ]);
        $this->dbfix_m->add_column('statements', [
            'subscription_balance' => [
                'type' => 'varchar(30)',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'subscription_paid' => [
                'type' => 'varchar(30)',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'cumulative_paid' => [
                'type' => 'varchar(30)',
            ],
        ]);

        $this->dbfix_m->add_column('online_payment_requests', [
            'invoice_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'reference_number' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'merchant_request_id' => [
                'type' => 'varchar(200)',
            ],
        ]);
        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'transaction_id' => [
                'type' => 'varchar(200)',
            ],
        ]);
        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'organization_balance' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'transaction_date' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'invoice_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('deposits', [
            'invoice_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('deposits', [
            'user_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('deposits', [
            'phone' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('deposits', [
            'stk_payment_id' => [
                'type' => 'int
                ',
            ],
        ]);

        $this->dbfix_m->modify_column('invoices',array(
            'invoice_number'=>array(
                'name'=>'invoice_number',
                'type'=>'varchar(200)',
           ))
        );

        $this->dbfix_m->add_column('safaricomstkpushrequests', [
            'request_reconcilled' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('invoices', [
            'status' => [
                'type' => 'int',
            ],
        ]);

        
        $this->dbfix_m->add_column('users', [
            'arrears' => [
                'type' => 'varchar(200)',
            ],
        ]);

        

        $this->dbfix_m->add_column('users', [
            'billing_date' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'lock_access' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'billing_package_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('invoices', [
            'tax' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('billing_payments', [
            'billing_receipt_number' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('billing_payments', [
            'billing_package_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('statements', [
            'bill_payment_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('deposits', [
            'bill_payment_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('invoices', [
            'is_auto_pay' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('invoices', [
            'is_paid' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('users', [
            'is_dismiss_dialogue' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('education_level_settings', [
            'parent_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('users', [
            'random' => [
                'type' => 'varchar(200)',
            ],
        ]); 

        $this->dbfix_m->add_column('users', [
            'is_onboarded' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('users', [
            'parent_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('education_level_settings', [
            'syllabus_level_id' => [
                'type' => 'int',
            ],
        ]);
        

        $this->dbfix_m->add_column('users', [
            'curriculum_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'level_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'class_id' => [
                'type' => 'int',
            ],
        ]);
        

        $this->dbfix_m->add_column('users', [
            'enable_sms' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'enable_stk_push' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->modify_column('users',array(
            'subscription_status'=>array(
                'name'=>'social_type',
                'type'=>'varchar(255)',
            ))
        ); 

        $this->dbfix_m->modify_column('users',array(
            'subscription_end_date'=>array(
                'name'=>'social_id',
                'type'=>'varchar(255)',
            ))
        ); 

        

        $this->dbfix_m->add_column('vehicles', [
            'school_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('user_school_driver_pairings', [
            'vehicle_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('route_points', [
            'slug' => [
                'type' => 'varchar(255)',
            ],
        ]); 

        $this->dbfix_m->add_column('route_points', [
            'distance' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('route_points', [
            'duration' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('trips', [
            'vehicle_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('trips', [
            'trip_time' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('trips', [
            'trip_type_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('trips', [
            'parent_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('vehicle_trips', [
            'route_id' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('vehicle_trips', [
            'parent_trip_id' => [
                'type' => 'int',
            ],
        ]); 
        $this->dbfix_m->add_column('vehicle_trips', [
            'is_return_trip' => [
                'type' => 'int',
            ],
        ]); 

        $this->dbfix_m->add_column('trips', [
            'is_reverse' => [
                'type' => 'int',
            ],
        ]);
        
        $this->dbfix_m->add_column('journey_cordinates', [
            'distance' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'distance_value' => [
                'type' => 'varchar(255)',
            ],
        ]);
        $this->dbfix_m->add_column('journey_cordinates', [
            'duration' => [
                'type' => 'varchar(255)',
            ],
        ]);
        $this->dbfix_m->add_column('journey_cordinates', [
            'duration_value' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('journeys', [
            'on_start_longitude' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('journeys', [
            'on_start_latitude' => [
                'type' => 'varchar(255)',
            ],
        ]);
         

        $this->dbfix_m->add_column('journeys', [
            'on_end_longitude' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('journeys', [
            'on_end_latitude' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('journeys', [
            'is_archived' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('user_student_pairings', [
            'parent_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'accuracy' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'speed' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'speed_accuracy' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'journey_time' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'vertical_accuracy' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('sms_queue', [
            'is_push' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('students_active_journey', [
            'on_end_longitude' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('students_active_journey', [
            'on_end_latitude' => [
                'type' => 'varchar(255)',
            ],
        ]);

        $this->dbfix_m->add_column('users', [
            'fcm_token' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('sms_queue', [
            'fcm_token' => [
                'type' => 'text',
            ],
        ]);
        
        $this->dbfix_m->add_column('sms', [
            'fcm_token' => [
                'type' => 'text',
            ],
        ]);

        $this->dbfix_m->add_column('sms', [
            'is_push' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('user_student_pairings', [
            'user_parent_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('user_student_pairings', [
            'point_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('user_student_trips', [
            'point_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('journey_cordinates', [
            'heading' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('user_student_pairings', [
            'registration_no' => [
                'type' => 'varchar(200)',
            ],
        ]);

        $this->dbfix_m->add_column('vehicles', [
            'type_id' => [
                'type' => 'int',
            ],
        ]);


        $this->dbfix_m->add_column('guardians', [
            'user_parent_id' => [
                'type' => 'int',
            ],
        ]);

        $this->dbfix_m->add_column('activity_log', [
            'execution_time' => [
                'type' => 'varchar(255)',
            ],
        ]);

        

        

        
        echo "DB Fixed\n<br/>";
    }
}
