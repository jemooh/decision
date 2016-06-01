<?php
$resources[] = array(
    
    (object)array(
        "url" => "/admin/experiments",
        "type" => MULTI,
        "title" => 'Experiments',
        "sql" => SQL_DEFAULT,
        "table" => "experiments",
        "methods" => array(
            "GET",
            "POST",
            "DELETE",
            "PUT"
        ) ,
        "admin" => true,
        "columns" => array(
            array(
                "column" => "title",
                "type" => "text",
                "list" => true,
                "title" => 'Title'
            ) ,
            array(
                "column" => "starting_time",
                "type" => "datetime",
                "list" => true,
                "title" => 'Start time'
            ) ,
            array(
                "column" => "ending_time",
                "type" => "datetime",
                "list" => true,
                "title" => 'End time'
            ) ,
            array(
                "column" => "template_negotiations",
                "type" => "select",
                "sql" => "SELECT id, title from `template_negotiations`",
                "list" => true,
                "title" => 'Template negotiations'
            ) ,
            array(
                "column" => "number_of_instances",
                "type" => "text",
                "list" => true,
                "title" => 'Number of instances'
            ) ,
        ) ,
        "key" => "id",
        "POST" => function ($arrgs, $db, $post) {

            
            for ($i = 0; $i < $post['number_of_instances']; $i++) {

                
                $query_negotiations = "INSERT INTO `negotiations` (`title`, `active`, `starting_time`, `ending_time`) VALUES (CONCAT('{$post['title']}',' (',$i,')'), 1, '{$post['starting_time']}', '{$post['ending_time']}');";

                $db->query($query_negotiations);
                $negotiation_id = $db->insert_id;
                $query_options = "INSERT INTO `options` (`title`, `negotiation_id`, `agreement_action`) SELECT `title`, $negotiation_id, `agreement_action` from `template_options` WHERE `negotiation_id`={$post['template_negotiations']};";

                $db->query($query_options);

            }
            return array();

        }
    ) ,
    
    (object)array(
        "url" => "/admin/negotiations",
        "type" => MULTI,
        "title" => 'Negotiations',
        "sql" => SQL_DEFAULT,
        "table" => "negotiations",
        "methods" => array(
            "GET",
            "POST",
            "DELETE",
            "PUT"
        ) ,
        "admin" => true,
        "columns" => array(
            array(
                "column" => "title",
                "type" => "text",
                "list" => true,
                "title" => 'Title'
            ) ,
            array(
                "column" => "active",
                "type" => "bool",
                "list" => true,
                "title" => 'Active'
            ) ,
            array(
                "column" => "starting_time",
                "type" => "datetime",
                "list" => true,
                "title" => 'Start time'
            ) ,
            array(
                "column" => "ending_time",
                "type" => "datetime",
                "list" => true,
                "title" => 'End time'
            ) ,
        ) ,
        "key" => "id",
        
        'dependencies' => array(
            
            (object)array(
                "url" => "/admin/negotiations/:parent/options",
                "type" => MULTI,
                "title" => 'Template options',
                "sql" => SQL_DEFAULT,
                "parent_table" => "negotiations",
                "parent_key" => "negotiation_id",
                "key" => "id",
                "table" => "options",
                "methods" => array(
                    "GET",
                    "POST",
                    "DELETE",
                    "PUT"
                ) ,
                "admin" => true,
                "columns" => array(
                    array(
                        "column" => "title",
                        "list" => true,
                        "type" => "text",
                        "title" => 'Title'
                    ) ,
                    array(
                        "column" => "agreement_action",
                        "list" => true,
                        "type" => "text",
                        "title" => 'Action'
                    )
                ),
               

            ) ,
            
            (object)array(
                "url" => "/admin/template_negotiations/:parent/offers",
                "type" => MULTI,
                "title" => 'Offers',
                "sql" => SQL_DEFAULT,
                "parent_table" => "negotiations",
                "parent_key" => "negotiation_id",
                "key" => "id",
                "table" => "offers",
                "methods" => array(
                    "GET",
                    "POST",
                    "DELETE",
                    "PUT"
                ) ,
                "admin" => true,
                "columns" => array(
                    array(
                        "column" => "id",
                        "list" => true,
                        "title" => 'id'
                    ) ,
                    array(
                        "column" => "option_id",
                        "list" => true,
                        "title" => 'Option'
                    ) ,
                    
                    array(
                        "column" => "time",
                        "list" => true,
                        "title" => 'Time'
                    )
                )
            )
        )
    ) ,
    
    (object)array(
        "url" => "/admin/users",
        "type" => MULTI,
        "title" => "Users",
        "sql" => SQL_DEFAULT,
        "table" => "users",
        "methods" => array(
            "GET",
            "POST",
            "DELETE",
            "PUT"
        ) ,

        "admin" => true,
        "columns" => array(
            array(
                "column" => "first_name",
                "type" => "text",
                "list" => true,
                "title" => 'First name'
            ) ,
            array(
                "column" => "last_name",
                "type" => "text",
                "list" => true,
                "title" => 'Last name'
            ),
            array(
                "column" => "login",
                "type" => "text",
                "list" => true,
                "title" => 'Username'
            ),
            array(
                "column" => "password",
                "type" => "password",
                "list" => true,
                "title" => 'Password'
            )
        ) ,
        "key" => "id"
    ) ,
    
    (object)array(
        "url" => "/admin/template_negotiations",
        "type" => MULTI,
        "title" => 'Template negotiations',
        "sql" => SQL_DEFAULT,
        "table" => "template_negotiations",
        "methods" => array(
            "GET",
            "POST",
            "DELETE",
            "PUT"
        ) ,
        
        /*"row_actions"=>array(
        
        array('title'=> 'Deploy',
        
        	'onClick' => <<<EOB
        
        		var options = \$.extend({
        			height : "250",
        			width : "500",
        			title:"JQuery Modal Box Demo",
        			description: "Example of how to create a modal box.",
        			top: "20%",
        			left: "30%",
        		},prop);
        EOB
        		,
        	"processor" =>""
        
        	),																								
        
        ),*/
        "admin" => true,
        "columns" => array(
            array(
                "column" => "title",
                "type" => "text",
                "list" => true,
                "title" => 'Title'
            ) ,
            
            /*array("column"=>"starting_time",
            "type" => "datetime",
            "list" => true,
            "title" => 'Start time'
            ),
            array("column"=>"ending_time",
            "type" => "datetime",
            "list" => true,
            "title" => 'End time'
            )*/
            
            /*array("column"=>"active",
            "type" => "bool",
            "list" => true,
            "title" => 'Active'
            ),
            array("column"=>"starting_time",
            "type" => "datetime",
            "list" => true,
            "title" => 'Start time'
            ),
            array("column"=>"ending_time",
            "type" => "datetime",
            "list" => true,
            "title" => 'End time'
            )*/
        ) ,
        "key" => "id",
        'dependencies' => array(
            
            (object)array(
                "url" => "/admin/template_negotiations/:parent/template_options",
                "type" => MULTI,
                "title" => 'Template options II',
                "sql" => SQL_DEFAULT,
                "parent_table" => "template_negotiations",
                "parent_key" => "negotiation_id",
                "key" => "id",
                "table" => "template_options",
                "methods" => array(
                    "GET",
                    "POST",
                    "DELETE",
                    "PUT"
                ) ,
                "admin" => true,
                "columns" => array(
                    array(
                        "column" => "title",
                        "list" => true,
                        "type" => "text",
                        "title" => 'Title'
                    ) ,
                    array(
                        "column" => "agreement_action",
                        "list" => true,
                        "type" => "text",
                        "title" => 'Action'
                    )
                )
            )
        )
    ) ,
    
    (object)array(
        "url" => "/admin/negotiations_users",
        "type" => MANY_TO_MANY,
        "title" => 'Matching negotiations and users',
        "sql" => SQL_DEFAULT,
        "table" => "negotiations_users",
        "methods" => array(
            "GET",
            "POST",
            "DELETE",
            "PUT"
        ) ,
        "admin" => true,
        "columns" => array(
            array(
                "column" => "negotiation_id",
                "type" => "select",
                "table" => "negotiations",
                "list" => true,
                "title" => 'Negotiations'
            ) ,
            array(
                "column" => "user_id",
                "type" => "select",
                "sql" => "SELECT id, CONCAT(first_name, \" \",last_name) as title from `users`",
                "list" => true,
                "title" => 'Users'
            )
        ) ,
        "key" => "id"
    ) ,
);

