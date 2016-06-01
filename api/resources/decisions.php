<?php

const NUSERS = 2;

const SHOW_CONCESSIONS = 2;

$nego_user_auth_sql = "SELECT COUNT(*)>0 as valid FROM `negotiations_users`, `users` WHERE login = \"{\$login}\" and `password` = \"{\$password}\" and negotiations_users.user_id = `users`.id and `negotiations_users`.`negotiation_id`={\$arrgs[0]} and negotiations_users.id={\$arrgs[1]}";
$login_object = new stdClass();

$login_object->url = "/login/";

$login_object->table = "users";

$login_object->methods = array(

    "POST",

);

$login_object->validator = function ($arrgs, $db, $post) use ($login_object) {

    $query = "SELECT count(*) as count, id FROM `users` WHERE login = \"{$post['login']}\" and `password` = \"" . ($post['password']) . "\"";

    $result = $db->query($query);

    $row = $result->fetch_array(MYSQLI_ASSOC);

    $login_object->id = $row['id'];

    $login_object->password = $post['password'];

    return $row['count'] == 1;

};

$login_object->POST = function ($arrgs, $db, $post) use ($login_object) {

    $negotiations = array();

    $query = "SELECT `negotiation_id`, n.`active`, n.`title`, u.`id` as `negotiations_users_id`, (SELECT `id` FROM `negotiations_users` where user_id!=u.user_id and negotiation_id=n.id) as opponent_id FROM `negotiations_users` as u, `negotiations` as n WHERE u.`negotiation_id` = n.`id` and u.`user_id`= {$login_object->id}";

    if ($result = $db->query($query)) {

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $negotiations[] = array(

                'id'                    => 0 + $row['negotiation_id'],

                'title'                 => $row['title'],

                'opponent'              => 0 + $row['opponent_id'],

                'negotiations_users_id' => $row['negotiations_users_id'],

                'active'                => $row['active'],

            );

        }

    }

    return array(

        "id"           => $login_object->id,

        'negotiations' => $negotiations

        , 'password' => md5($login_object->password),

    );

};

