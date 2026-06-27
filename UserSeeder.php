<?php


use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Install faker: composer require --dev fakerphp/faker
        $faker = Faker\Factory::create('my_MM'); // Use Myanmar locale for names

        $users = $this->table('users');
        // Clear existing data to avoid duplicates on re-run
        // Note: In production, you might want to be more careful than truncating.
        // $users->truncate(); // Comment out to prevent deleting users on every seed run

        // 1. Create a specific admin user
        $users->insert([
            'id'             => 1,
            'username'       => 'Admin',
            'phone_number'   => '09000000001',
            'password'       => password_hash('admin123', PASSWORD_DEFAULT),
            'role'           => 'admin',
            'referral_code'  => 'ADMIN01'
        ])->saveData();

        // 2. Create 10 sample users
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'username'      => $faker->name,
                'phone_number'  => $faker->unique()->numerify('09#########'),
                'password'      => password_hash('123456', PASSWORD_DEFAULT),
                'balance'       => $faker->numberBetween(5000, 50000),
                'referral_code' => strtoupper($faker->unique()->bothify('??####'))
            ];
        }
        $users->insert($data)->saveData();
    }
}