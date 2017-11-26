<?php

use yii\db\Migration;

/**
 * Class m171126_141419_base
 */
class m171126_141419_base extends Migration
{

    public function safeUp()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'fullName' => $this->string(200),   //ім'я користувача для відображення
            'username' => $this->string(200),   //ім'я, що використовується для реєстрації в Jira
            'email' => $this->string(200),
            'authKey' => $this->text(),         //ключ поточної сесії
            'jiraAuthKey' => $this->text(),     //ключ сесії в Jira
            'jiraUrl' => $this->string(500),
            'apiKey' => $this->text(),
        ]);
        
        $this->createTable('lang', [
            'id' => $this->primaryKey(),
            'code' => $this->string(10),
            'name' => $this->string(100),
        ]);
        
        $this->createTable('frequency_lang', [
            'id' => $this->primaryKey(),
            'lang_id' => $this->integer(),
            'code' => $this->string(10),
            'l' => $this->integer(),
            'frequency' => $this->float()
        ]);
        
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'jira_url' => $this->string(500),
            'key' => $this->string(100)
        ]);
        
        $this->createTable('frequency_project_lang', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer(),
            'code' => $this->string(10),
            'l' => $this->integer(),
            'frequency' => $this->float()
        ]);
    }


    public function safeDown()
    {
        $this->dropTable('user');
        $this->dropTable('lang');
        $this->dropTable('frequency_lang');
        $this->dropTable('project');
        $this->dropTable('frequency_project_lang');
    }

}
