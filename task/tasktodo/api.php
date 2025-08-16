<?php
//
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($method) {
    //响应，初始化任务栏
    case 'GET':
        try {
            $todosdb = $db->query("SELECT * FROM todos");
            $todos = $todosdb->fetchAll(PDO::FETCH_ASSOC);
           
            echo json_encode($todos);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => '数据库错误: ' . $e->getMessage()]);
        }
        break;
        //插入新任务
    case 'POST':
        if (!empty($input['text'])) {
            try {
                $text = htmlspecialchars($input['text'], ENT_QUOTES, 'UTF-8');

                $todo = $db->prepare('INSERT INTO todos (text, completed) VALUES (:text, 0)');
                $todo->bindParam(':text', $text);
                $todo->execute();
                // 
                $newId = $db->lastInsertId();

                $todo = $db->prepare("SELECT * FROM todos WHERE id = :id");
                $todo->bindParam(':id', $newId);
                $todo->execute();
                //  $todo->fetch
                $newTodo = $todo->fetch(PDO::FETCH_ASSOC);
                echo json_encode($newTodo);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => '数据库错误: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            // 数组语法
            echo json_encode(['error' => '任务内容不能为空']);
        }
        break;
        //更新数据库任务状态
    case 'PUT':
        if ($id) {
            try {
                $todo = $db->prepare("UPDATE todos SET completed = NOT completed WHERE id = :id");
                $todo->bindParam(':id', $id);
                $todo->execute();

                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => '更新失败: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => '缺少任务ID']);
        }
        break;
        //更新数据库状态
    case 'DELETE':
        if ($id) {
            try {
                $todo = $db->prepare('DELETE FROM todos WHERE id = :id');
                $todo->bindParam(':id', $id);
                $todo->execute();

                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            // 数组语法
            echo json_encode(['error' => '缺少任务ID']);
        }
        break;
        
    default:
        http_response_code(405);
        // 括号不匹配
        echo json_encode(['error' => '不支持的请求方法']);
}
?>
