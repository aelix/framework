<?php

use Phinx\Migration\AbstractMigration;

class AddSessionTable extends AbstractMigration
{
    public function up()
    {
        $sessionTable = $this->table('session', ['id' => false, 'primary_key' => 'id']);
        $sessionTable->addColumn('id', 'string')
            ->addColumn('sessionData', 'binary')
            ->addColumn('lastActivity', 'integer')
            ->create();

        $configTable = $this->table('config');
        $configTable->insert(['name', 'value'], [
            ['core.session.max_lifetime', serialize(31536000)],
            ['core.session.gc_probability', serialize(25)],
            ['core.session.cookie_lifetime', serialize(63072000)],
            ['core.session.cookie_name', serialize('aelix')]
        ]);
        $configTable->save();
    }

    public function down()
    {
        $sessionTable = $this->table('session');
        $sessionTable->drop();

        $this->execute('DELETE FROM `config` WHERE
            `name` = "core.session.max_lifetime" OR
            `name` = "core.session.gc_probability" OR
            `name` = "core.session.cookie_lifetime" OR
            `name` = "core.session.cookie_name"');
    }
}