$resources[] = array(

    //array("negotiation", MULTI),\

    $login_object,

    (object) array(

        "authenticate" => "SELECT COUNT(*)>0 as valid FROM `negotiations_users`, `users` WHERE login = \"{\$login}\" and `password` = \"{\$password}\" and negotiations_users.user_id = `users`.id and `negotiations_users`.`negotiation_id`={\$arrgs[0]}",

        "url"          => "/negotiations/:parent_id/options/",

        "type"         => MULTI,

        "sql"          => SQL_DEFAULT,

        "table"        => "options",

        "parent_table" => "negotiation",

        "methods"      => array(

            "GET",

        ),

    ),

    (object) array(

        "url"          => "/negotiations/:parent_id/users/:user_id/settings/",

        "authenticate" => $nego_user_auth_sql,

        "type"         => MULTI,

        "sql"          => "SELECT

                    (SELECT

                            SUM(POW(- 1, (nu.user_id <> this_nu.user_id)) * IF(a.points>0,a.points,0)) AS balance

                        FROM

                            agreements AS a,

                            negotiations_users AS nu,
                            (select user_id from negotiations_users WHERE id = {\$arrgs[1]}) as this_nu


                        WHERE

                            nu.id = a.negotiations_users_id

                                AND a.negotiation_id IN (SELECT

                                    negotiation_id

                                FROM

                                    (SELECT

                                        u.negotiation_id,

                                            SUM(IF(u.user_id = ul.user_id, 1, 0)) AS score,

                                            COUNT(DISTINCT ul.user_id) AS n_users

                                    FROM

                                        negotiations_users AS u, (SELECT

                                        user_id

                                    FROM

                                        negotiations_users

                                    WHERE

                                        negotiation_id = {\$arrgs[0]}) AS ul

                                    GROUP BY u.negotiation_id) AS ns

                                WHERE

                                    score = n_users)) AS balance,

                    (SELECT

                            CONCAT(first_name, \" \", last_name) AS name

                        FROM

                            negotiations_users AS nu,

                            users AS u

                        WHERE

                            nu.user_id = u.id AND nu.id <> {\$arrgs[1]} and nu.negotiation_id = {\$arrgs[0]}) AS name;

                    ",

        "methods"      => array(

            "GET",

        ),

    ),

    (object) array(

        "url"          => "/negotiations/:parent_id/users/:id/preferences/",

        "authenticate" => $nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT `preferences`.* FROM `preferences`, `options` WHERE  options.id = preferences.option_id and options.negotiation_id = {\$arrgs[0]} and negotiations_users_id = {\$arrgs[1]};",

        "methods"      => array(

            "GET",

            "POST",

        ),

        "POST"         => function ($arrgs, $db, $post) {

            foreach ($post['preferences'] as $key => $value) {

                $query = "DELETE FROM `preferences` WHERE `option_id` = $key AND `negotiations_users_id` = {$arrgs[1]};";

                $db->query($query);

                $query = "INSERT INTO `preferences` (`option_id`, `value`, `negotiations_users_id`) VALUES ($key, $value, {$arrgs[1]});";

                $db->query($query);

            }

            return array();

        },

    ),

    (object) array(

        "url"          => "/negotiations/:negotiation_id/users/:user_id/opponents/:opponent_id/points/",

        "authenticate" =>$nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT `preferences`.* FROM `preferences`, `options` WHERE  options.id = preferences.option_id and options.negotiation_id = {\$arrgs[0]} and (negotiations_users_id = {\$arrgs[1]} OR negotiations_users_id = {\$arrgs[2]});",

        "methods"      => array(

            "GET",

        ),

        "GET"          => function ($data, $arrgs, $db) {

            return calculatePoints($data, $arrgs);

        },

    ),

    (object) array(

        "url"          => "/negotiations/:negotiation_id/users/:user_id/offers/",

        "authenticate" => $nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT * FROM `offers` WHERE  negotiation_id = {\$arrgs[0]} and negotiations_users_id = {\$arrgs[1]};",

        "methods"      => array(

            "GET",

            "POST",

        ),

        "validator"    => function ($arrgs, $db, $post) use ($login_object) {

            $query = "SELECT active FROM `negotiations` WHERE id = \"{$arrgs[0]}\";";

            $result = $db->query($query);

            $row = $result->fetch_array(MYSQLI_ASSOC);

            return $row['agree'] == 0;

        },

        "post_sql"     => "INSERT INTO `offers` (`negotiation_id`, `negotiations_users_id`, `option_id`) VALUES ({\$arrgs[0]}, {\$arrgs[1]}, {\$post['option_id']});",

        "POST"         => function ($arrgs, $db, $post) {

            $agreement = 0;

            $results = array();

            $users = array();

            if ($result = $db->query("select o.* from offers o inner join (SELECT MAX(id) as id FROM offers where negotiation_id = {$arrgs[0]}  GROUP BY negotiations_users_id) m on m.id = o.id")) {

                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                    $option_ids[] = $row["option_id"];

                    $users[] = $row["negotiations_users_id"];

                }

                if (sizeof($option_ids) == NUSERS) {

                    $matching = 1;

                    for ($i = 0; $i < sizeof($option_ids); $i++) {

                        if ($post['option_id'] != $option_ids[$i]) {

                            $matching = 0;

                        }

                    }

                    $agreement = $matching;

                } else {

                    $agreement = 0;

                }

            } else {

                echo $db->error;

                $agreement = 0;

            }

            if ($agreement) {

                if ($result = $db->query("SELECT `preferences`.* FROM `preferences`, `options` WHERE  options.id = preferences.option_id and options.negotiation_id = {$arrgs[0]} ")) {

                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                        $data[] = $row;

                    }

                    $points[$users[1]] = calculatePoints($data, array(

                        $arrgs[0],

                        $users[1],

                        $users[0],

                    ));

                    $points[$users[0]] = calculatePoints($data, array(

                        $arrgs[0],

                        $users[0],

                        $users[1],

                    ));

                    foreach ($points as $user => $upoints) {

                        $findPoints = function ($array, $option_id) {

                            foreach ($array as $a) {

                                if ($a["option_id"] == $option_id) {

                                    return $a["points"];

                                }

                            };

                        };

                        $npoint = $findPoints($upoints, $post["option_id"]);

                        echo $points_sql = "INSERT INTO `agreements` (`negotiation_id`, `negotiations_users_id`, `option_id`,`points`) VALUES ({$arrgs[0]}, {$user}, {$post["option_id"]},{$npoint});";

                        if (!$db->query($points_sql)) {

                            $results["error"][] = $db->error;

                        }

                    }

                    if (!$db->query("UPDATE `negotiations` SET `active`=0 where `id`={$arrgs[0]};")) {

                        $result["error"][] = $db->error;

                    }

                }

            }

            return $results;

        }

    ),

    (object) array(

        "url"          => "/negotiations/:negotiation_id/users/:user_id/offers/last",

        "authenticate" => $nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT * FROM `offers` WHERE  negotiation_id = {\$arrgs[0]} and negotiations_users_id = {\$arrgs[1]} ORDER BY `id` DESC LIMIT 1;",

        "methods"      => array(

            "GET",

            "POST",

        ),

        "post_sql"     => "INSERT INTO `offers` (`negotiation_id`, `negotiations_users_id`, `option_id`) VALUES ({\$arrgs[0]}, {\$arrgs[1]}, {\$post['option_id']});",

    ),

    (object) array(

        "url"          => "/negotiations/:negotiation_id/users/:user_id/offers/incoming/",

        "authenticate" => $nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT * FROM `offers` WHERE  negotiation_id = {\$arrgs[0]} and negotiations_users_id != {\$arrgs[1]};",

        "methods"      => array(

            "GET",

        ),

    ),

    (object) array(

        "url"          => "/negotiations/:negotiation_id/users/:user_id/offers/incoming/since/:offer_id/last",

        "authenticate" => $nego_user_auth_sql ,

        "type"         => MULTI,

        "sql"          => "SELECT * FROM `offers` WHERE  negotiation_id = {\$arrgs[0]} and negotiations_users_id != {\$arrgs[1]} and id > {\$arrgs[2]} ORDER BY `id` DESC LIMIT 1;",

        "methods"      => array(

            "GET",

        ),

        "GET"          => function ($data, $arrgs, $db) {

            //replace with agreements tab;e

            if ($result = $db->query("select o.* from offers o inner join (SELECT MAX(id) as id FROM offers where negotiation_id = {$arrgs[0]} GROUP BY negotiations_users_id) m on m.id = o.id")) {

                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                    $option_ids[] = $row["option_id"];

                }

                if (sizeof($option_ids) == NUSERS) {

                    $option_id = $option_ids[0];

                    for ($i = 1; $i < sizeof($option_ids); $i++) {

                        if ($option_id != $option_ids[$i]) {

                            return $data;

                        }

                    }

                    $data[0]["agreement"] = "true";

                    $data[0]["agreement_action"] = getAgreementAction($db, $option_id);

                }

            }

            return $data;

        }

    ),

    /*(object) array(    "url" => "/negotiations/:negotiation_id/users/:user_id/preferences/",

"type" => MULTI,

"sql" => "SELECT `preferences`.option_id,`preferences`.value   FROM `preferences`, `options` WHERE  options.id = preferences.option_id and options.negotiation_id = {\$arrgs[0]} and usnegotiations_users_id = {\$arrgs[1]};",

"methods" => array("GET")

),

 */

);

