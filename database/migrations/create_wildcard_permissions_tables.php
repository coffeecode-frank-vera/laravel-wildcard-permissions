<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations
     */
    public function up(): void {
        $tableNames = config('permission_wildcard.table_names');
        $pivotNames = config('permission_wildcard.pivot_names');
        $pivotRole = $pivotNames['role_pivot_key'];
        $pivotPermission = $pivotNames['permission_pivot_key'];

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission_wildcard.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['wildcard_permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('short_name');
            $table->string('guard_name');
            $table->text('description');
            $table->timestamps();

            $table->unique(['short_name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($pivotNames) {
            $table->bigIncrements('id');
            $table->string('short_name');
            $table->string('guard_name');
            $table->text('description');
            $table->timestamps();
            $table->unique(['short_name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotNames, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($pivotNames['model_id']);
            $table->index([$pivotNames['model_id'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            $table->primary([$pivotPermission, $pivotPermission['model_id'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');

        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $pivotNames, $pivotRole) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($pivotNames['model_id']);
            $table->index([$pivotNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            $table->primary([$pivotRole, $pivotNames['model_id'], 'model_type'],
                'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};