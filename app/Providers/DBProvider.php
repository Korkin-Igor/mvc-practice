<?php

namespace Providers;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Src\Provider\AbstractProvider;

class DBProvider extends AbstractProvider
{
    private Capsule $dbManager;

    public function register(): void
    {
        $this->dbManager = new Capsule();
    }

    public function boot(): void
    {
        $this->dbManager->addConnection($this->app->settings->getDbSetting());
        $this->dbManager->setEventDispatcher(new Dispatcher(new Container()));
        $this->dbManager->setAsGlobal();
        $this->dbManager->bootEloquent();
        $this->ensureApiTokenTable();

        $this->app->bind('db', $this->dbManager);
    }

    private function ensureApiTokenTable(): void
    {
        $schema = $this->dbManager->schema();
        if ($schema->hasTable('api_tokens')) {
            return;
        }

        $schema->create('api_tokens', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('token', 64)->unique();
            $table->dateTime('created_at');
            $table->dateTime('last_used_at')->nullable();
            $table->index('user_id');
        });
    }
}
