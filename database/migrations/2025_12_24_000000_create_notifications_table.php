<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends  Migration {


    public function up()
    {
         $table = config("notifications.table_name") ?? "notifications";
         Schema::create($table,function (Blueprint $table){
             $table->id();
             $table->string('driver')->nullable();
             $table->nullableMorphs('notifiable', 'notifiable_index');
             $table->foreignId('user_id')->nullable()->index('notifications_user_id_index')
                 ->constrained('users')
                 ->cascadeOnDelete()->cascadeOnUpdate();
             $table->string('title')->nullable();
             $table->text('message')->nullable();
             $table->text('link')->nullable();
             $table->dateTime('seen_at')->nullable();
             $table->json('details')->nullable();
             $table->timestamps();
         });
    }


    public function down(): void
    {
        $table = config("notifications.table_name") ?? "notifications";
        Schema::dropIfExists($table);
    }
};