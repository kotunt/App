<?php

namespace Tests\Integration;

use Tests\DatabaseTestCase;

class UserTest extends DatabaseTestCase
{
    /** @test */
    public function it_can_create_a_user_and_retrieve_it_from_database()
    {
        $pdo = $this->getPdo();

        // 1. Prepare Data
        $username = 'John Doe';
        $phone = '09123456789';
        $password = password_hash('password123', PASSWORD_DEFAULT);

        // 2. Execute Action (Create User)
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, phone_number, password, role) VALUES (?, ?, ?, 'user')"
        );
        $stmt->execute([$username, $phone, $password]);
        $lastInsertId = $pdo->lastInsertId();

        // 3. Assert (Find the user and check data)
        $findStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $findStmt->execute([$lastInsertId]);
        $user = $findStmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertIsArray($user, 'User should be found in the database.');
        $this->assertEquals($username, $user['username']);
        $this->assertEquals($phone, $user['phone_number']);
        $this->assertEquals('user', $user['role']);
        $this->assertTrue(password_verify('password123', $user['password']));
    }
}