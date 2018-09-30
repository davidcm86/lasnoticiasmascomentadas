 <?php
 return [
        'Users.SimpleRbac.permissions' => [
            [
                'role' => 'admin',
                'controller' => 'Periodicos',
                'action' => ['index','add','edit','delete']
            ]
        ],
    ];