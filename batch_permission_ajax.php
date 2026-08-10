<?php

require_once 'connect.php';

$type = $_GET['type'] ?? 'permission';

if($_SERVER['REQUEST_METHOD']=="POST"){

    $action=$_POST['action'] ?? '';

    //==========================
    // Người bốc
    //==========================
    if($action=="toggle_delivery"){

        $emp_id=(int)$_POST['employee_id'];
        $trip_id=(int)$_POST['trip_id'];
        $checked=(int)$_POST['checked'];

        if($checked){

            $stmt=$pdo->prepare("
                INSERT IGNORE INTO
                delivery_workers(
                    delivery_order_id,
                    employee_id
                )
                VALUES(?,?)
            ");

            $stmt->execute([
                $trip_id,
                $emp_id
            ]);

        }else{

            $stmt=$pdo->prepare("
                DELETE
                FROM delivery_workers
                WHERE delivery_order_id=?
                AND employee_id=?
            ");

            $stmt->execute([
                $trip_id,
                $emp_id
            ]);

        }

        exit("ok");

    }

    //==========================
    // Phân quyền khai thác
    //==========================
    if($action=="toggle"){

        $emp_id=(int)$_POST['employee_id'];
        $batch_id=(int)$_POST['batch_id'];
        $checked=(int)$_POST['checked'];

        if($checked){

            $stmt=$pdo->prepare("
                INSERT IGNORE INTO
                employee_batches(
                    employee_id,
                    batch_id
                )
                VALUES(?,?)
            ");

            $stmt->execute([
                $emp_id,
                $batch_id
            ]);

        }else{

            $stmt=$pdo->prepare("
                DELETE
                FROM employee_batches
                WHERE employee_id=?
                AND batch_id=?
            ");

            $stmt->execute([
                $emp_id,
                $batch_id
            ]);

        }

        exit("ok");

    }

}

$batches = $pdo->query("
    SELECT *
    FROM batches
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$employees = $pdo->query("
    SELECT *
    FROM employees
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);
if($type=="permission"){

foreach($batches as $batch){

    echo "

    <div class='batch-perm-card'>

        <div class='batch-perm-header'>
            📍 {$batch['name']}
        </div>

        <div class='batch-perm-users'>

    ";

    foreach($employees as $emp){

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM employee_batches
            WHERE employee_id=?
            AND batch_id=?
        ");

        $stmt->execute([
            $emp['id'],
            $batch['id']
        ]);

        $checked =
            $stmt->fetchColumn()
            ? 'checked'
            : '';

        echo "

        <div class='perm-user-row'>

            <div class='perm-user-info'>

                <div class='perm-avatar'>
                    ".mb_substr($emp['name'],0,1)."
                </div>

                <div class='perm-name'>
                    {$emp['name']}
                </div>

            </div>

            <label class='switch'>

                <input
                    type='checkbox'
                    class='permCheck'
                    data-emp='{$emp['id']}'
                    data-batch='{$batch['id']}'
                    {$checked}
                >

                <span class='slider'></span>

            </label>

        </div>

        ";
    }

    echo "

        </div>

    </div>

    ";
}
//Người bóc hàng
}

if($type=="delivery"){

    $trip_id=(int)($_GET['trip'] ?? 0);

    echo "

    <div class='batch-perm-card'>

        <div class='batch-perm-header'>

            👷 Người tham gia bốc

        </div>

        <div class='batch-perm-users'>

    ";

    foreach($employees as $emp){

        $stmt=$pdo->prepare("

            SELECT COUNT(*)

            FROM delivery_workers

            WHERE delivery_order_id=?

            AND employee_id=?

        ");

        $stmt->execute([

            $trip_id,

            $emp['id']

        ]);

        $checked=$stmt->fetchColumn()

            ? "checked"

            : "";

        echo "

        <div class='perm-user-row'>

            <div class='perm-user-info'>

                <div class='perm-avatar'>

                    ".mb_substr($emp['name'],0,1)."

                </div>

                <div class='perm-name'>

                    {$emp['name']}

                </div>

            </div>

            <label class='switch'>

                <input

                    type='checkbox'

                    class='deliveryWorkerCheck'

                    data-emp='{$emp['id']}'

                    data-trip='{$trip_id}'

                    {$checked}

                >

                <span class='slider'></span>

            </label>

        </div>

        ";

    }

    echo "

        </div>

    </div>

    ";

}
