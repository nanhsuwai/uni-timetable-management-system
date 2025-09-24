<?php


return [
    'pagination' => 10,
    'granted_systems' => [
        [
            'name' => 'POS',
            'code' => 'pos',
            'permissions' => [
                
                [
                    'module_name' => 'Manage Dashboard',
                    'name' => 'manage dashboard',
                    'code' => 'manage_dashboard'
                ],
            

                [
                    'module_name' => 'Users Permission',
                    'name' => 'manage users permission',
                    'code' => 'manage_users_permission'
                ],
                [
                    'module_name' => 'Users Management',
                    'name' => 'manage users management',
                    'code' => 'manage_users_management'
                ],
                
               
              
            ]
        ],
        
        
    ],
    
];
