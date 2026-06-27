<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLastLoginIpToUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     * Phinx will automatically reverse commands like addColumn, createTable, etc.
     * when you run a rollback.
     */
    public function change(): void
    {
        // Get the 'users' table object
        $table = $this->table('users');

        // Add the new column
        $table->addColumn('last_login_ip', 'string', [
            'limit' => 45,      // For IPv4 and IPv6 addresses
            'null' => true,     // The column can be empty
            'default' => null,  // Default value is NULL
            'after' => 'last_active', // Place this column after the 'last_active' column
        ])->update(); // Save the changes to the table
    }
}