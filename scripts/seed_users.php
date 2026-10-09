<?php
/**
 * Database User Seeder & Synchronizer
 */
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();

    $users = [
        [
            'code' => '2026-1',
            'name' => 'Admin Director',
            'email' => 'admin@gymflow.com',
            'phone' => '+923001234567',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'Admin123!',
            'plan_id' => null
        ],
        [
            'code' => '2026-2',
            'name' => 'Coach Marcus Vance',
            'email' => 'staff@gymflow.com',
            'phone' => '+923001234568',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Staff123!',
            'plan_id' => null
        ],
        [
            'code' => '2026-3',
            'name' => 'Alex Johnson',
            'email' => 'member@gymflow.com',
            'phone' => '+923001234569',
            'role' => 'member',
            'status' => 'active',
            'password' => 'Member123!',
            'plan_id' => 2
        ],
        [
            'code' => '2026-4',
            'name' => 'Moeez Ahmad',
            'email' => 'moeezahmad.tech@gmail.com',
            'phone' => '+923266037125',
            'role' => 'member',
            'status' => 'active',
            'password' => 'password123',
            'plan_id' => 1
        ],
        [
            'code' => '2026-5',
            'name' => 'Alex Johnson',
            'email' => 'alex@gymflow.com',
            'phone' => '+923001234570',
            'role' => 'member',
            'status' => 'active',
            'password' => 'password123',
            'plan_id' => 2
        ]
    ];

    echo "--- Syncing Users to Database ---\n";

    foreach ($users as $u) {
        $hash = password_hash($u['password'], PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $u['email']]);
        $exists = $stmt->fetch();

        if ($exists) {
            $userId = (int)$exists['id'];
            $update = $db->prepare("
                UPDATE users 
                SET full_name = :name, phone = :phone, password = :pass, role = :role, status = :status, member_code = :code 
                WHERE id = :id
            ");
            $update->execute([
                ':name'   => $u['name'],
                ':phone'  => $u['phone'],
                ':pass'   => $hash,
                ':role'   => $u['role'],
                ':status' => $u['status'],
                ':code'   => $u['code'],
                ':id'     => $userId
            ]);
            echo "[UPDATED] {$u['email']} (Role: {$u['role']}, Status: {$u['status']}, ID: {$userId})\n";
        } else {
            $insert = $db->prepare("
                INSERT INTO users (member_code, full_name, email, phone, password, role, status) 
                VALUES (:code, :name, :email, :phone, :pass, :role, :status)
            ");
            $insert->execute([
                ':code'   => $u['code'],
                ':name'   => $u['name'],
                ':email'  => $u['email'],
                ':phone'  => $u['phone'],
                ':pass'   => $hash,
                ':role'   => $u['role'],
                ':status' => $u['status']
            ]);
            $userId = (int)$db->lastInsertId();
            echo "[INSERTED] {$u['email']} (Role: {$u['role']}, Status: {$u['status']}, ID: {$userId})\n";
        }

        if ($u['plan_id']) {
            $mStmt = $db->prepare("SELECT id FROM memberships WHERE user_id = :uid LIMIT 1");
            $mStmt->execute([':uid' => $userId]);
            $membership = $mStmt->fetch();

            if (!$membership) {
                $mInsert = $db->prepare("
                    INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew) 
                    VALUES (:uid, :pid, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active', 1)
                ");
                $mInsert->execute([':uid' => $userId, ':pid' => $u['plan_id']]);
                echo "  -> Linked Active Membership Plan ID {$u['plan_id']}\n";
            } else {
                $mUpdate = $db->prepare("
                    UPDATE memberships 
                    SET plan_id = :pid, status = 'active', end_date = DATE_ADD(CURDATE(), INTERVAL 90 DAY) 
                    WHERE id = :mid
                ");
                $mUpdate->execute([':pid' => $u['plan_id'], ':mid' => $membership['id']]);
                echo "  -> Refreshed Active Membership Plan ID {$u['plan_id']}\n";
            }
        }
    }

    echo "\nAll users successfully synchronized to the MySQL database!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
