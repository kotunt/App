<?php


use Phinx\Seed\AbstractSeed;

class BetSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        // 1. Check for dependencies (users and sessions)
        $users = $this->fetchAll('SELECT id FROM users WHERE role = "user"');
        $sessions = $this->fetchAll('SELECT id, game_type, section, target_date, open_time, close_time FROM betting_sessions WHERE status = "active"');

        if (empty($users) || empty($sessions)) {
            echo "Cannot run BetSeeder: Please make sure UserSeeder and default betting sessions exist.\n";
            return;
        }

        $faker = Faker\Factory::create();
        $betsTable = $this->table('bets');
        
        // Clear existing bet data to avoid duplicates
        $betsTable->truncate();

        $data = [];
        $betCount = 500; // Generate 500 sample bets

        for ($i = 0; $i < $betCount; $i++) {
            // 2. Pick a random user and session
            $randomUser = $users[array_rand($users)];
            $randomSession = $sessions[array_rand($sessions)];

            // 3. Generate bet details based on the session type
            $betNumber = '';
            if ($randomSession['game_type'] === '2d') {
                $betNumber = str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT);
                $odds = 80;
            } else { // 3d
                $betNumber = str_pad((string)rand(0, 999), 3, '0', STR_PAD_LEFT);
                $odds = 500;
            }

            $amount = rand(1, 50) * 100; // Bet amount in hundreds
            $status = $faker->randomElement(['pending', 'win', 'lose']);

            // Generate a realistic creation timestamp within the session's timeframe
            $openTime = strtotime($randomSession['open_time']);
            $closeTime = strtotime($randomSession['close_time']);
            $createdAtTimestamp = rand($openTime, min($closeTime, time())); // Don't create bets in the future
            $createdAt = date('Y-m-d H:i:s', $createdAtTimestamp);

            $data[] = [
                'user_id'       => $randomUser['id'],
                'bet_number'    => $betNumber,
                'amount'        => $amount,
                'odds'          => $odds,
                'bet_section'   => $randomSession['section'],
                'target_date'   => $randomSession['target_date'],
                'status'        => $status,
                'created_at'    => $createdAt,
            ];
        }

        // 4. Bulk insert the data for better performance
        if (!empty($data)) {
            $betsTable->insert($data)->saveData();
        }
    }
}