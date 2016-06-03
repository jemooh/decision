<?php

date_default_timezone_set('UTC');

ini_set("error_reporting", "E_ERRORS");

require '../Slim/Slim.php';

//require '../Slim/Middleware.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
//$app->add(new \Slim\Middleware\HttpBasicAuth());

const MULTI = 1;

const SINGLE = 0;

const SQL_DEFAULT = 1;

const VALIDATOR = 2;

include "resources/decisions.php";

include "resources/admin.php";

//select o.* from offers o inner join (SELECT id, MAX(time) FROM offers GROUP BY user_id) m on m.id = o.id

header("Content-Type: application/json");

$db = connect_db();

function getSqlData($db, $get_sql, $resource)
{

    if ($result = $db->query($get_sql)) {

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            $data[] = $row;


        }

    } else {

        $data = array("status" => "error", "error" => $db->error);


    }

    $data = checkEmpty($data);

    return json_encode(property_exists($resource, 'admin') ? array('aaData' => $data) : $data);

}

function checkEmpty($data)
{

    if ($data == null) {

        $data = array();

    }

    return $data;

}

function format_resources($resources, $dependencies = false)
{

    $output = array();

    foreach ($resources as $resource) {

        if ($resource->url != '/admin/resources' && preg_match("/^\/admin/", $resource->url) && property_exists($resource, 'admin')) {

            $oresource = array();

            $oresource['url'] = $resource->url;

            $oresource['type'] = $resource->type;

            $oresource['title'] = $resource->title;

            $oresource['authenticate'] = $resource->authenticate;

            $oresource['key'] = $resource->key;

            $oresource['dependency'] = $dependencies;

            if (is_array($resource->row_actions)) {

                $oresource['row_actions'] = $resource->row_actions;

            }

            if (is_array($resource->dependencies) && property_exists($resource, 'url')) {

                $oresource['dependencies'] = format_resources($resource->dependencies, true);

            }

            foreach ($resource->columns as $column) {

                if ($column['list'] == true) {

                    $oresource['columns'][] = array('data' => $column['column']);

                }

                $newcolumn = array('column' => $column['column'], 'title' => $column['title'], 'type' => $column['type']);

                if ($column['type'] == 'select') {

                    if (in_array('sql', $column)) {

                        $newcolumn['resource'] = column_resource($resource, $column);

                    }

                }

                $oresource['insert']['columns'][] = $newcolumn;

            }

            $output[] = $oresource;

        }

    }

    return $output;

}

$resources_all = array((object) array("url" => "/admin/resources", "methods" => array("GET"), "admin" => true, "GET" => function ($data, $arrgs, $db) {

    $output = format_resources($data);

    return $output;

},
));

function column_resource($resource, $column)
{

    return $resource->url . "/columns/" . $column['column'];

}

function collect_resources($resources, $dependency = false)
{

    $resources_all = array();

    foreach ($resources as $resource_block) {

        if (is_array($resource_block)) {

            foreach ($resource_block as $resource) {

                $resource->dependency = $dependency;

                if (is_array($resource->columns)) {

                    foreach ($resource->columns as $column) {

                        if ($column['type'] == 'select') {

                            if (in_array('sql', $column) || in_array('table', array_keys($column))) {

                                $newresource = (object) array("url" => column_resource($resource, $column), "type" => MULTI, "dependency" => 'true', "sql" => in_array('table', array_keys($column)) ? "SELECT * FROM `" . $column['table'] . "`" : $column['sql'], "methods" => array("GET"), "columns" => array(array("column" => "id", "type" => "text", "title" => 'Key'), array("column" => "title", "type" => "text", "title" => 'Title')));

                                $resources_all[] = $newresource;

                            }

                        }

                    }

                }

                if (is_array($resource->dependencies)) {

                    $resources_all = array_merge($resources_all, collect_resources($resource->dependencies, true));

                }

            }

            $resources_all = array_merge($resources_all, $resource_block);

        } else {

            $resource_block->dependency = $dependency;

            $resources_all[] = $resource_block;

        }

    }

    return $resources_all;

}

$resources_all = array_merge($resources_all, collect_resources($resources));

