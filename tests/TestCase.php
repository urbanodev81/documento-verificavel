<?php

namespace UrbanoDev\DocumentoVerificavel\Tests;

use Illuminate\Auth\GenericUser;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;
use UrbanoDev\DocumentoVerificavel\Acoes\EmitirDocumento;
use UrbanoDev\DocumentoVerificavel\DocumentoVerificavelServiceProvider;
use UrbanoDev\DocumentoVerificavel\Models\DocumentoVerificavel;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [DocumentoVerificavelServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('database.default', 'testing');

        // `phpunit -c tests/phpunit.pgsql.xml` roda a mesma suíte num Postgres.
        if (getenv('DOCUMENTO_TESTE_PGSQL')) {
            $app['config']->set('database.default', 'pgsql');
            $app['config']->set('database.connections.pgsql', [
                'driver' => 'pgsql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => getenv('DB_PORT') ?: '5432',
                'database' => getenv('DB_DATABASE') ?: 'documento',
                'username' => getenv('DB_USERNAME') ?: 'postgres',
                'password' => getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8',
                'prefix' => '',
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ]);
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function autor(string $nome = 'Ana Profissional', int $id = 7): GenericUser
    {
        return new GenericUser(['id' => $id, 'name' => $nome]);
    }

    /** @param array<string, mixed> $dados */
    protected function emitir(array $dados = ['paciente' => 'M. F.', 'data' => '2026-10-29'], ?string $inquilino = 'clinica-a', ?Model $dono = null): DocumentoVerificavel
    {
        return ($this->app->make(EmitirDocumento::class))('comparecimento', 'Declaração de comparecimento', $dados, $inquilino, $dono, $this->autor());
    }
}

