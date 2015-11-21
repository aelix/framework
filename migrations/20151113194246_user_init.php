<?php

use Phinx\Migration\AbstractMigration;

class UserInit extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $userTable = $this->table('user');
        $userTable->addColumn('username', 'string')
            ->addColumn('email', 'string')
            ->addColumn('passwordHash', 'string')
            ->addColumn('fullname', 'string')
            ->create();

        $userDataFieldTable = $this->table('user_data_field');
        $userDataFieldTable->addColumn('fieldName', 'string')
            ->create();

        $userDataTable = $this->table('user_data');
        $userDataTable->addColumn('fieldID', 'integer')
            ->addForeignKey('fieldID', $userDataFieldTable, 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addColumn('userID', 'integer')
            ->addForeignKey('userID', $userTable, 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->addColumn('value', 'binary')
            ->create();

    }
}
