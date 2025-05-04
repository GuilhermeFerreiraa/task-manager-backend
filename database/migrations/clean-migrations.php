<?php

// Este script irá consolidar as migrações relacionadas a tarefas

$migrationsToRemove = [
    '2025_04_28_213157_add_new_fields_to_tasks_table.php',
    '2025_04_28_213158_add_soft_deletes_to_tasks_table.php',
    '2025_04_30_165908_add_completed_to_tasks_table.php',
    '2025_05_01_171118_update_task_status_and_priority_to_uppercase.php',
    '2025_05_04_213048_ensure_task_status_and_priority_enum_values.php',
    '2025_05_04_213104_update_task_columns_to_use_enums.php'
];

$migrationsPath = __DIR__ . '/';

// Verifica se estamos no ambiente correto
if (file_exists($migrationsPath . '2025_04_28_213156_create_tasks_table.php')) {
    echo "Iniciando limpeza de migrações...\n";
    
    foreach ($migrationsToRemove as $migration) {
        $fullPath = $migrationsPath . $migration;
        if (file_exists($fullPath)) {
            echo "Removendo migração: $migration\n";
            unlink($fullPath);
        } else {
            echo "Migração não encontrada: $migration\n";
        }
    }
    
    echo "Limpeza concluída!\n";
    echo "Agora você pode executar:\n";
    echo "php artisan migrate:fresh\n";
    echo "para recriar o banco de dados com as migrações simplificadas.\n";
} else {
    echo "AVISO: Esse script deve ser executado a partir do diretório raiz do projeto.\n";
    exit(1);
} 