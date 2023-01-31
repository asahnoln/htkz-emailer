<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%post_original}}`.
 */
class m230131_201413_add_mail_end_date_column_to_post_original_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%post_original}}', 'mail_end_date', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%post_original}}', 'mail_end_date');
    }
}
