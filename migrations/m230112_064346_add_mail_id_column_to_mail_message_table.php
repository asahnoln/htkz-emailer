<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%mail_message}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%mail}}`
 */
class m230112_064346_add_mail_id_column_to_mail_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%mail_message}}', 'mail_id', $this->integer()->notNull());

        // creates index for column `mail_id`
        $this->createIndex(
            '{{%idx-mail_message-mail_id}}',
            '{{%mail_message}}',
            'mail_id'
        );

        // add foreign key for table `{{%mail}}`
        $this->addForeignKey(
            '{{%fk-mail_message-mail_id}}',
            '{{%mail_message}}',
            'mail_id',
            '{{%mail}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%mail}}`
        $this->dropForeignKey(
            '{{%fk-mail_message-mail_id}}',
            '{{%mail_message}}'
        );

        // drops index for column `mail_id`
        $this->dropIndex(
            '{{%idx-mail_message-mail_id}}',
            '{{%mail_message}}'
        );

        $this->dropColumn('{{%mail_message}}', 'mail_id');
    }
}