function getAgreementAction($db, $option_id)
{

    $result = $db->query("select agreement_action from options where id = {$option_id};");

    $result = $result->fetch_array(MYSQLI_ASSOC);

    return $result['agreement_action'];

}

function calculatePoints($data, $arrgs)
{

    $result = array();

    $preferences = array();

    if (sizeof($data) > 0) {

        foreach ($data as $preference) {

            $preferences[$preference['negotiations_users_id']][$preference['option_id']] = $preference['value'];

        }

        if (is_array($preferences[$arrgs[1]])) {

            $my_max_option_id = array_keys($preferences[$arrgs[1]], max($preferences[$arrgs[1]]))[0];

            $his_rating_my_max = isset($preferences[$arrgs[2]][$my_max_option_id]) ? $preferences[$arrgs[2]][$my_max_option_id] : null;

            foreach ($preferences[$arrgs[1]] as $option_id => $preference) {


                $result[] = array(

                    "option_id" => $option_id,

                    "myrating"  => $preferences[$arrgs[1]][$option_id],

                    "points"    => $his_rating_my_max === null ? null : ($preferences[$arrgs[2]][$option_id] - $his_rating_my_max > 0 ? $preferences[$arrgs[2]][$option_id] - $his_rating_my_max : 0),

                );

            }

        }

    }

    return $result;

}