foreach ($resources_all as $resource) {

    if (in_array("GET", $resource->methods)) {

        if ($resource->sql == SQL_DEFAULT) {

            if (property_exists($resource, "parent_table")) {

                $app->get($resource->url, function ($parent_id) use ($resource, $db, $app) {
                    $arrgs = func_get_args();

                    if (authenticate($resource, $arrgs, $db, $_POST, $app, "GET")) {

                        $data = array();

                        $parent_key = property_exists($resource, "parent_key") ? $resource->parent_key : ($resource->parent_table . '_id');

                        $get_sql = "SELECT * FROM `{$resource->table}` WHERE `{$parent_key}`='$parent_id'";

                        echo getSqlData($db, $get_sql, $resource);

                        exit;

                    } else {
                        fail_auth();
                    }

                }
                );

            } else {

                $app->get($resource->url, function () use ($resource, $db) {

                    $data = array();

                    $get_sql = "SELECT * FROM `{$resource->table}`;";

                    echo getSqlData($db, $get_sql, $resource);

                    exit;

                }
                );

            }

        } else {

            $app->get($resource->url, function () use ($resource, $db, $app) {

                $data = array();

                $arrgs = func_get_args();

                if (authenticate($resource, $arrgs, $db, $_POST, $app, "GET")) {

                    if (isset($resource->sql)) {

                        eval("\$get_sql = \"" . addslashes($resource->sql) . "\";");

                        if ($result = $db->query($get_sql)) {

                            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                                $data[] = $row;

                            }

                        } else {

                            $data = array("status" => "error", "error" => $db->error);

                        }

                    }

                    if (isset($resource->GET)) {

                        $func = $resource->GET;

                        $data = $func($resource->url == '/admin/resources' ? $GLOBALS['resources_all'] : $data, $arrgs, $db);

                    }

                    echo json_encode(checkEmpty($data));

                    exit;
                } else {

                    fail_auth();

                }

            }
            );

        }

    }

    if (in_array("POST", $resource->methods)) {

        $app->post($resource->url, function () use ($resource, $db, $app) {

            $error = null;

            $data = array();

            $arrgs = func_get_args();

            $post_sql = null;

            if (authenticate($resource, $arrgs, $db, $_POST, $app, "POST")) {

                if (validator($resource, $arrgs, $db, $_POST)) {

                    if (property_exists($resource, 'post_sql')) {

                        $post = $_POST;

                        $post_sql = preg_replace("/post\[\\\'(.*?)\\\'\]/", "post['$1']", addslashes($resource->post_sql));

                        eval("\$post_sql = \"" . $post_sql . "\";");

                    } else
                    if (property_exists($resource, 'columns')) {

                        // add for admin onl

                        list($keys, $values) = parse_post($resource->columns, $_POST);

                        if (property_exists($resource, 'parent_key') && is_array($arrgs)) {

                            $keys[] = $resource->parent_key;

                            $values[] = $arrgs[0];

                        }

                        $post_sql = sprintf("INSERT INTO {$resource->table} (%s) VALUES (\"%s\")", implode(',', array_values($keys)), implode('","', array_values($values)));

                    }

                    if ($post_sql != null && !$db->query($post_sql)) {

                        $error = $db->error . "SQL: " . $post_sql;

                    }

                    if ($error == null && property_exists($resource, 'POST')) {

                        $func = $resource->POST;

                        $data = $func($arrgs, $db, $_POST);

                    }

                } else {

                    $error = 'validation failed';

                }

                $data = ($error == null) ? array_merge($data, array('status' => 'ok')) : array("status" => "error", "error" => $error);

                echo json_encode($data);

                exit;

            } else {

                fail_auth();

            }

        }
        );

    }

    if (in_array("PUT", $resource->methods)) {

        $app->put($resource->url . "/:id", function ($id) use ($resource, $db, $app) {

            $arrgs = func_get_args();

            $data = array();

            if (authenticate($resource, $arrgs, $db, $_POST, $app, "POST")) {

                foreach ($resource->columns as $key) {

                    $data[$key['column']] = $app->request->put($key['column']);

                }

                list($keys, $values) = parse_post($resource->columns, $data);

                $set = array();

                foreach ($keys as $i => $key) {

                    $set[] = "`$key` = \"{$values[$i]}\"";

                }

                $put_sql = sprintf("UPDATE {$resource->table} SET %s WHERE `{$resource->key}` = {$arrgs[sizeof($arrgs) - 1]}", implode(',', $set));

                if (!$db->query($put_sql)) {

                    $error = $db->error . "SQL: " . $del_sql;

                }

                $data = ($error == null) ? array('status' => 'ok') : array("status" => "error", "error" => $error);

                echo json_encode($data);
            } else {

                fail_auth();

            }

        }
        );

    }

    if (in_array("DELETE", $resource->methods)) {

        $app->delete($resource->url . "/:id", function ($id) use ($resource, $db, $app) {

            $arrgs = func_get_args();

            if (authenticate($resource, $arrgs, $db, $_POST, $app, "DELETE")) {
                $id = $arrgs[sizeof($arrgs) - 1];

                $del_sql = "DELETE FROM `{$resource->table}` WHERE `{$resource->key}` = {$id}";

                if (!$db->query($del_sql)) {

                    $error = $db->error . "SQL: " . $del_sql;

                }

                if (property_exists($resource, 'dependencies')) {

                    foreach ($resource->dependencies as $dependency) {

                        $del_sql = "DELETE FROM `{$dependency->table}` WHERE `{$dependency->parent_key}` = {$id}";

                        if (!$db->query($del_sql)) {

                            $error = $db->error . "SQL: " . $del_sql;

                        }

                    }

                }

                $data = ($error == null) ? array('status' => 'ok') : array("status" => "error", "error" => $error);

                echo json_encode($data);

            } else {

                fail_auth();

            }

        }
        );

    }

}

$app->run();

function parse_post($fields, $post)
{

    $keys = array();

    $values = array();

    foreach ($fields as $key) {

        if ($key['type'] == 'password') {

            $post[$key["column"]] = md5($post[$key["column"]]);

        }

        $keys[] = addslashes($key["column"]);

        $value = $key["type"] == "bool" ? ($post[$key["column"]] == "on" ? 1 : 0) : $post[$key["column"]];

        $values[] = addslashes($value);

    }

    return array($keys, $values);

}

function validator($resource, $arrgs, $db, $post)
{

    if (!property_exists($resource, 'validator')) {

        return true;

    }

    $func = $resource->validator;

    return $func($arrgs, $db, $post);

}

function authenticate($resource, $arrgs, $db, $post, $app, $method)
{

    if (!property_exists($resource, 'authenticate')) {

        return true;

    }

    $login = $app->request()->cookies->login;

    $password = $app->request()->cookies->password;

    eval("\$get_sql = \"" . addslashes($resource->authenticate) . "\";");

 
    if ($result = $db->query($get_sql)) {

        if ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            return $row['valid'];

        }

    }

    return false;

}

function connect_db()
{

    $server = '127.0.0.1';

    // this may be an ip address instead

    $user = 'root';

    $pass = '';

    $database = 'decisions';

    $connection = new mysqli($server, $user, $pass, $database);

    return $connection;

}

function fail_auth()
{

    header('HTTP/1.1 401 Unauthorized', true, 401);
}

//______________
